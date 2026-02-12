<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Employés</h1>
                <p class="text-gray-500">Gérez votre équipe et leurs accès</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouvel Employé
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Employés</p>
                <p class="text-2xl font-bold text-gray-900">{{ employees.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Actifs</p>
                <p class="text-2xl font-bold text-green-600">{{ activeCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Ventes</p>
                <p class="text-2xl font-bold text-primary-600">{{ formatCurrency(totalSales) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Moyenne/Employé</p>
                <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(averageSales) }}</p>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
            <input 
                v-model="search"
                type="text"
                placeholder="Rechercher par ID, nom, prénom, email ou téléphone..."
                class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="active">Actifs</option>
                <option value="inactive">Inactifs</option>
                <option value="suspended">Suspendus</option>
            </select>
            <select v-model="filterRole" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les rôles</option>
                <option value="admin">Administrateur</option>
                <option value="manager">Manager</option>
                <option value="cashier">Caissier</option>
                <option value="vendor">Vendeur</option>
            </select>
        </div>

        <!-- Employees Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Employé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ville</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ventes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="employee in filteredEmployees" :key="employee.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ employee.employee_id || `EMP-${String(employee.id).padStart(4, '0')}` }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                    <span class="text-gray-900 font-medium">{{ getInitials(employee.nom, employee.prenom) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ employee.nom }} {{ employee.prenom }}</p>
                                    <p class="text-sm text-gray-500">{{ employee.email || '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ employee.phone || '-' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ employee.city || '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                {{ getRoleLabel(employee.role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ formatCurrency(employee.total_sales || 0) }}</p>
                            <p class="text-sm text-gray-500">{{ employee.sales_count || 0 }} ventes</p>
                        </td>
                        <td class="px-6 py-4">
                            <span :class="getStatusClass(employee.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ getStatusLabel(employee.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <router-link :to="`/employees/${employee.id}`" class="p-2 text-blue-400 hover:text-blue-600 rounded-lg hover:bg-blue-50" title="Voir détails">
                                    <EyeIcon class="w-5 h-5" />
                                </router-link>
                                <button @click="viewHistory(employee)" class="p-2 text-purple-400 hover:text-purple-600 rounded-lg hover:bg-purple-50" title="Historique des ventes">
                                    <ClockIcon class="w-5 h-5" />
                                </button>
                                <button @click="openForm(employee)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                    <PencilIcon class="w-5 h-5" />
                                </button>
                                <button @click="confirmDelete(employee)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredEmployees.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <UsersIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            Aucun employé trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Employee Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-2xl w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingEmployee ? 'Modifier l\'employé' : 'Nouvel employé' }}
                </h3>
                
                <form @submit.prevent="saveEmployee" class="space-y-4">
                    <!-- ID Employé -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID Employé</label>
                            <input 
                                :value="editingEmployee ? (editingEmployee.employee_id || `EMP-${String(editingEmployee.id).padStart(4, '0')}`) : 'Auto-généré'"
                                type="text" 
                                disabled 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                            <input v-model="form.nom" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom de famille">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                            <input v-model="form.prenom" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Prénom">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                            <input v-model="form.phone" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="+212 600 000 000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="form.email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="email@example.com">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                            <input v-model="form.city" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ville">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                            <input v-model="form.address" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Adresse">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rôle *</label>
                            <select v-model="form.role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="admin">Administrateur</option>
                                <option value="manager">Manager</option>
                                <option value="cashier">Caissier</option>
                                <option value="vendor">Vendeur</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                            <select v-model="form.status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                                <option value="suspended">Suspendu</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date d'entrée</label>
                            <input v-model="form.date_entree" type="datetime-local" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date de sortie</label>
                            <input v-model="form.date_sortie" type="datetime-local" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observations</label>
                        <textarea v-model="form.observations" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Notes et observations sur l'employé..."></textarea>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="button" @click="showForm = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                        <button type="submit" :disabled="saving" class="flex-1 px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- History Modal -->
        <div v-if="showHistoryModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showHistoryModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-3xl w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Historique des Ventes - {{ selectedEmployee?.nom }} {{ selectedEmployee?.prenom }}
                    </h3>
                    <button @click="showHistoryModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Employee Summary -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Total Ventes</p>
                        <p class="text-xl font-bold text-gray-900">{{ selectedEmployee?.sales_count || 0 }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Montant Total</p>
                        <p class="text-xl font-bold text-green-600">{{ formatCurrency(selectedEmployee?.total_sales || 0) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Moyenne/Vente</p>
                        <p class="text-xl font-bold text-primary-600">{{ formatCurrency(selectedEmployee?.sales_count > 0 ? selectedEmployee?.total_sales / selectedEmployee?.sales_count : 0) }}</p>
                    </div>
                </div>

                <!-- Sales History Table -->
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">N° Transaction</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="sale in employeeHistory" :key="sale.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(sale.date) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ sale.transaction_id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ sale.customer_name || 'Client comptoir' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ sale.items_count }} article(s)</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatCurrency(sale.total) }}</td>
                        </tr>
                        <tr v-if="employeeHistory.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                Aucune vente enregistrée
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer l'employé</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer "{{ employeeToDelete?.nom }} {{ employeeToDelete?.prenom }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteEmployee" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { employeesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { 
    PlusIcon, 
    PencilIcon, 
    TrashIcon, 
    UsersIcon, 
    ClockIcon,
    XMarkIcon,
    EyeIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const employees = ref([])
const search = ref('')
const filterStatus = ref('')
const filterRole = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const showHistoryModal = ref(false)
const editingEmployee = ref(null)
const employeeToDelete = ref(null)
const selectedEmployee = ref(null)
const employeeHistory = ref([])
const saving = ref(false)

const form = reactive({ 
    nom: '', 
    prenom: '',
    email: '', 
    phone: '', 
    city: '',
    address: '',
    role: 'cashier', 
    status: 'active',
    date_entree: '',
    date_sortie: '',
    observations: ''
})

const filteredEmployees = computed(() => {
    let result = employees.value
    
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(e => 
            e.nom?.toLowerCase().includes(query) ||
            e.prenom?.toLowerCase().includes(query) ||
            e.email?.toLowerCase().includes(query) ||
            e.phone?.includes(query) ||
            e.employee_id?.toLowerCase().includes(query)
        )
    }
    
    if (filterStatus.value) {
        result = result.filter(e => e.status === filterStatus.value)
    }
    
    if (filterRole.value) {
        result = result.filter(e => e.role === filterRole.value)
    }
    
    return result
})

const activeCount = computed(() => employees.value.filter(e => e.status === 'active').length)
const totalSales = computed(() => employees.value.reduce((sum, e) => sum + (e.total_sales || 0), 0))
const averageSales = computed(() => employees.value.length > 0 ? totalSales.value / employees.value.length : 0)

function getInitials(nom, prenom) {
    const n = nom ? nom[0] : ''
    const p = prenom ? prenom[0] : ''
    return (n + p).toUpperCase() || 'EM'
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function getRoleLabel(role) {
    const labels = { admin: 'Administrateur', manager: 'Manager', cashier: 'Caissier', vendor: 'Vendeur' }
    return labels[role] || role
}

function getStatusClass(status) {
    const classes = { 
        active: 'bg-green-100 text-green-800', 
        inactive: 'bg-gray-100 text-gray-700', 
        suspended: 'bg-red-100 text-red-700' 
    }
    return classes[status] || 'bg-gray-100 text-gray-700'
}

function getStatusLabel(status) {
    const labels = { active: 'Actif', inactive: 'Inactif', suspended: 'Suspendu' }
    return labels[status] || status
}

function openForm(employee = null) {
    editingEmployee.value = employee
    if (employee) {
        form.nom = employee.nom || ''
        form.prenom = employee.prenom || ''
        form.email = employee.email || ''
        form.phone = employee.phone || ''
        form.city = employee.city || ''
        form.address = employee.address || ''
        form.role = employee.role || 'cashier'
        form.status = employee.status || 'active'
        form.date_entree = employee.date_entree || ''
        form.date_sortie = employee.date_sortie || ''
        form.observations = employee.observations || ''
    } else {
        form.nom = ''
        form.prenom = ''
        form.email = ''
        form.phone = ''
        form.city = ''
        form.address = ''
        form.role = 'cashier'
        form.status = 'active'
        form.date_entree = ''
        form.date_sortie = ''
        form.observations = ''
    }
    showForm.value = true
}

function viewHistory(employee) {
    selectedEmployee.value = employee
    // Demo history data
    employeeHistory.value = [
        { id: 1, date: '2026-02-08', transaction_id: 'TRX-20260208-001', customer_name: 'Ahmed Benali', items_count: 5, total: 1250 },
        { id: 2, date: '2026-02-08', transaction_id: 'TRX-20260208-002', customer_name: null, items_count: 2, total: 450 },
        { id: 3, date: '2026-02-07', transaction_id: 'TRX-20260207-005', customer_name: 'Sara Mansouri', items_count: 8, total: 2100 },
        { id: 4, date: '2026-02-07', transaction_id: 'TRX-20260207-003', customer_name: null, items_count: 3, total: 680 },
        { id: 5, date: '2026-02-06', transaction_id: 'TRX-20260206-001', customer_name: 'Mohamed Tazi', items_count: 4, total: 950 },
    ]
    showHistoryModal.value = true
}

function confirmDelete(employee) {
    employeeToDelete.value = employee
    showDeleteModal.value = true
}

async function saveEmployee() {
    saving.value = true
    try {
        const employeeData = {
            name: `${form.nom} ${form.prenom}`,
            nom: form.nom,
            prenom: form.prenom,
            email: form.email,
            phone: form.phone,
            city: form.city,
            address: form.address,
            role: form.role,
            status: form.status,
            observations: form.observations
        }

        if (editingEmployee.value) {
            const response = await employeesApi.update(editingEmployee.value.id, employeeData)
            const index = employees.value.findIndex(e => e.id === editingEmployee.value.id)
            if (index > -1) employees.value[index] = { ...employees.value[index], ...employeeData }
        } else {
            const response = await employeesApi.create(employeeData)
            const newEmployee = response.data || {
                id: Date.now(),
                employee_id: `EMP-${String(Date.now()).slice(-4)}`,
                ...employeeData,
                total_sales: 0,
                sales_count: 0
            }
            employees.value.unshift(newEmployee)
        }
        showForm.value = false
    } catch (error) {
        alert('Erreur: ' + (error.response?.data?.message || error.message))
    } finally {
        saving.value = false
    }
}

async function deleteEmployee() {
    try {
        await employeesApi.delete(employeeToDelete.value.id)
        employees.value = employees.value.filter(e => e.id !== employeeToDelete.value.id)
        showDeleteModal.value = false
    } catch (error) {
        alert('Erreur lors de la suppression')
    }
}

onMounted(async () => {
    try {
        const response = await employeesApi.list()
        employees.value = Array.isArray(response.data) ? response.data : response.data.data || []
    } catch (error) {
        console.error('Error loading employees:', error)
        // Demo data
        employees.value = [
            { id: 1, employee_id: 'EMP-0001', nom: 'Benali', prenom: 'Ahmed', phone: '0612345678', email: 'ahmed.benali@pos.com', city: 'Casablanca', address: '123 Rue Mohammed V', role: 'admin', status: 'active', total_sales: 125000, sales_count: 85 },
            { id: 2, employee_id: 'EMP-0002', nom: 'Mansouri', prenom: 'Sara', phone: '0698765432', email: 'sara.mansouri@pos.com', city: 'Rabat', address: '45 Avenue Hassan II', role: 'manager', status: 'active', total_sales: 98000, sales_count: 62 },
            { id: 3, employee_id: 'EMP-0003', nom: 'Tazi', prenom: 'Mohamed', phone: '0655443322', email: 'mohamed.tazi@pos.com', city: 'Marrakech', address: '78 Boulevard Zerktouni', role: 'cashier', status: 'active', total_sales: 75000, sales_count: 120 },
            { id: 4, employee_id: 'EMP-0004', nom: 'El Amrani', prenom: 'Fatima', phone: '0677889900', email: 'fatima.elamrani@pos.com', city: 'Fès', address: '22 Rue Allal Ben Abdellah', role: 'vendor', status: 'inactive', total_sales: 45000, sales_count: 38 },
        ]
    }
})
</script>
