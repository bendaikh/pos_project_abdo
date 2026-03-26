<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center px-4 py-6">
            <div class="fixed inset-0 bg-slate-900/45 backdrop-blur-[2px]" @click="$emit('close')"></div>

            <div class="relative z-10 w-full max-w-3xl overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-2xl">
                <div class="border-b border-slate-100 px-6 py-4">
                    <div class="relative flex items-center justify-center">
                        <h2 class="text-2xl font-semibold text-slate-900">Enregistrer</h2>
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
                    <div class="grid gap-3 rounded-[24px] bg-slate-50 px-4 py-3 text-sm text-slate-600 sm:grid-cols-3">
                        <div>
                            <span class="font-semibold text-slate-800">Client:</span>
                            {{ defaultCustomerName || 'Client anonyme' }}
                        </div>
                        <div>
                            <span class="font-semibold text-slate-800">Total:</span>
                            {{ formatCurrency(cartData.total || 0) }}
                        </div>
                        <div>
                            <span class="font-semibold text-slate-800">Articles:</span>
                            {{ cartItems.length }}
                        </div>
                    </div>
                </div>

                <div class="px-6 pb-6 pt-4">
                    <div class="flex flex-col gap-1 rounded-[22px] bg-slate-100 p-1 sm:flex-row">
                        <button
                            v-for="tab in tabs"
                            :key="tab.value"
                            type="button"
                            class="w-full rounded-[18px] px-4 py-2.5 text-sm font-semibold transition sm:w-auto sm:min-w-[180px]"
                            :class="activeTab === tab.value ? 'bg-gradient-to-b from-blue-500 to-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white/70'"
                            @click="activeTab = tab.value"
                        >
                            {{ tab.label }}
                        </button>
                    </div>

                    <section class="mt-5 min-h-[460px]">
                        <div v-if="activeTab === 'liste'" class="space-y-5">
                            <div class="flex items-center justify-between gap-3 rounded-[20px] bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                <p>
                                    {{ canSaveCurrentCart ? 'Choisissez un ticket prédéfini pour enregistrer le ticket actuel.' : 'Ajoutez des articles au ticket avant de choisir un emplacement.' }}
                                </p>
                                <button
                                    type="button"
                                    class="shrink-0 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 disabled:opacity-50"
                                    :disabled="savedTicketsLoading"
                                    @click="$emit('refresh-tickets')"
                                >
                                    {{ savedTicketsLoading ? '...' : 'Actualiser' }}
                                </button>
                            </div>

                            <div v-if="ungroupedBoardTickets.length" class="space-y-3">
                                <h3 class="text-xl font-semibold text-slate-700">Sans groupe</h3>
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <div
                                        v-for="ticket in ungroupedBoardTickets"
                                        :key="ticket.key"
                                        class="relative rounded-[18px] border border-slate-200 bg-white p-3 shadow-sm transition"
                                        :class="ticket.ticket ? 'border-amber-300 bg-amber-50/40' : 'hover:border-slate-300 hover:shadow-md'"
                                    >
                                        <button
                                            type="button"
                                            class="w-full text-left disabled:cursor-not-allowed disabled:opacity-50"
                                            :disabled="isBoardTicketDisabled(ticket)"
                                            @click="handleBoardTicketClick(ticket)"
                                        >
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="truncate text-lg font-medium text-slate-700">{{ ticket.name }}</p>
                                                    <p class="mt-1 text-xs font-medium text-slate-500">{{ getBoardTicketCaption(ticket) }}</p>
                                                </div>
                                                <span
                                                    class="shrink-0 rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]"
                                                    :class="getBoardTicketBadgeClass(ticket)"
                                                >
                                                    {{ getBoardTicketBadgeLabel(ticket) }}
                                                </span>
                                            </div>
                                            <div v-if="ticket.ticket" class="mt-3 flex items-center justify-between gap-3 text-xs text-slate-500">
                                                <span class="truncate">{{ ticket.ticket.customer?.name || 'Client anonyme' }}</span>
                                                <span class="shrink-0 font-semibold text-amber-700">{{ formatCurrency(ticket.ticket.total || 0) }}</span>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="groupedBoardGroups.length" class="space-y-3">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="text-xl font-semibold text-slate-700">
                                        {{ selectedBoardGroup ? `Tables · ${selectedBoardGroup}` : 'Groupes' }}
                                    </h3>
                                    <button
                                        v-if="selectedBoardGroup"
                                        type="button"
                                        class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
                                        @click="selectedBoardGroup = null"
                                    >
                                        Retour aux groupes
                                    </button>
                                </div>

                                <div v-if="!selectedBoardGroup" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <button
                                        v-for="group in groupedBoardGroups"
                                        :key="group.group"
                                        type="button"
                                        class="rounded-[18px] bg-gradient-to-b from-blue-500 to-blue-600 p-4 text-left text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                                        @click="selectedBoardGroup = group.group"
                                    >
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-lg font-semibold">{{ group.group }}</p>
                                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-blue-100">
                                                    {{ group.availableCount }} table{{ group.availableCount > 1 ? 's' : '' }} disponible{{ group.availableCount > 1 ? 's' : '' }}
                                                </p>
                                            </div>
                                            <span class="shrink-0 rounded-full bg-white/15 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-blue-50">
                                                Ouvrir
                                            </span>
                                        </div>
                                    </button>
                                </div>

                                <div v-else class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <div
                                        v-for="ticket in selectedGroupedBoardTickets"
                                        :key="ticket.key"
                                        class="relative rounded-[18px] bg-gradient-to-b from-blue-500 to-blue-600 p-3 text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                                    >
                                        <button
                                            type="button"
                                            class="w-full text-left disabled:cursor-not-allowed disabled:opacity-50"
                                            :disabled="isBoardTicketDisabled(ticket)"
                                            @click="handleBoardTicketClick(ticket)"
                                        >
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="truncate text-lg font-semibold">{{ ticket.name }}</p>
                                                    <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-blue-100">
                                                        {{ ticket.group }}
                                                    </p>
                                                </div>
                                                <span
                                                    class="shrink-0 rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]"
                                                    :class="ticket.ticket ? 'bg-amber-100 text-amber-700' : 'bg-white/15 text-blue-50'"
                                                >
                                                    {{ getBoardTicketBadgeLabel(ticket) }}
                                                </span>
                                            </div>
                                            <div v-if="ticket.ticket" class="mt-3 flex items-center justify-between gap-3 text-xs text-blue-100">
                                                <span class="truncate">{{ ticket.ticket.customer?.name || 'Client anonyme' }}</span>
                                                <span class="shrink-0 font-semibold text-amber-100">{{ formatCurrency(ticket.ticket.total || 0) }}</span>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!ungroupedBoardTickets.length && !groupedBoardGroups.length" class="rounded-[22px] border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center text-sm text-slate-500">
                                {{ configuredBoardEntries.length ? 'Tous les emplacements sont occupés. Seuls les tickets disponibles sont affichés ici.' : 'Aucun ticket prédéfini n’est configuré.' }}
                            </div>
                        </div>

                        <div v-else-if="activeTab === 'personnalise'" class="space-y-5">
                            <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-5">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-medium text-slate-700">Nom du ticket</span>
                                        <input
                                            v-model.trim="personalizedForm.ticket_name"
                                            type="text"
                                            placeholder="Ex: Mariage Salma"
                                            class="w-full rounded-[18px] border border-slate-300 bg-white px-4 py-3 focus:border-blue-500 focus:outline-none"
                                        >
                                    </label>
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-medium text-slate-700">Groupe</span>
                                        <input
                                            v-model.trim="personalizedForm.ticket_group"
                                            type="text"
                                            placeholder="Ex: Evenements"
                                            class="w-full rounded-[18px] border border-slate-300 bg-white px-4 py-3 focus:border-blue-500 focus:outline-none"
                                        >
                                    </label>
                                </div>

                                <label class="mt-4 block">
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Commentaire</span>
                                    <textarea
                                        v-model.trim="personalizedForm.comment"
                                        rows="5"
                                        placeholder="Commentaire libre pour l’impression"
                                        class="w-full rounded-[22px] border border-slate-300 bg-white px-4 py-3 focus:border-blue-500 focus:outline-none"
                                    ></textarea>
                                </label>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="button"
                                    class="rounded-[18px] bg-gradient-to-b from-blue-500 to-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="saving || !personalizedForm.ticket_name || !canSaveCurrentCart"
                                    @click="savePersonalizedTicket"
                                >
                                    {{ saving ? 'Enregistrement...' : 'Enregistrer et imprimer' }}
                                </button>
                            </div>
                        </div>

                        <div v-else class="space-y-5">
                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-[22px] border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">N° cmd</p>
                                    <p class="mt-2 text-lg font-semibold text-slate-900">Automatique</p>
                                </div>
                                <label class="block xl:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Date du RDV avec heure</span>
                                    <input
                                        v-model="commandForm.appointment_at"
                                        type="datetime-local"
                                        class="w-full rounded-[18px] border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none"
                                    >
                                </label>
                                <label v-if="serviceModeEnabled" class="block">
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Mode de service</span>
                                    <select
                                        v-model="commandForm.delivery_mode"
                                        class="w-full rounded-[18px] border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none"
                                    >
                                        <option v-for="mode in commandDeliveryModes" :key="mode.value" :value="mode.value">
                                            {{ mode.label }}
                                        </option>
                                    </select>
                                </label>
                            </div>

                            <div class="rounded-[24px] border border-slate-200 p-4">
                                <div class="mb-4 flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        class="rounded-[18px] px-4 py-2 text-sm font-semibold transition"
                                        :class="customerMode === 'existing' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'"
                                        @click="customerMode = 'existing'"
                                    >
                                        Client existant
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-[18px] px-4 py-2 text-sm font-semibold transition"
                                        :class="customerMode === 'new' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'"
                                        @click="customerMode = 'new'"
                                    >
                                        Nouveau client
                                    </button>
                                </div>

                                <div v-if="customerMode === 'existing'" class="space-y-4">
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-medium text-slate-700">Recherche client</span>
                                        <input
                                            v-model.trim="customerSearch"
                                            type="text"
                                            placeholder="Nom ou téléphone"
                                            class="w-full rounded-[18px] border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none"
                                        >
                                    </label>

                                    <div class="grid gap-2 md:grid-cols-2">
                                        <button
                                            v-for="customer in filteredCustomers"
                                            :key="customer.id"
                                            type="button"
                                            class="rounded-[18px] border px-4 py-3 text-left transition"
                                            :class="selectedCustomer?.id === customer.id ? 'border-blue-500 bg-blue-50' : 'border-slate-200 bg-white hover:border-slate-300'"
                                            @click="selectCustomer(customer)"
                                        >
                                            <p class="font-semibold text-slate-900">{{ customer.name }}</p>
                                            <p class="mt-1 text-sm text-slate-500">{{ customer.phone || 'Sans téléphone' }}</p>
                                        </button>
                                    </div>
                                    <p v-if="loadingCustomers" class="text-sm text-slate-500">Chargement des clients...</p>

                                    <div v-if="selectedCustomer" class="grid gap-4 rounded-[22px] bg-slate-50 p-4 md:grid-cols-3">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Client</p>
                                            <p class="mt-1 font-semibold text-slate-900">{{ selectedCustomer.name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Téléphone</p>
                                            <p class="mt-1 font-semibold text-slate-900">{{ commandForm.customer_phone || '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Activité</p>
                                            <p class="mt-1 font-semibold text-slate-900">{{ commandForm.customer_activity || '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="grid gap-4 md:grid-cols-2">
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-medium text-slate-700">Client</span>
                                        <input
                                            v-model.trim="newCustomerForm.name"
                                            type="text"
                                            placeholder="Nom du client"
                                            class="w-full rounded-[18px] border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none"
                                        >
                                    </label>
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-medium text-slate-700">Téléphone</span>
                                        <input
                                            v-model.trim="newCustomerForm.phone"
                                            type="text"
                                            placeholder="Téléphone"
                                            class="w-full rounded-[18px] border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none"
                                        >
                                    </label>
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-medium text-slate-700">Activité</span>
                                        <input
                                            v-model.trim="newCustomerForm.activity"
                                            type="text"
                                            placeholder="Activité"
                                            class="w-full rounded-[18px] border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none"
                                        >
                                    </label>
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-medium text-slate-700">Adresse</span>
                                        <input
                                            v-model.trim="newCustomerForm.address"
                                            type="text"
                                            placeholder="Adresse"
                                            class="w-full rounded-[18px] border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none"
                                        >
                                    </label>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Montant de l'avance</span>
                                    <input
                                        v-model.number="commandForm.advance_amount"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full rounded-[18px] border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none"
                                    >
                                </label>
                                <div class="rounded-[22px] border border-amber-200 bg-amber-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-amber-700">Reste à payer</p>
                                    <p class="mt-2 text-lg font-semibold text-amber-900">{{ formatCurrency(commandRemainingAmount) }}</p>
                                </div>
                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Note</span>
                                    <textarea
                                        v-model.trim="commandForm.notes"
                                        rows="3"
                                        placeholder="Commentaire pour la commande"
                                        class="w-full rounded-[22px] border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none"
                                    ></textarea>
                                </label>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="button"
                                    class="rounded-[18px] bg-gradient-to-b from-blue-500 to-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="saving || !canSaveCommande || !canSaveCurrentCart"
                                    @click="saveCommandeTicket"
                                >
                                    {{ saving ? 'Enregistrement...' : 'Enregistrer, imprimer la commande' }}
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { customersApi, salesApi } from '../../api'
import { useCustomListsStore } from '../../stores/customLists'
import { useSettingsStore } from '../../stores/settings'

const props = defineProps({
    cartData: {
        type: Object,
        required: true,
    },
    cartItems: {
        type: Array,
        default: () => [],
    },
    defaultCustomerId: {
        type: [Number, String, null],
        default: null,
    },
    defaultCustomerName: {
        type: String,
        default: '',
    },
    defaultDeliveryMode: {
        type: String,
        default: 'pickup',
    },
    defaultNotes: {
        type: String,
        default: '',
    },
    savedTickets: {
        type: Array,
        default: () => [],
    },
    savedTicketsLoading: {
        type: Boolean,
        default: false,
    },
    currentSaleId: {
        type: [Number, String, null],
        default: null,
    },
})

const emit = defineEmits(['close', 'saved', 'refresh-tickets'])

const settingsStore = useSettingsStore()
const customListsStore = useCustomListsStore()

const tabs = [
    { value: 'liste', label: 'Liste tickets' },
    { value: 'personnalise', label: 'Ticket personnalisé' },
    { value: 'commande', label: 'Commande' },
]

const activeTab = ref('liste')
const selectedBoardGroup = ref(null)
const saving = ref(false)
const customers = ref([])
const loadingCustomers = ref(false)
const customerSearch = ref('')
const customerMode = ref('existing')
const selectedCustomer = ref(null)

const personalizedForm = ref({
    ticket_name: '',
    ticket_group: 'Personnalise',
    comment: '',
})

const newCustomerForm = ref({
    name: '',
    phone: '',
    activity: '',
    address: '',
})

const commandForm = ref({
    appointment_at: '',
    delivery_mode: normalizeDeliveryMode(props.defaultDeliveryMode),
    advance_amount: 0,
    notes: '',
    customer_phone: '',
    customer_activity: '',
    customer_address: '',
})

const commandDeliveryModes = computed(() => customListsStore.activeServiceModes)
const serviceModeEnabled = computed(() => {
    return customListsStore.serviceModeEnabled && commandDeliveryModes.value.length > 0
})

const canSaveCurrentCart = computed(() => props.cartItems.length > 0)

const configuredBoardEntries = computed(() => {
    const config = customListsStore.getPredefinedTickets()
    const entries = []

    for (const ticket of config.tickets_without_group || []) {
        entries.push({
            key: createTicketBoardKey('Sans groupe', ticket.label),
            name: String(ticket.label || '').trim(),
            group: 'Sans groupe',
            ticket: null,
            group_sort_order: 0,
            ticket_sort_order: Number(ticket.sort_order || 0),
            configured_index: entries.length,
        })
    }

    for (const group of config.ticket_groups || []) {
        for (const ticket of group.tickets || []) {
            entries.push({
                key: createTicketBoardKey(group.label, ticket.label),
                name: String(ticket.label || '').trim(),
                group: normalizeTicketGroup(group.label),
                ticket: null,
                group_sort_order: Number(group.sort_order || 0),
                ticket_sort_order: Number(ticket.sort_order || 0),
                configured_index: entries.length,
            })
        }
    }

    return entries.filter((entry) => entry.name)
})

const existingBoardTicketEntries = computed(() => {
    return (props.savedTickets || [])
        .map((ticket) => {
            const name = String(ticket?.ticket_name || ticket?.reference || `Ticket #${ticket?.id || '-'}`).trim()
            if (!name) return null

            return {
                key: createTicketBoardKey(ticket?.ticket_group, name),
                name,
                group: normalizeTicketGroup(ticket?.ticket_group),
                ticket,
                group_sort_order: Number.MAX_SAFE_INTEGER,
                ticket_sort_order: Number.MAX_SAFE_INTEGER,
                configured_index: Number.MAX_SAFE_INTEGER,
            }
        })
        .filter(Boolean)
})

const ticketBoardTiles = computed(() => {
    const tiles = new Map()

    for (const preset of configuredBoardEntries.value) {
        tiles.set(preset.key, {
            key: preset.key,
            name: preset.name,
            group: normalizeTicketGroup(preset.group),
            ticket: null,
            group_sort_order: preset.group_sort_order,
            ticket_sort_order: preset.ticket_sort_order,
            configured_index: preset.configured_index,
        })
    }

    existingBoardTicketEntries.value.forEach((entry, index) => {
        const existing = tiles.get(entry.key)
        tiles.set(entry.key, {
            ...(existing || {}),
            key: entry.key,
            name: entry.name,
            group: entry.group,
            ticket: entry.ticket,
            group_sort_order: existing?.group_sort_order ?? Number.MAX_SAFE_INTEGER,
            ticket_sort_order: existing?.ticket_sort_order ?? Number.MAX_SAFE_INTEGER,
            configured_index: existing?.configured_index ?? (Number.MAX_SAFE_INTEGER - 1000 + index),
        })
    })

    return Array.from(tiles.values()).sort((a, b) => {
        const ungroupedCompare = Number(isUngroupedTicket(a.group)) - Number(isUngroupedTicket(b.group))
        if (ungroupedCompare !== 0) return ungroupedCompare * -1

        if (a.group_sort_order !== b.group_sort_order) {
            return a.group_sort_order - b.group_sort_order
        }

        const groupCompare = a.group.localeCompare(b.group, 'fr')
        if (groupCompare !== 0) return groupCompare

        if (a.ticket_sort_order !== b.ticket_sort_order) {
            return a.ticket_sort_order - b.ticket_sort_order
        }

        if (a.configured_index !== b.configured_index) {
            return a.configured_index - b.configured_index
        }

        return a.name.localeCompare(b.name, 'fr')
    })
})

const availableTicketBoardTiles = computed(() => {
    return ticketBoardTiles.value.filter((ticket) => (
        !ticket.ticket
        || Number(ticket.ticket.id || 0) === Number(props.currentSaleId || 0)
    ))
})

const ungroupedBoardTickets = computed(() => {
    return availableTicketBoardTiles.value.filter((ticket) => isUngroupedTicket(ticket.group))
})

const groupedBoardGroups = computed(() => {
    const groups = new Map()

    availableTicketBoardTiles.value
        .filter((ticket) => !isUngroupedTicket(ticket.group))
        .forEach((ticket) => {
            const existing = groups.get(ticket.group) || {
                group: ticket.group,
                group_sort_order: ticket.group_sort_order,
                availableCount: 0,
            }

            existing.availableCount += 1
            existing.group_sort_order = Math.min(existing.group_sort_order, ticket.group_sort_order)

            groups.set(ticket.group, existing)
        })

    return Array.from(groups.values()).sort((a, b) => {
        if (a.group_sort_order !== b.group_sort_order) {
            return a.group_sort_order - b.group_sort_order
        }

        return a.group.localeCompare(b.group, 'fr')
    })
})

const selectedGroupedBoardTickets = computed(() => {
    if (!selectedBoardGroup.value) {
        return []
    }

    return availableTicketBoardTiles.value.filter((ticket) => (
        !isUngroupedTicket(ticket.group) && ticket.group === selectedBoardGroup.value
    ))
})

const filteredCustomers = computed(() => {
    const query = customerSearch.value.trim().toLowerCase()
    if (!query) {
        return customers.value.slice(0, 8)
    }

    return customers.value
        .filter((customer) => {
            const name = String(customer.name || '').toLowerCase()
            const phone = String(customer.phone || '').toLowerCase()
            return name.includes(query) || phone.includes(query)
        })
        .slice(0, 8)
})

const commandRemainingAmount = computed(() => {
    const total = Number(props.cartData.total || 0)
    const advance = Number(commandForm.value.advance_amount || 0)
    return Math.max(0, total - advance)
})

const canSaveCommande = computed(() => {
    if (!commandForm.value.appointment_at) return false
    if (Number(commandForm.value.advance_amount || 0) > Number(props.cartData.total || 0)) return false

    if (customerMode.value === 'existing') {
        return !!selectedCustomer.value
    }

    return !!newCustomerForm.value.name
})

watch(activeTab, async (tab) => {
    if (tab !== 'liste') {
        selectedBoardGroup.value = null
    }

    if (tab === 'commande' && !customers.value.length) {
        await fetchCustomers()
    }
})

watch(groupedBoardGroups, (groups) => {
    if (selectedBoardGroup.value && !groups.some((group) => group.group === selectedBoardGroup.value)) {
        selectedBoardGroup.value = null
    }
})

watch(
    () => props.defaultCustomerId,
    () => {
        hydrateDefaultCustomer()
    },
    { immediate: true }
)

watch(commandDeliveryModes, () => {
    if (!serviceModeEnabled.value) {
        return
    }

    const currentMode = customListsStore.findServiceMode(commandForm.value.delivery_mode, { includeInactive: false })
    if (!currentMode) {
        commandForm.value.delivery_mode = customListsStore.defaultServiceModeValue()
    }
}, { deep: true })

onMounted(async () => {
    await Promise.all([
        customListsStore.fetchList('mode_de_service', { force: true }),
        customListsStore.fetchList('tickets_predefinis', { force: true }),
    ])
    commandForm.value.delivery_mode = normalizeDeliveryMode(commandForm.value.delivery_mode)
    if (activeTab.value === 'commande') {
        await fetchCustomers()
    }
})

function normalizeDeliveryMode(mode) {
    return customListsStore.findServiceMode(mode, { includeInactive: true })?.value
        || customListsStore.defaultServiceModeValue()
}

function normalizeTicketGroup(group) {
    return String(group || 'Sans groupe').trim() || 'Sans groupe'
}

function isUngroupedTicket(group) {
    return normalizeTicketGroup(group).toLowerCase() === 'sans groupe'
}

function createTicketBoardKey(group, name) {
    return `${normalizeTicketGroup(group).toLowerCase()}::${String(name || '').trim().toLowerCase()}`
}

function hydrateDefaultCustomer() {
    if (!props.defaultCustomerId) return

    selectedCustomer.value = {
        id: props.defaultCustomerId,
        name: props.defaultCustomerName || 'Client',
        phone: '',
        activity: '',
        address: '',
    }
}

async function fetchCustomers() {
    loadingCustomers.value = true
    try {
        const { data } = await customersApi.list({
            active: true,
            paginate: false,
        })
        customers.value = Array.isArray(data) ? data : (data.data || [])
        if (props.defaultCustomerId) {
            const matched = customers.value.find((customer) => String(customer.id) === String(props.defaultCustomerId))
            if (matched) {
                selectCustomer(matched)
            }
        }
    } catch (error) {
        console.error('Erreur chargement clients:', error)
        customers.value = []
    } finally {
        loadingCustomers.value = false
    }
}

function selectCustomer(customer) {
    selectedCustomer.value = customer
    commandForm.value.customer_phone = customer.phone || ''
    commandForm.value.customer_activity = customer.activity || ''
    commandForm.value.customer_address = customer.address || ''
}

function formatCurrency(amount) {
    return settingsStore.formatCurrency(amount)
}

function formatDeliveryMode(mode) {
    return customListsStore.getServiceModeLabel(mode)
}

function getItemTotal(item) {
    const quantity = Number(item.quantity || 0)
    const unitPrice = Number(item.unit_price || 0)
    const variantPrice = Number(item.variant_price || 0)
    const optionsPrice = Number(item.options_price || 0)
    const discount = Number(item.discount_amount || 0)
    return (unitPrice + variantPrice + optionsPrice) * quantity - discount
}

function buildSalePayload(overrides = {}) {
    return {
        ...props.cartData,
        items: [...(props.cartData.items || [])],
        ...overrides,
    }
}

function buildNotes(parts) {
    return parts
        .map((part) => String(part || '').trim())
        .filter(Boolean)
        .join('\n')
}

async function saveListedTicket(ticket) {
    await saveAndPrint({
        saleId: ticket.ticket?.id || null,
        title: ticket.name,
        payload: buildSalePayload({
            origin: 'pos',
            ticket_type: 'liste',
            ticket_name: ticket.name,
            ticket_group: ticket.group,
            customer_id: props.defaultCustomerId || null,
            order_status: 'confirmee',
            notes: buildNotes([props.defaultNotes]),
        }),
    })
}

async function savePersonalizedTicket() {
    if (!canSaveCurrentCart.value) {
        alert('Ajoutez au moins un article avant d’enregistrer un ticket.')
        return
    }

    const ticketName = personalizedForm.value.ticket_name.trim()
    if (!ticketName) {
        alert('Veuillez saisir un nom de ticket.')
        return
    }

    const ticketGroup = personalizedForm.value.ticket_group.trim() || 'Personnalise'

    await saveAndPrint({
        title: ticketName,
        payload: buildSalePayload({
            origin: 'pos',
            ticket_type: 'personnalise',
            ticket_name: ticketName,
            ticket_group: ticketGroup,
            customer_id: props.defaultCustomerId || null,
            order_status: 'confirmee',
            notes: buildNotes([props.defaultNotes, personalizedForm.value.comment]),
        }),
    })
}

async function saveCommandeTicket() {
    if (!canSaveCurrentCart.value) {
        alert('Ajoutez au moins un article avant d’enregistrer une commande.')
        return
    }

    if (!commandForm.value.appointment_at) {
        alert('Veuillez renseigner la date et l’heure du rendez-vous.')
        return
    }

    const advanceAmount = Number(commandForm.value.advance_amount || 0)
    const total = Number(props.cartData.total || 0)
    if (advanceAmount > total) {
        alert("Le montant de l'avance ne peut pas dépasser le total.")
        return
    }

    const printTargetWindow = openPrintWindowSafely()
    saving.value = true

    try {
        const customerId = await resolveCustomerId()
        const payload = buildSalePayload({
            origin: 'menu_commande',
            ticket_type: 'commande',
            ticket_name: 'Commande client',
            ticket_group: 'Commandes',
            customer_id: customerId,
            customer_activity: commandForm.value.customer_activity || newCustomerForm.value.activity || null,
            service_mode: commandForm.value.delivery_mode,
            delivery_mode: customListsStore.getServiceModeMeta(commandForm.value.delivery_mode).operational_mode,
            delivery_address: customListsStore.getServiceModeMeta(commandForm.value.delivery_mode).requires_delivery_agent
                ? (commandForm.value.customer_address || newCustomerForm.value.address || null)
                : null,
            appointment_at: commandForm.value.appointment_at,
            pickup_date: commandForm.value.appointment_at.slice(0, 10),
            order_status: 'en_preparation',
            notes: buildNotes([props.defaultNotes, commandForm.value.notes]),
        })

        const { data: createdSale } = await salesApi.create(payload)
        let finalSale = createdSale

        if (advanceAmount > 0) {
            try {
                await salesApi.addPayment(createdSale.id, {
                    payment_type: 'cash',
                    amount: advanceAmount,
                    received_amount: advanceAmount,
                    notes: 'Avance enregistree depuis le POS',
                })
            } catch (paymentError) {
                console.error('Erreur enregistrement avance:', paymentError)
                alert("La commande a été créée, mais l'avance n'a pas pu être enregistrée automatiquement.")
            }
            const refreshed = await salesApi.get(createdSale.id)
            finalSale = refreshed.data
        }

        const printed = printSale(finalSale, 'commande', null, printTargetWindow)
        alert(printed
            ? 'Commande enregistrée et envoyée à l’impression.'
            : 'Commande enregistrée. Impression bloquée par le navigateur.')
        emit('saved', finalSale)
    } catch (error) {
        console.error('Erreur enregistrement commande:', error)
        alert(error.response?.data?.message || "Impossible d'enregistrer la commande.")
    } finally {
        saving.value = false
    }
}

async function resolveCustomerId() {
    if (customerMode.value === 'existing') {
        if (!selectedCustomer.value) {
            throw new Error('Client manquant')
        }
        return selectedCustomer.value.id
    }

    const { data } = await customersApi.create({
        name: newCustomerForm.value.name,
        phone: newCustomerForm.value.phone || null,
        activity: newCustomerForm.value.activity || null,
        address: newCustomerForm.value.address || null,
        is_active: true,
    })

    return data.id
}

async function saveAndPrint({ title, payload, saleId = null }) {
    const printTargetWindow = openPrintWindowSafely()
    saving.value = true
    try {
        let savedSaleId = saleId

        if (savedSaleId) {
            await salesApi.update(savedSaleId, payload)
        } else {
            const { data } = await salesApi.create(payload)
            savedSaleId = data.id
        }

        const refreshed = await salesApi.get(savedSaleId)
        const printed = printSale(refreshed.data, payload.ticket_type || 'liste', title, printTargetWindow)
        alert(printed
            ? 'Ticket enregistré et envoyé à l’impression.'
            : 'Ticket enregistré. Impression bloquée par le navigateur.')
        emit('saved', refreshed.data)
    } catch (error) {
        console.error('Erreur enregistrement ticket:', error)
        alert(error.response?.data?.message || "Impossible d'enregistrer le ticket.")
    } finally {
        saving.value = false
    }
}

function isBoardTicketBusy(ticket) {
    return saving.value
}

function isBoardTicketDisabled(ticket) {
    if (isBoardTicketBusy(ticket) || !canSaveCurrentCart.value) {
        return true
    }

    return !!ticket.ticket && Number(ticket.ticket.id || 0) !== Number(props.currentSaleId || 0)
}

function getBoardTicketCaption(ticket) {
    if (ticket.ticket) {
        if (Number(ticket.ticket.id || 0) === Number(props.currentSaleId || 0)) {
            return 'Mettre à jour le ticket actuellement ouvert'
        }

        return 'Déjà enregistré. Ouvrez-le depuis Tickets enregistrés'
    }

    return 'Enregistrer dans cet emplacement'
}

function getBoardTicketBadgeLabel(ticket) {
    if (!ticket.ticket) {
        return 'Libre'
    }

    if (Number(ticket.ticket.id || 0) === Number(props.currentSaleId || 0)) {
        return 'Actuel'
    }

    return 'Occupé'
}

function getBoardTicketBadgeClass(ticket) {
    if (!ticket.ticket) {
        return 'bg-slate-100 text-slate-500'
    }

    if (Number(ticket.ticket.id || 0) === Number(props.currentSaleId || 0)) {
        return 'bg-blue-100 text-blue-700'
    }

    return 'bg-amber-100 text-amber-700'
}

async function handleBoardTicketClick(ticket) {
    if (!canSaveCurrentCart.value) {
        alert('Ajoutez des articles avant d’enregistrer ce ticket.')
        return
    }

    if (ticket.ticket && Number(ticket.ticket.id || 0) !== Number(props.currentSaleId || 0)) {
        alert('Ce ticket est déjà occupé. Ouvrez-le depuis "Tickets enregistrés" ou choisissez un autre emplacement.')
        return
    }

    if (ticket.ticket && Number(ticket.ticket.id || 0) === Number(props.currentSaleId || 0)) {
        const shouldUpdate = confirm(`Mettre à jour le ticket actuel dans "${ticket.name}" ?`)
        if (!shouldUpdate) {
            return
        }
    }

    await saveListedTicket(ticket)
}

function openPrintWindowSafely() {
    const printTargetWindow = window.open('', '_blank', 'width=420,height=760')
    if (!printTargetWindow) {
        return null
    }

    printTargetWindow.document.write(`
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Préparation impression</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #334155;
                }
            </style>
        </head>
        <body>Préparation du ticket...</body>
        </html>
    `)
    printTargetWindow.document.close()
    return printTargetWindow
}

function printSale(sale, mode, forcedTitle = null, printTargetWindow = null) {
    return printSaleToWindow(printTargetWindow, sale, mode, forcedTitle)
}

function printSaleToWindow(printTargetWindow, sale, mode, forcedTitle = null) {
    const activePrintWindow = printTargetWindow || window.open('', '_blank', 'width=420,height=760')
    if (!activePrintWindow) {
        alert('La fenêtre d’impression a été bloquée par le navigateur.')
        return false
    }

    const title = forcedTitle || sale.ticket_name || sale.order_number || sale.reference || 'Ticket'
    const groupedLabel = sale.ticket_group ? `${escapeHtml(sale.ticket_group)} · ` : ''
    const paymentSummary = sale.payment_summary || {}
    const advanceAmount = Number(sale.paid_confirmed_amount ?? paymentSummary.paid_confirmed_amount ?? 0)
    const remainingAmount = Number(sale.remaining_amount ?? paymentSummary.remaining_amount ?? 0)
    const customerName = sale.customer?.name || props.defaultCustomerName || 'Client anonyme'
    const customerPhone = sale.customer?.phone || commandForm.value.customer_phone || newCustomerForm.value.phone || ''
    const itemsHtml = (sale.items || []).map((item) => `
        <tr>
            <td>${escapeHtml(item.article_name || '')}</td>
            <td style="text-align:center;">${escapeHtml(String(item.quantity || 0))}</td>
            <td style="text-align:right;">${escapeHtml(formatCurrency(item.total || 0))}</td>
        </tr>
    `).join('')

    const appointmentLine = sale.appointment_at
        ? `<p><strong>RDV:</strong> ${escapeHtml(formatDateTime(sale.appointment_at))}</p>`
        : ''
    const addressLine = sale.delivery_address
        ? `<p><strong>Adresse:</strong> ${escapeHtml(sale.delivery_address)}</p>`
        : ''
    const noteLine = sale.notes
        ? `<div class="note-box"><strong>Note:</strong><br>${escapeHtml(sale.notes).replace(/\n/g, '<br>')}</div>`
        : ''

    activePrintWindow.document.write(`
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>${escapeHtml(title)}</title>
            <style>
                body {
                    font-family: "Arial", sans-serif;
                    margin: 0;
                    padding: 18px;
                    color: #0f172a;
                }
                .header, .footer {
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 18px;
                }
                .header p, .meta p, .footer p {
                    margin: 4px 0;
                    font-size: 12px;
                }
                .pill {
                    display: inline-block;
                    margin-top: 8px;
                    padding: 6px 10px;
                    border-radius: 999px;
                    background: #e2e8f0;
                    font-size: 11px;
                    font-weight: bold;
                    text-transform: uppercase;
                    letter-spacing: 0.12em;
                }
                .section {
                    margin-top: 14px;
                    border-top: 1px dashed #94a3b8;
                    padding-top: 12px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 8px;
                    font-size: 12px;
                }
                th, td {
                    padding: 6px 0;
                    border-bottom: 1px dashed #cbd5e1;
                }
                th {
                    text-align: left;
                    font-size: 11px;
                    text-transform: uppercase;
                    color: #475569;
                }
                .totals p {
                    display: flex;
                    justify-content: space-between;
                    margin: 6px 0;
                    font-size: 12px;
                }
                .total {
                    font-size: 16px;
                    font-weight: bold;
                }
                .note-box {
                    margin-top: 12px;
                    border: 1px solid #cbd5e1;
                    border-radius: 12px;
                    padding: 10px;
                    font-size: 12px;
                    white-space: normal;
                }
                @media print {
                    body { padding: 0; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>${escapeHtml(settingsStore.storeName || 'POS')}</h1>
                <p>${escapeHtml(settingsStore.settings?.receipt?.receipt_header || 'Ticket')}</p>
                <span class="pill">${groupedLabel}${escapeHtml(title)}</span>
            </div>

            <div class="section meta">
                <p><strong>Reference:</strong> ${escapeHtml(sale.order_number || sale.reference || '-')}</p>
                <p><strong>Client:</strong> ${escapeHtml(customerName)}</p>
                <p><strong>Tel:</strong> ${escapeHtml(customerPhone || '-')}</p>
                <p><strong>Mode:</strong> ${escapeHtml(formatDeliveryMode(sale.service_mode || sale.delivery_mode))}</p>
                ${appointmentLine}
                ${addressLine}
            </div>

            <div class="section">
                <table>
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th style="text-align:center;">Qte</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>${itemsHtml}</tbody>
                </table>
            </div>

            <div class="section totals">
                <p><span>Sous-total</span><span>${escapeHtml(formatCurrency(sale.subtotal || 0))}</span></p>
                <p><span>TVA</span><span>${escapeHtml(formatCurrency(sale.tax_amount || 0))}</span></p>
                <p><span>Avance</span><span>${escapeHtml(formatCurrency(advanceAmount))}</span></p>
                <p><span>Reste</span><span>${escapeHtml(formatCurrency(remainingAmount))}</span></p>
                <p class="total"><span>Total</span><span>${escapeHtml(formatCurrency(sale.total || 0))}</span></p>
            </div>

            ${noteLine}

            <div class="section footer">
                <p>${escapeHtml(settingsStore.settings?.receipt?.receipt_footer || 'Merci')}</p>
                <p>${escapeHtml(formatDateTime(new Date().toISOString()))}</p>
                <p>${escapeHtml(mode === 'commande' ? 'Commande en preparation' : 'Ticket en attente de paiement')}</p>
            </div>
        </body>
        </html>
    `)
    activePrintWindow.document.close()
    activePrintWindow.focus()
    activePrintWindow.onload = () => {
        activePrintWindow.print()
        setTimeout(() => activePrintWindow.close(), 300)
    }

    return true
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
}

function formatDateTime(value) {
    if (!value) return '-'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return String(value)
    return date.toLocaleString('fr-FR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>
