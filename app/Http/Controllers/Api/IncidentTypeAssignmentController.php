<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncidentTypeAssignment;
use App\Models\CustomList;
use App\Models\CustomListItem;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IncidentTypeAssignmentController extends Controller
{
    public function index()
    {
        $assignments = IncidentTypeAssignment::with(['incidentType', 'employee'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($assignments);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'incident_type_id' => 'required|exists:custom_list_items,id|unique:incident_type_assignments,incident_type_id',
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $assignment = IncidentTypeAssignment::create($validator->validated());
        $assignment->load(['incidentType', 'employee']);

        return response()->json([
            'message' => 'Assignation créée avec succès',
            'assignment' => $assignment
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $assignment = IncidentTypeAssignment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'incident_type_id' => 'sometimes|required|exists:custom_list_items,id|unique:incident_type_assignments,incident_type_id,' . $id,
            'employee_id' => 'sometimes|required|exists:employees,id',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $assignment->update($validator->validated());
        $assignment->load(['incidentType', 'employee']);

        return response()->json([
            'message' => 'Assignation mise à jour avec succès',
            'assignment' => $assignment
        ]);
    }

    public function destroy($id)
    {
        $assignment = IncidentTypeAssignment::findOrFail($id);
        $assignment->delete();

        return response()->json([
            'message' => 'Assignation supprimée avec succès'
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'assignments' => 'required|array',
            'assignments.*.incident_type_id' => 'required|exists:custom_list_items,id',
            'assignments.*.employee_ids' => 'required|array',
            'assignments.*.employee_ids.*' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Clear existing assignments for the types being updated
        $typeIds = array_column($request->assignments, 'incident_type_id');
        IncidentTypeAssignment::whereIn('incident_type_id', $typeIds)->delete();

        // Create new assignments
        foreach ($request->assignments as $assignmentData) {
            foreach ($assignmentData['employee_ids'] as $employeeId) {
                IncidentTypeAssignment::create([
                    'incident_type_id' => $assignmentData['incident_type_id'],
                    'employee_id' => $employeeId,
                    'is_active' => true
                ]);
            }
        }

        $assignments = IncidentTypeAssignment::with(['incidentType', 'employee'])->get();

        return response()->json([
            'message' => 'Assignations mises à jour avec succès',
            'assignments' => $assignments
        ]);
    }

    public function getWithTypes()
    {
        $list = CustomList::where('name', 'incident_types')->first();
        if (!$list) {
            return response()->json([
                'types' => [],
                'assignments' => []
            ]);
        }

        $types = CustomListItem::where('list_id', $list->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Group assignments by incident_type_id, returning arrays of employees
        $assignmentsRaw = IncidentTypeAssignment::with('employee')
            ->where('is_active', true)
            ->get();
            
        $assignments = [];
        foreach ($assignmentsRaw as $assignment) {
            $typeId = $assignment->incident_type_id;
            if (!isset($assignments[$typeId])) {
                $assignments[$typeId] = [];
            }
            if ($assignment->employee) {
                $assignments[$typeId][] = $assignment->employee;
            }
        }

        $employees = Employee::where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'types' => $types,
            'assignments' => $assignments,
            'employees' => $employees
        ]);
    }
}
