<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Expense::with('creator:id,name')->orderByDesc('expense_date')->orderByDesc('id');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('from')) {
            $query->whereDate('expense_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('expense_date', '<=', $request->to);
        }

        return response()->json($query->paginate($request->get('per_page', 50)));
    }

    public function store(Request $request): JsonResponse
    {
        if (! StoreContext::id()) {
            return response()->json(['message' => 'Sélectionnez un point de vente.'], 422);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'expense_type' => 'nullable|in:fixed,variable',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'expense_date' => 'required|date',
            'is_recurring' => 'boolean',
            'frequency' => 'nullable|in:monthly,quarterly,semiannual,annual',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = $request->user()->id;

        // Keep legacy columns in sync when present
        if (\Illuminate\Support\Facades\Schema::hasColumn('expenses', 'designation')) {
            $validated['designation'] = $validated['label'];
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('expenses', 'expense_category')) {
            $validated['expense_category'] = $validated['category'] ?? null;
        }

        $expense = Expense::create($validated);

        return response()->json($expense->load('creator:id,name'), 201);
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'sometimes|required|string|max:255',
            'category' => 'nullable|string|max:255',
            'expense_type' => 'nullable|in:fixed,variable',
            'amount' => 'sometimes|required|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'expense_date' => 'sometimes|required|date',
            'is_recurring' => 'boolean',
            'frequency' => 'nullable|in:monthly,quarterly,semiannual,annual',
            'notes' => 'nullable|string',
        ]);

        $expense->update($validated);

        return response()->json($expense->fresh()->load('creator:id,name'));
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json(['message' => 'Charge supprimée.']);
    }
}
