<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentCollection;
use App\Models\Sale;
use App\Models\SaleLog;
use App\Models\Setting;
use App\Services\SalePaymentWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentCollectionController extends Controller
{
    public function __construct(private readonly SalePaymentWorkflowService $paymentWorkflow)
    {
    }

    /**
     * Get all deferred payments (cheque, virement, credit)
     */
    public function deferredPayments(Request $request): JsonResponse
    {
        $statusFilter = $this->paymentWorkflow->normalizeWorkflowStatus(
            $request->input('status', $request->input('collection_status'))
        );
        $typeFilter = $request->filled('payment_type')
            ? $this->paymentWorkflow->normalizePaymentType($request->payment_type)
            : null;

        $sales = Sale::with(['customer', 'payments.collections', 'payments.creator', 'payments.validator'])
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->get()
            ->map(fn (Sale $sale) => $this->paymentWorkflow->decorateSale($sale))
            ->map(fn (Sale $sale) => $this->buildSaleFollowUpItem($sale))
            ->filter()
            ->when($statusFilter, fn ($collection) => $collection
                ->filter(fn (array $item) => $item['workflow_status_code'] === $statusFilter))
            ->when($typeFilter, fn ($collection) => $collection
                ->filter(fn (array $item) => $item['payment_type'] === $typeFilter))
            ->when($request->filled('from_date'), fn ($collection) => $collection
                ->filter(fn (array $item) => ($item['due_date'] ?? '') >= $request->from_date))
            ->when($request->filled('to_date'), fn ($collection) => $collection
                ->filter(fn (array $item) => ($item['due_date'] ?? '') <= $request->to_date))
            ->when($request->boolean('overdue'), fn ($collection) => $collection
                ->filter(fn (array $item) => ($item['workflow_status_code'] === SalePaymentWorkflowService::STATUS_TO_COLLECT || $item['workflow_status_code'] === SalePaymentWorkflowService::STATUS_TO_PAY)
                    && !empty($item['due_date'])
                    && $item['due_date'] < today()->toDateString()))
            ->when($request->boolean('due_today'), fn ($collection) => $collection
                ->filter(fn (array $item) => ($item['due_date'] ?? null) === today()->toDateString()))
            ->when($request->boolean('due_soon'), fn ($collection) => $collection
                ->filter(fn (array $item) => !empty($item['due_date'])
                    && $item['due_date'] >= today()->toDateString()
                    && $item['due_date'] <= today()->addDays(7)->toDateString()))
            ->values();

        return response()->json($sales);
    }

    /**
     * Mark a payment as collected
     */
    public function markAsCollected(Request $request, Payment $payment): JsonResponse
    {
        if (!$payment->is_deferred) {
            return response()->json(['message' => 'Ce paiement ne nécessite pas de suivi d’encaissement.'], 422);
        }

        if ($payment->collection_status === 'collected') {
            return response()->json(['message' => 'Payment already collected'], 422);
        }

        $validated = $request->validate([
            'collected_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $payment->collection_status;

        $payment->markAsCollected(
            $validated['collected_by'] ?? null,
            $validated['notes'] ?? null
        );

        $this->syncCashBalanceForStatusChange($payment, $oldStatus, 'collected');
        $summary = $this->syncSalePaymentStatus($payment->sale);
        $this->logSalePaymentUpdate($payment, 'Encaissement validé', $summary['payment_status_label']);

        $payment->refresh()->load(['collections', 'sale.customer', 'creator', 'validator']);
        $this->paymentWorkflow->decoratePayment($payment);
        $this->paymentWorkflow->decorateSale($payment->sale, false);

        return response()->json([
            'message' => 'Payment marked as collected',
            'payment' => $payment,
        ]);
    }

    /**
     * Schedule a payment for collection
     */
    public function scheduleCollection(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'scheduled_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
        ]);

        $payment->scheduleCollection(
            $validated['scheduled_date'],
            $validated['notes'] ?? null
        );

        $payment->refresh()->load(['collections', 'sale.customer', 'creator', 'validator']);
        $this->paymentWorkflow->decoratePayment($payment);
        $this->paymentWorkflow->decorateSale($payment->sale, false);

        return response()->json([
            'message' => 'Payment scheduled for collection',
            'payment' => $payment,
        ]);
    }

    /**
     * Reschedule a payment collection
     */
    public function rescheduleCollection(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'new_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
        ]);

        $payment->rescheduleCollection(
            $validated['new_date'],
            $validated['notes'] ?? null
        );

        $payment->refresh()->load(['collections', 'sale.customer', 'creator', 'validator']);
        $this->paymentWorkflow->decoratePayment($payment);
        $this->paymentWorkflow->decorateSale($payment->sale, false);

        return response()->json([
            'message' => 'Payment rescheduled',
            'payment' => $payment,
        ]);
    }

    /**
     * Get payment collection history
     */
    public function collectionHistory(Payment $payment): JsonResponse
    {
        $collections = PaymentCollection::where('payment_id', $payment->id)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $payment->load(['sale.customer', 'creator', 'validator']);
        $this->paymentWorkflow->decoratePayment($payment);
        if ($payment->sale) {
            $this->paymentWorkflow->decorateSale($payment->sale, false);
        }

        return response()->json([
            'payment' => $payment,
            'collections' => $collections,
        ]);
    }

    public function changeStatus(Request $request, Payment $payment): JsonResponse
    {
        if (!$payment->is_deferred) {
            return response()->json(['message' => 'Ce paiement ne nécessite pas de suivi d’encaissement.'], 422);
        }

        $request->validate([
            'status' => ['required', Rule::in(['pending', 'collected', 'cancelled'])],
            'notes' => 'nullable|string|required_if:status,cancelled',
        ]);

        $oldStatus = $payment->collection_status;
        $status = $request->status;
        $notes = $request->notes;

        if ($status === 'collected') {
            $payment->markAsCollected(null, $notes);
        } else {
            $action = $status === 'cancelled' ? 'failed' : 'rescheduled';

            $payment->update(Payment::persistable([
                'collection_status' => $status,
                'payment_status' => $status === 'cancelled' ? 'failed' : 'pending',
                'collection_notes' => $notes,
                'confirmed_at' => null,
                'collected_at' => null,
                'collected_by' => null,
                'validated_by' => $status === 'cancelled' ? auth()->id() : null,
            ]));

            PaymentCollection::create([
                'payment_id' => $payment->id,
                'user_id' => auth()->id(),
                'action' => $action,
                'amount' => $payment->amount,
                'notes' => $notes ?: ($status === 'pending' ? 'Remis en attente' : null),
            ]);
        }

        $this->syncCashBalanceForStatusChange($payment, $oldStatus, $status);
        $summary = $this->syncSalePaymentStatus($payment->sale);
        $this->logSalePaymentUpdate($payment, 'Statut de paiement mis à jour', $summary['payment_status_label'], $notes);
        $payment->refresh()->load(['collections', 'sale.customer', 'creator', 'validator']);
        $this->paymentWorkflow->decoratePayment($payment);
        $this->paymentWorkflow->decorateSale($payment->sale, false);

        return response()->json([
            'payment' => $payment,
        ]);
    }

    /**
     * Get payment statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $total_deferred = Payment::deferred()->count();
        $pending_collection = Payment::deferred()->where('collection_status', 'pending')->count();
        $collected = Payment::deferred()->where('collection_status', 'collected')->count();
        $overdue = Payment::deferred()
            ->where('collection_status', 'pending')
            ->whereDate('due_date', '<', today())
            ->count();

        $total_amount = Payment::deferred()->sum('amount');
        $collected_amount = Payment::deferred()
            ->where('collection_status', 'collected')
            ->sum('amount');
        $pending_amount = Payment::deferred()
            ->where('collection_status', 'pending')
            ->sum('amount');
        $overdue_amount = Payment::deferred()
            ->where('collection_status', 'pending')
            ->whereDate('due_date', '<', today())
            ->sum('amount');

        return response()->json([
            'counts' => [
                'total_deferred' => $total_deferred,
                'pending_collection' => $pending_collection,
                'collected' => $collected,
                'overdue' => $overdue,
            ],
            'amounts' => [
                'total_amount' => $total_amount,
                'collected_amount' => $collected_amount,
                'pending_amount' => $pending_amount,
                'overdue_amount' => $overdue_amount,
            ],
        ]);
    }

    /**
     * Get upcoming collections (due soon)
     */
    public function upcomingCollections(Request $request): JsonResponse
    {
        $days = $request->get('days', 7);

        $payments = Payment::deferred()
            ->where('collection_status', 'pending')
            ->whereBetween('due_date', [today(), today()->addDays($days)])
            ->with(['sale.customer', 'collections'])
            ->orderBy('due_date', 'asc')
            ->get();

        $payments->transform(function (Payment $payment) {
            if ($payment->relationLoaded('sale') && $payment->sale) {
                $this->paymentWorkflow->decorateSale($payment->sale, false);
            }

            return $this->paymentWorkflow->decoratePayment($payment);
        });

        return response()->json($payments);
    }

    private function syncCashBalanceForStatusChange(Payment $payment, string $oldStatus, string $newStatus): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        $amount = (float) $payment->amount;
        $currentBalance = (float) Setting::get('cash_balance', 0);

        // Money enters caisse only when payment is actually collected.
        if ($newStatus === 'collected' && $oldStatus !== 'collected') {
            Setting::set('cash_balance', $currentBalance + $amount, 'number', 'finance');
            return;
        }

        // If a collected payment is moved back to pending/cancelled, remove it from caisse.
        if ($oldStatus === 'collected' && in_array($newStatus, ['pending', 'cancelled'], true)) {
            Setting::set('cash_balance', $currentBalance - $amount, 'number', 'finance');
        }
    }

    private function syncSalePaymentStatus(?Sale $sale): array
    {
        if (!$sale) {
            return [
                'payment_status_code' => SalePaymentWorkflowService::STATUS_TO_PAY,
                'payment_status_label' => $this->paymentWorkflow->saleStatusLabel(SalePaymentWorkflowService::STATUS_TO_PAY),
            ];
        }

        return $this->paymentWorkflow->syncSalePaymentStatus($sale);
    }

    /**
     * Delete a payment collection
     */
    public function destroy(Payment $payment): JsonResponse
    {
        try {
            $sale = $payment->sale;
            $oldStatus = $payment->collection_status;

            $payment->delete();
            $this->syncCashBalanceForStatusChange($payment, $oldStatus, 'cancelled');

            if ($sale) {
                $summary = $this->syncSalePaymentStatus($sale->fresh());
                SaleLog::create([
                    'sale_id' => $sale->id,
                    'user_id' => auth()->id(),
                    'status' => $summary['payment_status_label'],
                    'action' => 'paiement',
                    'comment' => 'Paiement différé supprimé',
                ]);
            }

            return response()->json([
                'message' => 'Paiement supprimé avec succès',
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    private function logSalePaymentUpdate(Payment $payment, string $prefix, string $statusLabel, ?string $notes = null): void
    {
        if (!$payment->sale_id) {
            return;
        }

        SaleLog::create([
            'sale_id' => $payment->sale_id,
            'user_id' => auth()->id(),
            'status' => $statusLabel,
            'action' => 'paiement',
            'comment' => trim($prefix . ' - ' . $this->paymentWorkflow->paymentMethodLabel($payment) . ($notes ? ' - ' . $notes : '')),
        ]);
    }

    private function buildSaleFollowUpItem(Sale $sale): ?array
    {
        $saleSummary = $sale->payment_summary ?? $this->paymentWorkflow->computeSaleSummary($sale);
        $statusCode = $saleSummary['payment_status_code'] ?? SalePaymentWorkflowService::STATUS_TO_PAY;

        if (in_array($statusCode, [SalePaymentWorkflowService::STATUS_PAID, SalePaymentWorkflowService::STATUS_COLLECTED], true)) {
            return null;
        }

        $deferredPayment = $sale->payments
            ->filter(fn (Payment $payment) => $payment->is_deferred)
            ->sortByDesc(fn (Payment $payment) => $payment->due_date ?? $payment->created_at)
            ->first();

        if ($deferredPayment) {
            $this->paymentWorkflow->decoratePayment($deferredPayment);
        }

        $remainingAmount = (float) ($saleSummary['remaining_amount'] ?? 0);
        $pendingAmount = (float) ($saleSummary['pending_collection_amount'] ?? 0);
        $followUpAmount = round($remainingAmount + $pendingAmount, 2);

        if ($followUpAmount <= 0) {
            return null;
        }

        return [
            'id' => 'sale-follow-up-' . $sale->id,
            'entry_type' => 'sale_follow_up',
            'sale_id' => $sale->id,
            'payment_id' => $deferredPayment?->id,
            'sale' => $sale,
            'sale_origin' => $sale->origin,
            'sale_reference_display' => $sale->origin === 'pos'
                ? ($sale->reference ?? $sale->order_number)
                : ($sale->order_number ?? $sale->reference),
            'payment_type' => $deferredPayment?->payment_type ?? 'balance_due',
            'payment_method_label' => $deferredPayment?->payment_method_label ?? 'Reste à payer',
            'reference_number' => $deferredPayment?->reference_number,
            'amount' => $followUpAmount,
            'due_date' => $deferredPayment?->due_date?->format('Y-m-d')
                ?? ($sale->pickup_date?->format('Y-m-d') ?? optional($sale->created_at)->format('Y-m-d')),
            'workflow_status_code' => $statusCode,
            'workflow_status_label' => $saleSummary['payment_status_label'] ?? $this->paymentWorkflow->saleStatusLabel($statusCode),
            'collection_notes' => $deferredPayment?->collection_notes ?? $deferredPayment?->notes ?? $sale->notes,
            'notes' => $sale->notes,
            'created_at' => optional($deferredPayment?->created_at ?? $sale->created_at)?->toISOString(),
        ];
    }
}
