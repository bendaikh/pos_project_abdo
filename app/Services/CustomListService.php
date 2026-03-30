<?php

namespace App\Services;

use App\Models\CustomList;
use App\Models\CustomListItem;
use App\Models\DeliveryAgent;
use Illuminate\Support\Str;

class CustomListService
{
    public const PREDEFINED_TICKET_LIST = 'tickets_predefinis';
    public const SERVICE_MODE_LIST = 'mode_de_service';
    public const PAYMENT_MODE_LIST = 'mode_de_paiement';
    public const TAX_LIST = 'taxes';
    public const DISCOUNT_LIST = 'remises';
    public const EXPENSE_LIST = 'depenses';
    public const EXPENSE_CATEGORY_LIST = 'categories_depenses';

    private const DEFAULT_LISTS = [
        self::PREDEFINED_TICKET_LIST => [
            'is_active' => true,
            'items' => [],
        ],
        self::SERVICE_MODE_LIST => [
            'is_active' => true,
            'items' => [
                [
                    'label' => 'Sur place',
                    'value' => 'Sur place',
                    'sort_order' => 1,
                    'metadata' => [
                        'operational_mode' => 'dine_in',
                        'requires_delivery_agent' => false,
                        'is_system' => true,
                        'system_key' => 'sur_place',
                    ],
                ],
                [
                    'label' => 'Emporté',
                    'value' => 'Emporté',
                    'sort_order' => 2,
                    'metadata' => [
                        'operational_mode' => 'pickup',
                        'requires_delivery_agent' => false,
                        'is_system' => true,
                        'system_key' => 'emporte',
                    ],
                ],
                [
                    'label' => 'Livraison',
                    'value' => 'Livraison',
                    'sort_order' => 3,
                    'metadata' => [
                        'operational_mode' => 'delivery',
                        'requires_delivery_agent' => true,
                        'is_system' => true,
                        'system_key' => 'livraison',
                    ],
                ],
            ],
        ],
        self::PAYMENT_MODE_LIST => [
            'is_active' => true,
            'items' => [
                [
                    'label' => 'Espèce',
                    'value' => 'Espèce',
                    'sort_order' => 1,
                    'metadata' => [
                        'payment_type' => 'cash',
                        'transfer_mode' => null,
                        'is_default' => true,
                        'payment_timing' => 'immediate',
                        'fields' => [
                            'transaction_number' => false,
                            'piece_number' => false,
                            'issue_date' => false,
                            'due_date' => false,
                            'bank_name' => false,
                            'notes' => true,
                        ],
                        'is_system' => true,
                        'system_key' => 'espece',
                    ],
                ],
                [
                    'label' => 'Carte',
                    'value' => 'Carte',
                    'sort_order' => 2,
                    'metadata' => [
                        'payment_type' => 'card',
                        'transfer_mode' => null,
                        'is_default' => false,
                        'payment_timing' => 'immediate',
                        'fields' => [
                            'transaction_number' => true,
                            'piece_number' => false,
                            'issue_date' => false,
                            'due_date' => false,
                            'bank_name' => false,
                            'notes' => true,
                        ],
                        'is_system' => true,
                        'system_key' => 'carte',
                    ],
                ],
                [
                    'label' => 'Mobile',
                    'value' => 'Mobile',
                    'sort_order' => 3,
                    'metadata' => [
                        'payment_type' => 'mobile',
                        'transfer_mode' => null,
                        'is_default' => false,
                        'payment_timing' => 'immediate',
                        'fields' => [
                            'transaction_number' => true,
                            'piece_number' => false,
                            'issue_date' => false,
                            'due_date' => false,
                            'bank_name' => false,
                            'notes' => true,
                        ],
                        'is_system' => true,
                        'system_key' => 'mobile',
                    ],
                ],
                [
                    'label' => 'Virement instantané',
                    'value' => 'Virement instantané',
                    'sort_order' => 4,
                    'metadata' => [
                        'payment_type' => 'virement',
                        'transfer_mode' => 'instant',
                        'is_default' => false,
                        'payment_timing' => 'immediate',
                        'fields' => [
                            'transaction_number' => true,
                            'piece_number' => false,
                            'issue_date' => false,
                            'due_date' => false,
                            'bank_name' => true,
                            'notes' => true,
                        ],
                        'is_system' => true,
                        'system_key' => 'virement_instantane',
                    ],
                ],
                [
                    'label' => 'Virement simple',
                    'value' => 'Virement simple',
                    'sort_order' => 5,
                    'metadata' => [
                        'payment_type' => 'virement',
                        'transfer_mode' => 'simple',
                        'is_default' => false,
                        'payment_timing' => 'deferred',
                        'fields' => [
                            'transaction_number' => true,
                            'piece_number' => true,
                            'issue_date' => true,
                            'due_date' => true,
                            'bank_name' => true,
                            'notes' => true,
                        ],
                        'is_system' => true,
                        'system_key' => 'virement_simple',
                    ],
                ],
                [
                    'label' => 'Crédit',
                    'value' => 'Crédit',
                    'sort_order' => 6,
                    'metadata' => [
                        'payment_type' => 'credit',
                        'transfer_mode' => null,
                        'is_default' => false,
                        'payment_timing' => 'deferred',
                        'fields' => [
                            'transaction_number' => false,
                            'piece_number' => true,
                            'issue_date' => true,
                            'due_date' => true,
                            'bank_name' => true,
                            'notes' => true,
                        ],
                        'is_system' => true,
                        'system_key' => 'credit',
                    ],
                ],
            ],
        ],
        self::TAX_LIST => [
            'is_active' => true,
            'items' => [],
        ],
        self::DISCOUNT_LIST => [
            'is_active' => true,
            'items' => [],
        ],
        self::EXPENSE_LIST => [
            'is_active' => true,
            'items' => [],
        ],
        self::EXPENSE_CATEGORY_LIST => [
            'is_active' => true,
            'items' => [],
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
    ];

    private const FALLBACK_SERVICE_MODE_METADATA = [
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
    ];

    public function all(bool $activeOnly = false): array
    {
        foreach (array_keys(self::DEFAULT_LISTS) as $name) {
            $this->ensureList($name);
        }

        $this->syncPlatformServiceModesFromDeliveryAgents();

        $lists = CustomList::with('items')->get()->keyBy('name');

        return $lists
            ->map(fn (CustomList $list) => $this->serializeList($list->fresh('items'), $activeOnly))
            ->values()
            ->all();
    }

    public function get(string $name, bool $activeOnly = false): array
    {
        if ($name === self::SERVICE_MODE_LIST) {
            $this->syncPlatformServiceModesFromDeliveryAgents();
        }

        return $this->serializeList($this->ensureList($name)->fresh('items'), $activeOnly);
    }

    public function update(string $name, bool $isActive, array $items): array
    {
        $list = $this->ensureList($name);
        $list->forceFill(['is_active' => $isActive])->save();

        $submittedIds = [];

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

            $existingMetadata = is_array($record->metadata) ? $record->metadata : [];

            $record->fill([
                'label' => trim((string) $item['label']),
                'value' => trim((string) ($item['value'] ?? $item['label'])),
                'metadata' => $this->buildItemMetadata($name, $item, $existingMetadata),
                'is_active' => (bool) ($item['is_active'] ?? true),
                'sort_order' => (int) ($item['sort_order'] ?? ($index + 1)),
            ]);
            $record->list_id = $list->id;
            $record->save();

            $submittedIds[] = $record->id;
        }

        if (empty($submittedIds)) {
            $list->items()
                ->get()
                ->reject(fn (CustomListItem $item) => $this->isSystemItem($item->metadata))
                ->each
                ->delete();
        } else {
            $list->items()
                ->whereNotIn('id', $submittedIds)
                ->get()
                ->reject(fn (CustomListItem $item) => $this->isSystemItem($item->metadata))
                ->each
                ->delete();
        }

        return $this->serializeList($list->fresh('items'));
    }

