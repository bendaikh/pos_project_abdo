import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { customListsApi } from '../api'

export const PREDEFINED_TICKET_LIST_NAME = 'tickets_predefinis'
export const SERVICE_MODE_LIST_NAME = 'mode_de_service'
export const PAYMENT_MODE_LIST_NAME = 'mode_de_paiement'
export const TAX_LIST_NAME = 'taxes'
export const DISCOUNT_LIST_NAME = 'remises'
export const EXPENSE_LIST_NAME = 'depenses'

const STORAGE_PREFIX = 'custom_list_cache_'

const FALLBACK_PREDEFINED_TICKETS_LIST = {
    id: null,
    name: PREDEFINED_TICKET_LIST_NAME,
    is_active: true,
    items: [],
}

const FALLBACK_SERVICE_MODE_LIST = {
    id: null,
    name: SERVICE_MODE_LIST_NAME,
    is_active: true,
    items: [
        {
            id: 'fallback-sur-place',
            label: 'Sur place',
            value: 'Sur place',
            is_active: true,
            sort_order: 1,
            operational_mode: 'dine_in',
            requires_delivery_agent: false,
            is_system: true,
            system_key: 'sur_place',
        },
        {
            id: 'fallback-emporte',
            label: 'Emporté',
            value: 'Emporté',
            is_active: true,
            sort_order: 2,
            operational_mode: 'pickup',
            requires_delivery_agent: false,
            is_system: true,
            system_key: 'emporte',
        },
        {
            id: 'fallback-livraison',
            label: 'Livraison',
            value: 'Livraison',
            is_active: true,
            sort_order: 3,
            operational_mode: 'delivery',
            requires_delivery_agent: true,
            is_system: true,
            system_key: 'livraison',
        },
    ],
}

const FALLBACK_PAYMENT_MODE_LIST = {
    id: null,
    name: PAYMENT_MODE_LIST_NAME,
    is_active: true,
    items: [
        {
            id: 'fallback-espece',
            label: 'Espèce',
            value: 'Espèce',
            is_active: true,
            sort_order: 1,
            payment_type: 'cash',
            transfer_mode: null,
            is_default: true,
            payment_timing: 'immediate',
            show_transaction_number: false,
            show_piece_number: false,
            show_issue_date: false,
            show_due_date: false,
            show_bank_name: false,
            show_notes: true,
            is_system: true,
            system_key: 'espece',
        },
        {
            id: 'fallback-carte',
            label: 'Carte',
            value: 'Carte',
            is_active: true,
            sort_order: 2,
            payment_type: 'card',
            transfer_mode: null,
            is_default: false,
            payment_timing: 'immediate',
            show_transaction_number: true,
            show_piece_number: false,
            show_issue_date: false,
            show_due_date: false,
            show_bank_name: false,
            show_notes: true,
            is_system: true,
            system_key: 'carte',
        },
        {
            id: 'fallback-mobile',
            label: 'Mobile',
            value: 'Mobile',
            is_active: true,
            sort_order: 3,
            payment_type: 'mobile',
            transfer_mode: null,
            is_default: false,
            payment_timing: 'immediate',
            show_transaction_number: true,
            show_piece_number: false,
            show_issue_date: false,
            show_due_date: false,
            show_bank_name: false,
            show_notes: true,
            is_system: true,
            system_key: 'mobile',
        },
        {
            id: 'fallback-virement-instant',
            label: 'Virement instantané',
            value: 'Virement instantané',
            is_active: true,
            sort_order: 4,
            payment_type: 'virement',
            transfer_mode: 'instant',
            is_default: false,
            payment_timing: 'immediate',
            show_transaction_number: true,
            show_piece_number: false,
            show_issue_date: false,
            show_due_date: false,
            show_bank_name: true,
            show_notes: true,
            is_system: true,
            system_key: 'virement_instantane',
        },
        {
            id: 'fallback-virement-simple',
            label: 'Virement simple',
            value: 'Virement simple',
            is_active: true,
            sort_order: 5,
            payment_type: 'virement',
            transfer_mode: 'simple',
            is_default: false,
            payment_timing: 'deferred',
            show_transaction_number: true,
            show_piece_number: true,
            show_issue_date: true,
            show_due_date: true,
            show_bank_name: true,
            show_notes: true,
            is_system: true,
            system_key: 'virement_simple',
        },
        {
            id: 'fallback-credit',
            label: 'Crédit',
            value: 'Crédit',
            is_active: true,
            sort_order: 6,
            payment_type: 'credit',
            transfer_mode: null,
            is_default: false,
            payment_timing: 'deferred',
            show_transaction_number: false,
            show_piece_number: true,
            show_issue_date: true,
            show_due_date: true,
            show_bank_name: true,
            show_notes: true,
            is_system: true,
            system_key: 'credit',
        },
    ],
}

