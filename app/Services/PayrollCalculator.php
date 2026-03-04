<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PayrollCalculator
{
    private Employee $employee;
    private int $month;
    private int $year;
    private Collection $attendances;

    public function __construct(Employee $employee, int $month, int $year)
    {
        $this->employee = $employee;
        $this->month = $month;
        $this->year = $year;
        $this->loadAttendances();
    }

    /**
     * Load attendances for the payroll period
     */
    private function loadAttendances(): void
    {
        $startDate = Carbon::create($this->year, $this->month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $this->attendances = Attendance::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
    }

    /**
     * Calculate complete payroll
     */
    public function calculate(): array
    {
        $startDate = Carbon::create($this->year, $this->month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $payrollData = [
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),
            'month' => $this->month,
            'year' => $this->year,
        ];

        // Calculate based on pay type
        match ($this->employee->pay_type) {
            'hourly' => $this->calculateHourly($payrollData),
            'daily' => $this->calculateDaily($payrollData),
            'weekly' => $this->calculateWeekly($payrollData),
            'monthly' => $this->calculateMonthly($payrollData),
        };

        return $payrollData;
    }

    /**
     * Calculate for hourly pay type
     */
    private function calculateHourly(array &$payrollData): void
    {
        $normalHours = 0;
        $overtimeHours = 0;
        $normalHoursPerDay = $this->employee->normal_hours_per_day ?? 8;

        foreach ($this->attendances as $attendance) {
            if ($attendance->status === 'présent' || $attendance->status === 'present') {
                $hours = $attendance->total_hours ?? 0;

                if ($hours > $normalHoursPerDay) {
                    $normalHours += $normalHoursPerDay;
                    $overtimeHours += ($hours - $normalHoursPerDay);
                } else {
                    $normalHours += $hours;
                }
            }
        }

        $baseRate = $this->employee->base_rate ?? 0;
        $overtimeMultiplier = $this->employee->overtime_multiplier ?? 1.25;

        $normalHoursAmount = $normalHours * $baseRate;
        $overtimeAmount = $overtimeHours * $baseRate * $overtimeMultiplier;
        $absenceDeduction = $this->calculateAbsenceDeduction('hourly', $baseRate, $normalHoursPerDay);

        $payrollData['normal_hours'] = $normalHours;
        $payrollData['overtime_hours'] = $overtimeHours;
        $payrollData['normal_hours_amount'] = $normalHoursAmount;
        $payrollData['overtime_amount'] = $overtimeAmount;
        $payrollData['absence_deduction'] = $absenceDeduction;
        $payrollData['base_salary'] = $baseRate;
        $payrollData['base_amount'] = $normalHoursAmount + $overtimeAmount;
    }

    /**
     * Calculate for daily pay type
     */
    private function calculateDaily(array &$payrollData): void
    {
        $workedDays = 0;
        $absentDays = 0;
        $restDay = strtolower($this->employee->rest_day ?? 'dimanche');

        foreach ($this->attendances as $attendance) {
            $dayOfWeek = strtolower($attendance->date->format('l'));
            
            // Skip rest days
            if ($dayOfWeek === str_replace('dimanche', 'sunday', $restDay)) {
                continue;
            }

            if ($attendance->status === 'présent' || $attendance->status === 'present') {
                $workedDays++;
            } elseif ($attendance->status === 'absent') {
                $absentDays++;
            }
        }

        $baseRate = $this->employee->base_rate ?? 0;
        $penaltyRate = $this->employee->absence_penalty_rate ?? 1;

        $baseAmount = $workedDays * $baseRate;
        $absenceDeduction = $absentDays * $baseRate * ($penaltyRate / 100);

        $payrollData['worked_days'] = $workedDays;
        $payrollData['absent_days'] = $absentDays;
        $payrollData['base_salary'] = $baseRate;
        $payrollData['base_amount'] = $baseAmount;
        $payrollData['absence_deduction'] = $absenceDeduction;
    }

    /**
     * Calculate for weekly pay type
     */
    private function calculateWeekly(array &$payrollData): void
    {
        $startDate = Carbon::create($this->year, $this->month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $weeks = 0;
        $extraDays = 0;
        $restDay = strtolower($this->employee->rest_day ?? 'dimanche');
        $dayCount = 0;

        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dayOfWeek = strtolower($current->format('l'));
            
            if ($dayOfWeek !== str_replace('dimanche', 'sunday', $restDay)) {
                $dayCount++;
                if ($dayCount % 6 == 0) {
                    $weeks++;
                    $dayCount = 0;
                }
            }

            $current->addDay();
        }

        $extraDays = $dayCount;

        $baseRate = $this->employee->base_rate ?? 0;
        $weekAmount = $weeks * $baseRate;
        $extraDayAmount = $extraDays * ($baseRate / 6); // Assuming 6-day week

        $payrollData['worked_weeks'] = $weeks;
        $payrollData['extra_days'] = $extraDays;
        $payrollData['base_salary'] = $baseRate;
        $payrollData['base_amount'] = $weekAmount + $extraDayAmount;
        $payrollData['normal_hours_amount'] = $weekAmount;
        $payrollData['overtime_amount'] = $extraDayAmount;
    }

    /**
     * Calculate for monthly pay type
     */
    private function calculateMonthly(array &$payrollData): void
    {
        $baseRate = $this->employee->base_rate ?? 0;
        $absentDays = 0;
        $restDay = strtolower($this->employee->rest_day ?? 'dimanche');
        $penaltyRate = $this->employee->absence_penalty_rate ?? 1;

        foreach ($this->attendances as $attendance) {
            if ($attendance->status === 'absent') {
                $dayOfWeek = strtolower($attendance->date->format('l'));
                if ($dayOfWeek !== str_replace('dimanche', 'sunday', $restDay)) {
                    $absentDays++;
                }
            }
        }

        // Assume 26 working days per month (standard)
        $absenceDeduction = $absentDays * ($baseRate / 26) * ($penaltyRate / 100);

        $payrollData['base_salary'] = $baseRate;
        $payrollData['base_amount'] = $baseRate;
        $payrollData['absent_days'] = $absentDays;
        $payrollData['absence_deduction'] = $absenceDeduction;
    }

    /**
     * Calculate absence deduction for hourly payroll
     */
    private function calculateAbsenceDeduction(string $type, float $baseRate, int $normalHoursPerDay): float
    {
        $absentDays = $this->attendances
            ->where('status', 'absent')
            ->count();

        if ($type === 'hourly') {
            $hourlyRate = $baseRate;
            return $absentDays * $normalHoursPerDay * $hourlyRate;
        }

        return 0;
    }

    /**
     * Get attendance summary
     */
    public function getAttendanceSummary(): array
    {
        $present = $this->attendances->where('status', 'present')->count() + 
                   $this->attendances->where('status', 'présent')->count();
        $absent = $this->attendances->where('status', 'absent')->count();
        $leave = $this->attendances->where('status', 'congé')->count();
        $late = $this->attendances->where('status', 'retard')->count();

        return [
            'present' => $present,
            'absent' => $absent,
            'leave' => $leave,
            'late' => $late,
            'total_hours' => $this->attendances->sum('total_hours'),
        ];
    }
}
