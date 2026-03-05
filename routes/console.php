<?php

use App\Services\TaskAutomationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tasks:run-automation', function () {
    $result = app(TaskAutomationService::class)->runAll();

    $this->info('Task automation executed.');
    $this->line("Low stock created: {$result['low_stock_created']}");
    $this->line("Low stock resolved: {$result['low_stock_resolved']}");
    $this->line("Recurring created: {$result['recurring_created']}");
})->purpose('Generate automated tasks (low stock + recurrence)');

Schedule::command('tasks:run-automation')->hourly();
