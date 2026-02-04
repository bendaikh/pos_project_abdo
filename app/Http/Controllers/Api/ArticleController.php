<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Article::with(['category', 'subcategory', 'options']);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by subcategory
        if ($request->has('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        // Filter active only
        if ($request->has('active')) {
            $query->active();
        }

        // Filter favorites
        if ($request->has('favorites')) {
            $query->favorites();
        }

        // Filter in stock
        if ($request->has('in_stock')) {
            $query->inStock();
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        if ($request->has('per_page')) {
            $articles = $query->paginate($request->per_page);
        } else {
            $articles = $query->get();
        }

        return response()->json($articles);
    }

    public function favorites(): JsonResponse
    {
        $articles = Article::with(['category'])
            ->active()
            ->favorites()
            ->inStock()
            ->orderBy('name')
            ->get();

        return response()->json($articles);
    }

    public function lowStock(): JsonResponse
    {
        $articles = Article::with(['category'])
            ->lowStock()
            ->orderBy('stock_quantity')
            ->get();

        return response()->json($articles);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'nullable|string|max:50|unique:articles',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'sell_price' => 'required|numeric|min:0',
            'buy_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'manage_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'stock_alert_threshold' => 'nullable|integer|min:0',
            'photo' => 'nullable|string',
            'is_favorite' => 'boolean',
            'is_active' => 'boolean',
            'options' => 'nullable|array',
            'options.*' => 'exists:options,id',
        ]);

        $optionIds = $validated['options'] ?? [];
        unset($validated['options']);

        $article = Article::create($validated);

        if (!empty($optionIds)) {
            $article->options()->sync($optionIds);
        }

        return response()->json($article->load(['category', 'subcategory', 'options']), 201);
    }

    public function show(Article $article): JsonResponse
    {
        return response()->json($article->load(['category', 'subcategory', 'options']));
    }

    public function update(Request $request, Article $article): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'nullable|string|max:50|unique:articles,sku,' . $article->id,
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'sell_price' => 'sometimes|required|numeric|min:0',
            'buy_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'manage_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'stock_alert_threshold' => 'nullable|integer|min:0',
            'photo' => 'nullable|string',
            'is_favorite' => 'boolean',
            'is_active' => 'boolean',
            'options' => 'nullable|array',
            'options.*' => 'exists:options,id',
        ]);

        if (isset($validated['options'])) {
            $article->options()->sync($validated['options']);
            unset($validated['options']);
        }

        $article->update($validated);

        return response()->json($article->load(['category', 'subcategory', 'options']));
    }

    public function destroy(Article $article): JsonResponse
    {
        $article->delete();

        return response()->json(null, 204);
    }
}
