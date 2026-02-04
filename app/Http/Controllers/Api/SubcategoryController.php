<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Subcategory::with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('active')) {
            $query->active();
        }

        $subcategories = $query->orderBy('sort_order')->orderBy('name')->get();

        return response()->json($subcategories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $subcategory = Subcategory::create($validated);

        return response()->json($subcategory->load('category'), 201);
    }

    public function show(Subcategory $subcategory): JsonResponse
    {
        return response()->json($subcategory->load(['category', 'articles']));
    }

    public function update(Request $request, Subcategory $subcategory): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $subcategory->update($validated);

        return response()->json($subcategory->load('category'));
    }

    public function destroy(Subcategory $subcategory): JsonResponse
    {
        $subcategory->delete();

        return response()->json(null, 204);
    }
}
