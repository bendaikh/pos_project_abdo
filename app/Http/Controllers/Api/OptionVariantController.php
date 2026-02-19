<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\OptionVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OptionVariantController extends Controller
{
    public function index(Option $option, Request $request): JsonResponse
    {
        $query = $option->variants()->orderBy('sort_order')->orderBy('name');

        if ($request->has('active')) {
            $query->where('is_active', true);
        }

        return response()->json($query->get());
    }

    public function store(Option $option, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_impact' => 'nullable|numeric',
            'color' => 'nullable|string|max:50',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (!array_key_exists('price_impact', $validated)) {
            $validated['price_impact'] = 0;
        }

        if (!array_key_exists('is_active', $validated)) {
            $validated['is_active'] = true;
        }

        if (!array_key_exists('sort_order', $validated)) {
            $validated['sort_order'] = (int) $option->variants()->max('sort_order') + 1;
        }

        $variant = $option->variants()->create($validated);

        return response()->json($variant, 201);
    }

    public function show(Option $option, OptionVariant $variant): JsonResponse
    {
        if ($variant->option_id !== $option->id) {
            return response()->json(['message' => 'Variant not found'], 404);
        }

        return response()->json($variant);
    }

    public function update(Request $request, Option $option, OptionVariant $variant): JsonResponse
    {
        if ($variant->option_id !== $option->id) {
            return response()->json(['message' => 'Variant not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price_impact' => 'nullable|numeric',
            'color' => 'nullable|string|max:50',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $variant->update($validated);

        return response()->json($variant);
    }

    public function destroy(Option $option, OptionVariant $variant): JsonResponse
    {
        if ($variant->option_id !== $option->id) {
            return response()->json(['message' => 'Variant not found'], 404);
        }

        $variant->delete();

        return response()->json(null, 204);
    }
}
