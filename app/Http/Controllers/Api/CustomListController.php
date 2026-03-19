<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CustomListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomListController extends Controller
{
    public function __construct(
        private readonly CustomListService $customListService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->customListService->all($request->boolean('active_only'))
        );
    }

    public function show(Request $request, string $name): JsonResponse
    {
        return response()->json(
            $this->customListService->get($name, $request->boolean('active_only'))
        );
    }

    public function update(Request $request, string $name): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer',
            'items.*.label' => 'required|string|max:255',
            'items.*.is_active' => 'required|boolean',
            'items.*.sort_order' => 'nullable|integer|min:0',
        ]);

        $labels = collect($validated['items'])
            ->map(fn (array $item) => mb_strtolower(trim((string) $item['label'])))
            ->filter()
            ->values();

        if ($labels->count() !== $labels->unique()->count()) {
            throw ValidationException::withMessages([
                'items' => 'Chaque élément doit avoir un libellé unique.',
            ]);
        }

        return response()->json(
            $this->customListService->update(
                $name,
                (bool) $validated['is_active'],
                $validated['items']
            )
        );
    }
}
