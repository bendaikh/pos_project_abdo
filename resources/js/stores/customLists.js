import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { customListsApi } from '../api'

const SERVICE_MODE_LIST_NAME = 'mode_de_service'
const STORAGE_PREFIX = 'custom_list_cache_'

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
        },
        {
            id: 'fallback-emporte',
            label: 'Emporté',
            value: 'Emporté',
            is_active: true,
            sort_order: 2,
            operational_mode: 'pickup',
            requires_delivery_agent: false,
        },
        {
            id: 'fallback-livraison',
            label: 'Livraison',
            value: 'Livraison',
            is_active: true,
            sort_order: 3,
            operational_mode: 'delivery',
            requires_delivery_agent: true,
        },
    ],
}

const LEGACY_SERVICE_MODE_LABELS = {
    dine_in: 'Sur place',
    sur_place: 'Sur place',
    pickup: 'Emporté',
    a_emporter: 'Emporté',
    takeaway: 'Emporté',
    delivery: 'Livraison',
    livraison: 'Livraison',
    glovo: 'Livraison',
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

function normalizeListPayload(list) {
    const base = list && list.name === SERVICE_MODE_LIST_NAME
        ? list
        : FALLBACK_SERVICE_MODE_LIST

    return {
        id: base.id ?? null,
        name: base.name,
        is_active: base.is_active !== false,
        items: [...(base.items || [])]
            .map((item, index) => ({
                id: item.id ?? `generated-${index}`,
                label: item.label || item.value || '',
                value: item.value || item.label || '',
                is_active: item.is_active !== false,
                sort_order: Number(item.sort_order ?? index + 1),
                operational_mode: item.operational_mode || 'pickup',
                requires_delivery_agent: item.requires_delivery_agent === true,
            }))
            .sort((a, b) => a.sort_order - b.sort_order),
    }
}

export const useCustomListsStore = defineStore('customLists', () => {
    const lists = ref({
        [SERVICE_MODE_LIST_NAME]: clone(FALLBACK_SERVICE_MODE_LIST),
    })
    const loadedLists = ref({})
    const loadingLists = ref({})

    function getCacheKey(name) {
        return `${STORAGE_PREFIX}${name}`
    }

    function setList(name, payload) {
        const normalized = normalizeListPayload({
            ...payload,
            name,
        })

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

    function getFallbackList(name) {
        if (name === SERVICE_MODE_LIST_NAME) {
            return clone(FALLBACK_SERVICE_MODE_LIST)
        }

        return {
            id: null,
            name,
            is_active: true,
            items: [],
        }
    }

    function loadCachedList(name) {
        try {
            const raw = localStorage.getItem(getCacheKey(name))
            if (!raw) return null
            return normalizeListPayload(JSON.parse(raw))
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

        const normalizedValue = normalizeKey(normalizeServiceModeValue(value))

        if (normalizedValue === normalizeKey('Livraison')) {
            return {
                operational_mode: 'delivery',
                requires_delivery_agent: true,
            }
        }

        if (normalizedValue === normalizeKey('Sur place')) {
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

    function defaultServiceModeValue() {
        return activeServiceModes.value[0]?.value
            || serviceModeList.value.items[0]?.value
            || FALLBACK_SERVICE_MODE_LIST.items[0].value
    }

    const serviceModeList = computed(() => {
        return normalizeListPayload(
            lists.value[SERVICE_MODE_LIST_NAME] || FALLBACK_SERVICE_MODE_LIST
        )
    })

    const serviceModeEnabled = computed(() => serviceModeList.value.is_active !== false)

    const activeServiceModes = computed(() => {
        if (!serviceModeEnabled.value) {
            return []
        }

        return serviceModeList.value.items.filter((item) => item.is_active !== false)
    })

    return {
        lists,
        loadedLists,
        loadingLists,
        serviceModeList,
        serviceModeEnabled,
        activeServiceModes,
        fetchList,
        setList,
        findServiceMode,
        getServiceModeLabel,
        getServiceModeMeta,
        defaultServiceModeValue,
    }
})
