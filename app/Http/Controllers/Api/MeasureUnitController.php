<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeasureUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeasureUnitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureDefaults();

        $query = MeasureUnit::query();

        if ($request->boolean('active')) {
            $query->active();
        }

        return response()->json($query->ordered()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:255',
            'symbol' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtolower(trim($validated['code']));

        $unit = MeasureUnit::create($validated);

        return response()->json($unit, 201);
    }

    public function show(MeasureUnit $measure_unit): JsonResponse
    {
        return response()->json($measure_unit);
    }

    public function update(Request $request, MeasureUnit $measure_unit): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:30',
            'name' => 'sometimes|required|string|max:255',
            'symbol' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['code'])) {
            $validated['code'] = strtolower(trim($validated['code']));
        }

        $measure_unit->update($validated);

        return response()->json($measure_unit);
    }

    public function destroy(MeasureUnit $measure_unit): JsonResponse
    {
        $measure_unit->delete();

        return response()->json(null, 204);
    }

    private function ensureDefaults(): void
    {
        if (MeasureUnit::query()->exists()) {
            return;
        }

        $defaults = [
            ['code' => 'piece', 'name' => 'Pièce', 'symbol' => 'pce', 'sort_order' => 1],
            ['code' => 'kg', 'name' => 'Kilogramme', 'symbol' => 'kg', 'sort_order' => 2],
            ['code' => 'g', 'name' => 'Gramme', 'symbol' => 'g', 'sort_order' => 3],
            ['code' => 'l', 'name' => 'Litre', 'symbol' => 'L', 'sort_order' => 4],
            ['code' => 'ml', 'name' => 'Millilitre', 'symbol' => 'ml', 'sort_order' => 5],
            ['code' => 'm', 'name' => 'Mètre', 'symbol' => 'm', 'sort_order' => 6],
        ];

        foreach ($defaults as $unit) {
            MeasureUnit::create($unit + ['is_active' => true]);
        }
    }
}
