<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Services\CustomListService;
use App\Services\SalePaymentWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeliveryAgentController extends Controller
{
    public function __construct(
        private readonly SalePaymentWorkflowService $paymentWorkflow,
        private readonly CustomListService $customListService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $this->filteredQuery($request)
            ->withCount([
                'sales as delivered_orders_count' => fn ($sales) => $sales->where('order_status', 'livree'),
            ])
            ->orderBy('name');

        $paginate = $request->boolean('paginate', true);
        $agents = $paginate
            ? $query->paginate($request->integer('per_page', 20))
            : $query->get();

        return response()->json($agents);
    }

    public function store(Request $request): JsonResponse
    {
        $deliveryAgent = DeliveryAgent::create($this->validatedPayload($request));

        if ($deliveryAgent->type === 'platform' && $deliveryAgent->platform_name) {
            $this->customListService->syncAllPlatformServiceModes();
        }

        return response()->json($deliveryAgent, 201);
    }

    public function show(DeliveryAgent $deliveryAgent): JsonResponse
    {
        $recentSales = $deliveryAgent->sales()
            ->with(['customer', 'payments'])
            ->latest()
            ->limit(10)
            ->get();

        $summary = $this->buildSummary($recentSales);

        return response()->json([
            'delivery_agent' => $deliveryAgent,
            'summary' => $summary,
            'recent_sales' => $recentSales->map(fn ($sale) => $this->paymentWorkflow->decorateSale($sale)),
        ]);
    }

    public function update(Request $request, DeliveryAgent $deliveryAgent): JsonResponse
    {
        $previousType = $deliveryAgent->type;
        $previousPlatformName = $deliveryAgent->platform_name;
        $deliveryAgent->update($this->validatedPayload($request, $deliveryAgent));

        if (($previousType === 'platform' && $previousPlatformName)
            || ($deliveryAgent->type === 'platform' && $deliveryAgent->platform_name)) {
            $this->customListService->syncAllPlatformServiceModes();
        }

        return response()->json($deliveryAgent->fresh());
    }

    public function deactivate(DeliveryAgent $deliveryAgent): JsonResponse
    {
        $shouldSyncPlatforms = $deliveryAgent->type === 'platform' && $deliveryAgent->platform_name;

        $deliveryAgent->update([
            'status' => 'inactive',
            'active' => false,
        ]);

        if ($shouldSyncPlatforms) {
            $this->customListService->syncAllPlatformServiceModes();
        }

        return response()->json([
            'message' => 'Livreur désactivé avec succès.',
            'delivery_agent' => $deliveryAgent->fresh(),
        ]);
    }

    public function destroy(DeliveryAgent $deliveryAgent): JsonResponse
    {
        $shouldSyncPlatforms = $deliveryAgent->type === 'platform' && $deliveryAgent->platform_name;
        $deletedAgent = $deliveryAgent->replicate();
        $deletedAgent->id = $deliveryAgent->id;

        $deliveryAgent->delete();

        if ($shouldSyncPlatforms) {
            $this->customListService->syncAllPlatformServiceModes();
        }

        return response()->json([
            'message' => 'Livreur supprimé avec succès.',
            'delivery_agent' => $deletedAgent,
        ]);
    }

    public function report(Request $request): JsonResponse
    {
        $agents = $this->filteredQuery($request)
            ->orderBy('name')
            ->get();

        $rows = $agents->map(function (DeliveryAgent $deliveryAgent) use ($request) {
            $sales = $deliveryAgent->sales()
                ->with(['payments'])
                ->where('order_status', 'livree')
                ->when(
                    $request->filled('from_date'),
                    fn ($query) => $query->whereDate('delivery_commission_calculated_at', '>=', $request->from_date)
                )
                ->when(
                    $request->filled('to_date'),
                    fn ($query) => $query->whereDate('delivery_commission_calculated_at', '<=', $request->to_date)
                )
                ->latest()
                ->get();

            $summary = $this->buildSummary($sales);

            return [
                'delivery_agent_id' => $deliveryAgent->id,
                'name' => $deliveryAgent->name,
                'display_name' => $deliveryAgent->display_name,
                'type' => $deliveryAgent->type,
                'phone' => $deliveryAgent->phone,
                'platform_name' => $deliveryAgent->platform_name,
                'status' => $deliveryAgent->status,
                'active' => $deliveryAgent->active,
                ...$summary,
            ];
        })->values();

        return response()->json([
            'rows' => $rows,
            'totals' => [
                'agents_count' => $rows->count(),
                'orders_count' => $rows->sum('orders_count'),
                'total_delivery_amount' => round((float) $rows->sum('total_delivery_amount'), 2),
                'total_commission_amount' => round((float) $rows->sum('total_commission_amount'), 2),
                'total_collected_amount' => round((float) $rows->sum('total_collected_amount'), 2),
                'total_remaining_amount' => round((float) $rows->sum('total_remaining_amount'), 2),
            ],
        ]);
    }

    private function filteredQuery(Request $request)
    {
        return DeliveryAgent::query()
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when(
                $request->has('active') && $request->active !== '',
                fn ($query) => $query->where('active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN))
            )
            ->when($request->filled('platform_name'), fn ($query) => $query->where('platform_name', $request->platform_name))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('platform_name', 'like', "%{$search}%");
                });
            });
    }

    private function validatedPayload(Request $request, ?DeliveryAgent $deliveryAgent = null): array
    {
        $validated = $request->validate([
            'name' => [$deliveryAgent ? 'sometimes' : 'required', 'string', 'max:255'],
            'type' => [$deliveryAgent ? 'sometimes' : 'required', Rule::in(['internal', 'platform'])],
            'phone' => 'nullable|string|max:50',
            'commission_type' => [$deliveryAgent ? 'sometimes' : 'required', Rule::in(['percentage', 'fixed'])],
            'commission_value' => [$deliveryAgent ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'platform_name' => 'nullable|string|max:255|required_if:type,platform',
            'active' => 'nullable|boolean',
        ]);

        $type = $validated['type'] ?? $deliveryAgent?->type;
        if ($type === 'internal') {
            $validated['platform_name'] = null;
        }

        $isActive = array_key_exists('active', $validated)
            ? (bool) $validated['active']
            : (($validated['status'] ?? $deliveryAgent?->status ?? 'active') === 'active');

        $validated['active'] = $isActive;
        $validated['status'] = $isActive ? 'active' : 'inactive';

        return $validated;
    }

    private function buildSummary($sales): array
    {
        $ordersCount = $sales->count();
        $totalDeliveryAmount = round((float) $sales->sum('total'), 2);
        $totalCommissionAmount = round((float) $sales->sum('delivery_commission_amount'), 2);
        $totalCollectedAmount = 0.0;
        $totalRemainingAmount = 0.0;

        foreach ($sales as $sale) {
            $summary = $this->paymentWorkflow->computeSaleSummary($sale);
            $totalRemainingAmount += (float) ($summary['remaining_amount'] ?? 0);
            $totalCollectedAmount += (float) ($summary['paid_confirmed_amount'] ?? 0) + (float) ($summary['pending_collection_amount'] ?? 0);
        }

        return [
            'orders_count' => $ordersCount,
            'total_delivery_amount' => $totalDeliveryAmount,
            'total_commission_amount' => $totalCommissionAmount,
            'total_collected_amount' => round($totalCollectedAmount, 2),
            'total_remaining_amount' => round($totalRemainingAmount, 2),
        ];
    }
}
