<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request): JsonResponse
    {
        $fromDate = $request->get('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->get('to_date', Carbon::now()->toDateString());

        $query = Sale::completed()
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);

        // Summary
        $summary = [
            'total_sales' => $query->count(),
            'total_revenue' => $query->sum('total'),
            'total_tax' => $query->sum('tax_amount'),
            'total_discount' => $query->sum('discount_amount'),
            'average_sale' => $query->avg('total') ?? 0,
        ];

        // Daily breakdown
        $dailySales = Sale::completed()
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total'),
                DB::raw('SUM(tax_amount) as tax'),
                DB::raw('SUM(discount_amount) as discount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
            'summary' => $summary,
            'daily' => $dailySales,
        ]);
    }

    public function articles(Request $request): JsonResponse
    {
        $fromDate = $request->get('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->get('to_date', Carbon::now()->toDateString());

        $topArticles = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('articles', 'sale_items.article_id', '=', 'articles.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->select(
                'articles.id',
                'articles.name',
                'articles.sell_price',
                DB::raw('SUM(sale_items.quantity) as quantity_sold'),
                DB::raw('SUM(sale_items.total) as revenue'),
                DB::raw('COUNT(DISTINCT sale_items.sale_id) as sale_count')
            )
            ->groupBy('articles.id', 'articles.name', 'articles.sell_price')
            ->orderByDesc('revenue')
            ->limit($request->get('limit', 20))
            ->get();

        $totalRevenue = $topArticles->sum('revenue') ?: 1;

        return response()->json([
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
            'articles' => $topArticles->map(function ($article) use ($totalRevenue) {
                return [
                    'id' => $article->id,
                    'name' => $article->name,
                    'sell_price' => $article->sell_price,
                    'quantity_sold' => $article->quantity_sold,
                    'revenue' => $article->revenue,
                    'sale_count' => $article->sale_count,
                    'percentage' => round($article->revenue / $totalRevenue * 100, 1),
                ];
            }),
        ]);
    }

    public function categories(Request $request): JsonResponse
    {
        $fromDate = $request->get('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->get('to_date', Carbon::now()->toDateString());

        $categoryStats = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('articles', 'sale_items.article_id', '=', 'articles.id')
            ->join('categories', 'articles.category_id', '=', 'categories.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->select(
                'categories.id',
                'categories.name',
                'categories.color',
                DB::raw('SUM(sale_items.quantity) as quantity_sold'),
                DB::raw('SUM(sale_items.total) as revenue'),
                DB::raw('COUNT(DISTINCT sale_items.sale_id) as sale_count')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderByDesc('revenue')
            ->get();

        $totalRevenue = $categoryStats->sum('revenue') ?: 1;

        return response()->json([
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
            'categories' => $categoryStats->map(function ($category) use ($totalRevenue) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color' => $category->color,
                    'quantity_sold' => $category->quantity_sold,
                    'revenue' => $category->revenue,
                    'sale_count' => $category->sale_count,
                    'percentage' => round($category->revenue / $totalRevenue * 100, 1),
                ];
            }),
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $fromDate = $request->get('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->get('to_date', Carbon::now()->toDateString());

        $paymentStats = Payment::join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('payments.created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->select(
                'payments.payment_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(payments.amount) as total')
            )
            ->groupBy('payments.payment_type')
            ->orderByDesc('total')
            ->get();

        $totalAmount = $paymentStats->sum('total') ?: 1;

        $paymentLabels = [
            'cash' => 'Espèces',
            'card' => 'Carte',
            'check' => 'Chèque',
            'mobile' => 'Mobile',
            'other' => 'Autre',
        ];

        return response()->json([
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
            'payments' => $paymentStats->map(function ($payment) use ($totalAmount, $paymentLabels) {
                return [
                    'type' => $payment->payment_type,
                    'label' => $paymentLabels[$payment->payment_type] ?? $payment->payment_type,
                    'count' => $payment->count,
                    'total' => $payment->total,
                    'percentage' => round($payment->total / $totalAmount * 100, 1),
                ];
            }),
        ]);
    }

    public function daily(Request $request): JsonResponse
    {
        $date = $request->get('date', Carbon::today()->toDateString());

        // Today's sales
        $sales = Sale::with(['customer', 'items', 'payments'])
            ->whereDate('created_at', $date)
            ->get();

        $completedSales = $sales->where('status', 'completed');

        // Hourly breakdown
        $hourlyStats = Sale::completed()
            ->whereDate('created_at', $date)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $hourlyData = [];
        for ($h = 0; $h < 24; $h++) {
            $hourlyData[] = [
                'hour' => sprintf('%02d:00', $h),
                'count' => $hourlyStats[$h]->count ?? 0,
                'total' => $hourlyStats[$h]->total ?? 0,
            ];
        }

        return response()->json([
            'date' => $date,
            'summary' => [
                'total_sales' => $completedSales->count(),
                'total_revenue' => $completedSales->sum('total'),
                'pending_sales' => $sales->where('status', 'pending')->count(),
                'cancelled_sales' => $sales->where('status', 'cancelled')->count(),
            ],
            'hourly' => $hourlyData,
            'recent_sales' => $sales->take(10)->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'reference' => $sale->reference,
                    'customer' => $sale->customer?->name ?? 'Client Anonyme',
                    'total' => $sale->total,
                    'status' => $sale->status,
                    'time' => $sale->created_at->format('H:i'),
                ];
            }),
        ]);
    }
}
