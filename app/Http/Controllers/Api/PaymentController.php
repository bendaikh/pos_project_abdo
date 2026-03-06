<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sale;
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

        $normalizedPaymentType = match ($request->input('payment_type')) {
            'check' => 'cheque',
            'simple_transfer', 'instant_transfer', 'transfer' => 'virement',
            default => $request->input('payment_type'),
        };

        $request->merge(['payment_type' => $normalizedPaymentType]);

        $rules = [
            'payment_type' => 'required|in:cash,check,cheque,virement,credit,card,mobile,other',
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

        if ($type === 'cash') {
            $rules['received_amount'] = 'required|numeric|gte:amount';
        }

        if (in_array($type, ['card', 'mobile'], true)) {
            $rules['transaction_number'] = 'required|string|max:255';
        }

        if (in_array($type, ['cheque', 'virement', 'credit'], true)) {
            $rules['issue_date'] = 'required|date';
            $rules['due_date'] = 'required|date|after_or_equal:issue_date';
            $rules['notes'] = 'nullable|string';
        }

        if ($type === 'cheque') {
            $rules['transaction_number'] = 'required|string|max:255';
            $rules['bank_name'] = 'required|string|max:255';
        }

        if ($type === 'virement') {
            $rules['transaction_number'] = 'required|string|max:255';
            $rules['bank_name'] = 'required|string|max:255';
        }

        if ($type === 'credit') {
            $rules['piece_number'] = 'required|string|max:255';
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        $remainingAmount = $sale->remaining_amount;
        $paymentAmount = min($validated['amount'], $remainingAmount);

        // Calculate change for cash payments
        $changeAmount = 0;
        if ($validated['payment_type'] === 'cash' && isset($validated['received_amount'])) {
            $changeAmount = max(0, $validated['received_amount'] - $paymentAmount);
        }

        // Determine if this is a deferred payment
        $isDeferredType = in_array($validated['payment_type'], ['cheque', 'virement', 'credit']);
        $collectionStatus = $isDeferredType ? 'pending' : null;

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
            'notes' => $validated['notes'] ?? null,
            'is_deferred' => $isDeferredType,
            'collection_status' => $collectionStatus,
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

        // Update sale payment status
        $totalPaid = $sale->payments()->sum('amount');
        if ($totalPaid >= $sale->total) {
            $sale->update(['payment_status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $sale->update(['payment_status' => 'partial']);
        }

        return response()->json([
            'payment' => $payment->fresh()->load(['collections']),
            'sale' => $sale->fresh()->load(['payments']),
            'remaining' => max(0, $sale->total - $totalPaid),
            'change' => $changeAmount,
        ], 201);
    }
}