const FALLBACK_TAX_LIST = {
    id: null,
    name: TAX_LIST_NAME,
    is_active: true,
    items: [],
}

const FALLBACK_DISCOUNT_LIST = {
    id: null,
    name: DISCOUNT_LIST_NAME,
    is_active: true,
    items: [],
}

const FALLBACK_EXPENSE_LIST = {
    id: null,
    name: EXPENSE_LIST_NAME,
    is_active: true,
    items: [],
}

const LEGACY_SERVICE_MODE_LABELS = {
    dine_in: 'Sur place',
    sur_place: 'Sur place',
    pickup: 'Emporté',
    a_emporter: 'Emporté',
    takeaway: 'Emporté',
    delivery: 'Livraison',
    livraison: 'Livraison',
}

function clone(value) {
    return JSON.parse(JSON.stringify(value))
}

function normalizeKey(value) {
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '')
}

function normalizeServiceModeValue(value) {
    const normalized = normalizeKey(value)
    const matchedLegacy = Object.entries(LEGACY_SERVICE_MODE_LABELS).find(
        ([legacyValue]) => normalizeKey(legacyValue) === normalized
    )

    return matchedLegacy ? matchedLegacy[1] : String(value || '').trim()
}

function inferPaymentModeMeta(label, item = {}) {
    const normalized = normalizeKey(label || item?.value || '')

    if (['espece', 'especes', 'cash', 'liquide'].includes(normalized)) {
        return {
            payment_type: 'cash',
            transfer_mode: null,
            is_default: true,
            payment_timing: 'immediate',
            show_transaction_number: false,
            show_piece_number: false,
            show_issue_date: false,
            show_due_date: false,
            show_bank_name: false,
            show_notes: true,
        }
    }

    if (normalized.includes('carte') || normalized.includes('card')) {
        return {
            payment_type: 'card',
            transfer_mode: null,
            is_default: false,
            payment_timing: 'immediate',
            show_transaction_number: true,
            show_piece_number: false,
            show_issue_date: false,
            show_due_date: false,
            show_bank_name: false,
            show_notes: true,
        }
    }

    if (normalized.includes('mobile')) {
        return {
            payment_type: 'mobile',
            transfer_mode: null,
            is_default: false,
            payment_timing: 'immediate',
            show_transaction_number: true,
            show_piece_number: false,
            show_issue_date: false,
            show_due_date: false,
            show_bank_name: false,
            show_notes: true,
        }
    }

    if ((normalized.includes('instant') || normalized.includes('instantane'))
        && (normalized.includes('virement') || normalized.includes('transfer'))) {
        return {
            payment_type: 'virement',
            transfer_mode: 'instant',
            is_default: false,
            payment_timing: 'immediate',
            show_transaction_number: true,
            show_piece_number: false,
            show_issue_date: false,
            show_due_date: false,
            show_bank_name: true,
            show_notes: true,
        }
    }

    if (normalized.includes('virement') || normalized.includes('transfer')) {
        return {
            payment_type: 'virement',
            transfer_mode: 'simple',
            is_default: false,
            payment_timing: 'deferred',
            show_transaction_number: true,
            show_piece_number: true,
            show_issue_date: true,
            show_due_date: true,
            show_bank_name: true,
            show_notes: true,
        }
    }

    if (normalized.includes('credit') || normalized.includes('lcn')) {
        return {
            payment_type: 'credit',
            transfer_mode: null,
            is_default: false,
            payment_timing: 'deferred',
            show_transaction_number: false,
            show_piece_number: true,
            show_issue_date: true,
            show_due_date: true,
            show_bank_name: true,
            show_notes: true,
        }
    }

    return {
        payment_type: item?.payment_type || 'other',
        transfer_mode: item?.transfer_mode ?? null,
        is_default: item?.is_default === true,
        payment_timing: item?.payment_timing === 'deferred' ? 'deferred' : 'immediate',
        show_transaction_number: item?.show_transaction_number === true,
        show_piece_number: item?.show_piece_number === true,
        show_issue_date: item?.show_issue_date === true,
        show_due_date: item?.show_due_date === true,
        show_bank_name: item?.show_bank_name === true,
        show_notes: item?.show_notes !== false,
    }
}

