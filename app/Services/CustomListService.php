<?php

namespace App\Services;

use App\Models\CustomList;
use App\Models\CustomListItem;
use Illuminate\Support\Str;

class CustomListService
{
    public const SERVICE_MODE_LIST = 'mode_de_service';

    private const DEFAULT_LISTS = [
        self::SERVICE_MODE_LIST => [
            'is_active' => true,
            'items' => [
                ['label' => 'Sur place', 'sort_order' => 1],
                ['label' => 'Emporté', 'sort_order' => 2],
                ['label' => 'Livraison', 'sort_order' => 3],
            ],
        ],
    ];

    private const SERVICE_MODE_METADATA = [
        'surplace' => [
            'operational_mode' => 'dine_in',
            'requires_delivery_agent' => false,
        ],
        'dinein' => [
            'operational_mode' => 'dine_in',
            'requires_delivery_agent' => false,
        ],
        'emporte' => [
            'operational_mode' => 'pickup',
            'requires_delivery_agent' => false,
        ],
        'aemporter' => [
            'operational_mode' => 'pickup',
            'requires_delivery_agent' => false,
        ],
        'pickup' => [
            'operational_mode' => 'pickup',
            'requires_delivery_agent' => false,
        ],
        'takeaway' => [
            'operational_mode' => 'pickup',
            'requires_delivery_agent' => false,
        ],
        'livraison' => [
            'operational_mode' => 'delivery',
            'requires_delivery_agent' => true,
        ],
        'delivery' => [
            'operational_mode' => 'delivery',
            'requires_delivery_agent' => true,
        ],
        'glovo' => [
            'operational_mode' => 'delivery',
            'requires_delivery_agent' => true,
        ],
    ];

    private const LEGACY_SERVICE_MODE_LABELS = [
        'dine_in' => 'Sur place',
        'sur_place' => 'Sur place',
        'pickup' => 'Emporté',
        'a_emporter' => 'Emporté',
        'takeaway' => 'Emporté',
        'delivery' => 'Livraison',
        'livraison' => 'Livraison',
        'glovo' => 'Livraison',
    ];

    public function all(bool $activeOnly = false): array
    {
        foreach (array_keys(self::DEFAULT_LISTS) as $name) {
            $this->ensureList($name);
        }

        $lists = CustomList::with('items')->get()->keyBy('name');

        return $lists
            ->map(fn (CustomList $list) => $this->serializeList($list->fresh('items'), $activeOnly))
            ->values()
            ->all();
    }

    public function get(string $name, bool $activeOnly = false): array
    {
        return $this->serializeList($this->ensureList($name), $activeOnly);
    }

    public function update(string $name, bool $isActive, array $items): array
    {
        $list = $this->ensureList($name);
        $list->forceFill(['is_active' => $isActive])->save();

        foreach (array_values($items) as $index => $item) {
            $record = null;
            $itemId = $item['id'] ?? null;

            if ($itemId) {
                $record = $list->items()->whereKey($itemId)->first();
            }

            if (! $record) {
                $record = $list->items()->firstOrNew([
                    'label' => trim((string) $item['label']),
                ]);
            }

            $record->fill([
                'label' => trim((string) $item['label']),
                'is_active' => (bool) ($item['is_active'] ?? true),
                'sort_order' => (int) ($item['sort_order'] ?? ($index + 1)),
            ]);
            $record->list_id = $list->id;
            $record->save();
        }

        return $this->serializeList($list->fresh('items'));
    }

    public function resolveServiceMode(?string $serviceMode = null, ?string $legacyDeliveryMode = null): string
    {
        $candidate = trim((string) ($serviceMode ?? ''));

        if ($candidate === '' && $legacyDeliveryMode !== null) {
            $candidate = $this->mapLegacyServiceMode($legacyDeliveryMode);
        }

        if ($candidate === '') {
            return $this->defaultServiceModeLabel();
        }

        $candidate = $this->mapLegacyServiceMode($candidate);
        $list = $this->ensureList(self::SERVICE_MODE_LIST);
        $normalized = $this->normalizeKey($candidate);

        $matched = $list->items->first(
            fn (CustomListItem $item) => $this->normalizeKey($item->label) === $normalized
        );

        return $matched?->label ?? $candidate;
    }

