<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Article::with('category')
            ->where('manage_stock', true);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by stock level
        if ($request->has('stock_level')) {
            switch ($request->stock_level) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'out':
                    $query->where('stock_quantity', 0);
                    break;
                case 'in':
                    $query->where('stock_quantity', '>', 0);
                    break;
            }
        }

        $articles = $query->orderBy('name')->paginate($request->get('per_page', 20));

        return response()->json($articles);
    }

    public function movements(Request $request): JsonResponse
    {
        $query = StockMovement::with(['article', 'user', 'sale']);

        if ($request->has('article_id')) {
            $query->where('article_id', $request->article_id);
        }

        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $movements = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json($movements);
    }

    public function recordMovement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'type' => 'required|in:in,out,adjustment,return',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $article = Article::findOrFail($validated['article_id']);

        if (!$article->manage_stock) {
            return response()->json(['message' => 'Cet article ne gère pas le stock'], 422);
        }

        // Check if we have enough stock for out movements
        if (in_array($validated['type'], ['out']) && $article->stock_quantity < $validated['quantity']) {
            return response()->json(['message' => 'Stock insuffisant'], 422);
        }

        $movement = StockMovement::record(
            $article,
            $validated['type'],
            $validated['quantity'],
            $validated['reason'] ?? null,
            auth()->id()
        );

        if (isset($validated['notes'])) {
            $movement->update(['notes' => $validated['notes']]);
        }

        return response()->json([
            'movement' => $movement->load(['article', 'user']),
            'article' => $article->fresh(),
        ], 201);
    }

    public function alerts(): JsonResponse
    {
        $lowStockArticles = Article::lowStock()
            ->with('category')
            ->orderBy('stock_quantity')
            ->get()
            ->map(function ($article) {
                return [
                    'id' => $article->id,
                    'name' => $article->name,
                    'sku' => $article->sku,
                    'category' => $article->category?->name,
                    'stock_quantity' => $article->stock_quantity,
                    'stock_alert_threshold' => $article->stock_alert_threshold,
                    'status' => $article->stock_quantity == 0 ? 'out_of_stock' : 'low',
                ];
            });

        return response()->json([
            'total_alerts' => $lowStockArticles->count(),
            'out_of_stock' => $lowStockArticles->where('status', 'out_of_stock')->count(),
            'low_stock' => $lowStockArticles->where('status', 'low')->count(),
            'articles' => $lowStockArticles,
        ]);
    }
}
