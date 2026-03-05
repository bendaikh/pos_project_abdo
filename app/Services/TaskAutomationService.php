<?php

namespace App\Services;

use App\Models\Article;
use App\Models\AutomationRule;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TaskAutomationService
{
    public function runAll(): array
    {
        $lowStock = $this->syncLowStockTasks();
        $automationRules = $this->triggerAutomationRules();
        $recurringCreated = $this->generateRecurringTasks();

        return [
            'low_stock_created' => $lowStock['created'],
            'low_stock_resolved' => $lowStock['resolved'],
            'automation_rules_triggered' => $automationRules,
            'recurring_created' => $recurringCreated,
        ];
    }

    public function syncLowStockTasks(): array
    {
        // Check if tasks table exists before trying to access it
        if (!Schema::hasTable('tasks')) {
            return ['created' => 0, 'resolved' => 0];
        }

        if (!Schema::hasColumn('tasks', 'automation_type') || !Schema::hasColumn('tasks', 'source_article_id')) {
            return ['created' => 0, 'resolved' => 0];
        }

        $enabled = (bool) $this->setting('task_automation_low_stock_enabled', true);
        if (!$enabled) {
            return ['created' => 0, 'resolved' => 0];
        }

        $mode = (string) $this->setting('task_automation_low_stock_mode', 'threshold_or_below');
        $fixedThreshold = (int) $this->setting('task_automation_low_stock_fixed_threshold', 5);
        $configuredPriority = (string) $this->setting('task_automation_low_stock_priority', 'moyenne');
        $configuredPriority = in_array($configuredPriority, ['faible', 'moyenne', 'urgente'], true)
            ? $configuredPriority
            : 'moyenne';

        $assigneeId = $this->resolveConfiguredEmployeeId() ?? $this->resolveDefaultEmployeeId();

        if (!$assigneeId) {
            return ['created' => 0, 'resolved' => 0];
        }

        $query = Article::query()->where('manage_stock', true);
        if ($mode === 'out_of_stock_only') {
            $query->where('stock_quantity', '<=', 0);
        } elseif ($mode === 'fixed_global_threshold') {
            $query->where('stock_quantity', '<=', $fixedThreshold);
        } else {
            $query->whereColumn('stock_quantity', '<=', 'stock_alert_threshold');
        }

        $lowStockArticles = $query->get(['id', 'name', 'stock_quantity', 'stock_alert_threshold', 'unit']);

        $created = $this->createMissingLowStockTasks($lowStockArticles, $assigneeId, $configuredPriority);
        $resolved = $this->resolveRecoveredLowStockTasks($lowStockArticles->pluck('id')->all());

        return ['created' => $created, 'resolved' => $resolved];
    }

    /**
     * Trigger all active automation rules that meet their conditions
     */
    public function triggerAutomationRules(): int
    {
        // Check if automation_rules table exists
        if (!Schema::hasTable('automation_rules')) {
            return 0;
        }

        $triggeredCount = 0;

        $rules = AutomationRule::where('is_active', true)->get();

        foreach ($rules as $rule) {
            if ($rule->shouldTrigger()) {
                $this->executeAutomationRule($rule);
                $triggeredCount++;

                // Update the rule's last triggered time
                $rule->update([
                    'last_triggered_at' => now(),
                    'execution_count' => $rule->execution_count + 1,
                ]);
            }
        }

        return $triggeredCount;
    }

    /**
     * Execute an automation rule by creating the associated task
     */
    private function executeAutomationRule(AutomationRule $rule): void
    {
        $assigneeId = $rule->assigned_to_employee_id;
        if (!$assigneeId) {
            $assigneeId = $this->resolveDefaultEmployeeId();
        }

        if (!$assigneeId) {
            return;
        }

        Task::create([
            'due_date' => now()->toDateString(),
            'subject' => $rule->task_subject,
            'description' => $rule->task_description,
            'employee_id' => $assigneeId,
            'priority' => $rule->task_priority ?? 'moyenne',
            'status' => 'en_attente',
            'is_automated' => true,
            'automation_type' => $rule->condition_type,
            'automation_rule_id' => $rule->id,
            'created_by_rule' => true,
        ]);
    }

    public function generateRecurringTasks(): int
    {
        if (!Schema::hasColumn('tasks', 'recurrence_parent_id')) {
            return 0;
        }

        $templates = Task::query()
            ->whereNull('recurrence_parent_id')
            ->where('status', '!=', 'annule')
            ->where(function ($query) {
                $query->where('recurrence_enabled', true)
                    ->orWhereNotNull('recurrence_pattern')
                    ->orWhereNotNull('recurrence_frequency');
            })
            ->get();

        $created = 0;
        $today = now()->startOfDay();

        foreach ($templates as $template) {
            $pattern = $this->resolveRecurrencePattern($template);
            if (!$pattern) {
                continue;
            }

            $seriesCount = $this->seriesCount($template);
            $repeatCount = $template->recurrence_repeat_count;
            $endDate = $template->recurrence_end_date ?? $template->recurrence_until;
            $endDate = $endDate ? Carbon::parse($endDate)->startOfDay() : null;

            $latestInSeries = $this->latestTaskInSeries($template);
            $nextDate = $this->nextDueDate($latestInSeries->due_date, $pattern);

            while ($nextDate->lte($today)) {
                if ($endDate && $nextDate->gt($endDate)) {
                    break;
                }

                if ($repeatCount && $seriesCount >= $repeatCount) {
                    break;
                }

                if (!$this->seriesHasDate($template, $nextDate)) {
                    $this->createRecurringTaskFromTemplate($template, $nextDate, $pattern);
                    $created++;
                    $seriesCount++;
                }

                $nextDate = $this->nextDueDate($nextDate, $pattern);
            }

            $template->update(['last_generated_at' => now()]);
        }

        return $created;
    }

    private function createMissingLowStockTasks(Collection $lowStockArticles, int $assigneeId, string $configuredPriority): int
    {
        $created = 0;

        foreach ($lowStockArticles as $article) {
            $existingOpenTask = Task::query()
                ->where('automation_type', 'low_stock')
                ->where('source_article_id', $article->id)
                ->whereIn('status', ['en_attente', 'en_cours'])
                ->exists();

            if ($existingOpenTask) {
                continue;
            }

            Task::create([
                'due_date' => now()->toDateString(),
                'subject' => "Reapprovisionner: {$article->name}",
                'description' => sprintf(
                    "Stock actuel: %d %s. Seuil d'alerte: %d. Merci de lancer le reapprovisionnement.",
                    (int) $article->stock_quantity,
                    $article->unit ?? 'unite(s)',
                    (int) $article->stock_alert_threshold
                ),
                'employee_id' => $assigneeId,
                'priority' => (int) $article->stock_quantity === 0 ? 'urgente' : $configuredPriority,
                'status' => 'en_attente',
                'is_automated' => true,
                'automation_type' => 'low_stock',
                'source_article_id' => $article->id,
            ]);

            $created++;
        }

        return $created;
    }

    private function resolveRecoveredLowStockTasks(array $lowStockArticleIds): int
    {
        $query = Task::query()
            ->where('automation_type', 'low_stock')
            ->whereIn('status', ['en_attente', 'en_cours']);

        if (!empty($lowStockArticleIds)) {
            $query->whereNotIn('source_article_id', $lowStockArticleIds);
        }

        $tasksToClose = $query->get();

        foreach ($tasksToClose as $task) {
            $task->update([
                'status' => 'termine',
                'completed_at' => now(),
                'description' => trim(($task->description ?? '') . "\n\nAuto-cloturee: stock revenu au-dessus du seuil."),
            ]);
        }

        return $tasksToClose->count();
    }

    private function resolveConfiguredEmployeeId(): ?int
    {
        $configuredEmployeeId = (int) $this->setting('task_automation_default_employee_id', 0);
        if ($configuredEmployeeId <= 0) {
            return null;
        }

        return Employee::query()->whereKey($configuredEmployeeId)->value('id');
    }

    private function resolveDefaultEmployeeId(): ?int
    {
        return Employee::query()
            ->where('status', 'active')
            ->orderByRaw("CASE role WHEN 'manager' THEN 1 WHEN 'admin' THEN 2 WHEN 'vendor' THEN 3 WHEN 'cashier' THEN 4 ELSE 5 END")
            ->value('id')
            ?? Employee::query()->orderBy('id')->value('id');
    }

    private function latestTaskInSeries(Task $template): Task
    {
        return Task::query()
            ->where(function ($query) use ($template) {
                $query->where('id', $template->id)
                    ->orWhere('recurrence_parent_id', $template->id);
            })
            ->orderBy('due_date', 'desc')
            ->orderBy('id', 'desc')
            ->firstOrFail();
    }

    private function seriesHasDate(Task $template, Carbon $dueDate): bool
    {
        return Task::query()
            ->where(function ($query) use ($template) {
                $query->where('id', $template->id)
                    ->orWhere('recurrence_parent_id', $template->id);
            })
            ->whereDate('due_date', $dueDate->toDateString())
            ->exists();
    }

    private function seriesCount(Task $template): int
    {
        return Task::query()
            ->where(function ($query) use ($template) {
                $query->where('id', $template->id)
                    ->orWhere('recurrence_parent_id', $template->id);
            })
            ->count();
    }

    private function createRecurringTaskFromTemplate(Task $template, Carbon $dueDate, string $pattern): void
    {
        Task::create([
            'due_date' => $dueDate->toDateString(),
            'due_time' => $template->due_time,
            'subject' => $template->subject,
            'description' => $template->description,
            'employee_id' => $template->employee_id,
            'priority' => $template->priority,
            'status' => 'en_attente',
            'attachments' => null,
            'reminder_enabled' => $template->reminder_enabled,
            'reminder_channel' => $template->reminder_channel,
            'reminder_timing' => $template->reminder_timing,
            'reminder_custom_value' => $template->reminder_custom_value,
            'reminder_custom_unit' => $template->reminder_custom_unit,
            'reminder_repeat_until_validation' => $template->reminder_repeat_until_validation,
            'reminder_repeat_interval' => $template->reminder_repeat_interval,
            'created_by' => $template->created_by,
            'is_automated' => true,
            'automation_type' => 'recurring',
            'recurrence_enabled' => false,
            'recurrence_pattern' => null,
            'recurrence_start_date' => null,
            'recurrence_end_date' => null,
            'recurrence_repeat_count' => null,
            'recurrence_frequency' => in_array($pattern, ['weekly', 'monthly', 'quarterly'], true) ? $pattern : null,
            'recurrence_until' => null,
            'recurrence_parent_id' => $template->id,
        ]);
    }

    private function resolveRecurrencePattern(Task $template): ?string
    {
        if (!empty($template->recurrence_pattern)) {
            return $template->recurrence_pattern;
        }

        return match ($template->recurrence_frequency) {
            'weekly' => 'weekly',
            'monthly' => 'monthly',
            'quarterly' => 'quarterly',
            default => null,
        };
    }

    private function nextDueDate($date, string $pattern): Carbon
    {
        $baseDate = Carbon::parse($date)->startOfDay();

        return match ($pattern) {
            'daily' => $baseDate->addDay(),
            'weekly' => $baseDate->addWeek(),
            'monthly' => $baseDate->addMonth(),
            'quarterly' => $baseDate->addMonths(3),
            'semiannual' => $baseDate->addMonths(6),
            'yearly' => $baseDate->addYear(),
            default => $baseDate->addWeek(),
        };
    }

    private function setting(string $key, $default = null)
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}
