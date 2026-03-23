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
            'items.*.value' => 'nullable|string|max:255',
            'items.*.is_active' => 'required|boolean',
            'items.*.sort_order' => 'nullable|integer|min:0',
            'items.*.operational_mode' => 'nullable|in:dine_in,pickup,delivery',
            'items.*.requires_delivery_agent' => 'nullable|boolean',
            'items.*.tickets_without_group' => 'nullable|array',
            'items.*.tickets_without_group.*.id' => 'nullable|integer',
            'items.*.tickets_without_group.*.label' => 'required|string|max:255',
            'items.*.tickets_without_group.*.is_active' => 'required|boolean',
            'items.*.tickets_without_group.*.sort_order' => 'nullable|integer|min:0',
            'items.*.ticket_groups' => 'nullable|array',
            'items.*.ticket_groups.*.id' => 'nullable|integer',
            'items.*.ticket_groups.*.label' => 'required|string|max:255',
            'items.*.ticket_groups.*.is_active' => 'required|boolean',
            'items.*.ticket_groups.*.sort_order' => 'nullable|integer|min:0',
            'items.*.ticket_groups.*.tickets' => 'nullable|array',
            'items.*.ticket_groups.*.tickets.*.id' => 'nullable|integer',
            'items.*.ticket_groups.*.tickets.*.label' => 'required|string|max:255',
            'items.*.ticket_groups.*.tickets.*.is_active' => 'required|boolean',
            'items.*.ticket_groups.*.tickets.*.sort_order' => 'nullable|integer|min:0',
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

        if ($name === CustomListService::SERVICE_MODE_LIST) {
            foreach ($validated['items'] as $itemIndex => $item) {
                $serviceModeLabel = trim((string) ($item['label'] ?? '')) ?: 'Mode de service';
                $position = $itemIndex + 1;

                $this->ensureUniqueLabels(
                    $item['tickets_without_group'] ?? [],
                    sprintf('Les tickets sans groupe du mode "%s" (position %d) doivent avoir un libellé unique.', $serviceModeLabel, $position)
                );

                $this->ensureUniqueLabels(
                    $item['ticket_groups'] ?? [],
                    sprintf('Les groupes du mode "%s" (position %d) doivent avoir un libellé unique.', $serviceModeLabel, $position)
                );

                foreach ($item['ticket_groups'] ?? [] as $groupIndex => $group) {
                    $groupLabel = trim((string) ($group['label'] ?? '')) ?: 'Groupe';

                    $this->ensureUniqueLabels(
                        $group['tickets'] ?? [],
                        sprintf(
                            'Les tickets du groupe "%s" dans le mode "%s" (groupe %d) doivent avoir un libellé unique.',
                            $groupLabel,
                            $serviceModeLabel,
                            $groupIndex + 1
                        )
                    );
                }
            }
        }

        return response()->json(
            $this->customListService->update(
                $name,
                (bool) $validated['is_active'],
                $validated['items']
            )
        );
    }

    private function ensureUniqueLabels(array $entries, string $message): void
    {
        $labels = collect($entries)
            ->map(fn (array $entry) => mb_strtolower(trim((string) ($entry['label'] ?? ''))))
            ->filter()
            ->values();

        if ($labels->count() !== $labels->unique()->count()) {
            throw ValidationException::withMessages([
                'items' => $message,
            ]);
        }
    }
}
