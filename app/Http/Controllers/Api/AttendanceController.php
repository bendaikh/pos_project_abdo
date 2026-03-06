<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * List attendances
     * GET /api/attendances?employee_id=1&date=2026-03-01

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|integer|exists:employees,id',
            'date' => 'nullable|date',
            'status' => 'nullable|in:present,absent,congé,retard',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $query = Attendance::with('employee');

        if ($validated['employee_id'] ?? null) {
            $query->where('employee_id', $validated['employee_id']);
        }

        if ($validated['date'] ?? null) {
            $query->whereDate('date', $validated['date']);
        }

        if ($validated['start_date'] ?? null && $validated['end_date'] ?? null) {
            $query->whereBetween('date', [$validated['start_date'], $validated['end_date']]);
        }

        if ($validated['status'] ?? null) {
            $query->where('status', $validated['status']);
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $attendances,
            'count' => $attendances->count(),
        ]);
    }

    /**
     * Record attendance
     * POST /api/attendances
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|integer|exists:employees,id',
                'date' => 'required|date',
                'time_in' => 'required|date_format:H:i:s',
                'time_out' => 'required|date_format:H:i:s',
                'break_duration' => 'nullable|numeric|min:0',
                'status' => 'required|in:present,absent,congé,retard,présent',
                'notes' => 'nullable|string|max:500',
            ]);

            // Calculate total hours
            $timeIn = Carbon::createFromFormat('H:i:s', $validated['time_in']);
            $timeOut = Carbon::createFromFormat('H:i:s', $validated['time_out']);
            $breakDuration = $validated['break_duration'] ?? 0;

            $totalMinutes = $timeOut->diffInMinutes($timeIn) - ($breakDuration * 60);
            $totalHours = $totalMinutes / 60;

            $attendance = Attendance::create([
                'employee_id' => $validated['employee_id'],
                'date' => $validated['date'],
                'time_in' => $validated['time_in'],
                'time_out' => $validated['time_out'],
                'break_duration' => $breakDuration,
                'total_hours' => max(0, $totalHours),
                'status' => $validated['status'],
                'notes' => $validated['notes'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance recorded successfully',
                'data' => $attendance->load('employee'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific attendance
     * GET /api/attendances/{attendance}
     */
    public function show(Attendance $attendance): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $attendance->load('employee'),
        ]);
    }

    /**
     * Update attendance
     * PUT /api/attendances/{attendance}
     */
    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        try {
            $validated = $request->validate([
                'time_in' => 'nullable|date_format:H:i:s',
                'time_out' => 'nullable|date_format:H:i:s',
                'break_duration' => 'nullable|numeric|min:0',
                'status' => 'nullable|in:present,absent,congé,retard,présent',
                'notes' => 'nullable|string|max:500',
            ]);

            $updates = [];
            
            if ($request->has('time_in')) {
                $updates['time_in'] = $validated['time_in'];
            }
            if ($request->has('time_out')) {
                $updates['time_out'] = $validated['time_out'];
            }
            if ($request->has('break_duration')) {
                $updates['break_duration'] = $validated['break_duration'];
            }
            if ($request->has('status')) {
                $updates['status'] = $validated['status'];
            }
            if ($request->has('notes')) {
                $updates['notes'] = $validated['notes'];
            }

            // Recalculate total hours if time_in or time_out changed
            if (isset($updates['time_in']) || isset($updates['time_out'])) {
                $timeIn = Carbon::createFromFormat('H:i:s', $updates['time_in'] ?? $attendance->time_in);
                $timeOut = Carbon::createFromFormat('H:i:s', $updates['time_out'] ?? $attendance->time_out);
                $breakDuration = $updates['break_duration'] ?? $attendance->break_duration ?? 0;

                $totalMinutes = $timeOut->diffInMinutes($timeIn) - ($breakDuration * 60);
                $updates['total_hours'] = max(0, $totalMinutes / 60);
            }

            $attendance->update($updates);

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance updated successfully',
                'data' => $attendance->fresh()->load('employee'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete attendance
     * DELETE /api/attendances/{attendance}
     */
    public function destroy(Attendance $attendance): JsonResponse
    {
        try {
            $attendance->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get monthly attendance summary for employee
     * GET /api/attendances/summary/monthly?employee_id=1&month=3&year=2026
     */
    public function monthlySummary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $startDate = Carbon::create($validated['year'], $validated['month'], 1);
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $validated['employee_id'])
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $summary = [
            'employee_id' => $validated['employee_id'],
            'month' => $validated['month'],
            'year' => $validated['year'],
            'present' => $attendances->where('status', 'present')->count() + $attendances->where('status', 'présent')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'leave' => $attendances->where('status', 'congé')->count(),
            'late' => $attendances->where('status', 'retard')->count(),
            'total_hours' => $attendances->sum('total_hours'),
            'total_days' => $attendances->count(),
            'details' => $attendances->map(function ($a) {
                return [
                    'date' => $a->date,
                    'time_in' => $a->time_in,
                    'time_out' => $a->time_out,
                    'total_hours' => $a->total_hours,
                    'status' => $a->status,
                ];
            }),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ]);
    }

    /**
     * Bulk record attendance
     * POST /api/attendances/bulk
     */
    public function bulk(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'attendances' => 'required|array|min:1',
                'attendances.*.employee_id' => 'required|integer|exists:employees,id',
                'attendances.*.date' => 'required|date',
                'attendances.*.time_in' => 'required|date_format:H:i:s',
                'attendances.*.time_out' => 'required|date_format:H:i:s',
                'attendances.*.break_duration' => 'nullable|numeric|min:0',
                'attendances.*.status' => 'required|in:present,absent,congé,retard,présent',
            ]);

            $created = [];
            $errors = [];

            foreach ($validated['attendances'] as $index => $data) {
                try {
                    $timeIn = Carbon::createFromFormat('H:i:s', $data['time_in']);
                    $timeOut = Carbon::createFromFormat('H:i:s', $data['time_out']);
                    $breakDuration = $data['break_duration'] ?? 0;

                    $totalMinutes = $timeOut->diffInMinutes($timeIn) - ($breakDuration * 60);
                    $totalHours = max(0, $totalMinutes / 60);

                    $attendance = Attendance::create([
                        'employee_id' => $data['employee_id'],
                        'date' => $data['date'],
                        'time_in' => $data['time_in'],
                        'time_out' => $data['time_out'],
                        'break_duration' => $breakDuration,
                        'total_hours' => $totalHours,
                        'status' => $data['status'],
                    ]);

                    $created[] = $attendance->id;
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'message' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => count($created) . ' attendances recorded successfully',
                'created' => $created,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
