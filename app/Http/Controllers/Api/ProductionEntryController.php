<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\MaterialConsumption;
use App\Models\ProductionEntry;
use App\Models\ProductionEntryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionEntryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductionEntry::with(['items.article', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('produced_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('produced_at', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('reference', 'like', "%{$search}%");
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        $entries = $query->orderByDesc('produced_at')->paginate($request->get('per_page', 20));

        return response()->json($entries);
    }

    public function show(ProductionEntry $productionEntry): JsonResponse
    {
        return response()->json(
            $productionEntry->load([
                'items.article',
                'consumptions.article',
                'consumptions.producedArticle',
                'user',
            ])
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'produced_at' => 'required|date',
            'status' => 'nullable|in:draft,validated',
            'notes' => 'nullable|string',
            'store_id' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.article_id' => 'required|exists:articles,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $entry = DB::transaction(function () use ($validated) {
            $entry = ProductionEntry::create([
                'reference' => ProductionEntry::generateReference(),
                'produced_at' => $validated['produced_at'],
                'status' => $validated['status'] ?? 'draft',
                'notes' => $validated['notes'] ?? null,
                'store_id' => $validated['store_id'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                ProductionEntryItem::create([
                    'production_entry_id' => $entry->id,
                    'article_id' => $item['article_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            if (($validated['status'] ?? 'draft') === 'validated') {
                $this->applyProduction($entry);
            }

            return $entry;
        });

        return response()->json($entry->load(['items.article', 'user']), 201);
    }

    public function update(Request $request, ProductionEntry $productionEntry): JsonResponse
    {
        if ($productionEntry->status === 'validated') {
            return response()->json(['message' => 'Production déjà validée'], 422);
        }

        $validated = $request->validate([
            'produced_at' => 'sometimes|date',
            'status' => 'nullable|in:draft,validated',
            'notes' => 'nullable|string',
            'store_id' => 'nullable|integer',
            'items' => 'sometimes|array|min:1',
            'items.*.article_id' => 'required_with:items|exists:articles,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        $entry = DB::transaction(function () use ($validated, $productionEntry) {
            $productionEntry->update([
                'produced_at' => $validated['produced_at'] ?? $productionEntry->produced_at,
                'notes' => $validated['notes'] ?? $productionEntry->notes,
                'store_id' => $validated['store_id'] ?? $productionEntry->store_id,
            ]);

            if (isset($validated['items'])) {
                $productionEntry->items()->delete();
                foreach ($validated['items'] as $item) {
                    ProductionEntryItem::create([
                        'production_entry_id' => $productionEntry->id,
                        'article_id' => $item['article_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }

            if (($validated['status'] ?? 'draft') === 'validated') {
                $this->applyProduction($productionEntry);
            }

            return $productionEntry;
        });

        return response()->json($entry->load(['items.article', 'user']));
    }

    public function validateEntry(ProductionEntry $productionEntry): JsonResponse
    {
        if ($productionEntry->status === 'validated') {
            return response()->json(['message' => 'Production déjà validée'], 422);
        }

        $entry = DB::transaction(function () use ($productionEntry) {
            $this->applyProduction($productionEntry);
            return $productionEntry;
        });

        return response()->json($entry->load(['items.article', 'consumptions.article', 'user']));
    }

    public function destroy(ProductionEntry $productionEntry): JsonResponse
    {
        if ($productionEntry->status === 'validated') {
            return response()->json(['message' => 'Impossible de supprimer une production validée'], 422);
        }

        $productionEntry->delete();

        return response()->json(null, 204);
    }

    private function applyProduction(ProductionEntry $entry): void
    {
        $entry->load(['items.article.bomItems.component']);

        if ($entry->items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => ['Veuillez ajouter au moins un article à produire.'],
            ]);
        }

        foreach ($entry->items as $item) {
            $article = $item->article;
            if (!$article || !$article->is_composite) {
                throw ValidationException::withMessages([
                    'items' => ["L'article {$item->article_id} n'est pas composite."],
                ]);
            }
            if ($article->bomItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => ["La fiche technique est vide pour {$article->name}."],
                ]);
            }
        }

        // Pre-check stock availability
        foreach ($entry->items as $item) {
            foreach ($item->article->bomItems as $bom) {
                $component = $bom->component;
                if (!$component) {
                    continue;
                }
                $consumeQty = (int) ((float) $item->quantity * (float) $bom->quantity);
                if ($consumeQty <= 0) {
                    continue;
                }
                if ($component->manage_stock && $component->stock_quantity < $consumeQty) {
                    throw ValidationException::withMessages([
                        'stock' => ["Stock insuffisant pour {$component->name}."],
                    ]);
                }
            }
        }

        $totalCost = 0;
        foreach ($entry->items as $item) {
            $article = $item->article;
            $bomItems = $article->bomItems;
            $itemCost = 0;

            foreach ($bomItems as $bom) {
                $component = $bom->component?->fresh();
                if (!$component) {
                    continue;
                }

                $consumeQty = (int) ((float) $item->quantity * (float) $bom->quantity);
                if ($consumeQty <= 0) {
                    continue;
                }
                $stockBefore = $component->stock_quantity;

                if ($component->manage_stock) {
                    $component->decrementStock($consumeQty);
                }

                $stockAfter = $component->fresh()->stock_quantity;
                $unitCost = $bom->unit_cost ?? $component->buy_price ?? 0;
                $lineTotal = $consumeQty * (float) $unitCost;

                MaterialConsumption::create([
                    'production_entry_id' => $entry->id,
                    'produced_article_id' => $article->id,
                    'article_id' => $component->id,
                    'reason' => 'production',
                    'quantity' => $consumeQty,
                    'unit' => $bom->unit ?? $component->unit,
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineTotal,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'consumed_at' => $entry->produced_at->copy()->startOfDay(),
                    'user_id' => $entry->user_id,
                    'store_id' => $entry->store_id,
                ]);

                $itemCost += $lineTotal;
            }

            if ($article->manage_stock) {
                $article->incrementStock((int) $item->quantity);
            }

            $unitCost = (float) $item->quantity > 0 ? $itemCost / (float) $item->quantity : 0;
            $item->update([
                'unit_cost' => $unitCost,
                'total_cost' => $itemCost,
            ]);

            $totalCost += $itemCost;
        }

        $entry->update([
            'status' => 'validated',
            'total_cost' => $totalCost,
            'validated_at' => now(),
        ]);
    }
}