    public function syncPlatformServiceMode(?string $platformName): array
    {
        $label = trim((string) $platformName);
        if ($label === '') {
            return $this->get(self::SERVICE_MODE_LIST);
        }

        $list = $this->ensureList(self::SERVICE_MODE_LIST);
        $this->upsertPlatformServiceMode($list, $label, true);

        return $this->syncAllPlatformServiceModes();
    }

    public function syncAllPlatformServiceModes(): array
    {
        $this->syncPlatformServiceModesFromDeliveryAgents();

        return $this->serializeList($this->ensureList(self::SERVICE_MODE_LIST)->fresh('items'));
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
            fn (CustomListItem $item) => $this->normalizeKey($item->value ?: $item->label) === $normalized
                || $this->normalizeKey($item->label) === $normalized
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
        $this->migrateLegacyTicketsIfNeeded($list);

        return $list->fresh('items');
    }

    private function ensureDefaultItems(CustomList $list, array $defaultItems): void
    {
        if (empty($defaultItems)) {
            return;
        }

        $existingItems = $list->items()->get()->keyBy(
            fn (CustomListItem $item) => $this->normalizeKey($item->label)
        );

        foreach ($defaultItems as $item) {
            $normalizedLabel = $this->normalizeKey($item['label']);
            $existing = $existingItems->get($normalizedLabel);

            if ($existing) {
                $existingMetadata = is_array($existing->metadata) ? $existing->metadata : [];
                $mergedMetadata = $this->mergeMetadata($existingMetadata, $item['metadata'] ?? []);

                if ($mergedMetadata !== $existingMetadata) {
                    $existing->forceFill(['metadata' => $mergedMetadata])->save();
                }

                continue;
            }

            $list->items()->create([
                'label' => $item['label'],
                'value' => $item['value'] ?? $item['label'],
                'metadata' => $item['metadata'] ?? null,
                'is_active' => true,
                'sort_order' => (int) $item['sort_order'],
            ]);
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
        $metadata = $this->normalizeSerializedMetadata($listName, $item);

        return [
            'id' => $item->id,
            'label' => $item->label,
            'value' => $item->value ?: $item->label,
            'is_active' => (bool) $item->is_active,
            'sort_order' => (int) $item->sort_order,
            'kind' => $metadata['kind'],
            'tickets' => $metadata['tickets'],
            'operational_mode' => $metadata['operational_mode'],
            'requires_delivery_agent' => $metadata['requires_delivery_agent'],
            'payment_type' => $metadata['payment_type'],
            'transfer_mode' => $metadata['transfer_mode'],
            'is_default' => $metadata['is_default'],
            'payment_timing' => $metadata['payment_timing'],
            'show_transaction_number' => $metadata['show_transaction_number'],
            'show_piece_number' => $metadata['show_piece_number'],
            'show_issue_date' => $metadata['show_issue_date'],
            'show_due_date' => $metadata['show_due_date'],
            'show_bank_name' => $metadata['show_bank_name'],
            'show_notes' => $metadata['show_notes'],
            'tax_type' => $metadata['tax_type'],
            'tax_rate' => $metadata['tax_rate'],
            'tax_is_default' => $metadata['tax_is_default'],
            'discount_type' => $metadata['discount_type'],
            'discount_value' => $metadata['discount_value'],
            'discount_limit' => $metadata['discount_limit'],
            'expense_category' => $metadata['expense_category'],
            'expense_type' => $metadata['expense_type'],
            'expense_is_recurring' => $metadata['expense_is_recurring'],
            'expense_frequency' => $metadata['expense_frequency'],
            'is_system' => $metadata['is_system'],
            'system_key' => $metadata['system_key'],
            'source' => $metadata['source'],
        ];
    }

    private function buildItemMetadata(string $listName, array $item, array $existingMetadata = []): ?array
    {
        if ($listName === self::PREDEFINED_TICKET_LIST) {
            return [
                'kind' => $this->normalizePredefinedTicketKind($item),
                'tickets' => $this->normalizeTicketCollection($item['tickets'] ?? []),
            ];
        }

        if ($listName === self::SERVICE_MODE_LIST) {
            $fallback = $this->resolveFallbackServiceModeMetadata($item['label'] ?? '');

            return $this->mergeMetadata($existingMetadata, [
                'operational_mode' => $item['operational_mode'] ?? $fallback['operational_mode'],
                'requires_delivery_agent' => (bool) ($item['requires_delivery_agent'] ?? $fallback['requires_delivery_agent']),
            ]);
        }

        if ($listName === self::PAYMENT_MODE_LIST) {
            $fallback = $this->resolveFallbackPaymentModeMetadata($item['label'] ?? '');

            return $this->mergeMetadata($existingMetadata, [
                'payment_type' => $item['payment_type'] ?? $fallback['payment_type'],
                'transfer_mode' => $item['transfer_mode'] ?? $fallback['transfer_mode'],
                'is_default' => (bool) ($item['is_default'] ?? $fallback['is_default']),
                'payment_timing' => $item['payment_timing'] ?? $fallback['payment_timing'],
                'fields' => $this->normalizePaymentFieldConfig($item),
            ]);
        }

        if ($listName === self::TAX_LIST) {
            return $this->mergeMetadata($existingMetadata, [
                'tax_type' => ($item['tax_type'] ?? 'percentage') === 'fixed' ? 'fixed' : 'percentage',
                'tax_rate' => round((float) ($item['tax_rate'] ?? 0), 2),
                'tax_is_default' => (bool) ($item['tax_is_default'] ?? false),
            ]);
        }

        if ($listName === self::DISCOUNT_LIST) {
            return $this->mergeMetadata($existingMetadata, [
                'discount_type' => ($item['discount_type'] ?? 'percentage') === 'fixed' ? 'fixed' : 'percentage',
                'discount_value' => round((float) ($item['discount_value'] ?? 0), 2),
                'discount_limit' => round((float) ($item['discount_limit'] ?? 0), 2),
            ]);
        }

        if ($listName === self::EXPENSE_LIST) {
            $isRecurring = (bool) ($item['expense_is_recurring'] ?? false);
            $frequency = $item['expense_frequency'] ?? null;

            return $this->mergeMetadata($existingMetadata, [
                'expense_category' => trim((string) ($item['expense_category'] ?? '')),
                'expense_type' => ($item['expense_type'] ?? 'fixed') === 'variable' ? 'variable' : 'fixed',
                'expense_is_recurring' => $isRecurring,
                'expense_frequency' => $isRecurring ? $frequency : null,
            ]);
        }

        return null;
    }

    private function normalizeSerializedMetadata(string $listName, CustomListItem $item): array
    {
        $defaults = $this->baseSerializedMetadata();

        if ($listName === self::PREDEFINED_TICKET_LIST) {
            $metadata = is_array($item->metadata) ? $item->metadata : [];
            $tickets = $this->normalizeTicketCollection($metadata['tickets'] ?? []);
            $kind = $metadata['kind'] ?? ($tickets !== [] ? 'group' : 'ticket');

            return array_merge($defaults, [
                'kind' => $kind === 'group' ? 'group' : 'ticket',
                'tickets' => $tickets,
            ]);
        }

        if ($listName === self::SERVICE_MODE_LIST) {
            $metadata = is_array($item->metadata) ? $item->metadata : [];
            $fallback = $this->resolveFallbackServiceModeMetadata($item->label);

            return array_merge($defaults, [
                'operational_mode' => $metadata['operational_mode'] ?? $fallback['operational_mode'],
                'requires_delivery_agent' => (bool) ($metadata['requires_delivery_agent'] ?? $fallback['requires_delivery_agent']),
                'is_system' => (bool) ($metadata['is_system'] ?? false),
                'system_key' => $metadata['system_key'] ?? null,
                'source' => $metadata['source'] ?? null,
            ]);
        }

        if ($listName === self::PAYMENT_MODE_LIST) {
            $metadata = is_array($item->metadata) ? $item->metadata : [];
            $fallback = $this->resolveFallbackPaymentModeMetadata($item->label);
            $fieldConfig = $this->normalizePaymentFieldConfig($metadata);

            return array_merge($defaults, [
                'payment_type' => $metadata['payment_type'] ?? $fallback['payment_type'],
                'transfer_mode' => $metadata['transfer_mode'] ?? $fallback['transfer_mode'],
                'is_default' => (bool) ($metadata['is_default'] ?? $fallback['is_default']),
                'payment_timing' => $metadata['payment_timing'] ?? $fallback['payment_timing'],
                'show_transaction_number' => $fieldConfig['transaction_number'],
                'show_piece_number' => $fieldConfig['piece_number'],
                'show_issue_date' => $fieldConfig['issue_date'],
                'show_due_date' => $fieldConfig['due_date'],
                'show_bank_name' => $fieldConfig['bank_name'],
                'show_notes' => $fieldConfig['notes'],
                'is_system' => (bool) ($metadata['is_system'] ?? false),
                'system_key' => $metadata['system_key'] ?? null,
                'source' => $metadata['source'] ?? null,
            ]);
        }

        if ($listName === self::TAX_LIST) {
            $metadata = is_array($item->metadata) ? $item->metadata : [];

            return array_merge($defaults, [
                'tax_type' => ($metadata['tax_type'] ?? 'percentage') === 'fixed' ? 'fixed' : 'percentage',
                'tax_rate' => round((float) ($metadata['tax_rate'] ?? 0), 2),
                'tax_is_default' => (bool) ($metadata['tax_is_default'] ?? false),
            ]);
        }

        if ($listName === self::DISCOUNT_LIST) {
            $metadata = is_array($item->metadata) ? $item->metadata : [];

            return array_merge($defaults, [
                'discount_type' => ($metadata['discount_type'] ?? 'percentage') === 'fixed' ? 'fixed' : 'percentage',
                'discount_value' => round((float) ($metadata['discount_value'] ?? 0), 2),
                'discount_limit' => round((float) ($metadata['discount_limit'] ?? 0), 2),
            ]);
        }

        if ($listName === self::EXPENSE_LIST) {
            $metadata = is_array($item->metadata) ? $item->metadata : [];
            $isRecurring = (bool) ($metadata['expense_is_recurring'] ?? false);

            return array_merge($defaults, [
                'expense_category' => trim((string) ($metadata['expense_category'] ?? '')),
                'expense_type' => ($metadata['expense_type'] ?? 'fixed') === 'variable' ? 'variable' : 'fixed',
                'expense_is_recurring' => $isRecurring,
                'expense_frequency' => $isRecurring ? ($metadata['expense_frequency'] ?? null) : null,
            ]);
        }

        return $defaults;
    }

    private function baseSerializedMetadata(): array
    {
        return [
            'kind' => 'ticket',
            'tickets' => [],
            'operational_mode' => 'pickup',
            'requires_delivery_agent' => false,
            'payment_type' => 'other',
            'transfer_mode' => null,
            'is_default' => false,
            'payment_timing' => 'immediate',
            'show_transaction_number' => false,
            'show_piece_number' => false,
            'show_issue_date' => false,
            'show_due_date' => false,
            'show_bank_name' => false,
            'show_notes' => false,
            'tax_type' => 'percentage',
            'tax_rate' => 0,
            'tax_is_default' => false,
            'discount_type' => 'percentage',
            'discount_value' => 0,
            'discount_limit' => 0,
            'expense_category' => '',
            'expense_type' => 'fixed',
            'expense_is_recurring' => false,
            'expense_frequency' => null,
            'is_system' => false,
            'system_key' => null,
            'source' => null,
        ];
    }

    private function migrateLegacyTicketsIfNeeded(CustomList $list): void
    {
        if ($list->name !== self::PREDEFINED_TICKET_LIST || $list->items()->exists()) {
            return;
        }

        $serviceModeList = CustomList::query()
            ->where('name', self::SERVICE_MODE_LIST)
            ->with('items')
            ->first();

        if (! $serviceModeList) {
            return;
        }

        $ungroupedTickets = [];
        $ticketGroups = [];

        foreach ($serviceModeList->items as $item) {
            $metadata = is_array($item->metadata) ? $item->metadata : [];

            foreach ($this->normalizeTicketCollection($metadata['tickets_without_group'] ?? []) as $ticket) {
                $key = $this->normalizeKey($ticket['label']);
                if ($key === '' || array_key_exists($key, $ungroupedTickets)) {
                    continue;
                }

                $ungroupedTickets[$key] = $ticket;
            }

            foreach ($this->normalizeTicketGroups($metadata['ticket_groups'] ?? []) as $group) {
                $groupKey = $this->normalizeKey($group['label']);
                if ($groupKey === '') {
                    continue;
                }

                if (! array_key_exists($groupKey, $ticketGroups)) {
                    $ticketGroups[$groupKey] = [
                        'label' => $group['label'],
                        'tickets' => [],
                    ];
                }

                foreach ($group['tickets'] as $ticket) {
                    $ticketKey = $this->normalizeKey($ticket['label']);
                    if ($ticketKey === '' || array_key_exists($ticketKey, $ticketGroups[$groupKey]['tickets'])) {
                        continue;
                    }

                    $ticketGroups[$groupKey]['tickets'][$ticketKey] = $ticket;
                }
            }
        }

        $sortOrder = 1;

        foreach (array_values($ungroupedTickets) as $ticket) {
            $list->items()->create([
                'label' => $ticket['label'],
                'value' => $ticket['label'],
                'metadata' => [
                    'kind' => 'ticket',
                    'tickets' => [],
                ],
                'is_active' => (bool) ($ticket['is_active'] ?? true),
                'sort_order' => $sortOrder++,
            ]);
        }

        foreach (array_values($ticketGroups) as $group) {
            $list->items()->create([
                'label' => $group['label'],
                'value' => $group['label'],
                'metadata' => [
                    'kind' => 'group',
                    'tickets' => array_values($group['tickets']),
                ],
                'is_active' => true,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    private function normalizePredefinedTicketKind(array $item): string
    {
        return ($item['kind'] ?? null) === 'group' ? 'group' : 'ticket';
    }

    private function resolveFallbackPaymentModeMetadata(string $value): array
    {
        $normalized = $this->normalizeKey($value);

        if (in_array($normalized, ['espece', 'especes', 'cash', 'liquide'], true)) {
            return [
                'payment_type' => 'cash',
                'transfer_mode' => null,
                'is_default' => true,
                'payment_timing' => 'immediate',
                'fields' => $this->normalizePaymentFieldConfig([]),
            ];
        }

        if (str_contains($normalized, 'carte') || str_contains($normalized, 'card')) {
            return [
                'payment_type' => 'card',
                'transfer_mode' => null,
                'is_default' => false,
                'payment_timing' => 'immediate',
                'fields' => $this->normalizePaymentFieldConfig([
                    'show_transaction_number' => true,
                    'show_notes' => true,
                ]),
            ];
        }

        if (str_contains($normalized, 'mobile')) {
            return [
                'payment_type' => 'mobile',
                'transfer_mode' => null,
                'is_default' => false,
                'payment_timing' => 'immediate',
                'fields' => $this->normalizePaymentFieldConfig([
                    'show_transaction_number' => true,
                    'show_notes' => true,
                ]),
            ];
        }

        if ((str_contains($normalized, 'instant') || str_contains($normalized, 'instantane'))
            && (str_contains($normalized, 'virement') || str_contains($normalized, 'transfer'))) {
            return [
                'payment_type' => 'virement',
                'transfer_mode' => 'instant',
                'is_default' => false,
                'payment_timing' => 'immediate',
                'fields' => $this->normalizePaymentFieldConfig([
                    'show_transaction_number' => true,
                    'show_bank_name' => true,
                    'show_notes' => true,
                ]),
            ];
        }

        if (str_contains($normalized, 'virement') || str_contains($normalized, 'transfer')) {
            return [
                'payment_type' => 'virement',
                'transfer_mode' => 'simple',
                'is_default' => false,
                'payment_timing' => 'deferred',
                'fields' => $this->normalizePaymentFieldConfig([
                    'show_transaction_number' => true,
                    'show_piece_number' => true,
                    'show_issue_date' => true,
                    'show_due_date' => true,
                    'show_bank_name' => true,
                    'show_notes' => true,
                ]),
            ];
        }

        if (str_contains($normalized, 'credit') || str_contains($normalized, 'lcn')) {
            return [
                'payment_type' => 'credit',
                'transfer_mode' => null,
                'is_default' => false,
                'payment_timing' => 'deferred',
                'fields' => $this->normalizePaymentFieldConfig([
                    'show_piece_number' => true,
                    'show_issue_date' => true,
                    'show_due_date' => true,
                    'show_bank_name' => true,
                    'show_notes' => true,
                ]),
            ];
        }

        return [
            'payment_type' => 'other',
            'transfer_mode' => null,
            'is_default' => false,
            'payment_timing' => 'immediate',
            'fields' => $this->normalizePaymentFieldConfig([
                'show_notes' => true,
            ]),
        ];
    }

    private function normalizeTicketGroups(array $groups): array
    {
        return collect($groups)
            ->map(function ($group, int $index) {
                return [
                    'id' => $group['id'] ?? null,
                    'label' => trim((string) ($group['label'] ?? '')),
                    'is_active' => (bool) ($group['is_active'] ?? true),
                    'sort_order' => (int) ($group['sort_order'] ?? ($index + 1)),
                    'tickets' => $this->normalizeTicketCollection($group['tickets'] ?? []),
                ];
            })
            ->filter(fn (array $group) => $group['label'] !== '')
            ->sortBy('sort_order')
            ->values()
            ->all();
    }

    private function normalizeTicketCollection(array $tickets): array
    {
        return collect($tickets)
            ->map(function ($ticket, int $index) {
                return [
                    'id' => $ticket['id'] ?? null,
                    'label' => trim((string) ($ticket['label'] ?? '')),
                    'is_active' => (bool) ($ticket['is_active'] ?? true),
                    'sort_order' => (int) ($ticket['sort_order'] ?? ($index + 1)),
                ];
            })
            ->filter(fn (array $ticket) => $ticket['label'] !== '')
            ->sortBy('sort_order')
            ->values()
            ->all();
    }

    private function resolveServiceModeMetadata(string $value): array
    {
        $resolvedLabel = $this->resolveServiceMode($value);
        $list = $this->ensureList(self::SERVICE_MODE_LIST);
        $normalized = $this->normalizeKey($resolvedLabel);

        $matched = $list->items->first(
            fn (CustomListItem $item) => $this->normalizeKey($item->label) === $normalized
                || $this->normalizeKey($item->value ?: $item->label) === $normalized
        );

        if ($matched) {
            $metadata = $this->normalizeSerializedMetadata(self::SERVICE_MODE_LIST, $matched);

            return [
                'operational_mode' => $metadata['operational_mode'],
                'requires_delivery_agent' => $metadata['requires_delivery_agent'],
            ];
        }

        return $this->resolveFallbackServiceModeMetadata($value);
    }

    private function resolveFallbackServiceModeMetadata(string $value): array
    {
        $mappedValue = $this->mapLegacyServiceMode($value);
        $normalized = $this->normalizeKey($mappedValue);

        return self::FALLBACK_SERVICE_MODE_METADATA[$normalized] ?? [
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

    private function syncPlatformServiceModesFromDeliveryAgents(): void
    {
        $list = $this->ensureList(self::SERVICE_MODE_LIST);
        $platformStates = $this->resolvePlatformStates();

        foreach ($platformStates as $platformState) {
            $this->upsertPlatformServiceMode($list, $platformState['label'], $platformState['is_active']);
        }

        $list->items->each(function (CustomListItem $item) use ($platformStates) {
            $metadata = is_array($item->metadata) ? $item->metadata : [];
            if (($metadata['source'] ?? null) !== 'platform') {
                return;
            }

            $normalized = $this->normalizeKey($item->value ?: $item->label);
            if ($normalized === '' || array_key_exists($normalized, $platformStates)) {
                return;
            }

            if ($item->is_active) {
                $item->forceFill(['is_active' => false])->save();
            }
        });
    }

    private function resolvePlatformStates(): array
    {
        $states = [];

        DeliveryAgent::query()
            ->where('type', 'platform')
            ->get(['platform_name', 'active'])
            ->each(function (DeliveryAgent $agent) use (&$states) {
                $label = trim((string) $agent->platform_name);
                $normalized = $this->normalizeKey($label);

                if ($label === '' || $normalized === '') {
                    return;
                }

                if (! array_key_exists($normalized, $states)) {
                    $states[$normalized] = [
                        'label' => $label,
                        'is_active' => false,
                    ];
                }

                $states[$normalized]['is_active'] = $states[$normalized]['is_active'] || (bool) $agent->active;
            });

        return $states;
    }

    private function upsertPlatformServiceMode(CustomList $list, string $label, bool $isActive): void
    {
        $normalized = $this->normalizeKey($label);
        if ($normalized === '') {
            return;
        }

        $matched = $list->items->first(
            fn (CustomListItem $item) => $this->normalizeKey($item->label) === $normalized
                || $this->normalizeKey($item->value ?: $item->label) === $normalized
        );

        $existingMetadata = is_array($matched?->metadata) ? $matched->metadata : [];
        $metadata = $this->mergeMetadata(
            $existingMetadata,
            [
                'operational_mode' => 'delivery',
                'requires_delivery_agent' => false,
                'source' => 'platform',
            ]
        );

        if (! $matched) {
            $created = $list->items()->create([
                'label' => $label,
                'value' => $label,
                'metadata' => $metadata,
                'is_active' => $isActive,
                'sort_order' => ((int) $list->items()->max('sort_order')) + 1,
            ]);

            $list->setRelation('items', $list->items->push($created));
            return;
        }

        $updates = [];

        if ($matched->label !== $label) {
            $updates['label'] = $label;
        }

        if (($matched->value ?: $matched->label) !== $label) {
            $updates['value'] = $label;
        }

        if ($matched->metadata !== $metadata) {
            $updates['metadata'] = $metadata;
        }

        if ($updates !== []) {
            $matched->forceFill($updates)->save();
        }
    }

    private function normalizeKey(string $value): string
    {
        return (string) Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->trim();
    }

    private function normalizePaymentFieldConfig(array $item): array
    {
        $fields = is_array($item['fields'] ?? null) ? $item['fields'] : $item;

        return [
            'transaction_number' => (bool) ($fields['transaction_number'] ?? $fields['show_transaction_number'] ?? false),
            'piece_number' => (bool) ($fields['piece_number'] ?? $fields['show_piece_number'] ?? false),
            'issue_date' => (bool) ($fields['issue_date'] ?? $fields['show_issue_date'] ?? false),
            'due_date' => (bool) ($fields['due_date'] ?? $fields['show_due_date'] ?? false),
            'bank_name' => (bool) ($fields['bank_name'] ?? $fields['show_bank_name'] ?? false),
            'notes' => (bool) ($fields['notes'] ?? $fields['show_notes'] ?? false),
        ];
    }

    private function mergeMetadata(array $existing, array $incoming): array
    {
        if (array_key_exists('fields', $existing) || array_key_exists('fields', $incoming)) {
            $existing['fields'] = $this->normalizePaymentFieldConfig($existing);
            $incoming['fields'] = $this->normalizePaymentFieldConfig($incoming);
        }

        return array_replace_recursive($existing, $incoming);
    }

    private function isSystemItem(mixed $metadata): bool
    {
        return is_array($metadata) && (bool) ($metadata['is_system'] ?? false);
    }
}
