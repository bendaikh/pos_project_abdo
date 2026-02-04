<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\OptionController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\SubcategoryController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/sales-chart', [DashboardController::class, 'salesChart']);
    Route::get('/dashboard/top-categories', [DashboardController::class, 'topCategories']);
    Route::get('/dashboard/recent-sales', [DashboardController::class, 'recentSales']);
    Route::get('/dashboard/low-stock', [DashboardController::class, 'lowStock']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Subcategories
    Route::apiResource('subcategories', SubcategoryController::class);

    // Options
    Route::apiResource('options', OptionController::class);

    // Articles
    Route::get('/articles/favorites', [ArticleController::class, 'favorites']);
    Route::get('/articles/low-stock', [ArticleController::class, 'lowStock']);
    Route::apiResource('articles', ArticleController::class);

    // Sales
    Route::get('/sales/pending', [SaleController::class, 'pending']);
    Route::post('/sales/{sale}/complete', [SaleController::class, 'complete']);
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel']);
    Route::apiResource('sales', SaleController::class);

    // Payments
    Route::post('/sales/{sale}/payments', [PaymentController::class, 'store']);
    Route::get('/payments', [PaymentController::class, 'index']);

    // Customers
    Route::get('/customers/{customer}/history', [CustomerController::class, 'history']);
    Route::apiResource('customers', CustomerController::class);

    // Employees
    Route::apiResource('employees', EmployeeController::class);

    // Stock
    Route::get('/stock', [StockController::class, 'index']);
    Route::get('/stock/movements', [StockController::class, 'movements']);
    Route::post('/stock/movement', [StockController::class, 'recordMovement']);
    Route::get('/stock/alerts', [StockController::class, 'alerts']);

    // Settings
    Route::get('/settings', [SettingController::class, 'index']);
    Route::get('/settings/{group}', [SettingController::class, 'byGroup']);
    Route::put('/settings', [SettingController::class, 'update']);

    // Reports
    Route::get('/reports/sales', [ReportController::class, 'sales']);
    Route::get('/reports/articles', [ReportController::class, 'articles']);
    Route::get('/reports/categories', [ReportController::class, 'categories']);
    Route::get('/reports/payments', [ReportController::class, 'payments']);
    Route::get('/reports/daily', [ReportController::class, 'daily']);
});
