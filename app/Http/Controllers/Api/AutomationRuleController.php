<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutomationRule;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomationRuleController extends Controller
{
    /**
     * Get all automation rules
     */
    public function index(Request $request): JsonResponse
    {
        $query = AutomationRule::with('assignedToEmployee');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('condition_type')) {
            $query->where('condition_type', $request->condition_type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('task_subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $rules = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json($rules);
    }

    /**
     * Create a new automation rule
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'condition_type' => 'required|in:stock_level,sales_threshold,production_event,time_based,custom',
            'condition_data' => 'required|array',
            'task_subject' => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'task_priority' => 'required|in:faible,moyenne,urgente',
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
            'assigned_to_role' => 'nullable|string',
            'is_repeatable' => 'boolean',
            'repeat_interval' => 'nullable|in:daily,weekly,monthly',
        ]);

        $rule = AutomationRule::create($validated);

        return response()->json($rule->load('assignedToEmployee'), 201);
    }

    /**
     * Get a specific automation rule
     */
    public function show(AutomationRule $automationRule): JsonResponse
    {
        $automationRule->load('assignedToEmployee');
        return response()->json($automationRule);
    }

    /**
     * Update an automation rule
     */
    public function update(Request $request, AutomationRule $automationRule): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'condition_type' => 'in:stock_level,sales_threshold,production_event,time_based,custom',
            'condition_data' => 'array',
            'task_subject' => 'string|max:255',
            'task_description' => 'nullable|string',
            'task_priority' => 'in:faible,moyenne,urgente',
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
            'assigned_to_role' => 'nullable|string',
            'is_repeatable' => 'boolean',
            'repeat_interval' => 'nullable|in:daily,weekly,monthly',
        ]);

        $automationRule->update($validated);

        return response()->json($automationRule->load('assignedToEmployee'));
    }

    /**
     * Delete an automation rule
     */
    public function destroy(AutomationRule $automationRule): JsonResponse
    {
        $automationRule->delete();
        return response()->json(null, 204);
    }

    /**
     * Manually trigger a rule to create a task
     */
    public function trigger(AutomationRule $automationRule): JsonResponse
    {
        if (!$automationRule->is_active) {
            return response()->json([
                'message' => 'Cette règle est désactivée',
            ], 422);
        }

        try {
            $task = $automationRule->createTask();
            return response()->json([
                'message' => 'Tâche créée avec succès',
                'task' => $task,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la tâche',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available articles for stock level conditions
     */
    public function getArticles(Request $request): JsonResponse
    {
        $articles = Article::select('id', 'name', 'stock')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($articles);
    }

    /**
     * Get available employees for assignment
     */
    public function getEmployees(Request $request): JsonResponse
    {
        $employees = \App\Models\Employee::where('status', 'active')
            ->select('id', 'name', 'role')
            ->orderBy('name')
            ->get();

        return response()->json($employees);
    }
}