    public function resolveOperationalMode(?string $serviceMode = null, ?string $legacyDeliveryMode = null): string
    {
        $resolvedServiceMode = $this->resolveServiceMode($serviceMode, $legacyDeliveryMode);
        $metadata = $this->resolveServiceModeMetadata($resolvedServiceMode);

        return $metadata['operational_mode'];
    }

    public function defaultServiceModeLabel(): string
    {
        $list = $this->ensureList(self::SERVICE_MODE_LIST);
        $firstActive = $list->items->firstWhere('is_active', true);

        if ($firstActive) {
            return $firstActive->label;
        }

        return $list->items->first()?->label
            ?? self::DEFAULT_LISTS[self::SERVICE_MODE_LIST]['items'][0]['label'];
    }

    private function ensureList(string $name): CustomList
    {
        $defaults = self::DEFAULT_LISTS[$name] ?? ['is_active' => true, 'items' => []];

        $list = CustomList::firstOrCreate(
            ['name' => $name],
            ['is_active' => $defaults['is_active']]
        );

        $this->ensureDefaultItems($list, $defaults['items']);

        return $list->fresh('items');
    }

    private function ensureDefaultItems(CustomList $list, array $defaultItems): void
    {
        if (empty($defaultItems)) {
            return;
        }

        $existingLabels = $list->items()
            ->get()
            ->map(fn (CustomListItem $item) => $this->normalizeKey($item->label))
            ->all();

        foreach ($defaultItems as $item) {
            $normalizedLabel = $this->normalizeKey($item['label']);
            if (in_array($normalizedLabel, $existingLabels, true)) {
                continue;
            }

            $list->items()->create([
                'label' => $item['label'],
                'is_active' => true,
                'sort_order' => (int) $item['sort_order'],
            ]);
            $existingLabels[] = $normalizedLabel;
        }
    }

    private function serializeList(CustomList $list, bool $activeOnly = false): array
    {
        $items = $list->items;

        if ($activeOnly) {
            $items = $items->where('is_active', true)->values();
        }

        return [
            'id' => $list->id,
            'name' => $list->name,
            'is_active' => (bool) $list->is_active,
            'items' => $items
                ->sortBy('sort_order')
                ->values()
                ->map(fn (CustomListItem $item) => $this->serializeItem($list->name, $item))
                ->all(),
        ];
    }

    private function serializeItem(string $listName, CustomListItem $item): array
    {
        $metadata = $listName === self::SERVICE_MODE_LIST
            ? $this->resolveServiceModeMetadata($item->label)
            : [
                'operational_mode' => 'pickup',
                'requires_delivery_agent' => false,
            ];

        return [
            'id' => $item->id,
            'label' => $item->label,
            'value' => $item->label,
            'is_active' => (bool) $item->is_active,
            'sort_order' => (int) $item->sort_order,
            'operational_mode' => $metadata['operational_mode'],
            'requires_delivery_agent' => $metadata['requires_delivery_agent'],
        ];
    }

    private function resolveServiceModeMetadata(string $value): array
    {
        $mappedValue = $this->mapLegacyServiceMode($value);
        $normalized = $this->normalizeKey($mappedValue);

        return self::SERVICE_MODE_METADATA[$normalized] ?? [
            'operational_mode' => 'pickup',
            'requires_delivery_agent' => false,
        ];
    }

    private function mapLegacyServiceMode(string $value): string
    {
        $trimmed = trim($value);
        $normalized = $this->normalizeKey($trimmed);

        foreach (self::LEGACY_SERVICE_MODE_LABELS as $legacyValue => $label) {
            if ($this->normalizeKey($legacyValue) === $normalized) {
                return $label;
            }
        }

        return $trimmed;
    }

    private function normalizeKey(string $value): string
    {
        return (string) Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->trim();
    }
}
