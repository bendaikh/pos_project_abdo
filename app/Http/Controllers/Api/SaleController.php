<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Sale::with(['customer', 'user', 'items']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Search by reference
        if ($request->has('search')) {
            $query->where('reference', 'like', "%{$request->search}%");
        }

        $sales = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json($sales);
    }

    public function pending(): JsonResponse
    {
        $sales = Sale::with(['customer', 'items.article'])
            ->pending()
            ->latest()
            ->get();

        return response()->json($sales);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.article_id' => 'required|exists:articles,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.selected_options' => 'nullable|array',
            'items.*.options_price' => 'nullable|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'delivery_mode' => 'nullable|in:pickup,delivery,dine_in',
            'notes' => 'nullable|string',
        ]);

        $taxRate = Setting::get('tax_rate', 0);
        $taxEnabled = Setting::get('tax_enabled', false);

        return DB::transaction(function () use ($validated, $taxRate, $taxEnabled) {
            // Create sale
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'customer_id' => $validated['customer_id'] ?? null,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'discount_percent' => $validated['discount_percent'] ?? 0,
                'tax_rate' => $taxEnabled ? $taxRate : 0,
                'delivery_mode' => $validated['delivery_mode'] ?? 'pickup',
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            // Create sale items
            foreach ($validated['items'] as $item) {
                $article = Article::find($item['article_id']);
                
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'article_id' => $item['article_id'],
                    'article_name' => $article->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'selected_options' => $item['selected_options'] ?? null,
                    'options_price' => $item['options_price'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total' => 0, // Will be calculated in model
                ]);
            }

            // Calculate totals
            $sale->load('items');
            $sale->calculateTotals();
            $sale->save();

            return response()->json($sale->load(['customer', 'items.article']), 201);
        });
    }

    public function show(Sale $sale): JsonResponse
    {
        return response()->json($sale->load(['customer', 'user', 'items.article', 'payments']));
    }

    public function update(Request $request, Sale $sale): JsonResponse
    {
        if ($sale->status !== 'pending') {
            return response()->json(['message' => 'Cannot update a completed or cancelled sale'], 422);
        }

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'sometimes|required|array|min:1',
            'items.*.article_id' => 'required|exists:articles,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.selected_options' => 'nullable|array',
            'items.*.options_price' => 'nullable|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'delivery_mode' => 'nullable|in:pickup,delivery,dine_in',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($sale, $validated) {
            // Update sale fields
            $sale->update([
                'customer_id' => $validated['customer_id'] ?? $sale->customer_id,
                'discount_amount' => $validated['discount_amount'] ?? $sale->discount_amount,
                'discount_percent' => $validated['discount_percent'] ?? $sale->discount_percent,
                'delivery_mode' => $validated['delivery_mode'] ?? $sale->delivery_mode,
                'notes' => $validated['notes'] ?? $sale->notes,
            ]);

            // Update items if provided
            if (isset($validated['items'])) {
                // Remove existing items
                $sale->items()->delete();

                // Create new items
                foreach ($validated['items'] as $item) {
                    $article = Article::find($item['article_id']);
                    
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'article_id' => $item['article_id'],
                        'article_name' => $article->name,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'selected_options' => $item['selected_options'] ?? null,
                        'options_price' => $item['options_price'] ?? 0,
                        'discount_amount' => $item['discount_amount'] ?? 0,
                        'total' => 0,
                    ]);
                }
            }

            // Recalculate totals
            $sale->load('items');
            $sale->calculateTotals();
            $sale->save();

            return response()->json($sale->load(['customer', 'items.article']));
        });
    }

    public function complete(Sale $sale): JsonResponse
    {
        if ($sale->status !== 'pending') {
            return response()->json(['message' => 'Sale is not pending'], 422);
        }

        if ($sale->payment_status !== 'paid') {
            return response()->json(['message' => 'Sale is not fully paid'], 422);
        }

        return DB::transaction(function () use ($sale) {
            // Update stock for items with managed stock
            foreach ($sale->items as $item) {
                if ($item->article && $item->article->manage_stock) {
                    StockMovement::record(
                        $item->article,
                        'out',
                        (int) $item->quantity,
                        'Vente #' . $sale->reference,
                        auth()->id(),
                        $sale->id
                    );
                }
            }

            $sale->update(['status' => 'completed']);

            return response()->json($sale->load(['customer', 'items.article', 'payments']));
        });
    }

    public function cancel(Sale $sale): JsonResponse
    {
        if ($sale->status === 'cancelled') {
            return response()->json(['message' => 'Sale is already cancelled'], 422);
        }

        // If sale was completed, restore stock
        if ($sale->status === 'completed') {
            foreach ($sale->items as $item) {
                if ($item->article && $item->article->manage_stock) {
                    StockMovement::record(
                        $item->article,
                        'return',
                        (int) $item->quantity,
                        'Annulation vente #' . $sale->reference,
                        auth()->id(),
                        $sale->id
                    );
                }
            }
        }

        $sale->update(['status' => 'cancelled']);

        return response()->json($sale);
    }

    public function destroy(Sale $sale): JsonResponse
    {
        if ($sale->status === 'completed') {
            return response()->json(['message' => 'Cannot delete a completed sale'], 422);
        }

        $sale->delete();

        return response()->json(null, 204);
    }
}
