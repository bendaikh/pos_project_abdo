<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleItemReturn;
use App\Models\SaleLog;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Services\CustomListService;
use App\Services\DeliveryCommissionService;
use App\Services\SalePaymentWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    private const ORDER_STATUSES = [
        'confirmee',
        'en_preparation',
        'envoyee',
        'livree',
        'retournee',
        'annulee',
    ];

    public function __construct(
        private readonly SalePaymentWorkflowService $paymentWorkflow,
        private readonly CustomListService $customListService,
        private readonly DeliveryCommissionService $deliveryCommissionService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Sale::with(['customer', 'user', 'items', 'payments', 'deliveryAgent']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        if ($request->filled('payment_status')) {
            $workflowStatus = $this->paymentWorkflow->normalizeWorkflowStatus($request->payment_status);
            if ($workflowStatus) {
                if (Sale::supportsColumn('payment_status_code')) {
                    $query->where('payment_status_code', $workflowStatus);
                } else {
                    $legacyStatuses = match ($workflowStatus) {
                        SalePaymentWorkflowService::STATUS_PAID,
                        SalePaymentWorkflowService::STATUS_COLLECTED => ['paid'],
                        SalePaymentWorkflowService::STATUS_TO_PAY,
                        SalePaymentWorkflowService::STATUS_UNPAID => ['unpaid', 'partial'],
                        SalePaymentWorkflowService::STATUS_TO_COLLECT => ['partial'],
                        default => [$request->payment_status],
                    };
                    $query->whereIn('payment_status', $legacyStatuses);
                }
            } else {
                $query->where('payment_status', $request->payment_status);
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('pickup_date_from')) {
            $query->whereDate('pickup_date', '>=', $request->pickup_date_from);
        }

        if ($request->filled('pickup_date_to')) {
            $query->whereDate('pickup_date', '<=', $request->pickup_date_to);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('delivery_agent_id')) {
            $query->where('delivery_agent_id', $request->delivery_agent_id);
        }

        if ($request->filled('origin')) {
            $query->where('origin', $request->origin);
        }

        if ($request->filled('exclude_origin')) {
            $query->where('origin', '!=', $request->exclude_origin);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('order_number', 'like', "%{$search}%")
                    ->orWhere('ticket_name', 'like', "%{$search}%")
                    ->orWhere('ticket_group', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $sales = $query->latest()->paginate($request->get('per_page', 20));
        $sales->getCollection()->transform(fn (Sale $sale) => $this->paymentWorkflow->decorateSale($sale));

        return response()->json($sales);
    }

    public function pending(): JsonResponse
    {
        $sales = Sale::with(['customer', 'user', 'items.article', 'payments', 'deliveryAgent'])
            ->pending()
            ->latest()
            ->get();

        $sales->transform(fn (Sale $sale) => $this->paymentWorkflow->decorateSale($sale));

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
            'items.*.variant_price' => 'nullable|numeric|min:0',
            'items.*.selected_options' => 'nullable|array',
            'items.*.options_price' => 'nullable|numeric',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'service_mode' => 'nullable|string|max:255',
            'delivery_mode' => 'nullable|string|max:255',
            'origin' => 'nullable|in:pos,menu_commande,livraison',
            'ticket_type' => 'nullable|in:liste,personnalise,commande',
            'ticket_name' => 'nullable|string|max:255',
            'ticket_group' => 'nullable|string|max:255',
            'delivery_agent_id' => 'nullable|exists:delivery_agents,id',
            'customer_activity' => 'nullable|string|max:255',
            'pickup_date' => 'nullable|date',
            'appointment_at' => 'nullable|date',
            'delivery_address' => 'nullable|string',
            'order_status' => 'nullable|in:'.implode(',', self::ORDER_STATUSES),
            'notes' => 'nullable|string',
        ]);

        $taxRate = Setting::get('tax_rate', 0);
        $taxEnabled = Setting::get('tax_enabled', false);

        return DB::transaction(function () use ($validated, $taxRate, $taxEnabled) {
            $resolvedServiceMode = $this->customListService->resolveServiceMode(
                $validated['service_mode'] ?? null,
                $validated['delivery_mode'] ?? null,
            );
            $resolvedDeliveryMode = $this->customListService->resolveOperationalMode(
                $resolvedServiceMode,
                $validated['delivery_mode'] ?? null,
            );
            if (! empty($validated['delivery_agent_id'])) {
                $resolvedDeliveryMode = 'delivery';
            }
            $pickupDate = $validated['pickup_date'] ?? null;
            if (! $pickupDate && ! empty($validated['appointment_at'])) {
                $pickupDate = date('Y-m-d', strtotime((string) $validated['appointment_at']));
            }

            $sale = Sale::create(Sale::persistable([
                'user_id' => auth()->id(),
                'customer_id' => $validated['customer_id'] ?? null,
                'delivery_agent_id' => $validated['delivery_agent_id'] ?? null,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'discount_percent' => $validated['discount_percent'] ?? 0,
                'tax_rate' => $taxEnabled ? $taxRate : 0,
                'delivery_mode' => $resolvedDeliveryMode,
                'service_mode' => $resolvedServiceMode,
                'origin' => $validated['origin'] ?? 'pos',
                'ticket_type' => $validated['ticket_type'] ?? null,
                'ticket_name' => $validated['ticket_name'] ?? null,
                'ticket_group' => $validated['ticket_group'] ?? null,
                'customer_activity' => $validated['customer_activity'] ?? null,
                'pickup_date' => $pickupDate,
                'appointment_at' => $validated['appointment_at'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
                'order_status' => $validated['order_status'] ?? 'confirmee',
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_status_code' => SalePaymentWorkflowService::STATUS_TO_PAY,
            ]));

            foreach ($validated['items'] as $item) {
                $article = Article::find($item['article_id']);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'article_id' => $item['article_id'],
                    'article_name' => $article->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'selected_options' => $item['selected_options'] ?? null,
                    'options_price' => ($item['options_price'] ?? 0) + ($item['variant_price'] ?? 0),
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total' => 0,
                ]);
            }

            $sale->load('items');
            $sale->calculateTotals();
            $sale->save();
            $this->captureDeliveryCommissionIfNeeded($sale);

            $this->addLog(
                $sale,
                'commande_confirmee',
                $sale->order_status,
                'Commande créée'
            );

            $sale = $sale->fresh()->load(['customer', 'user', 'items.article', 'payments', 'logs.user', 'returns.article', 'deliveryAgent']);

            return response()->json($this->paymentWorkflow->decorateSale($sale), 201);
        });
    }

    public function show(Sale $sale): JsonResponse
    {
        $sale = $sale->load([
            'customer',
            'user',
            'items.article',
            'payments',
            'deliveryAgent',
            'logs.user',
            'returns.article',
            'returns.user',
            'returns.saleItem',
        ]);

        return response()->json($this->paymentWorkflow->decorateSale($sale));
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
            'items.*.variant_price' => 'nullable|numeric|min:0',
            'items.*.selected_options' => 'nullable|array',
            'items.*.options_price' => 'nullable|numeric',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'service_mode' => 'nullable|string|max:255',
            'delivery_mode' => 'nullable|string|max:255',
            'origin' => 'nullable|in:pos,menu_commande,livraison',
            'ticket_type' => 'nullable|in:liste,personnalise,commande',
            'ticket_name' => 'nullable|string|max:255',
            'ticket_group' => 'nullable|string|max:255',
            'delivery_agent_id' => 'nullable|exists:delivery_agents,id',
            'customer_activity' => 'nullable|string|max:255',
            'pickup_date' => 'nullable|date',
            'appointment_at' => 'nullable|date',
            'delivery_address' => 'nullable|string',
            'order_status' => 'nullable|in:'.implode(',', self::ORDER_STATUSES),
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($sale, $validated) {
            $oldStatus = $sale->order_status;
            $serviceModeInputProvided = array_key_exists('service_mode', $validated) || array_key_exists('delivery_mode', $validated);
            $resolvedServiceMode = $serviceModeInputProvided
                ? $this->customListService->resolveServiceMode(
                    $validated['service_mode'] ?? $sale->service_mode,
                    $validated['delivery_mode'] ?? $sale->delivery_mode,
                )
                : ($sale->service_mode ?: $this->customListService->resolveServiceMode(null, $sale->delivery_mode));
            $resolvedDeliveryMode = $serviceModeInputProvided
                ? $this->customListService->resolveOperationalMode(
                    $resolvedServiceMode,
                    $validated['delivery_mode'] ?? $sale->delivery_mode,
                )
                : $sale->delivery_mode;
            if (! empty($validated['delivery_agent_id'])) {
                $resolvedDeliveryMode = 'delivery';
            }
            $pickupDate = $validated['pickup_date'] ?? $sale->pickup_date;
            if (! array_key_exists('pickup_date', $validated) && ! empty($validated['appointment_at'])) {
                $pickupDate = date('Y-m-d', strtotime((string) $validated['appointment_at']));
            }

            $sale->update([
                'customer_id' => $validated['customer_id'] ?? $sale->customer_id,
                'delivery_agent_id' => $validated['delivery_agent_id'] ?? $sale->delivery_agent_id,
                'discount_amount' => $validated['discount_amount'] ?? $sale->discount_amount,
                'discount_percent' => $validated['discount_percent'] ?? $sale->discount_percent,
                'delivery_mode' => $resolvedDeliveryMode,
                'service_mode' => $resolvedServiceMode,
                'origin' => $validated['origin'] ?? $sale->origin,
                'ticket_type' => $validated['ticket_type'] ?? $sale->ticket_type,
                'ticket_name' => $validated['ticket_name'] ?? $sale->ticket_name,
                'ticket_group' => $validated['ticket_group'] ?? $sale->ticket_group,
                'customer_activity' => $validated['customer_activity'] ?? $sale->customer_activity,
                'pickup_date' => $pickupDate,
                'appointment_at' => $validated['appointment_at'] ?? $sale->appointment_at,
                'delivery_address' => $validated['delivery_address'] ?? $sale->delivery_address,
                'order_status' => $validated['order_status'] ?? $sale->order_status,
                'notes' => $validated['notes'] ?? $sale->notes,
            ]);

            if (isset($validated['items'])) {
                $sale->items()->delete();

                foreach ($validated['items'] as $item) {
                    $article = Article::find($item['article_id']);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'article_id' => $item['article_id'],
                        'article_name' => $article->name,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'selected_options' => $item['selected_options'] ?? null,
                        'options_price' => ($item['options_price'] ?? 0) + ($item['variant_price'] ?? 0),
                        'discount_amount' => $item['discount_amount'] ?? 0,
                        'total' => 0,
                    ]);
                }
            }

            $sale->load('items');
            $sale->calculateTotals();
            $sale->save();
            $this->captureDeliveryCommissionIfNeeded($sale, $oldStatus === 'livree');

            if ($oldStatus !== $sale->order_status) {
                $this->addLog(
                    $sale,
                    'statut_commande_modifie',
                    $sale->order_status,
                    'Statut changé de '.$oldStatus.' vers '.$sale->order_status
                );
            } else {
                $this->addLog($sale, 'commande_modifiee', $sale->order_status, 'Commande modifiée');
            }

            $sale = $sale->fresh()->load([
                'customer',
                'user',
                'items.article',
                'payments',
                'deliveryAgent',
                'logs.user',
                'returns.article',
            ]);

            return response()->json($this->paymentWorkflow->decorateSale($sale));
        });
    }

    public function updateOrderStatus(Request $request, Sale $sale): JsonResponse
    {
        $validated = $request->validate([
            'order_status' => 'required|in:'.implode(',', self::ORDER_STATUSES),
            'comment' => 'nullable|string|max:1000',
        ]);

        $previousStatus = $sale->order_status;

        $sale->update([
            'order_status' => $validated['order_status'],
            'status' => $validated['order_status'] === 'annulee' ? 'cancelled' : $sale->status,
        ]);
        $this->captureDeliveryCommissionIfNeeded($sale, $previousStatus === 'livree');

        $this->addLog(
            $sale,
            'statut_commande_modifie',
            $validated['order_status'],
            $validated['comment'] ?? ('Statut changé de '.$previousStatus.' vers '.$validated['order_status'])
        );

        $sale = $sale->fresh()->load(['customer', 'items.article', 'payments', 'logs.user', 'deliveryAgent']);

        return response()->json($this->paymentWorkflow->decorateSale($sale));
    }

    public function journal(Sale $sale): JsonResponse
    {
        $logs = $sale->logs()->with('user')->latest()->get();

        return response()->json($logs);
    }

    public function returns(Sale $sale): JsonResponse
    {
        $returns = $sale->returns()->with(['article', 'user', 'saleItem'])->latest()->get();

        return response()->json($returns);
    }

    public function storeReturn(Request $request, Sale $sale): JsonResponse
    {
        $validated = $request->validate([
            'returns' => 'required|array|min:1',
            'returns.*.sale_item_id' => 'required|exists:sale_items,id',
            'returns.*.quantity' => 'required|numeric|min:0.001',
            'returns.*.condition' => 'required|in:bon_etat,endommage',
            'returns.*.reintegrate_stock' => 'boolean',
            'returns.*.note' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($sale, $validated) {
            $sale->load(['items.article', 'returns']);
            $createdReturns = [];

            foreach ($validated['returns'] as $line) {
                /** @var SaleItem|null $saleItem */
                $saleItem = $sale->items->firstWhere('id', $line['sale_item_id']);

                if (! $saleItem) {
                    return response()->json(['message' => 'Article de commande invalide'], 422);
                }

                $alreadyReturned = (float) $sale->returns
                    ->where('sale_item_id', $saleItem->id)
                    ->sum('quantity');

                $remainingReturnable = (float) $saleItem->quantity - $alreadyReturned;
                $returnQty = (float) $line['quantity'];

                if ($returnQty > $remainingReturnable) {
                    return response()->json([
                        'message' => "Quantité retournée invalide pour {$saleItem->article_name}",
                    ], 422);
                }

                $return = SaleItemReturn::create([
                    'sale_id' => $sale->id,
                    'sale_item_id' => $saleItem->id,
                    'article_id' => $saleItem->article_id,
                    'user_id' => auth()->id(),
                    'quantity' => $returnQty,
                    'condition' => $line['condition'],
                    'reintegrate_stock' => (bool) ($line['reintegrate_stock'] ?? false),
                    'note' => $line['note'] ?? null,
                ]);

                if (
                    $return->reintegrate_stock
                    && $return->condition === 'bon_etat'
                    && $saleItem->article
                    && $saleItem->article->manage_stock
                ) {
                    StockMovement::record(
                        $saleItem->article,
                        'return',
                        max(1, (int) round($returnQty)),
                        'Retour commande #'.($sale->order_number ?? $sale->reference),
                        auth()->id(),
                        $sale->id
                    );
                }

                $createdReturns[] = $return;
            }

            $sale->update([
                'order_status' => 'retournee',
                'status' => 'refunded',
            ]);

            $this->addLog(
                $sale,
                'retour',
                'retournee',
                'Retour de marchandise enregistré'
            );

            $loadedReturns = SaleItemReturn::with(['article', 'user', 'saleItem'])
                ->whereIn('id', collect($createdReturns)->pluck('id'))
                ->get();

            return response()->json([
                'returns' => $loadedReturns,
                'sale' => $this->paymentWorkflow->decorateSale(
                    $sale->fresh()->load(['customer', 'items.article', 'payments', 'logs.user', 'returns.article'])
                ),
            ], 201);
        });
    }

    public function complete(Sale $sale): JsonResponse
    {
        if ($sale->status !== 'pending') {
            return response()->json(['message' => 'Sale is not pending'], 422);
        }

        $totalCommitted = (float) $sale->payments()->sum('amount');
        if ($totalCommitted < (float) $sale->total) {
            return response()->json(['message' => 'Sale is not fully covered by payments'], 422);
        }

        return DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                if ($item->article && $item->article->manage_stock) {
                    StockMovement::record(
                        $item->article,
                        'out',
                        (int) $item->quantity,
                        'Vente #'.$sale->reference,
                        auth()->id(),
                        $sale->id
                    );
                }
            }

            $sale->update([
                'status' => 'completed',
                'order_status' => 'livree',
            ]);
            $this->captureDeliveryCommissionIfNeeded($sale);

            $this->addLog($sale, 'livraison', 'livree', 'Commande livrée');

            return response()->json(
                $this->paymentWorkflow->decorateSale(
                    $sale->load(['customer', 'items.article', 'payments', 'logs.user', 'returns.article', 'deliveryAgent'])
                )
            );
        });
    }

    public function cancel(Sale $sale): JsonResponse
    {
        if ($sale->status === 'cancelled') {
            return response()->json(['message' => 'Sale is already cancelled'], 422);
        }

        if ($sale->status === 'completed') {
            foreach ($sale->items as $item) {
                if ($item->article && $item->article->manage_stock) {
                    StockMovement::record(
                        $item->article,
                        'return',
                        (int) $item->quantity,
                        'Annulation vente #'.$sale->reference,
                        auth()->id(),
                        $sale->id
                    );
                }
            }
        }

        $sale->update([
            'status' => 'cancelled',
            'order_status' => 'annulee',
        ]);

        $this->addLog($sale, 'commande_annulee', 'annulee', 'Commande annulée');

        return response()->json(
            $this->paymentWorkflow->decorateSale(
                $sale->load(['customer', 'items.article', 'payments', 'logs.user', 'returns.article', 'deliveryAgent'])
            )
        );
    }

    public function destroy(Sale $sale): JsonResponse
    {
        if ($sale->status === 'completed') {
            return response()->json(['message' => 'Cannot delete a completed sale'], 422);
        }

        $sale->delete();

        return response()->json(null, 204);
    }

    private function addLog(Sale $sale, string $action, ?string $status = null, ?string $comment = null): void
    {
        SaleLog::create([
            'sale_id' => $sale->id,
            'user_id' => auth()->id(),
            'status' => $status,
            'action' => $action,
            'comment' => $comment,
        ]);
    }

    private function captureDeliveryCommissionIfNeeded(Sale $sale, bool $wasAlreadyDelivered = false): void
    {
        if ($sale->order_status !== 'livree' && ! $wasAlreadyDelivered) {
            return;
        }

        if (
            $wasAlreadyDelivered
            && $sale->delivery_commission_calculated_at
            && $sale->delivery_commission_amount !== null
        ) {
            return;
        }

        $this->deliveryCommissionService->captureSnapshot($sale, true);
    }
}
