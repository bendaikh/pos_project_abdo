<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center px-4 py-6">
            <div class="fixed inset-0 bg-slate-900/45 backdrop-blur-[2px]" @click="$emit('close')"></div>

            <div class="relative z-10 w-full max-w-6xl overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-2xl">
                <div class="border-b border-slate-100 px-6 py-4">
                    <div class="relative flex items-center justify-center">
                        <h2 class="text-2xl font-semibold text-slate-900">Tickets Enregistrés</h2>
                        <button
                            type="button"
                            class="absolute right-0 inline-flex h-10 w-10 items-center justify-center rounded-full text-3xl leading-none text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                            @click="$emit('close')"
                        >
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>

                <div class="px-6 pt-4">
                    <div class="rounded-[22px] bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        Cliquez sur une ligne pour sélectionner un ticket, puis sur "Sélectionner" pour l’ouvrir dans le POS.
                    </div>
                </div>

                <div class="px-6 pb-6 pt-4">
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex flex-col gap-1 rounded-[22px] bg-slate-100 p-1 sm:flex-row">
                                <button
                                    v-for="tab in tabs"
                                    :key="tab.value"
                                    type="button"
                                    class="w-full rounded-[18px] px-4 py-2.5 text-sm font-semibold transition sm:w-auto sm:min-w-[190px]"
                                    :class="activeTab === tab.value ? 'bg-gradient-to-b from-blue-500 to-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white/70'"
                                    @click="activeTab = tab.value"
                                >
                                    {{ tab.label }}
                                </button>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <button
                                    type="button"
                                    class="rounded-[16px] border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:opacity-50"
                                    :disabled="savedTicketsLoading"
                                    @click="$emit('refresh-tickets')"
                                >
                                    {{ savedTicketsLoading ? 'Actualisation...' : 'Actualiser' }}
                                </button>

                                <label class="relative block min-w-[260px]">
                                    <input
                                        v-model.trim="searchQuery"
                                        type="text"
                                        placeholder="Rechercher..."
                                        class="w-full rounded-[16px] border border-slate-200 bg-white px-4 py-2.5 pr-11 text-sm text-slate-700 outline-none transition focus:border-blue-500"
                                    >
                                    <svg class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.765l2.63 2.631a.75.75 0 1 0 1.06-1.06l-2.63-2.632A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                                    </svg>
                                </label>
                            </div>
                        </div>

                        <div v-if="activeTab === 'liste' && listGroupOptions.length > 0" class="flex flex-wrap gap-2">
                            <button
                                v-for="group in listGroupOptions"
                                :key="group"
                                type="button"
                                class="rounded-[14px] border px-4 py-2 text-sm font-medium transition"
                                :class="selectedListGroup === group ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                @click="selectedListGroup = group"
                            >
                                {{ group }}
                            </button>
                        </div>

                        <div v-if="activeTab === 'commande' && commandStatusOptions.length > 0" class="flex flex-wrap gap-2">
                            <button
                                v-for="status in commandStatusOptions"
                                :key="status.value"
                                type="button"
                                class="rounded-[14px] border px-4 py-2 text-sm font-medium transition"
                                :class="selectedCommandStatus === status.value ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                @click="selectedCommandStatus = status.value"
                            >
                                {{ status.label }}
                            </button>
                        </div>

                        <section class="min-h-[440px] overflow-hidden rounded-[24px] border border-slate-200">
                            <div v-if="savedTicketsLoading && !currentRows.length" class="flex min-h-[440px] items-center justify-center bg-slate-50 text-sm text-slate-500">
                                Chargement des tickets...
                            </div>

                            <div v-else-if="!currentRows.length" class="flex min-h-[440px] items-center justify-center bg-slate-50 px-6 text-center text-sm text-slate-500">
                                Aucun ticket trouvé pour ce filtre.
                            </div>

                            <div v-else class="max-h-[440px] overflow-auto">
                                <table v-if="activeTab === 'liste'" class="min-w-full table-fixed border-collapse text-left">
                                    <thead class="sticky top-0 bg-slate-50 text-sm font-semibold text-slate-700">
                                        <tr>
                                            <th class="px-4 py-3">Date/Heure</th>
                                            <th class="px-4 py-3">Nom</th>
                                            <th class="px-4 py-3">Employé</th>
                                            <th class="px-4 py-3 text-right">Montant</th>
                                            <th class="w-16 px-4 py-3 text-center">Suppr.</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 bg-white text-sm text-slate-700">
                                        <tr
                                            v-for="ticket in currentRows"
                                            :key="ticket.id"
                                            class="cursor-pointer transition hover:bg-slate-50"
                                            :class="getRowClass(ticket)"
                                            @click="selectTicket(ticket.id)"
                                            @dblclick="handleConfirmSelection"
                                        >
                                            <td class="px-4 py-3 font-medium text-slate-800">{{ formatTicketTime(ticket) }}</td>
                                            <td class="px-4 py-3">
                                                <p class="font-semibold text-slate-900">{{ getTicketTitle(ticket) }}</p>
                                                <p class="text-xs text-slate-500">{{ normalizeTicketGroup(ticket?.ticket_group) }}</p>
                                            </td>
                                            <td class="px-4 py-3">{{ getEmployeeLabel(ticket) }}</td>
                                            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ formatCurrency(ticket.total || 0) }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <button
                                                    type="button"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full text-rose-600 transition hover:bg-rose-50 disabled:opacity-40"
                                                    :disabled="isTicketBusy(ticket)"
                                                    title="Supprimer"
                                                    @click.stop="$emit('delete-ticket', ticket.id)"
                                                >
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table v-else-if="activeTab === 'personnalise'" class="min-w-full table-fixed border-collapse text-left">
                                    <thead class="sticky top-0 bg-slate-50 text-sm font-semibold text-slate-700">
                                        <tr>
                                            <th class="px-4 py-3">Date/heure</th>
                                            <th class="px-4 py-3">Nom ticket</th>
                                            <th class="px-4 py-3 text-right">Montant</th>
                                            <th class="w-16 px-4 py-3 text-center">Suppr.</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 bg-white text-sm text-slate-700">
                                        <tr
                                            v-for="ticket in currentRows"
                                            :key="ticket.id"
                                            class="cursor-pointer transition hover:bg-slate-50"
                                            :class="getRowClass(ticket)"
                                            @click="selectTicket(ticket.id)"
                                            @dblclick="handleConfirmSelection"
                                        >
                                            <td class="px-4 py-3">
                                                <p class="font-medium text-slate-800">{{ formatTicketDay(ticket) }}</p>
                                                <p class="text-xs text-slate-500">{{ formatTicketTime(ticket) }}</p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <p class="font-semibold text-slate-900">{{ getTicketTitle(ticket) }}</p>
                                                <p class="text-xs text-slate-500">
                                                    {{ getPersonalizedSubtitle(ticket) }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ formatCurrency(ticket.total || 0) }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <button
                                                    type="button"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full text-rose-600 transition hover:bg-rose-50 disabled:opacity-40"
                                                    :disabled="isTicketBusy(ticket)"
                                                    title="Supprimer"
                                                    @click.stop="$emit('delete-ticket', ticket.id)"
                                                >
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table v-else class="min-w-full table-fixed border-collapse text-left">
                                    <thead class="sticky top-0 bg-slate-50 text-sm font-semibold text-slate-700">
                                        <tr>
                                            <th class="px-4 py-3">Date/heure</th>
                                            <th class="px-4 py-3">Client</th>
                                            <th class="px-4 py-3">Article</th>
                                            <th class="px-4 py-3 text-right">Avance</th>
                                            <th class="px-4 py-3 text-right">Reste</th>
                                            <th class="px-4 py-3">RDV</th>
                                            <th class="px-4 py-3">Statut</th>
                                            <th class="w-16 px-4 py-3 text-center">Suppr.</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 bg-white text-sm text-slate-700">
                                        <tr
                                            v-for="ticket in currentRows"
                                            :key="ticket.id"
                                            class="cursor-pointer transition hover:bg-slate-50"
                                            :class="getRowClass(ticket)"
                                            @click="selectTicket(ticket.id)"
                                            @dblclick="handleConfirmSelection"
                                        >
                                            <td class="px-4 py-3">
                                                <p class="font-medium text-slate-800">{{ formatTicketDay(ticket) }}</p>
                                                <p class="text-xs text-slate-500">{{ formatTicketTime(ticket) }}</p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <p class="font-semibold text-slate-900">{{ getCustomerLabel(ticket) }}</p>
                                                <p class="text-xs text-slate-500">{{ getEmployeeLabel(ticket) }}</p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <p class="font-medium text-slate-900">{{ getArticleSummary(ticket) }}</p>
                                                <p class="text-xs text-slate-500">{{ ticket.order_number || ticket.reference }}</p>
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ formatCurrency(getAdvanceAmount(ticket)) }}</td>
                                            <td class="px-4 py-3 text-right font-semibold" :class="getRemainingAmount(ticket) > 0 ? 'text-amber-700' : 'text-emerald-700'">
                                                {{ formatCurrency(getRemainingAmount(ticket)) }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <p class="font-medium text-slate-800">{{ formatAppointmentDate(ticket) }}</p>
                                                <p class="text-xs text-slate-500">{{ formatAppointmentTime(ticket) }}</p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="getCommandStatusClass(ticket?.order_status)">
                                                    {{ getCommandStatusLabel(ticket?.order_status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button
                                                    type="button"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full text-rose-600 transition hover:bg-rose-50 disabled:opacity-40"
                                                    :disabled="isTicketBusy(ticket)"
                                                    title="Supprimer"
                                                    @click.stop="$emit('delete-ticket', ticket.id)"
                                                >
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-sm text-slate-500">
                                <span v-if="selectedTicket">
                                    Sélection: <span class="font-semibold text-slate-800">{{ getTicketTitle(selectedTicket) }}</span>
                                </span>
                                <span v-else>
                                    Aucun ticket sélectionné.
                                </span>
                            </div>

                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    class="rounded-[16px] border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                    @click="$emit('close')"
                                >
                                    Annuler
                                </button>
                                <button
                                    type="button"
                                    class="rounded-[16px] bg-gradient-to-b from-emerald-500 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="!selectedTicket || isTicketBusy(selectedTicket)"
                                    @click="handleConfirmSelection"
                                >
                                    Sélectionner
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useCustomListsStore } from '../../stores/customLists'
import { useSettingsStore } from '../../stores/settings'

const props = defineProps({
    savedTickets: {
        type: Array,
        default: () => [],
    },
    savedTicketsLoading: {
        type: Boolean,
        default: false,
    },
    loadingSavedTicketId: {
        type: [Number, String, null],
        default: null,
    },
    deletingSavedTicketId: {
        type: [Number, String, null],
        default: null,
    },
    currentSaleId: {
        type: [Number, String, null],
        default: null,
    },
    currentServiceMode: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['close', 'refresh-tickets', 'load-ticket', 'delete-ticket'])

const customListsStore = useCustomListsStore()
const settingsStore = useSettingsStore()

const tabs = [
    { value: 'liste', label: 'Liste tickets' },
    { value: 'personnalise', label: 'Tickets personnalisés' },
    { value: 'commande', label: 'Commandes' },
]

const COMMAND_STATUS_META = {
    confirmee: {
        label: 'Confirmée',
        className: 'bg-amber-100 text-amber-700',
        priority: 1,
    },
    en_preparation: {
        label: 'En cours',
        className: 'bg-blue-100 text-blue-700',
        priority: 0,
    },
    envoyee: {
        label: 'Envoyée',
        className: 'bg-indigo-100 text-indigo-700',
        priority: 2,
    },
    livree: {
        label: 'Livrée',
        className: 'bg-emerald-100 text-emerald-700',
        priority: 3,
    },
    retournee: {
        label: 'Retournée',
        className: 'bg-rose-100 text-rose-700',
        priority: 4,
    },
    annulee: {
        label: 'Annulée',
        className: 'bg-slate-200 text-slate-700',
        priority: 5,
    },
}

const activeTab = ref('liste')
const searchQuery = ref('')
const selectedListGroup = ref('Tous')
const selectedCommandStatus = ref('all')
const selectedTicketId = ref(null)

const listTickets = computed(() => {
    return (props.savedTickets || [])
        .filter((ticket) => normalizeTicketType(ticket) === 'liste')
        .sort(sortByLatestUpdate)
})

const personalizedTickets = computed(() => {
    return (props.savedTickets || [])
        .filter((ticket) => normalizeTicketType(ticket) === 'personnalise')
        .sort(sortByLatestUpdate)
})

const commandTickets = computed(() => {
    return (props.savedTickets || [])
        .filter((ticket) => normalizeTicketType(ticket) === 'commande')
        .sort(sortCommandTickets)
})

const listGroupOptions = computed(() => {
    const groups = new Set()
    const config = customListsStore.getPredefinedTickets({ includeInactive: true })

    if ((config.tickets_without_group || []).length > 0) {
        groups.add('Sans groupe')
    }

    for (const group of config.ticket_groups || []) {
        groups.add(normalizeTicketGroup(group.label))
    }

    listTickets.value.forEach((ticket) => {
        groups.add(normalizeTicketGroup(ticket?.ticket_group))
    })

    return ['Tous', ...Array.from(groups).sort((left, right) => {
        if (left === 'Sans groupe') return -1
        if (right === 'Sans groupe') return 1
        return left.localeCompare(right, 'fr')
    })]
})

const commandStatusOptions = computed(() => {
    const statuses = Array.from(new Set(commandTickets.value.map((ticket) => String(ticket?.order_status || '').trim()).filter(Boolean)))

    return [
        { value: 'all', label: 'Tous' },
        ...statuses
            .sort((left, right) => getCommandStatusPriority(left) - getCommandStatusPriority(right))
            .map((status) => ({
                value: status,
                label: getCommandStatusLabel(status),
            })),
    ]
})

const filteredListTickets = computed(() => {
    return listTickets.value.filter((ticket) => {
        if (selectedListGroup.value !== 'Tous' && normalizeTicketGroup(ticket?.ticket_group) !== selectedListGroup.value) {
            return false
        }

        return matchesSearch(ticket, searchQuery.value)
    })
})

const filteredPersonalizedTickets = computed(() => {
    return personalizedTickets.value.filter((ticket) => matchesSearch(ticket, searchQuery.value))
})

const filteredCommandTickets = computed(() => {
    return commandTickets.value.filter((ticket) => {
        if (selectedCommandStatus.value !== 'all' && String(ticket?.order_status || '') !== selectedCommandStatus.value) {
            return false
        }

        return matchesSearch(ticket, searchQuery.value)
    })
})

const currentRows = computed(() => {
    if (activeTab.value === 'personnalise') return filteredPersonalizedTickets.value
    if (activeTab.value === 'commande') return filteredCommandTickets.value
    return filteredListTickets.value
})

const selectedTicket = computed(() => {
    return (props.savedTickets || []).find((ticket) => Number(ticket?.id || 0) === Number(selectedTicketId.value || 0)) || null
})

watch(activeTab, () => {
    searchQuery.value = ''
    selectedListGroup.value = 'Tous'
    selectedCommandStatus.value = 'all'
})

watch(
    [() => props.savedTickets, () => props.currentSaleId, activeTab, currentRows],
    () => {
        syncSelectedTicket()
    },
    { immediate: true, deep: true }
)

onMounted(async () => {
    await customListsStore.fetchList('tickets_predefinis')

    const currentTicket = (props.savedTickets || [])
        .find((ticket) => Number(ticket?.id || 0) === Number(props.currentSaleId || 0))

    const initialTab = normalizeTicketType(currentTicket)
    if (initialTab) {
        activeTab.value = initialTab
    }

    syncSelectedTicket()
})

function syncSelectedTicket() {
    const visibleIds = new Set(currentRows.value.map((ticket) => Number(ticket?.id || 0)))
    const currentSaleId = Number(props.currentSaleId || 0)
    const selectedId = Number(selectedTicketId.value || 0)

    if (selectedId && visibleIds.has(selectedId)) {
        return
    }

    if (currentSaleId && visibleIds.has(currentSaleId)) {
        selectedTicketId.value = currentSaleId
        return
    }

    selectedTicketId.value = null
}

function normalizeTicketType(ticket) {
    const type = String(ticket?.ticket_type || '').trim().toLowerCase()
    if (['liste', 'personnalise', 'commande'].includes(type)) {
        return type
    }

    const origin = String(ticket?.origin || '').trim().toLowerCase()
    if (origin === 'menu_commande' || origin === 'livraison') {
        return 'commande'
    }

    return ''
}

function normalizeTicketGroup(group) {
    return String(group || 'Sans groupe').trim() || 'Sans groupe'
}

function sortByLatestUpdate(left, right) {
    return new Date(right?.updated_at || right?.created_at || 0) - new Date(left?.updated_at || left?.created_at || 0)
}

function sortCommandTickets(left, right) {
    const priorityDiff = getCommandStatusPriority(left?.order_status) - getCommandStatusPriority(right?.order_status)
    if (priorityDiff !== 0) {
        return priorityDiff
    }

    return sortByLatestUpdate(left, right)
}

function getCommandStatusPriority(status) {
    return COMMAND_STATUS_META[String(status || '').toLowerCase()]?.priority ?? 99
}

function formatCurrency(amount) {
    return settingsStore.formatCurrency(amount)
}

function getTicketTitle(ticket) {
    return ticket?.ticket_name || ticket?.order_number || ticket?.reference || `Ticket #${ticket?.id || '-'}`
}

function getPersonalizedSubtitle(ticket) {
    return ticket?.notes || normalizeTicketGroup(ticket?.ticket_group)
}

function getEmployeeLabel(ticket) {
    return ticket?.user?.name || ticket?.employee?.name || 'Serveur'
}

function getCustomerLabel(ticket) {
    return ticket?.customer?.name || 'Client anonyme'
}

function getArticleSummary(ticket) {
    const items = Array.isArray(ticket?.items) ? ticket.items : []
    if (!items.length) return 'Aucun article'

    const firstLabel = items[0]?.article_name || items[0]?.article?.name || 'Article'
    const extraCount = items.length - 1

    return extraCount > 0 ? `${firstLabel} +${extraCount}` : firstLabel
}

function getAdvanceAmount(ticket) {
    return Number(ticket?.paid_confirmed_amount ?? ticket?.payment_summary?.paid_confirmed_amount ?? 0)
}

function getRemainingAmount(ticket) {
    return Number(ticket?.remaining_amount ?? ticket?.payment_summary?.remaining_amount ?? 0)
}

function getCommandStatusLabel(status) {
    return COMMAND_STATUS_META[String(status || '').toLowerCase()]?.label || 'En attente'
}

function getCommandStatusClass(status) {
    return COMMAND_STATUS_META[String(status || '').toLowerCase()]?.className || 'bg-amber-100 text-amber-700'
}

function getTicketDateSource(ticket) {
    return ticket?.updated_at || ticket?.created_at || null
}

function formatTicketDay(ticket) {
    const source = getTicketDateSource(ticket)
    if (!source) return '-'

    return new Date(source).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    })
}

function formatTicketTime(ticket) {
    const source = getTicketDateSource(ticket)
    if (!source) return '-'

    return new Date(source).toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
    })
}

