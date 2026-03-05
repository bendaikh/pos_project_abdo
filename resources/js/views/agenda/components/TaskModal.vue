<template>
    <teleport to="body">
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" @click="handleClose">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
                <!-- Header -->
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ task ? 'Modifier' : 'Créer' }} Tâche
                    </h2>
                    <button @click="handleClose" class="p-1 hover:bg-gray-100 rounded-lg">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
                    <!-- Row 1: Date & Time -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date d'échéance *</label>
                            <input 
                                v-model="form.due_date" 
                                type="date" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Heure (optionnel)</label>
                            <input 
                                v-model="form.due_time" 
                                type="time" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            />
                        </div>
                    </div>

                    <!-- Objet -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Objet de la tâche *</label>
                        <input 
                            v-model="form.subject" 
                            type="text" 
                            required
                            placeholder="Ex: Préparer commande client"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea 
                            v-model="form.description" 
                            rows="3"
                            placeholder="Détails de la tâche..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        ></textarea>
                    </div>

                    <!-- Employee & Priority -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Employé assigné *</label>
                            <div class="space-y-2 rounded-lg border border-gray-300 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm text-gray-700 truncate">
                                        {{ selectedEmployee?.name || 'Aucun employé sélectionné' }}
                                    </p>
                                    <button
                                        type="button"
                                        @click="showEmployeeSelector = !showEmployeeSelector"
                                        class="px-3 py-1 text-xs font-semibold rounded-md bg-green-50 text-green-700 hover:bg-green-100"
                                    >
                                        {{ showEmployeeSelector ? 'Fermer' : 'Choisir' }}
                                    </button>
                                </div>
                                <div v-if="showEmployeeSelector" class="space-y-2">
                                    <input
                                        v-model="employeeSearch"
                                        type="text"
                                        placeholder="Chercher un employé..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                    />
                                    <div class="max-h-36 overflow-y-auto space-y-1">
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Priorité</label>
                            <select 
                                v-model="form.priority" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            >
                                <option value="faible">Faible</option>
                                <option value="moyenne">Moyenne</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                    </div>

                    <!-- Statut -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <select 
                            v-model="form.status" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            <option value="en_attente">En attente</option>
                            <option value="en_cours">En cours</option>
                            <option value="termine">Terminé</option>
                            <option value="annule">Annulé</option>
                        </select>
                    </div>

                    <!-- Recurrence -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex items-center mb-4">
                            <input
                                v-model="form.recurrence_enabled"
                                type="checkbox"
                                id="recurrence-enabled"
                                class="w-4 h-4 text-green-600"
                            />
                            <label for="recurrence-enabled" class="ml-2 text-sm font-medium text-gray-700">
                                Tâche récurrente
                            </label>
                        </div>

                        <div v-if="form.recurrence_enabled" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Répéter</label>
                                <select
                                    v-model="form.recurrence_pattern"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                >
                                    <option value="">Sélectionner</option>
                                    <option value="daily">Chaque jour</option>
                                    <option value="weekly">Chaque semaine</option>
                                    <option value="monthly">Chaque mois</option>
                                    <option value="quarterly">Chaque trimestre</option>
                                    <option value="semiannual">Chaque semestre</option>
                                    <option value="yearly">Chaque année</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                                    <input
                                        v-model="form.recurrence_start_date"
                                        type="date"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin (optionnelle)</label>
                                    <input
                                        v-model="form.recurrence_end_date"
                                        type="date"
                                        :disabled="form.recurrence_repeat_count !== null && form.recurrence_repeat_count !== ''"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 disabled:bg-gray-100"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de répétitions</label>
                                <input
                                    v-model.number="form.recurrence_repeat_count"
                                    type="number"
                                    min="1"
                                    :disabled="!!form.recurrence_end_date"
                                    placeholder="Optionnel (si pas de date de fin)"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 disabled:bg-gray-100"
                                />
                                <p class="mt-1 text-xs text-gray-500">Utilisez soit la date de fin, soit le nombre de répétitions.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Reminder Section -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rappel Employé</h3>
                        
                        <div class="flex items-center mb-4">
                            <input 
                                v-model="form.reminder_enabled" 
                                type="checkbox" 
                                id="reminder-enabled"
                                class="w-4 h-4 text-green-600"
                            />
                            <label for="reminder-enabled" class="ml-2 text-sm font-medium text-gray-700">Activer le rappel</label>
                        </div>

                        <div v-if="form.reminder_enabled" class="space-y-4">
                            <!-- Reminder Channel -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Canal</label>
                                <select 
                                    v-model="form.reminder_channel" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                >
                                    <option value="notification">Notification app</option>
                                    <option value="sms">SMS</option>
                                    <option value="whatsapp">WhatsApp</option>
                                </select>
                            </div>

                            <!-- Reminder Timing -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quand rappeler ?</label>
                                <select 
                                    v-model="form.reminder_timing" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                >
                                    <option value="at_time">À l'heure</option>
                                    <option value="1h">1h avant</option>
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
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Unité</label>
                                    <select 
                                        v-model="form.reminder_custom_unit" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                    >
                                        <option value="minutes">Minutes</option>
                                        <option value="hours">Heures</option>
                                        <option value="days">Jours</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Repeat Until Validation -->
                            <div class="flex items-center">
                                <input 
                                    v-model="form.reminder_repeat_until_validation" 
                                    type="checkbox" 
                                    id="repeat-enabled"
                                    class="w-4 h-4 text-green-600"
                                />
                                <label for="repeat-enabled" class="ml-2 text-sm font-medium text-gray-700">Répéter jusqu'à validation (toutes les 30 min)</label>
                            </div>

                            <div v-if="form.reminder_repeat_until_validation">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Intervalle de répétition (minutes)</label>
                                <input 
                                    v-model="form.reminder_repeat_interval" 
                                    type="number" 
                                    min="1"
                                    value="30"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 justify-end border-t border-gray-200 pt-6">
                        <button @click="handleClose" type="button" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded-lg font-medium hover:bg-green-600">
                            {{ task ? 'Mettre à jour' : 'Créer' }}
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
    task: {
        type: Object,
        default: null
    },
    initialDate: {
        type: Date,
        default: null
    }
})