function normalizeTicket(ticket, index) {
    return {
        id: ticket?.id ?? `generated-ticket-${index}`,
        label: String(ticket?.label || '').trim(),
        is_active: ticket?.is_active !== false,
        sort_order: Number(ticket?.sort_order ?? index + 1),
    }
}

function normalizePredefinedTicketItem(item, index) {
    const tickets = [...(item?.tickets || [])]
        .map((ticket, ticketIndex) => normalizeTicket(ticket, ticketIndex))
        .filter((ticket) => ticket.label)
        .sort((a, b) => a.sort_order - b.sort_order)

    return {
        id: item?.id ?? `generated-ticket-entry-${index}`,
        label: String(item?.label || item?.value || '').trim(),
        value: String(item?.value || item?.label || '').trim(),
        is_active: item?.is_active !== false,
        sort_order: Number(item?.sort_order ?? index + 1),
        kind: item?.kind === 'group' ? 'group' : 'ticket',
        tickets,
    }
}

function normalizeServiceModeItem(item, index) {
    return {
        id: item?.id ?? `generated-service-mode-${index}`,
        label: String(item?.label || item?.value || '').trim(),
        value: String(item?.value || item?.label || '').trim(),
        is_active: item?.is_active !== false,
        sort_order: Number(item?.sort_order ?? index + 1),
        operational_mode: item?.operational_mode || inferServiceModeMeta(item).operational_mode,
        requires_delivery_agent: item?.requires_delivery_agent === true
            || inferServiceModeMeta(item).requires_delivery_agent,
        is_system: item?.is_system === true,
        system_key: item?.system_key || null,
        source: item?.source || null,
    }
}

function normalizePaymentModeItem(item, index) {
    const label = String(item?.label || item?.value || '').trim()
    const inferred = inferPaymentModeMeta(label, item)

    return {
        id: item?.id ?? `generated-payment-mode-${index}`,
        label,
        value: String(item?.value || item?.label || '').trim(),
        is_active: item?.is_active !== false,
        sort_order: Number(item?.sort_order ?? index + 1),
        payment_type: item?.payment_type || inferred.payment_type,
        transfer_mode: item?.transfer_mode ?? inferred.transfer_mode,
        is_default: item?.is_default === true || (item?.is_default == null && inferred.is_default),
        payment_timing: item?.payment_timing === 'deferred' ? 'deferred' : inferred.payment_timing,
        show_transaction_number: item?.show_transaction_number ?? inferred.show_transaction_number,
        show_piece_number: item?.show_piece_number ?? inferred.show_piece_number,
        show_issue_date: item?.show_issue_date ?? inferred.show_issue_date,
        show_due_date: item?.show_due_date ?? inferred.show_due_date,
        show_bank_name: item?.show_bank_name ?? inferred.show_bank_name,
        show_notes: item?.show_notes ?? inferred.show_notes,
        is_system: item?.is_system === true,
        system_key: item?.system_key || null,
    }
}

function normalizeTaxItem(item, index) {
    return {
        id: item?.id ?? `generated-tax-${index}`,
        label: String(item?.label || item?.value || '').trim(),
        value: String(item?.value || item?.label || '').trim(),
        is_active: item?.is_active !== false,
        sort_order: Number(item?.sort_order ?? index + 1),
        tax_type: item?.tax_type === 'fixed' ? 'fixed' : 'percentage',
        tax_rate: Number(item?.tax_rate ?? 0),
        tax_is_default: item?.tax_is_default === true,
    }
}

