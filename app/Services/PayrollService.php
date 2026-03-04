<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Calculate and create/update payroll record
     */
    public function calculatePayroll(Employee $employee, int $month, int $year, array $adjustments = []): Payroll
    {
        // Check if payroll already exists
        $payroll = Payroll::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $calculator = new PayrollCalculator($employee, $month, $year);
        $calculatedData = $calculator->calculate();

        // Initialize adjustments
        $prime = $adjustments['prime'] ?? 0;
        $bonus = $adjustments['bonus'] ?? 0;
        $advance = $adjustments['advance'] ?? 0;
        $retention = $adjustments['retention'] ?? 0;
        $adjustmentNotes = $adjustments['notes'] ?? null;

        // Calculate totals
        $baseAmount = $calculatedData['base_amount'] ?? 0;
        $overtimeAmount = $calculatedData['overtime_amount'] ?? 0;
        $absenceDeduction = $calculatedData['absence_deduction'] ?? 0;

        $grossAmount = $baseAmount + $overtimeAmount + $prime + $bonus - $absenceDeduction;
        $totalDeductions = $advance + $retention + $absenceDeduction;
        $netAmount = $grossAmount - $totalDeductions;

        $payrollData = array_merge($calculatedData, [
            'employee_id' => $employee->id,
            'prime' => $prime,
            'bonus' => $bonus,
            'advance' => $advance,
            'retention' => $retention,
            'adjustment_notes' => $adjustmentNotes,
            'gross_amount' => $grossAmount,
            'total_deductions' => $totalDeductions,
            'net_amount' => $netAmount,
            'payment_method' => $adjustments['payment_method'] ?? $employee->payment_method ?? 'transfer',
            'payment_status' => 'pending',
            'comments' => $adjustments['comments'] ?? null,
        ]);

        if ($payroll) {
            $payroll->update($payrollData);
        } else {
            $payroll = Payroll::create($payrollData);
        }

        return $payroll;
    }

    /**
     * Get payroll for a specific employee and month
     */
    public function getPayroll(Employee $employee, int $month, int $year): ?Payroll
    {
        return Payroll::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }

    /**
     * Get payroll history for employee
     */
    public function getPayrollHistory(Employee $employee, ?int $limit = null): Collection
    {
        $query = Payroll::where('employee_id', $employee->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Process payment
     */
    public function processPayment(Payroll $payroll, array $paymentData = []): Payroll
    {
        $payroll->update([
            'payment_status' => $paymentData['status'] ?? 'paid',
            'payment_date' => $paymentData['date'] ?? now(),
            'payment_method' => $paymentData['method'] ?? $payroll->payment_method,
            'comments' => $paymentData['comments'] ?? $payroll->comments,
        ]);

        return $payroll->fresh();
    }

    /**
     * Get payroll statistics for a period
     */
    public function getStatistics(int $month, int $year): array
    {
        $payrolls = Payroll::where('month', $month)
            ->where('year', $year)
            ->get();

        return [
            'total_gross' => $payrolls->sum('gross_amount'),
            'total_net' => $payrolls->sum('net_amount'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'total_primes' => $payrolls->sum('prime'),
            'total_bonus' => $payrolls->sum('bonus'),
            'total_advances' => $payrolls->sum('advance'),
            'total_retentions' => $payrolls->sum('retention'),
            'employee_count' => $payrolls->count(),
            'paid_count' => $payrolls->where('payment_status', 'paid')->count(),
            'pending_count' => $payrolls->where('payment_status', 'pending')->count(),
        ];
    }

    /**
     * Get payroll summary for employee
     */
    public function getEmployeeSummary(Employee $employee, int $month, int $year): array
    {
        $payroll = $this->getPayroll($employee, $month, $year);

        if (!$payroll) {
            return [];
        }

        return [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'pay_type' => $employee->pay_type,
                'base_rate' => $employee->base_rate,
            ],
            'period' => [
                'month' => $month,
                'year' => $year,
                'start' => $payroll->period_start,
                'end' => $payroll->period_end,
            ],
            'calculations' => [
                'base_amount' => $payroll->base_amount,
                'normal_hours' => $payroll->normal_hours,
                'overtime_hours' => $payroll->overtime_hours,
                'worked_days' => $payroll->worked_days,
                'absent_days' => $payroll->absent_days,
                'absence_deduction' => $payroll->absence_deduction,
            ],
            'adjustments' => [
                'prime' => $payroll->prime,
                'bonus' => $payroll->bonus,
                'advance' => $payroll->advance,
                'retention' => $payroll->retention,
            ],
            'totals' => [
                'gross' => $payroll->gross_amount,
                'deductions' => $payroll->total_deductions,
                'net' => $payroll->net_amount,
            ],
            'payment' => [
                'method' => $payroll->payment_method,
                'status' => $payroll->payment_status,
                'date' => $payroll->payment_date,
            ],
        ];
    }

    /**
     * Validate payment can be processed
     */
    public function validatePaymentProcessing(Payroll $payroll): array
    {
        $errors = [];

        if (!$payroll->employee) {
            $errors[] = 'Employee not found';
        }

        if ($payroll->payment_status === 'paid') {
            $errors[] = 'Payment already processed';
        }

        if ($payroll->net_amount <= 0) {
            $errors[] = 'Invalid net amount';
        }

        return $errors;
    }
}
