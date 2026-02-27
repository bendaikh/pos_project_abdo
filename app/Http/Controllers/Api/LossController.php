<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Loss;
use App\Models\LossItem;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class LossController extends Controller
{
    public function reference(): JsonResponse
    {
        return response()->json(['reference' => Loss::generateReference()]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = LossItem::with([
            'article',
            'loss.responsibleEmployee',
            'loss.user',
        ]);

        if ($request->filled('article_id')) {
            $query->where('article_id', $request->article_id);
        }

        if ($request->filled('loss_type')) {
            $query->where('loss_type', $request->loss_type);
        }

        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('store_id')) {
            $query->whereHas('loss', function ($lossQuery) use ($request) {
                if ($request->filled('from_date')) {
                    $lossQuery->whereDate('loss_date', '>=', $request->from_date);
                }

                if ($request->filled('to_date')) {
                    $lossQuery->whereDate('loss_date', '<=', $request->to_date);
                }

                if ($request->filled('store_id')) {
                    $lossQuery->where('store_id', $request->store_id);
                }
            });
        }

        $items = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isManager()) {
            return response()->json(['message' => 'Action non autorisee'], 403);
        }

        if (!Schema::hasTable('losses') || !Schema::hasTable('loss_items')) {
            return response()->json([
                'message' => 'Module de gestion des pertes non initialisé. Veuillez exécuter les migrations (php artisan migrate).',
            ], 500);
        }

        $validated = $request->validate([
            'loss_date' => 'required|date',
            'responsible_employee_id' => 'nullable|exists:employees,id',
            'responsible_name' => 'nullable|string|max:255',
            'store_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.article_id' => 'required|exists:articles,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.loss_type' => 'required|in:loss,breakage,expiration,theft',
        ]);

        if (empty($validated['responsible_employee_id']) && empty($validated['responsible_name'])) {
            throw ValidationException::withMessages([
                'responsible' => ['Responsable requis.'],
            ]);
        }

        $loss = DB::transaction(function () use ($validated, $user) {
            $loss = Loss::create([
                'reference' => Loss::generateReference(),
                'loss_date' => $validated['loss_date'],
                'responsible_employee_id' => $validated['responsible_employee_id'] ?? null,
                'responsible_name' => $validated['responsible_name'] ?? null,
                'store_id' => $validated['store_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $user->id,
                'validated_at' => now(),
            ]);

            $totalQuantity = 0;
            $totalCost = 0;

            foreach ($validated['items'] as $item) {
                $article = Article::with('bomItems.component')->findOrFail($item['article_id']);

                if (!$article->manage_stock) {
                    throw ValidationException::withMessages([
                        'items' => ["L'article {$article->name} ne gere pas le stock."],
                    ]);
                }

                if ($article->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["Stock insuffisant pour {$article->name}."],
                    ]);
                }

                $movement = StockMovement::record(
                    $article,
                    'out',
                    $item['quantity'],
                    'loss:' . $item['loss_type'],
                    $user->id
                );

                $unitCost = $article->cost_basis;
                $lineTotal = $item['quantity'] * (float) $unitCost;

                LossItem::create([
                    'loss_id' => $loss->id,
                    'article_id' => $article->id,
                    'loss_type' => $item['loss_type'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineTotal,
                    'stock_before' => $movement->stock_before,
                    'stock_after' => $movement->stock_after,
                ]);

                $movement->update([
                    'notes' => trim('Perte ' . $loss->reference),
                ]);

                $totalQuantity += $item['quantity'];
                $totalCost += $lineTotal;
            }

            $loss->update([
                'total_quantity' => $totalQuantity,
                'total_cost' => $totalCost,
            ]);

            return $loss;
        });

        return response()->json($loss->load(['items.article', 'responsibleEmployee', 'user']), 201);
    }
}
