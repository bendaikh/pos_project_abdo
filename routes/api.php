<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\OptionController;
use App\Http\Controllers\Api\OptionVariantController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\ProductionEntryController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\Api\MaterialConsumptionController;
use App\Http\Controllers\Api\LossController;
use App\Http\Controllers\Api\AutomationRuleController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\TaskController;
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
    Route::get('/options/{option}/variants', [OptionVariantController::class, 'index']);
    Route::post('/options/{option}/variants', [OptionVariantController::class, 'store']);
    Route::get('/options/{option}/variants/{variant}', [OptionVariantController::class, 'show']);
    Route::put('/options/{option}/variants/{variant}', [OptionVariantController::class, 'update']);
    Route::delete('/options/{option}/variants/{variant}', [OptionVariantController::class, 'destroy']);

    // Articles
    Route::get('/articles/favorites', [ArticleController::class, 'favorites']);
    Route::get('/articles/low-stock', [ArticleController::class, 'lowStock']);
    Route::apiResource('articles', ArticleController::class);
    // Article Variants
    Route::get('/articles/{article}/variants', [ArticleController::class, 'listVariants']);
    Route::post('/articles/{article}/variants', [ArticleController::class, 'createVariant']);
    Route::get('/articles/{article}/variants/{variantId}', [ArticleController::class, 'getVariant']);
    Route::put('/articles/{article}/variants/{variantId}', [ArticleController::class, 'updateVariant']);
    Route::delete('/articles/{article}/variants/{variantId}', [ArticleController::class, 'deleteVariant']);

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
    Route::get('/employees/{employee}/payroll-history', [PayrollController::class, 'employeeHistory']);
    Route::get('/employees/{employee}/attendance-summary', [PayrollController::class, 'attendanceSummary']);

    // Payroll
    Route::apiResource('payrolls', PayrollController::class);
    Route::post('/payrolls/{payroll}/process-payment', [PayrollController::class, 'processPayment']);
    Route::get('/payroll-statistics', [PayrollController::class, 'statistics']);
    Route::post('/payroll-preview', [PayrollController::class, 'preview']);
    Route::post('/payroll-bulk-calculate', [PayrollController::class, 'bulkCalculate']);

    // Attendance
    Route::apiResource('attendances', AttendanceController::class);
    Route::get('/attendances/summary/monthly', [AttendanceController::class, 'monthlySummary']);
    Route::post('/attendances/bulk', [AttendanceController::class, 'bulk']);

    // Stock
    Route::get('/stock', [StockController::class, 'index']);
    Route::get('/stock/movements', [StockController::class, 'movements']);
    Route::post('/stock/movement', [StockController::class, 'recordMovement']);
    Route::get('/stock/alerts', [StockController::class, 'alerts']);

    // Losses
    Route::get('/losses/reference', [LossController::class, 'reference']);
    Route::get('/losses', [LossController::class, 'index']);
    Route::post('/losses', [LossController::class, 'store']);

    // Production
    Route::get('/production', [ProductionEntryController::class, 'index']);
    Route::post('/production', [ProductionEntryController::class, 'store']);
    Route::get('/production/{productionEntry}', [ProductionEntryController::class, 'show']);
    Route::put('/production/{productionEntry}', [ProductionEntryController::class, 'update']);
    Route::delete('/production/{productionEntry}', [ProductionEntryController::class, 'destroy']);
    Route::post('/production/{productionEntry}/validate', [ProductionEntryController::class, 'validateEntry']);

    // Material Consumption
    Route::get('/consumptions', [MaterialConsumptionController::class, 'index']);
    Route::post('/consumptions', [MaterialConsumptionController::class, 'store']);

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

    // Appointments
    Route::get('/appointments/upcoming', [AppointmentController::class, 'upcoming']);
    Route::get('/appointments/statistics', [AppointmentController::class, 'statistics']);
    Route::get('/appointments/needing-reminders', [AppointmentController::class, 'needingReminders']);
    Route::post('/appointments/{appointment}/mark-reminder-sent', [AppointmentController::class, 'markReminderSent']);
    Route::apiResource('appointments', AppointmentController::class);

    // Tasks
    Route::get('/tasks/pending', [TaskController::class, 'pending']);
    Route::get('/tasks/overdue', [TaskController::class, 'overdue']);
    Route::get('/tasks/statistics', [TaskController::class, 'statistics']);
    Route::get('/tasks/needing-reminders', [TaskController::class, 'needingReminders']);
    Route::post('/tasks/run-automation', [TaskController::class, 'runAutomation']);
    Route::post('/tasks/{task}/mark-completed', [TaskController::class, 'markCompleted']);
    Route::post('/tasks/{task}/mark-reminder-sent', [TaskController::class, 'markReminderSent']);
    Route::delete('/tasks/{task}/attachments', [TaskController::class, 'deleteAttachment']);
    Route::apiResource('tasks', TaskController::class);

    // Automation Rules
    Route::get('/automation-rules/articles', [AutomationRuleController::class, 'getArticles']);
    Route::get('/automation-rules/employees', [AutomationRuleController::class, 'getEmployees']);
    Route::post('/automation-rules/{automationRule}/trigger', [AutomationRuleController::class, 'trigger']);
    Route::apiResource('automation-rules', AutomationRuleController::class);
});
