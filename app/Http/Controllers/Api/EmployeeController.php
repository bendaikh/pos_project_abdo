<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::with(['user', 'commission']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('role')) {
            $query->byRole($request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('name')->paginate($request->get('per_page', 20));

        return response()->json($employees);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:admin,manager,cashier,vendor',
            'commission_id' => 'nullable|exists:commissions,id',
            'hire_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,suspended',
        ]);

        $employee = Employee::create($validated);

        return response()->json($employee->load(['user', 'commission']), 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee->load(['user', 'commission']);
        $employee->loadCount('sales');
        $employee->loadSum(['sales as total_sales' => function ($q) {
            $q->where('status', 'completed');
        }], 'total');

        return response()->json($employee);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => 'sometimes|required|in:admin,manager,cashier,vendor',
            'commission_id' => 'nullable|exists:commissions,id',
            'hire_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,suspended',
        ]);

        $employee->update($validated);

        return response()->json($employee->load(['user', 'commission']));
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(null, 204);
    }
}
