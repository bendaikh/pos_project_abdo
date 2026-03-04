<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
        'break_duration',
        'total_hours',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'total_hours' => 'float',
        'break_duration' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate total hours from time_in and time_out
     */
    public function calculateTotalHours(): float
    {
        if (!$this->time_in || !$this->time_out) {
            return 0;
        }

        $start = \DateTime::createFromFormat('H:i:s', $this->time_in);
        $end = \DateTime::createFromFormat('H:i:s', $this->time_out);

        if (!$start || !$end) {
            return 0;
        }

        $diff = $end->diff($start);
        $hours = $diff->h + ($diff->i / 60);
        
        // Subtract break duration (in minutes)
        $hours -= ($this->break_duration / 60);

        return max(0, $hours);
    }

    /**
     * Auto-update total_hours on save if times are set
     */
    protected static function booting()
    {
        static::saving(function ($model) {
            if ($model->time_in && $model->time_out) {
                $model->total_hours = $model->calculateTotalHours();
            }
        });
    }
}