function normalizeDiscountItem(item, index) {
    return {
        id: item?.id ?? `generated-discount-${index}`,
        label: String(item?.label || item?.value || '').trim(),
        value: String(item?.value || item?.label || '').trim(),
        is_active: item?.is_active !== false,
        sort_order: Number(item?.sort_order ?? index + 1),
        discount_type: item?.discount_type === 'fixed' ? 'fixed' : 'percentage',
        discount_value: Number(item?.discount_value ?? 0),
        discount_limit: Number(item?.discount_limit ?? 0),
    }
}

function normalizeExpenseItem(item, index) {
    const isRecurring = item?.expense_is_recurring === true

    return {
        id: item?.id ?? `generated-expense-${index}`,
        label: String(item?.label || item?.value || '').trim(),
        value: String(item?.value || item?.label || '').trim(),
        is_active: item?.is_active !== false,
        sort_order: Number(item?.sort_order ?? index + 1),
        expense_category: String(item?.expense_category || '').trim(),
        expense_type: item?.expense_type === 'variable' ? 'variable' : 'fixed',
        expense_is_recurring: isRecurring,
        expense_frequency: isRecurring ? (item?.expense_frequency || 'monthly') : null,
    }
}

function inferServiceModeMeta(item) {
    const normalized = normalizeKey(item?.label || item?.value || '')

    if (normalized === normalizeKey('Livraison')) {
        return {
            operational_mode: 'delivery',
            requires_delivery_agent: true,
        }
    }

    if (normalized === normalizeKey('Sur place')) {
        return {
            operational_mode: 'dine_in',
            requires_delivery_agent: false,
        }
    }

    return {
        operational_mode: 'pickup',
        requires_delivery_agent: false,
    }
}

function normalizeListPayload(list, name = list?.name) {
    if (name === PREDEFINED_TICKET_LIST_NAME) {
        const base = list && list.name === name ? list : FALLBACK_PREDEFINED_TICKETS_LIST
        return {
            id: base.id ?? null,
            name,
            is_active: base.is_active !== false,
            items: [...(base.items || [])]
                .map((item, index) => normalizePredefinedTicketItem(item, index))
                .filter((item) => item.label)
                .sort((a, b) => a.sort_order - b.sort_order),
        }
    }

    if (name === PAYMENT_MODE_LIST_NAME) {
        const base = list && list.name === name ? list : FALLBACK_PAYMENT_MODE_LIST
        const items = [...(base.items || [])]
            .map((item, index) => normalizePaymentModeItem(item, index))
            .filter((item) => item.label)
            .sort((a, b) => a.sort_order - b.sort_order)

        const defaultIndex = items.findIndex((item) => item.is_default === true)
        if (defaultIndex < 0 && items.length > 0) {
            items[0] = {
                ...items[0],
                is_default: true,
            }
        }

        return {
            id: base.id ?? null,
            name,
            is_active: base.is_active !== false,
            items,
        }
    }

    if (name === TAX_LIST_NAME) {
        const base = list && list.name === name ? list : FALLBACK_TAX_LIST

        return {
            id: base.id ?? null,
            name,
            is_active: base.is_active !== false,
            items: [...(base.items || [])]
                .map((item, index) => normalizeTaxItem(item, index))
                .filter((item) => item.label)
                .sort((a, b) => a.sort_order - b.sort_order),
        }
    }

    if (name === DISCOUNT_LIST_NAME) {
        const base = list && list.name === name ? list : FALLBACK_DISCOUNT_LIST

        return {
            id: base.id ?? null,
            name,
            is_active: base.is_active !== false,
            items: [...(base.items || [])]
                .map((item, index) => normalizeDiscountItem(item, index))
                .filter((item) => item.label)
                .sort((a, b) => a.sort_order - b.sort_order),
        }
    }

    if (name === EXPENSE_LIST_NAME) {
        const base = list && list.name === name ? list : FALLBACK_EXPENSE_LIST

        return {
            id: base.id ?? null,
            name,
            is_active: base.is_active !== false,
            items: [...(base.items || [])]
                .map((item, index) => normalizeExpenseItem(item, index))
                .filter((item) => item.label)
                .sort((a, b) => a.sort_order - b.sort_order),
        }
    }

    const base = list && list.name === SERVICE_MODE_LIST_NAME
        ? list
        : FALLBACK_SERVICE_MODE_LIST

    return {
        id: base.id ?? null,
        name: SERVICE_MODE_LIST_NAME,
        is_active: base.is_active !== false,
        items: [...(base.items || [])]
            .map((item, index) => normalizeServiceModeItem(item, index))
            .filter((item) => item.label)
            .sort((a, b) => a.sort_order - b.sort_order),
    }
}

