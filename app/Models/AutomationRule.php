<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutomationRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'condition_type',
        'condition_data',
        'task_subject',
        'task_description',
        'task_priority',
        'assigned_to_employee_id',
        'assigned_to_role',
        'is_repeatable',
        'repeat_interval',
        'last_triggered_at',
        'execution_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_repeatable' => 'boolean',
        'condition_data' => 'array',
        'last_triggered_at' => 'datetime',
        'execution_count' => 'integer',
    ];

    /**
     * Get the employee this rule is assigned to.
     */
    public function assignedToEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to_employee_id');
    }

    /**
     * Check if rule should be triggered
     */
    public function shouldTrigger(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check repeat interval
        if ($this->is_repeatable && $this->last_triggered_at) {
            $lastTriggered = $this->last_triggered_at;
            $now = now();

            switch ($this->repeat_interval) {
                case 'daily':
                    if ($lastTriggered->addDay() > $now) {
                        return false;
                    }
                    break;
                case 'weekly':
                    if ($lastTriggered->addWeek() > $now) {
                        return false;
                    }
                    break;
                case 'monthly':
                    if ($lastTriggered->addMonth() > $now) {
                        return false;
                    }
                    break;
            }
        }

        return $this->evaluateCondition();
    }

    /**
     * Evaluate if the condition is met
     */
    public function evaluateCondition(): bool
    {
        switch ($this->condition_type) {
            case 'stock_level':
                return $this->checkStockLevel();
            case 'sales_threshold':
                return $this->checkSalesThreshold();
            case 'production_event':
                return $this->checkProductionEvent();
            case 'time_based':
                return $this->checkTimeBased();
            case 'custom':
                return true; // Custom conditions are manually evaluated
            default:
                return false;
        }
    }

    /**
     * Check stock level condition
     */
    private function checkStockLevel(): bool
    {
        if (!isset($this->condition_data['article_id']) || !isset($this->condition_data['minimum_stock'])) {
            return false;
        }

        $article = Article::find($this->condition_data['article_id']);
        if (!$article) {
            return false;
        }

        // Get current stock
        $currentStock = $article->stock ?? 0;
        $minimumStock = $this->condition_data['minimum_stock'];

        return $currentStock < $minimumStock;
    }

    /**
     * Check sales threshold condition
     */
    private function checkSalesThreshold(): bool
    {
        if (!isset($this->condition_data['article_id']) || !isset($this->condition_data['sales_threshold'])) {
            return false;
        }

        $article = Article::find($this->condition_data['article_id']);
        if (!$article) {
            return false;
        }

        // Count sales in last period (default: today)
        $period = $this->condition_data['period'] ?? 'today';
        $query = $article->saleItems();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
        }

        $totalSales = $query->sum('quantity') ?? 0;

        return $totalSales > $this->condition_data['sales_threshold'];
    }

    /**
     * Check production event condition
     */
    private function checkProductionEvent(): bool
    {
        // Check if there are recently completed productions
        if (isset($this->condition_data['article_id'])) {
            $recentProduction = ProductionEntry::where('article_id', $this->condition_data['article_id'])
                ->where('status', 'completed')
                ->where('updated_at', '>=', now()->subHour())
                ->exists();
            
            return $recentProduction;
        }

        return false;
    }

    /**
     * Check time-based condition
     */
    private function checkTimeBased(): bool
    {
        $trigger = $this->condition_data['trigger'] ?? null;

        switch ($trigger) {
            case 'end_of_day':
                // Check if it's after 5 PM
                return now()->hour >= 17;
            case 'start_of_day':
                // Check if it's before 9 AM
                return now()->hour < 9;
            default:
                return false;
        }
    }

    /**
     * Create a task from this rule
     */
    public function createTask(): Task
    {
        $task = Task::create([
            'subject' => $this->task_subject,
            'description' => $this->task_description,
            'due_date' => now()->toDateString(),
            'due_time' => '09:00',
            'priority' => $this->task_priority,
            'status' => 'en_attente',
            'employee_id' => $this->assigned_to_employee_id,
            'created_by_rule' => true,
            'automation_rule_id' => $this->id,
        ]);

        // Update rule execution tracking
        $this->update([
            'last_triggered_at' => now(),
            'execution_count' => $this->execution_count + 1,
        ]);

        return $task;
    }
}
