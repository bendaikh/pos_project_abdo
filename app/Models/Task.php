<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'due_date',
        'due_time',
        'subject',
        'description',
        'employee_id',
        'priority',
        'status',
        'attachments',
        'reminder_enabled',
        'reminder_channel',
        'reminder_timing',
        'reminder_custom_value',
        'reminder_custom_unit',
        'reminder_repeat_until_validation',
        'reminder_repeat_interval',
        'reminder_sent_at',
        'completed_at',
        'created_by',
        'automation_rule_id',
        'created_by_rule',
        'is_automated',
        'automation_type',
        'source_article_id',
        'recurrence_enabled',
        'recurrence_pattern',
        'recurrence_start_date',
        'recurrence_end_date',
        'recurrence_repeat_count',
        'recurrence_frequency',
        'recurrence_until',
        'recurrence_parent_id',
        'last_generated_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'reminder_enabled' => 'boolean',
        'reminder_repeat_until_validation' => 'boolean',
        'reminder_sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'attachments' => 'array',
        'is_automated' => 'boolean',
        'recurrence_enabled' => 'boolean',
        'recurrence_start_date' => 'date',
        'recurrence_end_date' => 'date',
        'recurrence_until' => 'date',
        'last_generated_at' => 'datetime',
    ];

    /**
     * Get the employee assigned to the task.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the automation rule that created this task.
     */
    public function automationRule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class);
    }

    public function sourceArticle(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'source_article_id');
    }

    public function recurrenceParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'recurrence_parent_id');
    }

    public function recurrenceChildren(): HasMany
    {
        return $this->hasMany(self::class, 'recurrence_parent_id');
    }

    /**
     * Get the user who created the task.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get pending tasks.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['en_attente', 'en_cours'])
                     ->orderBy('priority', 'desc')
                     ->orderBy('due_date')
                     ->orderBy('due_time');
    }

    /**
     * Scope to get tasks by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get tasks by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get tasks by date range.
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('due_date', [$start, $end]);
    }

    /**
     * Scope to get overdue tasks.
     */
    public function scopeOverdue($query)
    {
        return $query->where(function ($q) {
            $q->where('due_date', '<', now()->toDateString())
              ->orWhere(function ($subQ) {
                  $subQ->where('due_date', '=', now()->toDateString())
                       ->where('due_time', '<', now()->toTimeString());
              });
        })->whereIn('status', ['en_attente', 'en_cours']);
    }

    /**
     * Get the full datetime of the task.
     */
    public function getFullDateTimeAttribute()
    {
        $dateTime = $this->due_date->format('Y-m-d');
        if ($this->due_time) {
            $dateTime .= ' ' . $this->due_time;
        }
        return $dateTime;
    }

    /**
     * Check if task is overdue.
     */
    public function isOverdue()
    {
        if (in_array($this->status, ['termine', 'annule'])) {
            return false;
        }

        $now = now();
        $dueDate = $this->due_date;

        if ($this->due_time) {
            $dueDateTime = \Carbon\Carbon::parse($this->full_date_time);
            return $now > $dueDateTime;
        }

        return $now->toDateString() > $dueDate->toDateString();
    }

    /**
     * Check if task needs reminder.
     */
    public function needsReminder()
    {
        if (!$this->reminder_enabled || $this->status === 'termine') {
            return false;
        }

        // If repeat is enabled and not completed, check if enough time has passed since last reminder
        if ($this->reminder_repeat_until_validation && $this->reminder_sent_at) {
            $interval = $this->reminder_repeat_interval ?? 30; // minutes
            $nextReminderTime = $this->reminder_sent_at->addMinutes($interval);
            return now() >= $nextReminderTime;
        }

        // If already sent and no repeat, don't send again
        if ($this->reminder_sent_at && !$this->reminder_repeat_until_validation) {
            return false;
        }

        $taskDateTime = \Carbon\Carbon::parse($this->full_date_time);
        $now = now();

        // Calculate reminder time based on settings
        $reminderTime = $this->getReminderDateTime();

        return $now >= $reminderTime && $now < $taskDateTime;
    }

    /**
     * Get the datetime when reminder should be sent.
     */
    public function getReminderDateTime()
    {
        $taskDateTime = \Carbon\Carbon::parse($this->full_date_time);

        switch ($this->reminder_timing) {
            case 'at_time':
                return $taskDateTime;
            case '1h':
                return $taskDateTime->copy()->subHour();
            case '30min':
                return $taskDateTime->copy()->subMinutes(30);
            case 'custom':
                $unit = $this->reminder_custom_unit ?? 'hours';
                $value = $this->reminder_custom_value ?? 1;
                return $taskDateTime->copy()->sub($value, $unit);
            default:
                return $taskDateTime->copy()->subHour();
        }
    }

    /**
     * Mark task as completed.
     */
    public function markCompleted()
    {
        $this->status = 'termine';
        $this->completed_at = now();
        $this->save();
    }
}
