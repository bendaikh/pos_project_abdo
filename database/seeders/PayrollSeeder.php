<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample employees with different pay types
        $this->createSampleEmployees();
        
        // Create sample attendance records for March 2026
        $this->createSampleAttendances();
        
        // Create sample payroll records
        $this->createSamplePayrolls();
    }

    /**
     * Create sample employees with payroll configuration
     */
    private function createSampleEmployees(): void
    {
        $employees = [
            [
                'name' => 'Ahmed Mohamed',
                'email' => 'ahmed@example.com',
                'phone' => '0612345678',
                'role' => 'manager',
                'pay_type' => 'monthly',
                'base_rate' => 12000,
                'overtime_multiplier' => 1.25,
                'normal_hours_per_day' => 8,
                'rest_day' => 'dimanche',
                'absence_penalty_rate' => 100,
                'payment_method' => 'transfer',
            ],
            [
                'name' => 'Fatima Hassan',
                'email' => 'fatima@example.com',
                'phone' => '0612345679',
                'role' => 'cashier',
                'pay_type' => 'daily',
                'base_rate' => 400,
                'overtime_multiplier' => 1.25,
                'normal_hours_per_day' => 8,
                'rest_day' => 'dimanche',
                'absence_penalty_rate' => 100,
                'payment_method' => 'transfer',
            ],
            [
                'name' => 'Mohammed Ali',
                'email' => 'mohammed@example.com',
                'phone' => '0612345680',
                'role' => 'vendor',
                'pay_type' => 'hourly',
                'base_rate' => 50,
                'overtime_multiplier' => 1.5,
                'normal_hours_per_day' => 8,
                'rest_day' => 'dimanche',
                'absence_penalty_rate' => 100,
                'payment_method' => 'cash',
            ],
            [
                'name' => 'Aisha Ibrahim',
                'email' => 'aisha@example.com',
                'phone' => '0612345681',
                'role' => 'cashier',
                'pay_type' => 'weekly',
                'base_rate' => 2400,
                'overtime_multiplier' => 1.25,
                'normal_hours_per_day' => 8,
                'rest_day' => 'dimanche',
                'absence_penalty_rate' => 100,
                'payment_method' => 'transfer',
            ],
            [
                'name' => 'Omar Saied',
                'email' => 'omar@example.com',
                'phone' => '0612345682',
                'role' => 'vendor',
                'pay_type' => 'monthly',
                'base_rate' => 8000,
                'overtime_multiplier' => 1.25,
                'normal_hours_per_day' => 8,
                'rest_day' => 'dimanche',
                'absence_penalty_rate' => 100,
                'payment_method' => 'transfer',
            ],
        ];

        foreach ($employees as $employeeData) {
            Employee::updateOrCreate(
                ['email' => $employeeData['email']],
                array_merge($employeeData, [
                    'status' => 'active',
                    'hire_date' => now()->subMonths(12),
                ])
            );
        }

        $this->command->info('Created 5 sample employees with payroll configuration');
    }

    /**
     * Create sample attendance records for March 2026
     */
    private function createSampleAttendances(): void
    {
        $employees = Employee::all();
        $start = Carbon::create(2026, 3, 1);
        $end = $start->copy()->endOfMonth();

        foreach ($employees as $employee) {
            $current = $start->copy();
            $dayCounter = 0;

            while ($current <= $end) {
                // Skip rest days (Sunday)
                if ($current->dayOfWeek != 0) { // 0 = Sunday
                    $dayCounter++;
                    
                    // Create attendance (mostly present, 2 absences)
                    $status = $dayCounter <= 18 ? 'present' : 'absent';
                    if ($dayCounter == 15) {
                        $status = 'absent';
                    }

                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $current->copy(),
                        'time_in' => $status === 'present' ? '08:00:00' : null,
                        'time_out' => $status === 'present' ? '17:00:00' : null,
                        'break_duration' => $status === 'present' ? 1 : 0,
                        'total_hours' => $status === 'present' ? 8 : 0,
                        'status' => $status,
                        'notes' => $status === 'present' ? 'Regular work day' : 'Absence',
                    ]);
                }

                $current->addDay();
            }
        }

        $this->command->info('Created attendance records for March 2026');
    }

    /**
     * Create sample payroll records
     */
    private function createSamplePayrolls(): void
    {
        $employees = Employee::all();
        $month = 3;
        $year = 2026;

        foreach ($employees as $employee) {
            // Calculate basic payroll
            $start = Carbon::create($year, $month, 1);
            $end = $start->copy()->endOfMonth();

            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$start, $end])
                ->get();

            $baseAmount = 0;
            $overtimeAmount = 0;
            $absenceDeduction = 0;

            switch ($employee->pay_type) {
                case 'hourly':
                    $normalHours = $attendances->where('status', 'present')->sum('total_hours');
                    $baseAmount = $normalHours * $employee->base_rate;
                    $absenceDeduction = $attendances->where('status', 'absent')->count() * 8 * $employee->base_rate;
                    break;

                case 'daily':
                    $workedDays = $attendances->where('status', 'present')->count();
                    $baseAmount = $workedDays * $employee->base_rate;
                    $absenceDeduction = $attendances->where('status', 'absent')->count() * $employee->base_rate;
                    break;

                case 'weekly':
                    $workedDays = $attendances->where('status', 'present')->count();
                    $weeks = intval($workedDays / 6);
                    $extraDays = $workedDays % 6;
                    $baseAmount = ($weeks * $employee->base_rate) + ($extraDays * $employee->base_rate / 6);
                    break;

                case 'monthly':
                default:
                    $baseAmount = $employee->base_rate;
                    $absenceDeduction = $attendances->where('status', 'absent')->count() * ($employee->base_rate / 26);
                    break;
            }

            // Add adjustments
            $prime = 500;
            $bonus = 1000;
            $advance = 1000;
            $retention = 0;

            $grossAmount = $baseAmount + $prime + $bonus;
            $totalDeductions = $absenceDeduction + $advance + $retention;
            $netAmount = $grossAmount - $totalDeductions;

            Payroll::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'period_start' => $start->toDateString(),
                    'period_end' => $end->toDateString(),
                    'base_salary' => $employee->base_rate,
                    'normal_hours' => $attendances->where('status', 'present')->sum('total_hours'),
                    'overtime_hours' => 0,
                    'worked_days' => $attendances->where('status', 'present')->count(),
                    'absent_days' => $attendances->where('status', 'absent')->count(),
                    'base_amount' => $baseAmount,
                    'absence_deduction' => $absenceDeduction,
                    'prime' => $prime,
                    'bonus' => $bonus,
                    'advance' => $advance,
                    'retention' => $retention,
                    'gross_amount' => $grossAmount,
                    'total_deductions' => $totalDeductions,
                    'net_amount' => $netAmount,
                    'payment_method' => $employee->payment_method,
                    'payment_status' => 'pending',
                ]
            );
        }

        $this->command->info('Created sample payroll records');
    }
}

/**
 * USAGE:
 * Run in terminal: php artisan db:seed --class=PayrollSeeder
 * Or add to DatabaseSeeder: $this->call(PayrollSeeder::class);
 */
