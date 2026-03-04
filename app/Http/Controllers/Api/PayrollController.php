<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayrollRequest;
use App\Http\Requests\UpdatePayrollRequest;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    protected PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Get payroll for a specific employee and period
     * GET /api/payrolls?employee_id=1&month=3&year=2026
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|integer|exists:employees,id',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
            'status' => 'nullable|in:pending,paid,partially_paid',
        ]);

        $query = Payroll::query();

        if ($validated['employee_id'] ?? null) {
            $query->where('employee_id', $validated['employee_id']);
        }

        if ($validated['month'] ?? null) {
            $query->where('month', $validated['month']);
        }

        if ($validated['year'] ?? null) {
            $query->where('year', $validated['year']);
        }

        if ($validated['status'] ?? null) {
            $query->where('payment_status', $validated['status']);
        }

        $payrolls = $query->with('employee')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $payrolls,
            'count' => $payrolls->count(),
        ]);
    }

    /**
     * Calculate and create payroll
     * POST /api/payrolls
     */
    public function store(StorePayrollRequest $request): JsonResponse
    {
        try {
            $employee = Employee::findOrFail($request->employee_id);

            $payroll = $this->payrollService->calculatePayroll(
                $employee,
                $request->month,
                $request->year,
                [
                    'prime' => $request->prime ?? 0,
                    'bonus' => $request->bonus ?? 0,
                    'advance' => $request->advance ?? 0,
                    'retention' => $request->retention ?? 0,
                    'notes' => $request->adjustment_notes,
                    'payment_method' => $request->payment_method,
                    'comments' => $request->comments,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Payroll calculated successfully',
                'data' => $payroll->fresh()->load('employee'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific payroll
     * GET /api/payrolls/{payroll}
     */
    public function show(Payroll $payroll): JsonResponse
    {
        $summary = $this->payrollService->getEmployeeSummary(
            $payroll->employee,
            $payroll->month,
            $payroll->year
        );

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ]);
    }

    /**
     * Update payroll adjustments
     * PUT /api/payrolls/{payroll}
     */
    public function update(UpdatePayrollRequest $request, Payroll $payroll): JsonResponse
    {
        try {
            $updates = [];

            if ($request->has('prime')) {
                $updates['prime'] = $request->prime;
            }
            if ($request->has('bonus')) {
                $updates['bonus'] = $request->bonus;
            }
            if ($request->has('advance')) {
                $updates['advance'] = $request->advance;
            }
            if ($request->has('retention')) {
                $updates['retention'] = $request->retention;
            }
            if ($request->has('adjustment_notes')) {
                $updates['adjustment_notes'] = $request->adjustment_notes;
            }
            if ($request->has('comments')) {
                $updates['comments'] = $request->comments;
            }

            // Recalculate totals if any adjustment changed
            if (!empty($updates)) {
                $baseAmount = $payroll->base_amount ?? 0;
                $overtimeAmount = $payroll->overtime_amount ?? 0;
                $absenceDeduction = $payroll->absence_deduction ?? 0;

                $prime = $updates['prime'] ?? $payroll->prime;
                $bonus = $updates['bonus'] ?? $payroll->bonus;
                $advance = $updates['advance'] ?? $payroll->advance;
                $retention = $updates['retention'] ?? $payroll->retention;

                $grossAmount = $baseAmount + $overtimeAmount + $prime + $bonus - $absenceDeduction;
                $totalDeductions = $advance + $retention + $absenceDeduction;
                $netAmount = $grossAmount - $totalDeductions;

                $updates['gross_amount'] = $grossAmount;
                $updates['total_deductions'] = $totalDeductions;
                $updates['net_amount'] = $netAmount;
            }

            $payroll->update($updates);

            return response()->json([
                'status' => 'success',
                'message' => 'Payroll updated successfully',
                'data' => $payroll->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete payroll
     * DELETE /api/payrolls/{payroll}
     */
    public function destroy(Payroll $payroll): JsonResponse
    {
        try {
            if ($payroll->payment_status === 'paid') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete paid payroll',
                ], 422);
            }

            $payroll->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Payroll deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process payment for payroll
     * POST /api/payrolls/{payroll}/process-payment
     */
    public function processPayment(Request $request, Payroll $payroll): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:paid,partially_paid',
                'method' => 'required|in:cash,transfer,check',
                'date' => 'nullable|date',
                'comments' => 'nullable|string',
            ]);

            $errors = $this->payrollService->validatePaymentProcessing($payroll);
            if (!empty($errors)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot process payment',
                    'errors' => $errors,
                ], 422);
            }

            $updated = $this->payrollService->processPayment($payroll, [
                'status' => $validated['status'],
                'method' => $validated['method'],
                'date' => $validated['date'] ?? now(),
                'comments' => $validated['comments'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully',
                'data' => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payroll history for employee
     * GET /api/employees/{employee}/payroll-history
     */
    public function employeeHistory(Employee $employee, Request $request): JsonResponse
    {
        $limit = $request->query('limit', 12);
        $history = $this->payrollService->getPayrollHistory($employee, $limit);

        return response()->json([
            'status' => 'success',
            'data' => $history,
            'count' => $history->count(),
        ]);
    }

    /**
     * Get statistics for a period
     * GET /api/payroll-statistics?month=3&year=2026
     */
    public function statistics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $stats = $this->payrollService->getStatistics($validated['month'], $validated['year']);

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ]);
    }

    /**
     * Get attendance summary for payroll calculation
     * GET /api/employees/{employee}/attendance-summary?month=3&year=2026
     */
    public function attendanceSummary(Employee $employee, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $calculator = new \App\Services\PayrollCalculator($employee, $validated['month'], $validated['year']);
        $summary = $calculator->getAttendanceSummary();

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ]);
    }

    /**
     * Get payroll calculation preview (without saving)
     * POST /api/payroll-preview
     */
    public function preview(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|integer|exists:employees,id',
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2000|max:2100',
            ]);

            $employee = Employee::findOrFail($validated['employee_id']);
            $calculator = new \App\Services\PayrollCalculator(
                $employee,
                $validated['month'],
                $validated['year']
            );

            $calculated = $calculator->calculate();
            $attendance = $calculator->getAttendanceSummary();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'employee' => [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'pay_type' => $employee->pay_type,
                        'base_rate' => $employee->base_rate,
                    ],
                    'calculation' => $calculated,
                    'attendance' => $attendance,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk recalculate payrolls for all employees
     * POST /api/payroll-bulk-calculate
     */
    public function bulkCalculate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2000|max:2100',
            ]);

            $employees = Employee::where('status', 'active')->get();
            $results = [];

            foreach ($employees as $employee) {
                try {
                    $payroll = $this->payrollService->calculatePayroll($employee, $validated['month'], $validated['year']);
                    $results[] = [
                        'employee_id' => $employee->id,
                        'name' => $employee->name,
                        'status' => 'success',
                        'payroll_id' => $payroll->id,
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'employee_id' => $employee->id,
                        'name' => $employee->name,
                        'status' => 'error',
                        'message' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Bulk calculation completed',
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
