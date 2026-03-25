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
            'items' => 'required|array',
            'items.*.id' => 'nullable|integer',
            'items.*.label' => 'required|string|max:255',
            'items.*.value' => 'nullable|string|max:255',
            'items.*.is_active' => 'required|boolean',
            'items.*.sort_order' => 'nullable|integer|min:0',
            'items.*.kind' => 'nullable|in:ticket,group',
            'items.*.tickets' => 'nullable|array',
            'items.*.tickets.*.id' => 'nullable|integer',
            'items.*.tickets.*.label' => 'required|string|max:255',
            'items.*.tickets.*.is_active' => 'required|boolean',
            'items.*.tickets.*.sort_order' => 'nullable|integer|min:0',
            'items.*.operational_mode' => 'nullable|in:dine_in,pickup,delivery',
            'items.*.requires_delivery_agent' => 'nullable|boolean',
            'items.*.payment_type' => 'nullable|in:cash,card,mobile,virement,credit,other',
            'items.*.transfer_mode' => 'nullable|in:simple,instant',
            'items.*.is_default' => 'nullable|boolean',
            'items.*.payment_timing' => 'nullable|in:immediate,deferred',
            'items.*.show_transaction_number' => 'nullable|boolean',
            'items.*.show_piece_number' => 'nullable|boolean',
            'items.*.show_issue_date' => 'nullable|boolean',
            'items.*.show_due_date' => 'nullable|boolean',
            'items.*.show_bank_name' => 'nullable|boolean',
            'items.*.show_notes' => 'nullable|boolean',
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

        if ($name === CustomListService::PREDEFINED_TICKET_LIST) {
            foreach ($validated['items'] as $itemIndex => $item) {
                if (($item['kind'] ?? 'ticket') !== 'group') {
                    continue;
                }

                $this->ensureUniqueLabels(
                    $item['tickets'] ?? [],
                    sprintf(
                        'Les tickets du groupe "%s" (position %d) doivent avoir un libellé unique.',
                        trim((string) ($item['label'] ?? '')) ?: 'Groupe',
                        $itemIndex + 1
                    )
                );
            }
        }

        if ($name === CustomListService::PAYMENT_MODE_LIST) {
            $defaultCount = collect($validated['items'])
                ->filter(fn (array $item) => (bool) ($item['is_default'] ?? false))
                ->count();

            if ($defaultCount > 1) {
                throw ValidationException::withMessages([
                    'items' => 'Un seul mode de paiement peut être défini par défaut.',
                ]);
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
