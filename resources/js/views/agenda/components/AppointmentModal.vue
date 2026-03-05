<template>
    <teleport to="body">
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" @click="handleClose">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
                <!-- Header -->
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ appointment ? 'Modifier' : 'Créer' }} Rendez-vous
                    </h2>
                    <button @click="handleClose" class="p-1 hover:bg-gray-100 rounded-lg">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
                    <!-- Row 1: Date & Time -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                            <input 
                                v-model="form.date" 
                                type="date" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Heure *</label>
                            <input 
                                v-model="form.time" 
                                type="time" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Durée (min)</label>
                            <input 
                                v-model="form.duration" 
                                type="number" 
                                min="1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Objet -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Objet du RDV *</label>
                        <input 
                            v-model="form.subject" 
                            type="text" 
                            required
                            placeholder="Ex: Consultation"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Client -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Client *</label>
                        <div class="space-y-2 rounded-lg border border-gray-300 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm text-gray-700 truncate">
                                    {{ selectedCustomer?.name || 'Aucun client sélectionné' }}
                                </p>
                                <button
                                    type="button"
                                    @click="showCustomerSelector = !showCustomerSelector"
                                    class="px-3 py-1 text-xs font-semibold rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100"
                                >
                                    {{ showCustomerSelector ? 'Fermer' : 'Choisir' }}
                                </button>
                            </div>
                            <div v-if="showCustomerSelector" class="space-y-2">
                                <input
                                    v-model="customerSearch"
                                    type="text"
                                    placeholder="Chercher un client..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                                <div class="max-h-36 overflow-y-auto space-y-1">
                                    <button
                                        v-for="customer in filteredCustomers"
                                        :key="customer.id"
                                        type="button"
                                        @click="selectCustomer(customer)"
                                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 text-sm"
                                    >
                                        {{ customer.name }}
                                        <span v-if="customer.isLocalOnly" class="text-xs text-orange-600 ml-2">(local)</span>
                                    </button>
                                    <p v-if="filteredCustomers.length === 0" class="text-xs text-gray-500 px-3 py-2">Aucun client trouvé.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Téléphone & WhatsApp -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                            <input 
                                v-model="form.phone" 
                                type="tel" 
                                placeholder="Auto-rempli depuis le client"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp</label>
                            <input 
                                v-model="form.whatsapp" 
                                type="tel" 
                                placeholder="Auto-rempli depuis le client"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Responsable & Lieu -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Responsable</label>
                            <div class="space-y-2 rounded-lg border border-gray-300 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm text-gray-700 truncate">
                                        {{ selectedEmployee?.name || 'Aucun employé sélectionné' }}
                                    </p>
                                    <button
                                        type="button"
                                        @click="showEmployeeSelector = !showEmployeeSelector"
                                        class="px-3 py-1 text-xs font-semibold rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100"
                                    >
                                        {{ showEmployeeSelector ? 'Fermer' : 'Choisir' }}
                                    </button>
                                </div>
                                <div v-if="showEmployeeSelector" class="space-y-2">
                                    <input
                                        v-model="employeeSearch"
                                        type="text"
                                        placeholder="Chercher un employé..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                    <div class="max-h-36 overflow-y-auto space-y-1">
                                        <button
                                            type="button"
                                            class="w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 text-sm text-gray-500"
                                            @click="form.responsible_id = ''; showEmployeeSelector = false"
                                        >
                                            Aucun responsable
                                        </button>
                                        <button
                                            v-for="employee in filteredEmployees"
                                            :key="employee.id"
                                            type="button"
                                            @click="selectEmployee(employee)"
                                            class="w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 text-sm"
                                        >
                                            {{ employee.name }}
                                            <span v-if="employee.isLocalOnly" class="text-xs text-orange-600 ml-2">(local)</span>
                                        </button>
                                        <p v-if="filteredEmployees.length === 0" class="text-xs text-gray-500 px-3 py-2">Aucun employé trouvé.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type de lieu</label>
                            <select 
                                v-model="form.location_type" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="magasin">Magasin</option>
                                <option value="sur_place">Sur place</option>
                                <option value="livraison">Livraison</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    </div>

                    <!-- Adresse -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adresse/Lieu</label>
                        <input 
                            v-model="form.location" 
                            type="text" 
                            placeholder="Adresse du rendez-vous"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Statut -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <select 
                            v-model="form.status" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="en_cours">En cours</option>
                            <option value="confirme">Confirmé</option>
                            <option value="termine">Terminé</option>
                            <option value="annule">Annulé</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea 
                            v-model="form.notes" 
                            rows="3"
                            placeholder="Notes additionnelles..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <!-- Reminder Section -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rappel Client</h3>
                        
                        <div class="flex items-center mb-4">
                            <input 
                                v-model="form.reminder_enabled" 
                                type="checkbox" 
                                id="reminder-enabled"
                                class="w-4 h-4 text-blue-600"
                            />
                            <label for="reminder-enabled" class="ml-2 text-sm font-medium text-gray-700">Activer le rappel</label>
                        </div>

                        <div v-if="form.reminder_enabled" class="space-y-4">
                            <!-- Reminder Channel -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Canal</label>
                                <select 
                                    v-model="form.reminder_channel" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="sms">SMS</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="notification">Notification app</option>
                                    <option value="email">Email</option>
                                </select>
                            </div>

                            <!-- Reminder Timing -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quand rappeler ?</label>
                                <select 
                                    v-model="form.reminder_timing" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="24h">24h avant</option>
                                    <option value="2h">2h avant</option>
                                    <option value="30min">30 min avant</option>
                                    <option value="custom">Personnalisé</option>
                                </select>
                            </div>

                            <!-- Custom Timing -->
                            <div v-if="form.reminder_timing === 'custom'" class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Valeur</label>
                                    <input 
                                        v-model="form.reminder_custom_value" 
                                        type="number" 
                                        min="1"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Unité</label>
                                    <select 
                                        v-model="form.reminder_custom_unit" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="minutes">Minutes</option>
                                        <option value="hours">Heures</option>
                                        <option value="days">Jours</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Reminder Message -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                                <textarea 
                                    v-model="form.reminder_message" 
                                    rows="2"
                                    placeholder="Ex: Bonjour {NomClient}, rappel : {Objet} le {Date} à {Heure}."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                ></textarea>
                                <p class="text-xs text-gray-500 mt-1">Variables disponibles: {NomClient}, {Objet}, {Date}, {Heure}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 justify-end border-t border-gray-200 pt-6">
                        <button @click="handleClose" type="button" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600">
                            {{ appointment ? 'Mettre à jour' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </teleport>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import api from '../../../api'

const props = defineProps({
    appointment: {
        type: Object,
        default: null
    },
    initialDate: {
        type: Date,
        default: null
    }
})

const emit = defineEmits(['close', 'saved'])

const customers = ref([])
const employees = ref([])
const loading = ref(false)
const showCustomerSelector = ref(false)
const customerSearch = ref('')
const showEmployeeSelector = ref(false)
const employeeSearch = ref('')

const defaultForm = () => ({
    date: '',
    time: '',
    duration: 60,
    subject: '',
    customer_id: '',
    phone: '',
    whatsapp: '',
    responsible_id: '',
    location_type: 'magasin',
    location: '',
    status: 'confirme',
    notes: '',
    reminder_enabled: false,
    reminder_channel: 'sms',
    reminder_timing: '24h',
    reminder_custom_value: null,
    reminder_custom_unit: 'hours',
    reminder_message: '',
})

const form = ref(defaultForm())

function extractCollection(payload) {
    if (Array.isArray(payload)) return payload
    if (Array.isArray(payload?.data)) return payload.data
    return []
}

function readStorageArray(key) {
    try {
        const raw = localStorage.getItem(key)
        return raw ? JSON.parse(raw) : []
    } catch (error) {
        console.warn(`Unable to read ${key} from localStorage`, error)
        return []
    }
}

function normalizeCustomer(item, source = 'api') {
    const name = item.name || `${item.nom || ''} ${item.prenom || ''}`.trim() || item.raison_sociale || 'Client'
    const id = source === 'api' ? item.id : `local-${item.id ?? Date.now()}`
    return {
        id,
        name,
        phone: item.phone || '',
        whatsapp: item.whatsapp || item.phone || '',
        email: item.email || '',
        address: item.address || '',
        city: item.city || '',
        country: item.country || '',
        role: item.role || 'cashier',
        isLocalOnly: source !== 'api',
    }
}

function normalizeEmployee(item, source = 'api') {
    const name = item.name || `${item.nom || ''} ${item.prenom || ''}`.trim() || 'Employé'
    const id = source === 'api' ? item.id : `local-${item.id ?? Date.now()}`
    return {
        id,
        name,
        phone: item.phone || '',
        email: item.email || '',
        role: item.role || 'cashier',
        status: item.status || 'active',
        isLocalOnly: source !== 'api',
    }
}

function mergeByNameAndPhone(apiItems, localItems) {
    const keys = new Set(
        apiItems.map((item) => `${(item.name || '').toLowerCase()}|${item.phone || ''}`)
    )

    const merged = [...apiItems]
    for (const local of localItems) {
        const key = `${(local.name || '').toLowerCase()}|${local.phone || ''}`
        if (!keys.has(key)) {
            merged.push(local)
        }
    }

    return merged
}

const filteredCustomers = computed(() => {
    const q = customerSearch.value.trim().toLowerCase()
    if (!q) return customers.value
    return customers.value.filter((customer) =>
        (customer.name || '').toLowerCase().includes(q) ||
        (customer.phone || '').toLowerCase().includes(q) ||
        (customer.email || '').toLowerCase().includes(q)
    )
})

const filteredEmployees = computed(() => {
    const q = employeeSearch.value.trim().toLowerCase()
    if (!q) return employees.value
    return employees.value.filter((employee) =>
        (employee.name || '').toLowerCase().includes(q) ||
        (employee.phone || '').toLowerCase().includes(q) ||
        (employee.email || '').toLowerCase().includes(q)
    )
})

const selectedCustomer = computed(() =>
    customers.value.find((customer) => String(customer.id) === String(form.value.customer_id))
)

const selectedEmployee = computed(() =>
    employees.value.find((employee) => String(employee.id) === String(form.value.responsible_id))
)

function selectCustomer(customer) {
    form.value.customer_id = customer.id
    showCustomerSelector.value = false
    customerSearch.value = ''
}

function selectEmployee(employee) {
    form.value.responsible_id = employee.id
    showEmployeeSelector.value = false
    employeeSearch.value = ''
}

function normalizeDate(value) {
    if (!value) return ''
    if (typeof value === 'string') return value.slice(0, 10)
    return new Date(value).toISOString().slice(0, 10)
}

function normalizeTime(value) {
    if (!value) return ''
    return String(value).slice(0, 5)
}

function hydrateFromAppointment(appointment) {
    form.value = {
        ...defaultForm(),
        ...appointment,
        date: normalizeDate(appointment.date),
        time: normalizeTime(appointment.time),
        customer_id: appointment.customer_id ?? '',
        responsible_id: appointment.responsible_id ?? '',
    }
}

// Initialize form from appointment or initial date
watch([() => props.appointment, () => props.initialDate], ([apt, date]) => {
    if (apt) {
        hydrateFromAppointment(apt)
    } else if (date) {
        form.value = defaultForm()
        const dateStr = date.toISOString().split('T')[0]
        const timeStr = date.toTimeString().slice(0, 5)
        form.value.date = dateStr
        form.value.time = timeStr
    } else {
        form.value = defaultForm()
    }
}, { immediate: true })

watch(() => form.value.customer_id, (customerId) => {
    const selected = customers.value.find((customer) => String(customer.id) === String(customerId))
    if (!selected || props.appointment) return

    form.value.phone = selected.phone || ''
    form.value.whatsapp = selected.whatsapp || selected.phone || ''
})

async function ensureCustomerPersisted(customerId) {
    const selected = customers.value.find((customer) => String(customer.id) === String(customerId))
    if (!selected || !selected.isLocalOnly) {
        return customerId
    }

    const response = await api.post('/customers', {
        name: selected.name,
        phone: selected.phone || null,
        email: selected.email || null,
        address: selected.address || null,
        city: selected.city || null,
        country: selected.country || null,
        is_active: true,
    })

    const created = normalizeCustomer(response.data?.data || response.data, 'api')
    customers.value = [created, ...customers.value.filter((customer) => String(customer.id) !== String(customerId))]

    return created.id
}

async function ensureEmployeePersisted(employeeId) {
    if (!employeeId) return null

    const selected = employees.value.find((employee) => String(employee.id) === String(employeeId))
    if (!selected || !selected.isLocalOnly) {
        return employeeId
    }

    const response = await api.post('/employees', {
        name: selected.name,
        email: selected.email || null,
        phone: selected.phone || null,
        role: selected.role || 'cashier',
        status: 'active',
    })

    const created = normalizeEmployee(response.data?.data || response.data, 'api')
    employees.value = [created, ...employees.value.filter((employee) => String(employee.id) !== String(employeeId))]

    return created.id
}

// Load customers and employees
async function loadCustomers() {
    try {
        const [apiResponse] = await Promise.all([
            api.get('/customers', { params: { paginate: false, _ts: Date.now() } }),
        ])

        const apiCustomers = extractCollection(apiResponse.data).map((item) => normalizeCustomer(item, 'api'))
        const localCustomers = readStorageArray('pos_customers').map((item) => normalizeCustomer(item, 'local'))

        customers.value = mergeByNameAndPhone(apiCustomers, localCustomers)
    } catch (error) {
        console.error('Error loading customers:', error)
        customers.value = readStorageArray('pos_customers').map((item) => normalizeCustomer(item, 'local'))
    }
}

async function loadEmployees() {
    try {
        const [apiResponse] = await Promise.all([
            api.get('/employees', { params: { paginate: false, _ts: Date.now() } }),
        ])

        const apiEmployees = extractCollection(apiResponse.data).map((item) => normalizeEmployee(item, 'api'))
        const localEmployees = readStorageArray('pos_employees').map((item) => normalizeEmployee(item, 'local'))

        employees.value = mergeByNameAndPhone(apiEmployees, localEmployees)
    } catch (error) {
        console.error('Error loading employees:', error)
        employees.value = readStorageArray('pos_employees').map((item) => normalizeEmployee(item, 'local'))
    }
}

// Handle form submission
async function handleSubmit() {
    try {
        loading.value = true
        const url = props.appointment ? `/appointments/${props.appointment.id}` : '/appointments'
        const method = props.appointment ? 'put' : 'post'

        if (!form.value.customer_id) {
            alert('Veuillez sélectionner un client')
            return
        }

        const customerId = await ensureCustomerPersisted(form.value.customer_id)
        const employeeId = await ensureEmployeePersisted(form.value.responsible_id)

        const payload = {
            ...form.value,
            customer_id: customerId,
            responsible_id: employeeId,
        }

        await api[method](url, payload)
        emit('saved')
    } catch (error) {
        console.error('Error saving appointment:', error)
        const apiMessage = error?.response?.data?.message
            || error?.response?.data?.errors
            || 'Erreur lors de la sauvegarde'
        alert(typeof apiMessage === 'string' ? apiMessage : 'Erreur lors de la sauvegarde')
    } finally {
        loading.value = false
    }
}

function handleClose() {
    emit('close')
}

// Load data on mount
onMounted(() => {
    loadCustomers()
    loadEmployees()
})
</script>