const emit = defineEmits(['close', 'saved'])

const employees = ref([])
const loading = ref(false)
const showEmployeeSelector = ref(false)
const employeeSearch = ref('')

const defaultForm = () => ({
    due_date: '',
    due_time: '',
    subject: '',
    description: '',
    employee_id: '',
    priority: 'moyenne',
    status: 'en_attente',
    reminder_enabled: false,
    reminder_channel: 'notification',
    reminder_timing: 'at_time',
    reminder_custom_value: null,
    reminder_custom_unit: 'hours',
    reminder_repeat_until_validation: false,
    reminder_repeat_interval: 30,
    recurrence_enabled: false,
    recurrence_pattern: '',
    recurrence_start_date: '',
    recurrence_end_date: '',
    recurrence_repeat_count: null,
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

const filteredEmployees = computed(() => {
    const q = employeeSearch.value.trim().toLowerCase()
    if (!q) return employees.value
    return employees.value.filter((employee) =>
        (employee.name || '').toLowerCase().includes(q) ||
        (employee.phone || '').toLowerCase().includes(q) ||
        (employee.email || '').toLowerCase().includes(q)
    )
})

const selectedEmployee = computed(() =>
    employees.value.find((employee) => String(employee.id) === String(form.value.employee_id))
)

function selectEmployee(employee) {
    form.value.employee_id = employee.id
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

function toNullableCount(value) {
    if (value === '' || value === null || value === undefined) return null
    const parsed = Number(value)
    return Number.isFinite(parsed) && parsed > 0 ? parsed : null
}

function hydrateFromTask(task) {
    const recurrencePattern = task.recurrence_pattern || task.recurrence_frequency || ''
    const recurrenceEnd = task.recurrence_end_date || task.recurrence_until || ''
    const recurrenceStart = task.recurrence_start_date || task.due_date || ''

    form.value = {
        ...defaultForm(),
        ...task,
        due_date: normalizeDate(task.due_date),
        due_time: normalizeTime(task.due_time),
        employee_id: task.employee_id ?? '',
        recurrence_enabled: Boolean(task.recurrence_enabled || recurrencePattern),
        recurrence_pattern: recurrencePattern,
        recurrence_start_date: normalizeDate(recurrenceStart),
        recurrence_end_date: normalizeDate(recurrenceEnd),
        recurrence_repeat_count: toNullableCount(task.recurrence_repeat_count),
    }
}

// Initialize form from task or initial date
watch([() => props.task, () => props.initialDate], ([tsk, date]) => {
    if (tsk) {
        hydrateFromTask(tsk)
    } else if (date) {
        form.value = defaultForm()
        const dateStr = date.toISOString().split('T')[0]
        const timeStr = date.toTimeString().slice(0, 5)
        form.value.due_date = dateStr
        form.value.due_time = timeStr
        form.value.recurrence_start_date = dateStr
    } else {
        form.value = defaultForm()
    }
}, { immediate: true })

watch(() => form.value.recurrence_enabled, (enabled) => {
    if (enabled && !form.value.recurrence_start_date) {
        form.value.recurrence_start_date = form.value.due_date || new Date().toISOString().slice(0, 10)
    }

    if (!enabled) {
        form.value.recurrence_pattern = ''
        form.value.recurrence_start_date = ''
        form.value.recurrence_end_date = ''
        form.value.recurrence_repeat_count = null
    }
})

watch(() => form.value.recurrence_start_date, (startDate) => {
    if (form.value.recurrence_enabled && startDate) {
        form.value.due_date = startDate
    }
})

watch(() => form.value.recurrence_end_date, (endDate) => {
    if (endDate) {
        form.value.recurrence_repeat_count = null
    }
})

watch(() => form.value.recurrence_repeat_count, (count) => {
    if (count && Number(count) > 0) {
        form.value.recurrence_end_date = ''
    }
})

async function ensureEmployeePersisted(employeeId) {
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

// Load employees
async function loadEmployees() {
    try {
        const response = await api.get('/employees', {
            params: { paginate: false, _ts: Date.now() }
        })
        const apiEmployees = extractCollection(response.data).map((item) => normalizeEmployee(item, 'api'))
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
        const url = props.task ? `/tasks/${props.task.id}` : '/tasks'
        const method = props.task ? 'put' : 'post'

        const payload = {
            ...form.value,
            recurrence_repeat_count: toNullableCount(form.value.recurrence_repeat_count),
        }

        if (!payload.employee_id) {
            alert('Veuillez sélectionner un employé')
            return
        }

        payload.employee_id = await ensureEmployeePersisted(payload.employee_id)

        await api[method](url, payload)
        emit('saved')
    } catch (error) {
        console.error('Error saving task:', error)
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
    loadEmployees()
})
</script>
