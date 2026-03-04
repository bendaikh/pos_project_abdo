<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'week_number',
        'period_start',
        'period_end',
        'base_salary',
        'normal_hours',
        'overtime_hours',
        'worked_days',
        'absent_days',
        'worked_weeks',
        'extra_days',
        'normal_hours_amount',
        'overtime_amount',
        'base_amount',
        'absence_deduction',
        'bonus',
        'prime',
        'advance',
        'retention',
        'adjustment_notes',
        'gross_amount',
        'total_deductions',
        'net_amount',
        'payment_method',
        'payment_status',
        'payment_date',
        'comments',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'payment_date' => 'date',
        'base_salary' => 'float',
        'normal_hours' => 'float',
        'overtime_hours' => 'float',
        'worked_weeks' => 'float',
        'extra_days' => 'float',
        'normal_hours_amount' => 'float',
        'overtime_amount' => 'float',
        'base_amount' => 'float',
        'absence_deduction' => 'float',
        'bonus' => 'float',
        'prime' => 'float',
        'advance' => 'float',
        'retention' => 'float',
        'gross_amount' => 'float',
        'total_deductions' => 'float',
        'net_amount' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate payroll based on employee's pay_type and attendance
     */
    public function calculatePayroll(): void
    {
        $employee = $this->employee;
        
        if (!$employee) {
            return;
        }

        // Get attendance records for this period
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$this->period_start, $this->period_end])
            ->get();

        // Calculate based on pay_type
        match ($employee->pay_type) {
            'hourly' => $this->calculateHourlyPayroll($attendances, $employee),
            'daily' => $this->calculateDailyPayroll($attendances, $employee),
            'weekly' => $this->calculateWeeklyPayroll($attendances, $employee),
            'monthly' => $this->calculateMonthlyPayroll($attendances, $employee),
            default => $this->calculateMonthlyPayroll($attendances, $employee),
        };

        // Apply adjustments
        $this->applyAdjustments();
    }

    private function calculateHourlyPayroll($attendances, $employee): void
    {
        $this->normal_hours = 0;
        $this->overtime_hours = 0;

        foreach ($attendances as $attendance) {
            if ($attendance->status === 'present') {
                $hours = $attendance->total_hours;
                $normalLimit = $employee->normal_hours_per_day ?? 8;
                
                if ($hours <= $normalLimit) {
                    $this->normal_hours += $hours;
                } else {
                    $this->normal_hours += $normalLimit;
                    $this->overtime_hours += ($hours - $normalLimit);
                }
            } elseif ($attendance->status === 'absent') {
                // Absence retenue
                $this->absence_deduction += ($employee->base_rate ?? 0) * $employee->normal_hours_per_day * $employee->absence_penalty_rate;
            }
        }

        $this->normal_hours_amount = $this->normal_hours * ($employee->base_rate ?? 0);
        $this->overtime_amount = $this->overtime_hours * ($employee->base_rate ?? 0) * $employee->overtime_multiplier;
        $this->base_amount = $this->normal_hours_amount + $this->overtime_amount;
    }

    private function calculateDailyPayroll($attendances, $employee): void
    {
        $this->worked_days = 0;
        $this->absent_days = 0;

        foreach ($attendances as $attendance) {
            if ($attendance->status === 'present') {
                $this->worked_days++;
            } elseif ($attendance->status === 'absent') {
                $this->absent_days++;
            }
        }

        $this->base_amount = $this->worked_days * ($employee->base_rate ?? 0);
        $this->absence_deduction = $this->absent_days * ($employee->base_rate ?? 0) * $employee->absence_penalty_rate;
    }

    private function calculateWeeklyPayroll($attendances, $employee): void
    {
        $presentDays = $attendances->where('status', 'present')->count();
        $this->worked_weeks = floor($presentDays / 6); // Assuming 6-day work week
        $this->extra_days = $presentDays % 6;

        $this->base_amount = ($this->worked_weeks * ($employee->base_rate ?? 0));
        
        if ($this->extra_days > 0) {
            $dailyRate = ($employee->base_rate ?? 0) / 6;
            $this->base_amount += ($this->extra_days * $dailyRate);
        }
    }

    private function calculateMonthlyPayroll($attendances, $employee): void
    {
        // Fixed monthly salary
        $this->base_salary = $employee->base_rate ?? 0;
        $this->base_amount = $this->base_salary;

        // Calculate absence retenue if enabled
        $absentDays = $attendances->where('status', 'absent')->count();
        if ($absentDays > 0) {
            $dailyRate = $this->base_salary / 30; // Assuming 30-day month
            $this->absence_deduction = $absentDays * $dailyRate * $employee->absence_penalty_rate;
        }
    }

    private function applyAdjustments(): void
    {
        // Calculate totals
        $this->gross_amount = $this->base_amount + $this->prime + $this->bonus;
        $this->total_deductions = $this->absence_deduction + $this->advance + $this->retention;
        $this->net_amount = $this->gross_amount - $this->total_deductions;
    }
}