export const useCustomListsStore = defineStore('customLists', () => {
    const lists = ref({
        [PREDEFINED_TICKET_LIST_NAME]: clone(FALLBACK_PREDEFINED_TICKETS_LIST),
        [SERVICE_MODE_LIST_NAME]: clone(FALLBACK_SERVICE_MODE_LIST),
        [PAYMENT_MODE_LIST_NAME]: clone(FALLBACK_PAYMENT_MODE_LIST),
        [TAX_LIST_NAME]: clone(FALLBACK_TAX_LIST),
        [DISCOUNT_LIST_NAME]: clone(FALLBACK_DISCOUNT_LIST),
        [EXPENSE_LIST_NAME]: clone(FALLBACK_EXPENSE_LIST),
    })
    const loadedLists = ref({})
    const loadingLists = ref({})

    function getCacheKey(name) {
        return `${STORAGE_PREFIX}${name}`
    }

    function getFallbackList(name) {
        if (name === PREDEFINED_TICKET_LIST_NAME) {
            return clone(FALLBACK_PREDEFINED_TICKETS_LIST)
        }

        if (name === PAYMENT_MODE_LIST_NAME) {
            return clone(FALLBACK_PAYMENT_MODE_LIST)
        }

        if (name === TAX_LIST_NAME) {
            return clone(FALLBACK_TAX_LIST)
        }

        if (name === DISCOUNT_LIST_NAME) {
            return clone(FALLBACK_DISCOUNT_LIST)
        }

        if (name === EXPENSE_LIST_NAME) {
            return clone(FALLBACK_EXPENSE_LIST)
        }

        return clone(FALLBACK_SERVICE_MODE_LIST)
    }

    function setList(name, payload) {
        const normalized = normalizeListPayload(
            {
                ...payload,
                name,
            },
            name
        )

        lists.value = {
            ...lists.value,
            [name]: normalized,
        }

        try {
            localStorage.setItem(getCacheKey(name), JSON.stringify(normalized))
        } catch (error) {
            console.warn('Failed to cache custom list:', error)
        }

        return normalized
    }

    function loadCachedList(name) {
        try {
            const raw = localStorage.getItem(getCacheKey(name))
            if (!raw) return null
            return normalizeListPayload(JSON.parse(raw), name)
        } catch (error) {
            console.warn('Failed to load cached custom list:', error)
            return null
        }
    }

    async function fetchList(name, { force = false, activeOnly = false } = {}) {
        if (loadedLists.value[name] && !force && !activeOnly) {
            return lists.value[name] || getFallbackList(name)
        }

        if (loadingLists.value[name]) {
            return lists.value[name] || getFallbackList(name)
        }

        loadingLists.value = {
            ...loadingLists.value,
            [name]: true,
        }

        try {
            const { data } = await customListsApi.get(name, activeOnly ? { active_only: true } : {})
            loadedLists.value = {
                ...loadedLists.value,
                [name]: true,
            }
            return setList(name, data)
        } catch (error) {
            console.error(`Failed to fetch custom list "${name}":`, error)
            const cached = loadCachedList(name)
            if (cached) {
                lists.value = {
                    ...lists.value,
                    [name]: cached,
                }
                return cached
            }
            return getFallbackList(name)
        } finally {
            loadingLists.value = {
                ...loadingLists.value,
                [name]: false,
            }
        }
    }

    const predefinedTicketsList = computed(() => {
        return normalizeListPayload(
            lists.value[PREDEFINED_TICKET_LIST_NAME] || FALLBACK_PREDEFINED_TICKETS_LIST,
            PREDEFINED_TICKET_LIST_NAME
        )
    })

    const serviceModeList = computed(() => {
        return normalizeListPayload(
            lists.value[SERVICE_MODE_LIST_NAME] || FALLBACK_SERVICE_MODE_LIST,
            SERVICE_MODE_LIST_NAME
        )
    })

    const paymentModeList = computed(() => {
        return normalizeListPayload(
            lists.value[PAYMENT_MODE_LIST_NAME] || FALLBACK_PAYMENT_MODE_LIST,
            PAYMENT_MODE_LIST_NAME
        )
    })

    const expenseList = computed(() => {
        return normalizeListPayload(
            lists.value[EXPENSE_LIST_NAME] || FALLBACK_EXPENSE_LIST,
            EXPENSE_LIST_NAME
        )
    })

    const serviceModeEnabled = computed(() => serviceModeList.value.is_active !== false)
    const activeServiceModes = computed(() => {
        if (!serviceModeEnabled.value) {
            return []
        }

        return serviceModeList.value.items.filter((item) => item.is_active !== false)
    })

    const activePaymentModes = computed(() => {
        if (paymentModeList.value.is_active === false) {
            return []
        }

        return paymentModeList.value.items.filter((item) => item.is_active !== false)
    })

    const defaultPaymentMode = computed(() => {
        return activePaymentModes.value.find((item) => item.is_default === true)
            || paymentModeList.value.items.find((item) => item.is_default === true)
            || activePaymentModes.value[0]
            || paymentModeList.value.items[0]
            || null
    })

    function findServiceMode(value, { includeInactive = true } = {}) {
        const normalizedValue = normalizeKey(normalizeServiceModeValue(value))
        if (!normalizedValue) return null

        const sourceItems = includeInactive
            ? serviceModeList.value.items
            : activeServiceModes.value

        return sourceItems.find((item) => {
            return normalizeKey(item.value) === normalizedValue || normalizeKey(item.label) === normalizedValue
        }) || null
    }

    function getServiceModeLabel(value) {
        const match = findServiceMode(value)
        if (match) return match.label

        return normalizeServiceModeValue(value) || FALLBACK_SERVICE_MODE_LIST.items[0].label
    }

    function getServiceModeMeta(value) {
        const match = findServiceMode(value)

        if (match) {
            return {
                operational_mode: match.operational_mode || 'pickup',
                requires_delivery_agent: match.requires_delivery_agent === true,
            }
        }

        return inferServiceModeMeta({ label: normalizeServiceModeValue(value) })
    }

    function getPredefinedTickets({ includeInactive = false } = {}) {
        const ticketFilter = includeInactive
            ? () => true
            : (entry) => entry.is_active !== false

        return {
            tickets_without_group: predefinedTicketsList.value.items
                .filter((item) => item.kind !== 'group')
                .filter(ticketFilter)
                .sort((a, b) => a.sort_order - b.sort_order)
                .map((item) => ({
                    id: item.id,
                    label: item.label,
                    is_active: item.is_active,
                    sort_order: item.sort_order,
                })),
            ticket_groups: predefinedTicketsList.value.items
                .filter((item) => item.kind === 'group')
                .filter(ticketFilter)
                .sort((a, b) => a.sort_order - b.sort_order)
                .map((group) => ({
                    id: group.id,
                    label: group.label,
                    is_active: group.is_active,
                    sort_order: group.sort_order,
                    tickets: (group.tickets || [])
                        .filter(ticketFilter)
                        .sort((a, b) => a.sort_order - b.sort_order),
                })),
        }
    }

    function defaultServiceModeValue() {
        return activeServiceModes.value[0]?.value
            || serviceModeList.value.items[0]?.value
            || FALLBACK_SERVICE_MODE_LIST.items[0].value
    }

    return {
        lists,
        loadedLists,
        loadingLists,
        predefinedTicketsList,
        serviceModeList,
        paymentModeList,
        expenseList,
        serviceModeEnabled,
        activeServiceModes,
        activePaymentModes,
        defaultPaymentMode,
        fetchList,
        setList,
        findServiceMode,
        getServiceModeLabel,
        getServiceModeMeta,
        getPredefinedTickets,
        defaultServiceModeValue,
    }
})
