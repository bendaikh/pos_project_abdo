<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\MaterialConsumption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialConsumptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MaterialConsumption::with([
            'article',
            'producedArticle',
            'productionEntry',
            'user',
        ]);

        if ($request->filled('from_date')) {
            $query->whereDate('consumed_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('consumed_at', '<=', $request->to_date);
        }

        if ($request->filled('article_id')) {
            $query->where('article_id', $request->article_id);
        }

        if ($request->filled('produced_article_id')) {
            $query->where('produced_article_id', $request->produced_article_id);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        $consumptions = $query->latest('consumed_at')->paginate($request->get('per_page', 20));

        return response()->json($consumptions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'produced_article_id' => 'nullable|exists:articles,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|in:production,loss,adjustment',
            'consumed_at' => 'required|date',
            'notes' => 'nullable|string',
            'store_id' => 'nullable|integer',
        ]);

        $article = Article::findOrFail($validated['article_id']);

        if (!$article->manage_stock) {
            return response()->json(['message' => 'Cet article ne gère pas le stock'], 422);
        }

        if ($article->stock_quantity < $validated['quantity']) {
            return response()->json(['message' => 'Stock insuffisant'], 422);
        }

        $consumption = DB::transaction(function () use ($validated, $article) {
            $stockBefore = $article->stock_quantity;
            $article->decrementStock($validated['quantity']);
            $stockAfter = $article->fresh()->stock_quantity;

            return MaterialConsumption::create([
                'production_entry_id' => null,
                'produced_article_id' => $validated['produced_article_id'] ?? null,
                'article_id' => $article->id,
                'reason' => $validated['reason'],
                'quantity' => $validated['quantity'],
                'unit' => $article->unit,
                'unit_cost' => $article->buy_price ?? 0,
                'total_cost' => $validated['quantity'] * (float) ($article->buy_price ?? 0),
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'consumed_at' => $validated['consumed_at'],
                'user_id' => auth()->id(),
                'store_id' => $validated['store_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return response()->json($consumption->load(['article', 'producedArticle', 'user']), 201);
    }
}
