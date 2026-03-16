<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentCollection;
use App\Models\Sale;
use App\Models\SaleLog;
use App\Services\SalePaymentWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function __construct(private readonly SalePaymentWorkflowService $paymentWorkflow)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['sale.customer', 'collections', 'creator', 'validator']);

        if ($request->has('payment_type')) {
            $query->byType($this->paymentWorkflow->normalizePaymentType($request->payment_type));
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $payments = $query->latest()->paginate($request->get('per_page', 20));
        $payments->getCollection()->transform(function (Payment $payment) {
            if ($payment->relationLoaded('sale') && $payment->sale) {
                $this->paymentWorkflow->decorateSale($payment->sale, false);
            }

            return $this->paymentWorkflow->decoratePayment($payment);
        });

        return response()->json($payments);
    }

    public function store(Request $request, Sale $sale): JsonResponse
    {
        if ($sale->status === 'cancelled') {
            return response()->json(['message' => 'Cannot add payment to cancelled sale'], 422);
        }

        $incomingPaymentType = $request->input('payment_type');
        $normalizedPaymentType = $this->paymentWorkflow->normalizePaymentType($incomingPaymentType);

        $transferMode = $this->paymentWorkflow->resolveTransferMode(
            $normalizedPaymentType,
            $request->input('transfer_mode'),
            $request->input('notes')
        );

        $request->merge([
            'payment_type' => $normalizedPaymentType,
            'transfer_mode' => $transferMode,
        ]);

        $rules = [
            'payment_type' => 'required|in:cash,cheque,virement,credit,card,mobile,other',
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

        // Ensure we always have an identifiable reference for deferred instruments
        if (in_array($type, ['cheque', 'virement'], true) && !$request->filled('transaction_number')) {
            $request->merge([
                'transaction_number' => strtoupper(substr($type, 0, 3)) . '-' . now()->format('YmdHis'),
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

        $sale->loadMissing(['payments', 'customer']);
        $saleSummary = $this->paymentWorkflow->computeSaleSummary($sale);
        $remainingAmount = (float) $saleSummary['remaining_amount'];

        if ($remainingAmount <= 0) {
            $message = (float) ($saleSummary['pending_collection_amount'] ?? 0) > 0
                ? 'Cette commande est déjà entièrement couverte par des paiements enregistrés, dont une partie est en attente d’encaissement.'
                : 'Cette commande n’a plus de reste à payer.';

            return response()->json(['message' => $message], 422);
        }

        $paymentAmount = round((float) $validated['amount'], 2);
        if ($paymentAmount > $remainingAmount + 0.00001) {
            return response()->json([
                'message' => 'Le montant dépasse le reste à couvrir pour cette commande.',
            ], 422);
        }

        $changeAmount = 0;
        if ($validated['payment_type'] === 'cash' && isset($validated['received_amount'])) {
            $changeAmount = max(0, $validated['received_amount'] - $paymentAmount);
        }

        $requiresConfirmation = $this->paymentWorkflow->requiresConfirmation(
            $validated['payment_type'],
            $validated['transfer_mode'] ?? null
        );
        $collectionStatus = $requiresConfirmation ? 'pending' : 'collected';

        $notes = $validated['notes'] ?? null;
        if ($validated['payment_type'] === 'virement' && isset($validated['transfer_mode'])) {
            $modeTag = $validated['transfer_mode'] === 'instant' ? '[VIREMENT_INSTANT]' : '[VIREMENT_SIMPLE]';
            $notes = trim($modeTag . ' ' . ($notes ?? ''));
        }

        $payment = DB::transaction(function () use (
            $sale,
            $validated,
            $paymentAmount,
            $changeAmount,
            $requiresConfirmation,
            $collectionStatus,
            $notes
        ) {
            $payment = Payment::create(Payment::persistable([
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'payment_type' => $validated['payment_type'],
                'transfer_mode' => $validated['transfer_mode'] ?? null,
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
                'payment_status' => $requiresConfirmation ? 'pending' : 'completed',
                'paid_at' => now(),
                'confirmed_at' => $requiresConfirmation ? null : now(),
                'created_by' => auth()->id(),
                'validated_by' => $requiresConfirmation ? null : auth()->id(),
                'notes' => $notes,
                'is_deferred' => $requiresConfirmation,
                'collection_status' => $collectionStatus,
                'collected_at' => $requiresConfirmation ? null : now(),
                'collected_by' => $requiresConfirmation ? null : (auth()->user()?->name ?? null),
            ]));

            if ($requiresConfirmation) {
                PaymentCollection::create([
                    'payment_id' => $payment->id,
                    'user_id' => auth()->id(),
                    'action' => 'created',
                    'amount' => $paymentAmount,
                    'notes' => 'Paiement différé enregistré - en attente de confirmation',
                ]);
            }

            $summary = $this->paymentWorkflow->syncSalePaymentStatus($sale);

            SaleLog::create([
                'sale_id' => $sale->id,
                'user_id' => auth()->id(),
                'status' => $summary['payment_status_label'],
                'action' => 'paiement',
                'comment' => sprintf(
                    'Paiement de %.2f enregistré via %s (%s)',
                    $paymentAmount,
                    $this->paymentWorkflow->paymentMethodLabel($payment),
                    $summary['payment_status_label']
                ),
            ]);

            return $payment;
        });

        $payment = $payment->fresh(['collections', 'sale.customer']);
        $sale = $sale->fresh(['customer', 'user', 'items.article', 'payments', 'logs.user', 'returns.article']);
        $this->paymentWorkflow->decoratePayment($payment);
        $this->paymentWorkflow->decorateSale($sale);

        return response()->json([
            'payment' => $payment,
            'sale' => $sale,
            'remaining' => $sale->payment_summary['remaining_amount'],
            'change' => $changeAmount,
        ], 201);
    }
}
