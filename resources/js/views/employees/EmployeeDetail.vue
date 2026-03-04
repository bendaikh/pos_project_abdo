<template>
    <div class="min-h-screen bg-gray-50 p-6">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm mb-6">
            <router-link to="/dashboard" class="text-primary-600 hover:text-primary-700 flex items-center">
                <HomeIcon class="w-4 h-4 mr-1" />
                Accueil
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-400" />
            <router-link to="/employees" class="text-primary-600 hover:text-primary-700">
                Employés
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-400" />
            <span class="text-gray-600">Fiche employé</span>
        </div>

        <!-- Main Card -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary-50 to-primary-100 border-b border-gray-200 px-6 py-6 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <UsersIcon class="w-8 h-8 text-primary-600" />
                    <h1 class="text-2xl font-bold text-gray-900">{{ employee.prenom }} {{ employee.nom }}</h1>
                </div>
                <div class="flex space-x-3">
                    <button @click="editEmployee" class="px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition flex items-center">
                        <PencilIcon class="w-5 h-5 mr-2" />
                        Éditer
                    </button>
                    <button @click="confirmDelete" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition flex items-center">
                        <TrashIcon class="w-5 h-5 mr-2" />
                        Supprimer
                    </button>
                </div>
            </div>

            <!-- Profile Section -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-start space-x-6">
                    <!-- Avatar -->
                    <div class="w-40 h-40 bg-gradient-to-br from-primary-50 to-primary-100 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden border-2 border-primary-200 shadow-sm">
                        <img v-if="employee.photo_url || employee.avatar" :key="employee.id" :src="getEmployeePhotoUrl(employee)" :alt="employee.prenom + ' ' + employee.nom" class="w-full h-full object-cover" />
                        <UsersIcon v-else class="w-20 h-20 text-primary-400" />
                    </div>
                    
                    <!-- Info -->
                    <div class="flex-1 space-y-4">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-900">{{ employee.prenom }} {{ employee.nom }}</h2>
                            <p class="text-gray-600 mt-1">ID : <span class="font-mono text-gray-900">#{{ employee.id }}</span></p>
                        </div>
                        
                        <div class="space-y-2">
                            <p class="text-gray-700"><span class="font-medium">Rôle :</span> <span v-if="employee.role" class="text-gray-900">{{ getRoleLabel(employee.role) }}</span><span v-else class="text-gray-400 italic">Non spécifié</span></p>
                            <p class="text-gray-700"><span class="font-medium">Poste :</span> <span v-if="employee.poste" class="text-gray-900">{{ employee.poste }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                        </div>

                        <div class="flex items-center space-x-3">
                            <span :class="getStatusBadgeClass(employee.status)" class="px-3 py-1 text-sm font-medium rounded-full">
                                {{ getStatusLabel(employee.status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <div class="flex">
                    <button
                        @click="activeTab = 'informations'"
                        :class="activeTab === 'informations' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-600 hover:text-gray-900'"
                        class="px-6 py-4 font-medium transition"
                    >
                        Informations
                    </button>
                    <button
                        @click="activeTab = 'coordonnees'"
                        :class="activeTab === 'coordonnees' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-600 hover:text-gray-900'"
                        class="px-6 py-4 font-medium transition"
                    >
                        Coordonnées
                    </button>
                    <button
                        @click="activeTab = 'ventes'"
                        :class="activeTab === 'ventes' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-600 hover:text-gray-900'"
                        class="px-6 py-4 font-medium transition"
                    >
                        Historique des Ventes
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Informations Tab -->
                <div v-if="activeTab === 'informations'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Email</p>
                            <p class="text-gray-900 font-semibold text-lg"><span v-if="employee.email">{{ employee.email }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Téléphone</p>
                            <p class="text-gray-900 font-semibold text-lg"><span v-if="employee.phone || employee.telephone">{{ employee.phone || employee.telephone }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Date d'entrée</p>
                            <p class="text-gray-900 font-semibold text-lg">{{ formatDate(employee.date_entree) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Total ventes</p>
                            <p class="text-primary-600 font-bold text-2xl">{{ formatCurrency(employee.total_sales || 0) }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Nombre de ventes</p>
                            <p class="text-green-600 font-bold text-2xl">{{ employee.sales_count || 0 }}</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Salaire de base</p>
                            <p class="text-blue-600 font-bold text-2xl">{{ formatCurrency(employee.salaire_base || 0) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Coordonnées Tab -->
                <div v-if="activeTab === 'coordonnees'" class="space-y-6">
                    <div class="space-y-4">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Adresse complète</p>
                            <p class="text-gray-900 mt-1">{{ employee.address || employee.adresse || 'Non renseignée' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Ville</p>
                            <p class="text-gray-900 mt-1">{{ employee.city || employee.ville || 'Non renseignée' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Pays</p>
                            <p class="text-gray-900 mt-1">{{ employee.pays || 'Non renseigné' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Observations</p>
                            <p class="text-gray-900 mt-1">{{ employee.observations || 'Aucune observation' }}</p>
                        </div>
                    </div>

                    <!-- Documents Section -->
                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <DocumentIcon class="w-5 h-5 mr-2 text-primary-600" />
                            Documents
                        </h3>
                        
                        <!-- CIN Document -->
                        <div class="mb-4">
                            <p class="text-gray-700 font-medium mb-3">Carte d'Identité Nationale (CIN)</p>
                            <div v-if="employee.documents?.cin" class="bg-white rounded border border-gray-300 p-4 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <DocumentIcon class="w-6 h-6 text-primary-600" />
                                    <a :href="getFileUrl(employee.documents.cin)" target="_blank" rel="noreferrer" class="text-primary-600 hover:text-primary-700 font-medium">
                                        {{ extractFileName(employee.documents.cin) }}
                                    </a>
                                </div>
                                <button @click="downloadFile(employee.documents.cin)" class="text-gray-600 hover:text-gray-900 transition">
                                    <ArrowDownTrayIcon class="w-5 h-5" />
                                </button>
                            </div>
                            <div v-else class="bg-white rounded border-2 border-dashed border-gray-300 p-6 text-center">
                                <DocumentIcon class="w-10 h-10 text-gray-400 mx-auto mb-2" />
                                <p class="text-gray-600">Aucun document CIN disponible</p>
                            </div>
                        </div>

                        <!-- Diplômes Document -->
                        <div class="mb-4">
                            <p class="text-gray-700 font-medium mb-3">Diplômes</p>
                            <div v-if="employee.documents?.diplomes" class="bg-white rounded border border-gray-300 p-4 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <DocumentIcon class="w-6 h-6 text-primary-600" />
                                    <a :href="getFileUrl(employee.documents.diplomes)" target="_blank" rel="noreferrer" class="text-primary-600 hover:text-primary-700 font-medium">
                                        {{ extractFileName(employee.documents.diplomes) }}
                                    </a>
                                </div>
                                <button @click="downloadFile(employee.documents.diplomes)" class="text-gray-600 hover:text-gray-900 transition">
                                    <ArrowDownTrayIcon class="w-5 h-5" />
                                </button>
                            </div>
                            <div v-else class="bg-white rounded border-2 border-dashed border-gray-300 p-6 text-center">
                                <DocumentIcon class="w-10 h-10 text-gray-400 mx-auto mb-2" />
                                <p class="text-gray-600">Aucun document diplôme disponible</p>
                            </div>
                        </div>

                        <!-- Contrats Document -->
                        <div>
                            <p class="text-gray-700 font-medium mb-3">Contrats</p>
                            <div v-if="employee.documents?.contrats" class="bg-white rounded border border-gray-300 p-4 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <DocumentIcon class="w-6 h-6 text-primary-600" />
                                    <a :href="getFileUrl(employee.documents.contrats)" target="_blank" rel="noreferrer" class="text-primary-600 hover:text-primary-700 font-medium">
                                        {{ extractFileName(employee.documents.contrats) }}
                                    </a>
                                </div>
                                <button @click="downloadFile(employee.documents.contrats)" class="text-gray-600 hover:text-gray-900 transition">
                                    <ArrowDownTrayIcon class="w-5 h-5" />
                                </button>
                            </div>
                            <div v-else class="bg-white rounded border-2 border-dashed border-gray-300 p-6 text-center">
                                <DocumentIcon class="w-10 h-10 text-gray-400 mx-auto mb-2" />
                                <p class="text-gray-600">Aucun document contrat disponible</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales History Tab -->
                <div v-if="activeTab === 'ventes'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Total des ventes</p>
                            <p class="text-gray-900 font-bold text-2xl">{{ employee.sales_count || 0 }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Montant total</p>
                            <p class="text-gray-900 font-bold text-2xl">{{ formatCurrency(employee.total_sales || 0) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Moyenne par vente</p>
                            <p class="text-gray-900 font-bold text-2xl">{{ formatCurrency(employee.sales_count > 0 ? employee.total_sales / employee.sales_count : 0) }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">N° Transaction</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Client</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Articles</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="sale in employeeSalesHistory" :key="sale.id" class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(sale.date) }}</td>
                                    <td class="px-4 py-3 text-sm font-mono text-primary-600">{{ sale.transaction_id || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ sale.customer_name || 'Client comptoir' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ sale.items_count || 0 }} article(s)</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ formatCurrency(sale.total || 0) }}</td>
                                </tr>
                                <tr v-if="employeeSalesHistory.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        Aucune vente enregistrée
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white border border-gray-200 rounded-xl p-6 max-w-md w-full mx-4 shadow-lg">
                <h3 class="text-xl font-bold text-red-600 mb-3">Supprimer l'employé</h3>
                <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir supprimer <span class="font-bold">{{ employee.prenom }} {{ employee.nom }}</span> ? Cette action est irréversible.</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 border border-gray-300 font-medium transition">
                        Annuler
                    </button>
                    <button @click="deleteEmployee" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useSettingsStore } from '../../stores/settings'
import {
    HomeIcon,
    ChevronRightIcon,
    UsersIcon,
    UserCircleIcon,
    PencilIcon,
    TrashIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    IdentificationIcon,
    ClockIcon,
    DocumentIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

const STORAGE_KEY = 'pos_employees'
const SALES_STORAGE_KEY = 'pos_sales'

const router = useRouter()
const route = useRoute()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const employee = ref({})
const employeeSalesHistory = ref([])
const showDeleteModal = ref(false)
const activeTab = ref('informations')

const readStoredEmployees = () => {
    try {
        const stored = localStorage.getItem(STORAGE_KEY)
        return stored ? JSON.parse(stored) : []
    } catch (error) {
        console.error('Unable to parse stored employees:', error)
        return []
    }
}

const calculateEmployeeSalesFromPOS = (employeeId) => {
    try {
        const storedSales = localStorage.getItem(SALES_STORAGE_KEY)
        if (!storedSales) return { total_sales: 0, sales_count: 0 }
        const sales = JSON.parse(storedSales)
        const employeeSales = sales.filter(s => s.employee_id === employeeId)
        return {
            total_sales: employeeSales.reduce((sum, s) => sum + (s.total || 0), 0),
            sales_count: employeeSales.length
        }
    } catch (error) {
        console.error('Unable to calculate sales:', error)
        return { total_sales: 0, sales_count: 0 }
    }
}

function formatDate(date) {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('fr-FR')
}

function extractFileName(path) {
    if (!path) return 'document'
    if (typeof path === 'object') {
        return path.name || 'document'
    }
    return path.split('/').pop().replace(/\.pdf|\.doc|\.docx/i, '')
}

function downloadFile(url) {
    const source = typeof url === 'string' ? url : url?.url
    const filename = typeof url === 'object' ? url?.name || extractFileName(source) : extractFileName(source)
    if (source) {
        const link = document.createElement('a')
        link.href = source
        link.download = filename
        link.click()
    }
}

function getFileUrl(file) {
    if (!file) return ''
    return typeof file === 'string' ? file : file?.url || ''
}

function getRoleLabel(role) {
    const labels = { 
        'admin': 'Administrateur', 
        'manager': 'Manager', 
        'cashier': 'Caissier',
        'vendor': 'Vendeur'
    }
    return labels[role] || role
}

function getStatusBadgeClass(status) {
    const classes = {
        'active': 'bg-green-100 text-green-800 border border-green-300',
        'inactive': 'bg-gray-100 text-gray-800 border border-gray-300',
        'suspended': 'bg-red-100 text-red-800 border border-red-300',
        'Actif': 'bg-green-100 text-green-800 border border-green-300',
        'Inactif': 'bg-gray-100 text-gray-800 border border-gray-300',
        'Suspendu': 'bg-red-100 text-red-800 border border-red-300'
    }
    return classes[status] || 'bg-gray-100 text-gray-800 border border-gray-300'
}

function getStatusLabel(status) {
    const labels = { 
        'active': 'Actif', 
        'inactive': 'Inactif', 
        'suspended': 'Suspendu' 
    }
    return labels[status] || status
}

function isDataUrl(url) {
    return typeof url === 'string' && (url.startsWith('data:') || url.startsWith('blob:'))
}

function getEmployeePhotoUrl(employee) {
    const url = employee?.photo_url || employee?.avatar
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

function editEmployee() {
    router.push(`/employees/${route.params.id}/edit`)
}

function confirmDelete() {
    showDeleteModal.value = true
}

function deleteEmployee() {
    try {
        const storedEmployees = readStoredEmployees()
        const filteredEmployees = storedEmployees.filter(e => String(e.id) !== String(route.params.id))
        localStorage.setItem(STORAGE_KEY, JSON.stringify(filteredEmployees))
        router.push('/employees')
    } catch (error) {
        console.error('Error deleting employee:', error)
    }
}

onMounted(() => {
    loadEmployeeData()
})

watch(() => route.params.id, () => {
    loadEmployeeData()
})

function loadEmployeeData() {
    try {
        const employees = readStoredEmployees()
        const foundEmployee = employees.find(e => String(e.id) === String(route.params.id))
        
        if (foundEmployee) {
            const salesData = calculateEmployeeSalesFromPOS(foundEmployee.id)
            employee.value = {
                ...foundEmployee,
                ...salesData
            }
            
            // Load sales history from localStorage
            try {
                const storedSales = localStorage.getItem(SALES_STORAGE_KEY)
                if (storedSales) {
                    const allSales = JSON.parse(storedSales)
                    employeeSalesHistory.value = allSales
                        .filter(s => s.employee_id === foundEmployee.id)
                        .sort((a, b) => new Date(b.date) - new Date(a.date))
                } else {
                    employeeSalesHistory.value = []
                }
            } catch (error) {
                console.error('Error loading sales history:', error)
                employeeSalesHistory.value = []
            }
        } else {
            // No employee found - show empty state or redirect
            console.warn('Employee not found with ID:', route.params.id)
            employee.value = {
                id: route.params.id,
                nom: 'Non trouvé',
                prenom: '',
                status: 'inactive'
            }
            employeeSalesHistory.value = []
        }
    } catch (error) {
        console.error('Error loading employee data:', error)
    }
}
</script>