function formatAppointmentDate(ticket) {
    const source = ticket?.appointment_at || ticket?.pickup_date
    if (!source) return '-'

    return new Date(source).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    })
}

function formatAppointmentTime(ticket) {
    const source = ticket?.appointment_at
    if (!source) return '-'

    return new Date(source).toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
    })
}

function matchesSearch(ticket, query) {
    const normalizedQuery = String(query || '').trim().toLowerCase()
    if (!normalizedQuery) return true

    const haystack = [
        ticket?.ticket_name,
        ticket?.reference,
        ticket?.order_number,
        ticket?.ticket_group,
        ticket?.customer?.name,
        ticket?.customer?.phone,
        ticket?.user?.name,
        ticket?.employee?.name,
        ticket?.notes,
        ...(Array.isArray(ticket?.items) ? ticket.items.map((item) => item?.article_name || item?.article?.name) : []),
    ]
        .filter(Boolean)
        .join(' ')
        .toLowerCase()

    return haystack.includes(normalizedQuery)
}

function selectTicket(ticketId) {
    selectedTicketId.value = ticketId
}

function handleConfirmSelection() {
    if (!selectedTicket.value || isTicketBusy(selectedTicket.value)) {
        return
    }

    emit('load-ticket', selectedTicket.value.id)
}

function isTicketBusy(ticket) {
    const id = Number(ticket?.id || 0)
    return id > 0 && (
        id === Number(props.loadingSavedTicketId || 0)
        || id === Number(props.deletingSavedTicketId || 0)
    )
}

function getRowClass(ticket) {
    const isSelected = Number(selectedTicketId.value || 0) === Number(ticket?.id || 0)
    const isActive = Number(props.currentSaleId || 0) === Number(ticket?.id || 0)

    if (isSelected) {
        return 'bg-blue-50'
    }

    if (isActive) {
        return 'bg-emerald-50'
    }

    return ''
}
</script>
