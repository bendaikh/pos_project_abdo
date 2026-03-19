<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center px-4 py-6">
            <div class="fixed inset-0 bg-slate-900/60" @click="$emit('close')"></div>

            <div class="relative z-10 w-full max-w-5xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">POS</p>
                            <h2 class="mt-1 text-2xl font-semibold text-slate-900">Enregistrer le ticket</h2>
                            <p class="mt-1 text-sm text-slate-600">
                                Total {{ formatCurrency(cartData.total || 0) }} · {{ cartItems.length }} article(s)
                            </p>
                        </div>
                        <button
                            type="button"
                            class="rounded-full border border-slate-200 px-3 py-1.5 text-sm text-slate-600 transition hover:bg-white"
                            @click="$emit('close')"
                        >
                            Fermer
                        </button>
                    </div>
                </div>

                <div class="border-b border-slate-200 px-6 py-3">
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="tab in tabs"
                            :key="tab.value"
                            type="button"
                            class="rounded-2xl px-4 py-2 text-sm font-semibold transition"
                            :class="activeTab === tab.value ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            @click="activeTab = tab.value"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                </div>

                <div class="grid gap-6 px-6 py-6 lg:grid-cols-[300px,1fr]">
                    <aside class="space-y-4">
                        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Résumé</p>
                            <div class="mt-4 space-y-3">
                                <div class="rounded-2xl bg-slate-50 p-3">
                                    <p class="text-xs uppercase text-slate-500">Client</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ defaultCustomerName || 'Client anonyme' }}</p>
                                </div>
                                <div v-if="serviceModeEnabled" class="rounded-2xl bg-slate-50 p-3">
                                    <p class="text-xs uppercase text-slate-500">Mode service</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ formatDeliveryMode(defaultDeliveryMode) }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-3">
                                    <p class="text-xs uppercase text-slate-500">Articles</p>
                                    <div class="mt-2 max-h-48 space-y-2 overflow-y-auto pr-1">
                                        <div
                                            v-for="(item, index) in cartItems"
                                            :key="`${item.article_id}-${index}`"
                                            class="flex items-start justify-between gap-3 text-sm"
                                        >
                                            <div class="min-w-0">
                                                <p class="font-medium text-slate-900">{{ item.article_name }}</p>
                                                <p class="text-xs text-slate-500">x{{ item.quantity }}</p>
                                            </div>
                                            <p class="whitespace-nowrap font-semibold text-slate-700">{{ formatCurrency(getItemTotal(item)) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <section class="min-h-[460px]">
                        <div v-if="activeTab === 'liste'" class="space-y-5">
                            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-sm text-emerald-900">
                                    Cliquez directement sur un nom de ticket. L’addition s’imprime immédiatement et le ticket est enregistré pour un paiement ultérieur.
                                </p>
                            </div>

                            <div v-if="groupedPresetTickets.length" class="space-y-5">
                                <div v-for="group in groupedPresetTickets" :key="group.name" class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-white">
                                            {{ group.name }}
                                        </span>
                                        <span class="text-sm text-slate-500">{{ group.items.length }} ticket(s)</span>
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                        <button
                                            v-for="ticket in group.items"
                                            :key="ticket.key"
                                            type="button"
                                            class="rounded-3xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50"
                                            :disabled="saving"
                                            @click="saveListedTicket(ticket)"
                                        >
                                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ ticket.group }}</p>
                                            <p class="mt-2 text-lg font-semibold text-slate-900">{{ ticket.name }}</p>
                                            <p class="mt-1 text-sm text-slate-500">Imprimer et enregistrer</p>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="activeTab === 'personnalise'" class="space-y-5">
                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Nom du ticket</span>
                                    <input
                                        v-model.trim="personalizedForm.ticket_name"
                                        type="text"
                                        placeholder="Ex: Mariage Salma"
                                        class="w-full rounded-2xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"
                                    >
                                </label>
                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Groupe</span>
                                    <input
                                        v-model.trim="personalizedForm.ticket_group"
                                        type="text"
                                        placeholder="Ex: Evenements"
                                        class="w-full rounded-2xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"
                                    >
                                </label>
                            </div>

                            <label class="block">
                                <span class="mb-2 block text-sm font-medium text-slate-700">Commentaire</span>
                                <textarea
                                    v-model.trim="personalizedForm.comment"
                                    rows="5"
                                    placeholder="Commentaire libre pour l’impression"
                                    class="w-full rounded-3xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"
                                ></textarea>
                            </label>

                            <div class="flex justify-end">
                                <button
                                    type="button"
                                    class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="saving || !personalizedForm.ticket_name"
                                    @click="savePersonalizedTicket"
                                >
                                    {{ saving ? 'Enregistrement...' : 'Enregistrer et imprimer' }}
                                </button>
                            </div>
                        </div>

                        <div v-else class="space-y-5">
                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">N° cmd</p>
                                    <p class="mt-2 text-lg font-semibold text-slate-900">Automatique</p>
                                </div>
                                <label class="block xl:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Date du RDV avec heure</span>
                                    <input
                                        v-model="commandForm.appointment_at"
                                        type="datetime-local"
                                        class="w-full rounded-2xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"
                                    >
                                </label>
                                <label v-if="serviceModeEnabled" class="block">
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Mode de service</span>
                                    <select
                                        v-model="commandForm.delivery_mode"
                                        class="w-full rounded-2xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"
                                    >
                                        <option v-for="mode in commandDeliveryModes" :key="mode.value" :value="mode.value">
                                            {{ mode.label }}
                                        </option>
                                    </select>
                                </label>
                            </div>

                            <div class="rounded-3xl border border-slate-200 p-4">
                                <div class="mb-4 flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        class="rounded-2xl px-4 py-2 text-sm font-semibold transition"
                                        :class="customerMode === 'existing' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'"
                                        @click="customerMode = 'existing'"
                                    >
                                        Client existant
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-2xl px-4 py-2 text-sm font-semibold transition"
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
                                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"
                                        >
                                    </label>

                                    <div class="grid gap-2 md:grid-cols-2">
                                        <button
                                            v-for="customer in filteredCustomers"
                                            :key="customer.id"
                                            type="button"
                                            class="rounded-2xl border px-4 py-3 text-left transition"
                                            :class="selectedCustomer?.id === customer.id ? 'border-blue-500 bg-blue-50' : 'border-slate-200 bg-white hover:border-slate-300'"
                                            @click="selectCustomer(customer)"
                                        >
                                            <p class="font-semibold text-slate-900">{{ customer.name }}</p>
                                            <p class="mt-1 text-sm text-slate-500">{{ customer.phone || 'Sans téléphone' }}</p>
                                        </button>
                                    </div>

                                    <p v-if="loadingCustomers" class="text-sm text-slate-500">Chargement des clients...</p>

                                    <div v-if="selectedCustomer" class="grid gap-4 rounded-3xl bg-slate-50 p-4 md:grid-cols-3">
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
                                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"
                                        >
                                    </label>
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-medium text-slate-700">Téléphone</span>
                                        <input
                                            v-model.trim="newCustomerForm.phone"
                                            type="text"
                                            placeholder="Téléphone"
                                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"
                                        >
                                    </label>
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-medium text-slate-700">Activité</span>
                                        <input
                                            v-model.trim="newCustomerForm.activity"
                                            type="text"
                                            placeholder="Activité"
                                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"
                                        >
                                    </label>
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-medium text-slate-700">Adresse</span>
                                        <input
                                            v-model.trim="newCustomerForm.address"
                                            type="text"
                                            placeholder="Adresse"
                                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"
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
                                        class="w-full rounded-2xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"
                                    >
                                </label>
                                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-amber-700">Reste à payer</p>
                                    <p class="mt-2 text-lg font-semibold text-amber-900">{{ formatCurrency(commandRemainingAmount) }}</p>
                                </div>
                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Note</span>
                                    <textarea
                                        v-model.trim="commandForm.notes"
                                        rows="3"
                                        placeholder="Commentaire pour la commande"
                                        class="w-full rounded-3xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"
                                    ></textarea>
                                </label>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="button"
                                    class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="saving || !canSaveCommande"
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
})

