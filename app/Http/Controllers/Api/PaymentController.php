<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $validated = $request->validate([
            'payment_type' => 'required|in:cash,card,check,mobile,other',
            'amount' => 'required|numeric|min:0.01',
            'received_amount' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $remainingAmount = $sale->remaining_amount;
        $paymentAmount = min($validated['amount'], $remainingAmount);

        // Calculate change for cash payments
        $changeAmount = 0;
        if ($validated['payment_type'] === 'cash' && isset($validated['received_amount'])) {
            $changeAmount = max(0, $validated['received_amount'] - $paymentAmount);
        }

        $payment = Payment::create([
            'sale_id' => $sale->id,
            'payment_type' => $validated['payment_type'],
            'amount' => $paymentAmount,
            'received_amount' => $validated['received_amount'] ?? $paymentAmount,
            'change_amount' => $changeAmount,
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update sale payment status
        $totalPaid = $sale->payments()->sum('amount');
        if ($totalPaid >= $sale->total) {
            $sale->update(['payment_status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $sale->update(['payment_status' => 'partial']);
        }

        return response()->json([
            'payment' => $payment,
            'sale' => $sale->fresh()->load(['payments']),
            'remaining' => max(0, $sale->total - $totalPaid),
            'change' => $changeAmount,
        ], 201);
    }
}
