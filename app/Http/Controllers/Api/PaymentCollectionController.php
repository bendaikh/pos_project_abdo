<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentCollection;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentCollectionController extends Controller
{
    /**
     * Get all deferred payments (cheque, virement, credit)
     */
    public function deferredPayments(Request $request): JsonResponse
    {
        $query = Payment::deferred()
            ->with(['sale.customer', 'collections'])
            ->orderBy('due_date', 'asc');

        // Filter by collection status
        if ($request->has('collection_status')) {
            $query->where('collection_status', $request->collection_status);
        }

        // Filter by payment type
        if ($request->has('payment_type')) {
            if ($request->payment_type === 'cheque') {
                $query->whereIn('payment_type', ['cheque', 'check']);
            } else {
                $query->where('payment_type', $request->payment_type);
            }
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('due_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('due_date', '<=', $request->to_date);
        }

        // Filter overdue
        if ($request->boolean('overdue')) {
            $query->where('collection_status', 'pending')
                ->whereDate('due_date', '<', today());
        }

        // Filter due today
        if ($request->boolean('due_today')) {
            $query->where('collection_status', 'pending')
                ->whereDate('due_date', today());
        }

        // Filter due soon (next 7 days)
        if ($request->boolean('due_soon')) {
            $query->where('collection_status', 'pending')
                ->whereBetween('due_date', [today(), today()->addDays(7)]);
        }

        $payments = $request->boolean('paginate', false)
            ? $query->paginate($request->get('per_page', 20))
            : $query->get();

        return response()->json($payments);
    }

    /**
     * Mark a payment as collected
     */
    public function markAsCollected(Request $request, Payment $payment): JsonResponse
    {
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

        $payment->update(['payment_status' => 'completed']);
        $this->syncCashBalanceForStatusChange($payment, $oldStatus, 'collected');

        return response()->json([
            'message' => 'Payment marked as collected',
            'payment' => $payment->load(['collections']),
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

        return response()->json([
            'message' => 'Payment scheduled for collection',
            'payment' => $payment->load(['collections']),
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

        return response()->json([
            'message' => 'Payment rescheduled',
            'payment' => $payment->load(['collections']),
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

        return response()->json([
            'payment' => $payment,
            'collections' => $collections,
        ]);
    }

    public function changeStatus(Request $request, Payment $payment): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['pending', 'collected', 'cancelled'])],
            'notes' => 'nullable|string|required_if:status,cancelled',
        ]);

        $oldStatus = $payment->collection_status;
        $status = $request->status;
        $notes = $request->notes;

        if ($status === 'collected') {
            $payment->markAsCollected(null, $notes);
            $payment->update(['payment_status' => 'completed']);
        } else {
            $action = $status === 'cancelled' ? 'cancelled' : 'rescheduled';

            $payment->update([
                'collection_status' => $status,
                'payment_status' => $status === 'cancelled' ? 'failed' : 'pending',
                'collection_notes' => $notes,
                'collected_at' => null,
                'collected_by' => null,
            ]);

            PaymentCollection::create([
                'payment_id' => $payment->id,
                'user_id' => auth()->id(),
                'action' => $action,
                'amount' => $payment->amount,
                'notes' => $notes ?: ($status === 'pending' ? 'Remis en attente' : null),
            ]);
        }

        $this->syncCashBalanceForStatusChange($payment, $oldStatus, $status);
        $payment->refresh()->load(['collections', 'sale.customer']);

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
}
