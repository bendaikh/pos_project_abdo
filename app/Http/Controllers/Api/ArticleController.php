<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\TaskAutomationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Article::with(['category', 'subcategory', 'options.variants', 'variants', 'photos']);

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

        // Filter on sale
        if ($request->has('on_sale')) {
            $query->where('is_on_sale', true);
        }

        if ($request->has('is_composite')) {
            $query->where('is_composite', filter_var($request->is_composite, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('manage_stock')) {
            $query->where('manage_stock', filter_var($request->manage_stock, FILTER_VALIDATE_BOOLEAN));
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
        try {
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
                'has_options' => 'boolean',
                'has_variants' => 'boolean',
                'is_on_sale' => 'boolean',
                'options' => 'nullable|array',
                'options.*' => 'exists:options,id',
                'variants' => 'nullable|array',
                'variants.*.name' => 'required|string|max:255',
                'variants.*.price_impact' => 'nullable|numeric|min:0',
                'variants.*.cost_price' => 'nullable|numeric|min:0',
                'variants.*.sku' => 'nullable|string|max:100',
                'variants.*.barcode' => 'nullable|string|max:100',
                'variants.*.template_name' => 'nullable|string|max:100',
                'variants.*.template_value' => 'nullable|string|max:100',
                'variants.*.is_active' => 'nullable|boolean',
                'variants.*.sort_order' => 'nullable|integer|min:0',
                'photos' => 'nullable|array',
                'photos.*.photo_url' => 'required|string',
                'photos.*.sort_order' => 'nullable|integer',
                'photos.*.is_primary' => 'nullable|boolean',
                'bom_items' => 'nullable|array',
                'bom_items.*.component_id' => 'required|exists:articles,id',
                'bom_items.*.quantity' => 'required|integer|min:1',
                'bom_items.*.unit' => 'nullable|string|max:20',
                'bom_items.*.unit_cost' => 'nullable|numeric|min:0',
            ]);

            $optionIds = $validated['options'] ?? [];
            $variantsData = $validated['variants'] ?? [];
            $photos = $validated['photos'] ?? [];
            $bomItems = $validated['bom_items'] ?? [];
            unset($validated['options'], $validated['variants'], $validated['photos']);
            unset($validated['bom_items']);

            $article = DB::transaction(function () use ($validated, $optionIds, $variantsData, $photos, $bomItems) {
                $article = Article::create($validated);

                if (!empty($optionIds)) {
                    $article->options()->sync($optionIds);
                }

                if (!empty($variantsData)) {
                    foreach ($variantsData as $index => $variant) {
                        $article->variants()->create([
                            'name' => $variant['name'],
                            'price_impact' => $variant['price_impact'] ?? 0,
                            'cost_price' => $variant['cost_price'] ?? 0,
                            'sku' => $variant['sku'] ?? null,
                            'barcode' => $variant['barcode'] ?? null,
                            'template_name' => $variant['template_name'] ?? null,
                            'template_value' => $variant['template_value'] ?? null,
                            'is_active' => $variant['is_active'] ?? true,
                            'sort_order' => $variant['sort_order'] ?? $index,
                        ]);
                    }
                }

                if (!empty($photos)) {
                    foreach ($photos as $index => $photo) {
                        $article->photos()->create([
                            'photo_url' => $photo['photo_url'],
                            'sort_order' => $photo['sort_order'] ?? $index,
                            'is_primary' => $photo['is_primary'] ?? ($index === 0),
                        ]);
                    }
                }

                if (!empty($bomItems)) {
                    $seenComponents = [];
                    foreach ($bomItems as $index => $item) {
                        if ((int) $item['component_id'] === (int) $article->id) {
                            throw ValidationException::withMessages([
                                'bom_items' => ['Un article composite ne peut pas se contenir lui-même.'],
                            ]);
                        }

                        if (in_array($item['component_id'], $seenComponents, true)) {
                            continue;
                        }
                        $seenComponents[] = $item['component_id'];

                        $article->bomItems()->create([
                            'component_id' => $item['component_id'],
                            'quantity' => $item['quantity'],
                            'unit' => $item['unit'] ?? null,
                            'unit_cost' => $item['unit_cost'] ?? null,
                            'sort_order' => $index,
                        ]);
                    }

                    if (!$article->is_composite) {
                        $article->update(['is_composite' => true]);
                    }
                }

                return $article;
            });

            // Try to sync low stock tasks, but don't fail if tables don't exist
            try {
                app(TaskAutomationService::class)->syncLowStockTasks();
            } catch (\Exception $e) {
                \Log::warning('Could not sync low stock tasks: ' . $e->getMessage());
            }

            return response()->json($article->load(['category', 'subcategory', 'options.variants', 'variants', 'photos', 'bomItems.component']), 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Article creation failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to create article: ' . $e->getMessage()], 500);
        }
    }

    public function show(Article $article): JsonResponse
    {
        return response()->json($article->load(['category', 'subcategory', 'options.variants', 'variants', 'photos', 'bomItems.component']));
    }

    public function update(Request $request, Article $article): JsonResponse
    {
        try {
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
                'has_options' => 'boolean',
                'has_variants' => 'boolean',
                'is_on_sale' => 'boolean',
                'options' => 'nullable|array',
                'options.*' => 'exists:options,id',
                'variants' => 'nullable|array',
                'variants.*.id' => 'nullable|exists:variants,id',
                'variants.*.name' => 'required|string|max:255',
                'variants.*.price_impact' => 'nullable|numeric|min:0',
                'variants.*.cost_price' => 'nullable|numeric|min:0',
                'variants.*.sku' => 'nullable|string|max:100',
                'variants.*.barcode' => 'nullable|string|max:100',
                'variants.*.template_name' => 'nullable|string|max:100',
                'variants.*.template_value' => 'nullable|string|max:100',
                'variants.*.is_active' => 'nullable|boolean',
                'variants.*.sort_order' => 'nullable|integer|min:0',
                'photos' => 'nullable|array',
                'photos.*.id' => 'nullable|exists:article_photos,id',
                'photos.*.photo_url' => 'required|string',
                'photos.*.sort_order' => 'nullable|integer',
                'photos.*.is_primary' => 'nullable|boolean',
                'bom_items' => 'nullable|array',
                'bom_items.*.component_id' => 'required|exists:articles,id',
                'bom_items.*.quantity' => 'required|integer|min:1',
                'bom_items.*.unit' => 'nullable|string|max:20',
                'bom_items.*.unit_cost' => 'nullable|numeric|min:0',
            ]);

            $updatedArticle = DB::transaction(function () use ($validated, $article) {
                if (isset($validated['options'])) {
                    $article->options()->sync($validated['options']);
                    unset($validated['options']);
                }

                if (isset($validated['variants'])) {
                    // Handle variants: delete old ones and create new ones
                    $article->variants()->delete();
                    foreach ($validated['variants'] as $index => $variant) {
                        $article->variants()->create([
                            'name' => $variant['name'],
                            'price_impact' => $variant['price_impact'] ?? 0,
                            'cost_price' => $variant['cost_price'] ?? 0,
                            'sku' => $variant['sku'] ?? null,
                            'barcode' => $variant['barcode'] ?? null,
                            'template_name' => $variant['template_name'] ?? null,
                            'template_value' => $variant['template_value'] ?? null,
                            'is_active' => $variant['is_active'] ?? true,
                            'sort_order' => $variant['sort_order'] ?? $index,
                        ]);
                    }
                    unset($validated['variants']);
                }

                if (isset($validated['photos'])) {
                    // Delete existing photos
                    $article->photos()->delete();
                    
                    // Create new photos
                    foreach ($validated['photos'] as $index => $photo) {
                        $article->photos()->create([
                            'photo_url' => $photo['photo_url'],
                            'sort_order' => $photo['sort_order'] ?? $index,
                            'is_primary' => $photo['is_primary'] ?? ($index === 0),
                        ]);
                    }
                    unset($validated['photos']);
                }

                if (isset($validated['bom_items'])) {
                    $article->bomItems()->delete();
                    $seenComponents = [];
                    foreach ($validated['bom_items'] as $index => $item) {
                        if ((int) $item['component_id'] === (int) $article->id) {
                            throw ValidationException::withMessages([
                                'bom_items' => ['Un article composite ne peut pas se contenir lui-même.'],
                            ]);
                        }
                        if (in_array($item['component_id'], $seenComponents, true)) {
                            continue;
                        }
                        $seenComponents[] = $item['component_id'];
                        $article->bomItems()->create([
                            'component_id' => $item['component_id'],
                            'quantity' => $item['quantity'],
                            'unit' => $item['unit'] ?? null,
                            'unit_cost' => $item['unit_cost'] ?? null,
                            'sort_order' => $index,
                        ]);
                    }
                    if (!empty($validated['bom_items'])) {
                        $validated['is_composite'] = true;
                    }
                    unset($validated['bom_items']);
                }

                $article->update($validated);

                return $article->load(['category', 'subcategory', 'options.variants', 'variants', 'photos', 'bomItems.component']);
            });

            // Try to sync low stock tasks, but don't fail if tables don't exist
            try {
                app(TaskAutomationService::class)->syncLowStockTasks();
            } catch (\Exception $e) {
                \Log::warning('Could not sync low stock tasks: ' . $e->getMessage());
            }

            return response()->json($updatedArticle);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Article update failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to update article: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Article $article): JsonResponse
    {
        $article->delete();

        return response()->json(null, 204);
    }

    // Variant Management Endpoints
    public function listVariants(Article $article): JsonResponse
    {
        return response()->json($article->variants);
    }

    public function getVariant(Article $article, $variantId): JsonResponse
    {
        $variant = $article->variants()->findOrFail($variantId);
        return response()->json($variant);
    }

    public function createVariant(Request $request, Article $article): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_impact' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'template_name' => 'nullable|string|max:100',
            'template_value' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $variant = $article->variants()->create([
            'name' => $validated['name'],
            'price_impact' => $validated['price_impact'] ?? 0,
            'cost_price' => $validated['cost_price'] ?? 0,
            'sku' => $validated['sku'] ?? null,
            'barcode' => $validated['barcode'] ?? null,
            'template_name' => $validated['template_name'] ?? null,
            'template_value' => $validated['template_value'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return response()->json($variant, 201);
    }

    public function updateVariant(Request $request, Article $article, $variantId): JsonResponse
    {
        $variant = $article->variants()->findOrFail($variantId);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'price_impact' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'template_name' => 'nullable|string|max:100',
            'template_value' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $variant->update([
            'name' => $validated['name'] ?? $variant->name,
            'price_impact' => $validated['price_impact'] ?? $variant->price_impact,
            'cost_price' => $validated['cost_price'] ?? $variant->cost_price,
            'sku' => $validated['sku'] ?? $variant->sku,
            'barcode' => $validated['barcode'] ?? $variant->barcode,
            'template_name' => $validated['template_name'] ?? $variant->template_name,
            'template_value' => $validated['template_value'] ?? $variant->template_value,
            'is_active' => $validated['is_active'] ?? $variant->is_active,
            'sort_order' => $validated['sort_order'] ?? $variant->sort_order,
        ]);

        return response()->json($variant);
    }

    public function deleteVariant(Article $article, $variantId): JsonResponse
    {
        $variant = $article->variants()->findOrFail($variantId);
        $variant->delete();

        return response()->json(null, 204);
    }
}
