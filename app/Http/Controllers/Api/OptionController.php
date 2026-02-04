<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Option::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('active')) {
            $query->active();
        }

        $options = $query->orderBy('name')->get();

        return response()->json($options);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:fixed,multiple',
            'values' => 'required|array|min:1',
            'values.*' => 'string',
            'extra_price' => 'nullable|numeric|min:0',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $option = Option::create($validated);

        return response()->json($option, 201);
    }

    public function show(Option $option): JsonResponse
    {
        return response()->json($option->load('articles'));
    }

    public function update(Request $request, Option $option): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:fixed,multiple',
            'values' => 'sometimes|required|array|min:1',
            'values.*' => 'string',
            'extra_price' => 'nullable|numeric|min:0',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $option->update($validated);

        return response()->json($option);
    }

    public function destroy(Option $option): JsonResponse
    {
        $option->delete();

        return response()->json(null, 204);
    }
}