const emit = defineEmits(['close', 'saved'])

const settingsStore = useSettingsStore()
const customListsStore = useCustomListsStore()

const PRESET_STORAGE_KEY = 'pos_ticket_presets_v1'
const DEFAULT_PRESET_TICKETS = [
    { group: 'Caisse', name: 'Ticket comptoir' },
    { group: 'Caisse', name: 'Ticket retrait' },
    { group: 'Production', name: 'Ticket atelier' },
    { group: 'Livraison', name: 'Ticket livraison' },
]

const tabs = [
    { value: 'liste', label: 'Liste tickets' },
    { value: 'personnalise', label: 'Ticket personnalise' },
    { value: 'commande', label: 'Commandes' },
]

const activeTab = ref('liste')
const saving = ref(false)
const savedPresets = ref([])
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

const groupedPresetTickets = computed(() => {
    const groups = new Map()

    for (const ticket of savedPresets.value) {
        const groupName = ticket.group || 'Sans groupe'
        if (!groups.has(groupName)) {
            groups.set(groupName, [])
        }
        groups.get(groupName).push({
            ...ticket,
            key: `${groupName}-${ticket.name}`,
        })
    }

    return Array.from(groups.entries()).map(([name, items]) => ({
        name,
        items: items.sort((a, b) => a.name.localeCompare(b.name, 'fr')),
    }))
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
    if (tab === 'commande' && !customers.value.length) {
        await fetchCustomers()
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
    await customListsStore.fetchList('mode_de_service')
    commandForm.value.delivery_mode = normalizeDeliveryMode(commandForm.value.delivery_mode)
    savedPresets.value = loadPresetTickets()
    if (activeTab.value === 'commande') {
        await fetchCustomers()
    }
})

function normalizeDeliveryMode(mode) {
    return customListsStore.findServiceMode(mode, { includeInactive: true })?.value
        || customListsStore.defaultServiceModeValue()
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

function loadPresetTickets() {
    try {
        const raw = localStorage.getItem(PRESET_STORAGE_KEY)
        const parsed = raw ? JSON.parse(raw) : []
        return mergePresetTickets(DEFAULT_PRESET_TICKETS, parsed)
    } catch {
        return [...DEFAULT_PRESET_TICKETS]
    }
}

function savePresetTicketsToStorage(list) {
    localStorage.setItem(PRESET_STORAGE_KEY, JSON.stringify(list))
}

function mergePresetTickets(baseTickets, customTickets) {
    const seen = new Set()
    const merged = []

    for (const ticket of [...baseTickets, ...(customTickets || [])]) {
        const normalized = {
            group: String(ticket.group || 'Sans groupe').trim(),
            name: String(ticket.name || '').trim(),
        }
        if (!normalized.name) continue
        const key = `${normalized.group}::${normalized.name}`.toLowerCase()
        if (seen.has(key)) continue
        seen.add(key)
        merged.push(normalized)
    }

    return merged
}

function persistPresetTicket(ticketName, ticketGroup) {
    const updated = mergePresetTickets(savedPresets.value, [{
        name: ticketName,
        group: ticketGroup || 'Sans groupe',
    }])
    savedPresets.value = updated
    savePresetTicketsToStorage(updated)
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
    const ticketName = personalizedForm.value.ticket_name.trim()
    if (!ticketName) {
        alert('Veuillez saisir un nom de ticket.')
        return
    }

    const ticketGroup = personalizedForm.value.ticket_group.trim() || 'Personnalise'
    persistPresetTicket(ticketName, ticketGroup)

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

        printSale(finalSale, 'commande')
        alert('Commande enregistrée et envoyée à l’impression.')
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

async function saveAndPrint({ title, payload }) {
    saving.value = true
    try {
        const { data } = await salesApi.create(payload)
        const refreshed = await salesApi.get(data.id)
        printSale(refreshed.data, payload.ticket_type || 'liste', title)
        alert('Ticket enregistré et envoyé à l’impression.')
        emit('saved', refreshed.data)
    } catch (error) {
        console.error('Erreur enregistrement ticket:', error)
        alert(error.response?.data?.message || "Impossible d'enregistrer le ticket.")
    } finally {
        saving.value = false
    }
}

function printSale(sale, mode, forcedTitle = null) {
    const printWindow = window.open('', '_blank', 'width=420,height=760')
    if (!printWindow) {
        alert('La fenêtre d’impression a été bloquée par le navigateur.')
        return
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

    printWindow.document.write(`
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
    printWindow.document.close()
    printWindow.focus()
    printWindow.onload = () => {
        printWindow.print()
        setTimeout(() => printWindow.close(), 300)
    }
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
