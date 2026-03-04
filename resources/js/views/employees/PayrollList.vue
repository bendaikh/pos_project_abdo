<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion de la Paie</h1>
                <p class="text-gray-500">Gérez les salaires et rémunérations de votre équipe</p>
            </div>
            <button type="button" @click="handleNewPayrollClick" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouveau Paiement
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Employés</p>
                <p class="text-2xl font-bold text-gray-900">{{ employees.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Salaires ce mois</p>
                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(totalSalaries) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Salaire moyen</p>
                <p class="text-2xl font-bold text-primary-600">{{ formatCurrency(averageSalary) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Paiements effectués</p>
                <p class="text-2xl font-bold text-blue-600">{{ paidCount }}</p>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
            <input
                v-model="search"
                type="text"
                placeholder="Rechercher par nom, prénom ou ID employé..."
                class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <select v-model="filterPaymentStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="paid">Payé</option>
                <option value="pending">En attente</option>
            </select>
        </div>

        <!-- Payroll Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Poste</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Salaire de base</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prime / Bonus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mode de paiement</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="employee in filteredPayroll" :key="employee.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-100 bg-primary-100">
                                    <img v-if="employee.photo_url" :src="getEmployeePhotoUrl(employee)" alt="Photo" class="w-full h-full object-cover" />
                                    <span v-else class="flex items-center justify-center w-full h-full text-xs font-semibold text-primary-600">{{ getInitials(employee.nom, employee.prenom) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ employee.nom }} {{ employee.prenom }}</p>
                                    <p class="text-sm text-gray-500">{{ employee.email || '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ employee.poste || '-' }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ formatCurrency(employee.salaire_base || 0) }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ formatCurrency(employee.prime || 0) }}</td>
                        <td class="px-6 py-4 font-bold text-gray-900">{{ formatCurrency((employee.salaire_base || 0) + (employee.prime || 0)) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ getPaymentMethodLabel(employee.mode_paiement) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span :class="['px-2 py-1 text-xs font-medium rounded-full', employee.payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800']">
                                {{ employee.payment_status === 'paid' ? 'Payé' : 'En attente' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="viewPayrollDetails(employee)" class="p-2 text-blue-400 hover:text-blue-600 rounded-lg hover:bg-blue-50" title="Détails">
                                    <EyeIcon class="w-5 h-5" />
                                </button>
                                <button @click="editPayroll(employee)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                    <PencilIcon class="w-5 h-5" />
                                </button>
                                <button @click="markAsPaid(employee)" v-if="employee.payment_status !== 'paid'" class="p-2 text-green-400 hover:text-green-600 rounded-lg hover:bg-green-50" title="Marquer comme payé">
                                    <CheckIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredPayroll.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <UsersIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            Aucun employé trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Payroll Form Modal -->
        <teleport to="body">
            <div v-if="showPayrollForm" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ editingPayroll ? 'Modifier Paiement' : 'Nouveau Paiement' }}</h3>
                        <button @click="closePayrollForm" class="text-gray-400 hover:text-gray-600" :disabled="loading">
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>
                    <form @submit.prevent="savePayroll" class="space-y-4">
                        <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                            {{ errorMessage }}
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employé *</label>
                            <select v-model="payrollForm.employee_id" required :disabled="loading" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:bg-gray-100">
                                <option value="">Sélectionner un employé</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                    {{ emp.name || emp.nom }} {{ emp.prenom }}
                                </option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prime/Bonus (DH)</label>
                                <input v-model.number="payrollForm.prime" type="number" min="0" step="0.01" :disabled="loading" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:bg-gray-100" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Avance (DH)</label>
                                <input v-model.number="payrollForm.advance" type="number" min="0" step="0.01" :disabled="loading" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:bg-gray-100" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement *</label>
                            <select v-model="payrollForm.mode_paiement" required :disabled="loading" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:bg-gray-100">
                                <option value="transfer">Virement</option>
                                <option value="cash">Espèces</option>
                                <option value="check">Chèque</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Commentaires</label>
                            <textarea v-model="payrollForm.comments" :disabled="loading" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:bg-gray-100 text-sm" rows="2" placeholder="Notes supplémentaires..."></textarea>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="closePayrollForm" :disabled="loading" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 disabled:bg-gray-100">
                                Annuler
                            </button>
                            <button type="submit" :disabled="loading || !payrollForm.employee_id" class="flex-1 px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 disabled:bg-gray-400 flex items-center justify-center">
                                <span v-if="!loading">{{ editingPayroll ? 'Modifier' : 'Créer' }}</span>
                                <span v-else>Traitement...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </teleport>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useSettingsStore } from '../../stores/settings'
import { employeesApi, payrollApi } from '../../api'
import {
    PlusIcon,
    EyeIcon,
    PencilIcon,
    CheckIcon,
    UsersIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const employees = ref([])
const search = ref('')
const filterPaymentStatus = ref('')
const showPayrollForm = ref(false)
const editingPayroll = ref(null)
const loading = ref(false)
const errorMessage = ref('')

const defaultPayrollForm = () => ({
    employee_id: '',
    salaire_base: '',
    prime: '',
    mode_paiement: 'virement',
    date_paiement: new Date().toISOString().split('T')[0]
})

const payrollForm = reactive(defaultPayrollForm())

const filteredPayroll = computed(() => {
    let result = employees.value

    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(e =>
            e.nom?.toLowerCase().includes(query) ||
            e.prenom?.toLowerCase().includes(query) ||
            e.employee_id?.toLowerCase().includes(query)
        )
    }

    if (filterPaymentStatus.value) {
        result = result.filter(e => e.payment_status === filterPaymentStatus.value)
    }

    return result
})

const totalSalaries = computed(() =>
    employees.value.reduce((sum, e) => sum + ((e.salaire_base || 0) + (e.prime || 0)), 0)
)
const averageSalary = computed(() =>
    employees.value.length > 0 ? totalSalaries.value / employees.value.length : 0
)
const paidCount = computed(() =>
    employees.value.filter(e => e.payment_status === 'paid').length
)

function getInitials(nom, prenom) {
    return ((nom || '').charAt(0) + (prenom || '').charAt(0)).toUpperCase()
}

function isDataUrl(url) {
    return typeof url === 'string' && (url.startsWith('data:') || url.startsWith('blob:'))
}

function getEmployeePhotoUrl(employee) {
    const url = employee?.photo_url
    if (!url) {
        return ''
    }
    if (isDataUrl(url)) {
        return url
    }
    const cacheKey = employee.photo_cache_key || 0
    const separator = url.includes('?') ? '&' : '?'
    return `${url}${separator}t=${cacheKey}`
}

function getPaymentMethodLabel(mode) {
    const labels = {
        'virement': 'Virement',
        'especes': 'Espèces',
        'cheque': 'Chèque'
    }
    return labels[mode] || 'N/A'
}

async function loadEmployees() {
    try {
        const response = await employeesApi.list()
        employees.value = response.data || []
    } catch (error) {
        console.error('Error loading employees:', error)
    }
}

function openPayrollForm() {
    editingPayroll.value = null
    Object.assign(payrollForm, defaultPayrollForm())
    showPayrollForm.value = true
}

function handleNewPayrollClick() {
    console.log('"Nouveau Paiement" button clicked')
    openPayrollForm()
}

function closePayrollForm() {
    showPayrollForm.value = false
    editingPayroll.value = null
}

watch(showPayrollForm, (value) => {
    console.log('showPayrollForm changed:', value)
})

async function savePayroll() {
    try {
        loading.value = true
        errorMessage.value = ''
        
        console.log('savePayroll called - payrollForm:', payrollForm)
        
        if (!payrollForm.employee_id) {
            throw new Error('Veuillez sélectionner un employé')
        }
        
        const currentDate = new Date()
        const month = currentDate.getMonth() + 1
        const year = currentDate.getFullYear()
        
        const payrollData = {
            employee_id: Number(payrollForm.employee_id),
            month: month,
            year: year,
            prime: Number(payrollForm.prime) || 0,
            bonus: 0,
            advance: Number(payrollForm.advance) || 0,
            retention: 0,
            payment_method: payrollForm.mode_paiement || 'transfer',
            comments: `Paiement manuel`
        }
        
        console.log('Sending payroll data to API:', payrollData)
        
        // Call the API to calculate and create payroll
        const response = await payrollApi.store(payrollData)
        
        console.log('API response:', response)
        
        if (response && response.data) {
            // Reload employees to get updated payroll data
            await loadEmployees()
            closePayrollForm()
            alert('✅ Paiement créé avec succès')
        }
    } catch (error) {
        console.error('Error saving payroll:', error)
        const message = error.response?.data?.message || error.message || 'Erreur lors de la création du paiement'
        errorMessage.value = message
        alert('❌ ' + message)
    } finally {
        loading.value = false
    }
}

function viewPayrollDetails(employee) {
    console.log('View payroll details:', employee)
}

function editPayroll(employee) {
    editingPayroll.value = employee
    payrollForm.employee_id = employee.id
    payrollForm.salaire_base = employee.salaire_base || ''
    payrollForm.prime = employee.prime || ''
    payrollForm.mode_paiement = employee.mode_paiement || 'virement'
    payrollForm.date_paiement = employee.date_paiement || new Date().toISOString().split('T')[0]
    showPayrollForm.value = true
}

function markAsPaid(employee) {
    employee.payment_status = 'paid'
}

onMounted(() => {
    loadEmployees()
})
</script>
