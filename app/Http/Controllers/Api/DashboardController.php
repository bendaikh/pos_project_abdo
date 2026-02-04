<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Today's sales
        $todaySales = Sale::completed()->today()->sum('total');
        $yesterdaySales = Sale::completed()->whereDate('created_at', $yesterday)->sum('total');
        $salesChange = $yesterdaySales > 0 
            ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1) 
            : 0;

        // Low stock alerts
        $lowStockCount = Article::lowStock()->count();

        // Today's transactions
        $todayTransactions = Sale::completed()->today()->count();
        $pendingOrders = Sale::pending()->count();

        // New customers today
        $newCustomersToday = Customer::whereDate('created_at', $today)->count();

        return response()->json([
            'today_sales' => [
                'amount' => $todaySales,
                'change_percent' => $salesChange,
            ],
            'low_stock' => [
                'count' => $lowStockCount,
                'status' => $lowStockCount > 5 ? 'critical' : ($lowStockCount > 0 ? 'warning' : 'ok'),
            ],
            'transactions' => [
                'total' => $todayTransactions,
                'pending' => $pendingOrders,
            ],
            'new_customers' => [
                'count' => $newCustomersToday,
            ],
        ]);
    }

    public function salesChart(Request $request): JsonResponse
    {
        $days = $request->get('days', 7);
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Current period
        $currentPeriodSales = Sale::completed()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Previous period for comparison
        $previousStart = Carbon::now()->subDays($days * 2 - 1)->startOfDay();
        $previousEnd = Carbon::now()->subDays($days)->endOfDay();

        $previousPeriodSales = Sale::completed()
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Build chart data
        $labels = [];
        $currentData = [];
        $previousData = [];

        for ($i = 0; $i < $days; $i++) {
            $currentDate = Carbon::now()->subDays($days - 1 - $i)->format('Y-m-d');
            $previousDate = Carbon::now()->subDays($days * 2 - 1 - $i)->format('Y-m-d');
            
            $labels[] = Carbon::parse($currentDate)->locale('fr')->isoFormat('ddd');
            $currentData[] = $currentPeriodSales[$currentDate]->total ?? 0;
            $previousData[] = $previousPeriodSales[$previousDate]->total ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Cette semaine',
                    'data' => $currentData,
                ],
                [
                    'label' => 'Semaine précédente',
                    'data' => $previousData,
                ],
            ],
        ]);
    }

    public function topCategories(): JsonResponse
    {
        $topCategories = Category::withCount(['articles as sales_count' => function ($query) {
            $query->join('sale_items', 'articles.id', '=', 'sale_items.article_id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.status', 'completed')
                ->whereMonth('sales.created_at', now()->month);
        }])
        ->withSum(['articles as revenue' => function ($query) {
            $query->join('sale_items', 'articles.id', '=', 'sale_items.article_id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.status', 'completed')
                ->whereMonth('sales.created_at', now()->month);
        }], 'sale_items.total')
        ->orderByDesc('revenue')
        ->take(4)
        ->get();

        $totalRevenue = $topCategories->sum('revenue') ?: 1;

        return response()->json($topCategories->map(function ($category) use ($totalRevenue) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color,
                'icon' => $category->icon,
                'revenue' => $category->revenue ?? 0,
                'percentage' => round(($category->revenue ?? 0) / $totalRevenue * 100),
            ];
        }));
    }

    public function recentSales(): JsonResponse
    {
        $recentSales = Sale::with(['customer', 'user'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'reference' => $sale->reference,
                    'customer' => $sale->customer?->name ?? 'Client Anonyme',
                    'date' => $sale->created_at->format('Y-m-d H:i'),
                    'date_formatted' => $sale->created_at->isToday() 
                        ? "Aujourd'hui, " . $sale->created_at->format('H:i')
                        : ($sale->created_at->isYesterday() 
                            ? "Hier, " . $sale->created_at->format('H:i')
                            : $sale->created_at->format('d/m/Y, H:i')),
                    'total' => $sale->total,
                    'status' => $sale->status,
                    'payment_status' => $sale->payment_status,
                ];
            });

        return response()->json($recentSales);
    }

    public function lowStock(): JsonResponse
    {
        $lowStockArticles = Article::lowStock()
            ->with('category')
            ->orderBy('stock_quantity')
            ->take(10)
            ->get()
            ->map(function ($article) {
                return [
                    'id' => $article->id,
                    'name' => $article->name,
                    'category' => $article->category?->name,
                    'stock_quantity' => $article->stock_quantity,
                    'stock_alert_threshold' => $article->stock_alert_threshold,
                    'status' => $article->stock_quantity == 0 ? 'out_of_stock' : 'low',
                ];
            });

        return response()->json($lowStockArticles);
    }
}
