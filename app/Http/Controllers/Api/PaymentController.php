<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['sale.customer']);

        if ($request->has('payment_type')) {
            $query->byType($request->payment_type);
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $payments = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json($payments);
    }

    public function store(Request $request, Sale $sale): JsonResponse
    {
        if ($sale->status === 'cancelled') {
            return response()->json(['message' => 'Cannot add payment to cancelled sale'], 422);
        }

        if ($sale->payment_status === 'paid') {
            return response()->json(['message' => 'Sale is already fully paid'], 422);
        }

        $incomingPaymentType = $request->input('payment_type');
        $normalizedPaymentType = match ($incomingPaymentType) {
            'check' => 'cheque',
            'simple_transfer', 'instant_transfer', 'transfer' => 'virement',
            default => $incomingPaymentType,
        };

        $transferMode = $request->input('transfer_mode');
        if ($normalizedPaymentType === 'virement' && !$transferMode) {
            if ($incomingPaymentType === 'simple_transfer' || $incomingPaymentType === 'transfer') {
                $transferMode = 'simple';
            } elseif ($incomingPaymentType === 'instant_transfer') {
                $transferMode = 'instant';
            }
        }

        if ($normalizedPaymentType !== 'virement') {
            $transferMode = null;
        }

        $request->merge([
            'payment_type' => $normalizedPaymentType,
            'transfer_mode' => $transferMode,
        ]);

        $rules = [
            'payment_type' => 'required|in:cash,check,cheque,virement,credit,card,mobile,other',
            'transfer_mode' => 'nullable|in:simple,instant',
            'amount' => 'required|numeric|min:0.01',
            'received_amount' => 'nullable|numeric|min:0.01',
            'reference' => 'nullable|string|max:255',
            'transaction_number' => 'nullable|string|max:255',
            'piece_number' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'bank_name' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];

        $type = $request->input('payment_type');

        if (in_array($type, ['cheque', 'virement', 'credit'], true)) {
            $issueDate = $request->input('issue_date') ?: now()->toDateString();
            $dueDate = $request->input('due_date') ?: $issueDate;
            $request->merge([
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
            ]);
        }

        if ($type === 'cash') {
            $rules['received_amount'] = 'required|numeric|gte:amount';
        }

        if (in_array($type, ['card', 'mobile'], true)) {
            $rules['transaction_number'] = 'required|string|max:255';
        }

        $transferMode = $request->input('transfer_mode');
        $isInstantTransfer = $type === 'virement' && $transferMode === 'instant';
        $isSimpleTransfer = $type === 'virement' && !$isInstantTransfer;

        if (in_array($type, ['cheque', 'credit'], true) || $isSimpleTransfer) {
            $rules['issue_date'] = 'required|date';
            $rules['due_date'] = 'required|date|after_or_equal:issue_date';
            $rules['notes'] = 'nullable|string';
        }

        if ($type === 'cheque') {
            $rules['transaction_number'] = 'required|string|max:255';
        }

        if ($type === 'virement') {
            $rules['transaction_number'] = 'required|string|max:255';
        }

        if ($type === 'credit') {
            $rules['piece_number'] = 'required|string|max:255';
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        $remainingAmount = $sale->remaining_amount;
        if ($remainingAmount <= 0) {
            return response()->json(['message' => 'Sale has no remaining amount'], 422);
        }
        $paymentAmount = min($validated['amount'], $remainingAmount);

        // Calculate change for cash payments
        $changeAmount = 0;
        if ($validated['payment_type'] === 'cash' && isset($validated['received_amount'])) {
            $changeAmount = max(0, $validated['received_amount'] - $paymentAmount);
        }

        // Determine if this is a deferred payment
        $isDeferredType = in_array($validated['payment_type'], ['cheque', 'credit'], true)
            || ($validated['payment_type'] === 'virement' && ($validated['transfer_mode'] ?? null) !== 'instant');
        // SQLite schema enforces NOT NULL for collection_status.
        // Deferred payments start as pending; immediate payments are considered collected.
        $collectionStatus = $isDeferredType ? 'pending' : 'collected';

        $notes = $validated['notes'] ?? null;
        if ($validated['payment_type'] === 'virement' && isset($validated['transfer_mode'])) {
            $modeTag = $validated['transfer_mode'] === 'instant' ? '[VIREMENT_INSTANT]' : '[VIREMENT_SIMPLE]';
            $notes = trim($modeTag . ' ' . ($notes ?? ''));
        }

        $payment = Payment::create([
            'sale_id' => $sale->id,
            'payment_type' => $validated['payment_type'],
            'amount' => $paymentAmount,
            'received_amount' => $validated['received_amount'] ?? $paymentAmount,
            'change_amount' => $changeAmount,
            'reference' => $validated['reference']
                ?? $validated['transaction_number']
                ?? $validated['piece_number']
                ?? null,
            'transaction_number' => $validated['transaction_number'] ?? null,
            'piece_number' => $validated['piece_number'] ?? null,
            'issue_date' => $validated['issue_date'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'payment_status' => $isDeferredType ? 'pending' : 'completed',
            'notes' => $notes,
            'is_deferred' => $isDeferredType,
            'collection_status' => $collectionStatus,
            'collected_at' => $isDeferredType ? null : now(),
            'collected_by' => $isDeferredType ? null : (auth()->user()?->name ?? null),
        ]);

        // If deferred, record the initial creation in collections history
        if ($isDeferredType) {
            \App\Models\PaymentCollection::create([
                'payment_id' => $payment->id,
                'user_id' => auth()->id(),
                'action' => 'created',
                'amount' => $paymentAmount,
                'notes' => 'Payment created - awaiting collection',
            ]);
        }

        // Update sale payment status from effectively collected money only.
        $this->syncSalePaymentStatus($sale);
        $totalCommitted = (float) $sale->payments()->sum('amount');

        SaleLog::create([
            'sale_id' => $sale->id,
            'user_id' => auth()->id(),
            'status' => $sale->fresh()->payment_status,
            'action' => 'paiement',
            'comment' => 'Paiement de ' . $paymentAmount . ' enregistré via ' . $validated['payment_type'],
        ]);

        return response()->json([
            'payment' => $payment->fresh()->load(['collections']),
            'sale' => $sale->fresh()->load(['payments']),
            'remaining' => max(0, $sale->total - $totalCommitted),
            'change' => $changeAmount,
        ], 201);
    }

    private function syncSalePaymentStatus(Sale $sale): void
    {
        $collectedAmount = (float) $sale->payments()
            ->where(function ($query) {
                $query
                    ->where(function ($immediate) {
                        $immediate
                            ->where(function ($typeQuery) {
                                $typeQuery->where('is_deferred', false)->orWhereNull('is_deferred');
                            })
                            ->where('payment_status', 'completed');
                    })
                    ->orWhere(function ($deferred) {
                        $deferred
                            ->where('is_deferred', true)
                            ->where('payment_status', 'completed')
                            ->where('collection_status', 'collected');
                    });
            })
            ->sum('amount');

        $status = 'unpaid';
        if ($collectedAmount >= (float) $sale->total) {
            $status = 'paid';
        } elseif ($collectedAmount > 0) {
            $status = 'partial';
        }

        if ($sale->payment_status !== $status) {
            $sale->update(['payment_status' => $status]);
        }
    }
}
