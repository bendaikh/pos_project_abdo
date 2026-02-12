<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Clients</h1>
                <p class="text-gray-500">Gérez vos clients et leur historique d'achats</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouveau Client
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Clients</p>
                <p class="text-2xl font-bold text-gray-900">{{ customers.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Clients Fidèles</p>
                <p class="text-2xl font-bold text-primary-600">{{ loyalCustomersCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Achats</p>
                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(totalSpent) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Moyenne/Client</p>
                <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(averageSpent) }}</p>
            </div>
        </div>

        <!-- Search -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
            <input 
                v-model="search"
                type="text"
                placeholder="Rechercher par ID, nom, ICE, email ou téléphone..."
                class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <select v-model="filterLoyalty" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les clients</option>
                <option value="loyal">Clients fidèles</option>
                <option value="new">Nouveaux clients</option>
            </select>
        </div>

        <!-- Customers Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ICE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fidélité</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Achats</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="customer in filteredCustomers" :key="customer.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ customer.client_id || `CLI-${String(customer.id).padStart(4, '0')}` }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                    <span class="text-gray-900 font-medium">{{ getInitials(customer.name) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ customer.name }}</p>
                                    <p class="text-sm text-gray-500">{{ customer.city || 'Pas de ville' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ customer.ice || '-' }}</td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ customer.phone || '-' }}</p>
                            <p class="text-sm text-gray-500">{{ customer.email || '-' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <span v-if="customer.loyalty_discount > 0" class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    -{{ customer.loyalty_discount }}%
                                </span>
                                <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                                    Standard
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ formatCurrency(customer.total_spent || 0) }}</p>
                            <p class="text-sm text-gray-500">{{ customer.completed_sales_count || 0 }} achats</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <router-link :to="`/customers/${customer.client_id || customer.id}`" class="p-2 text-blue-400 hover:text-blue-600 rounded-lg hover:bg-blue-50" title="Voir détails">
                                    <EyeIcon class="w-5 h-5" />
                                </router-link>
                                <button @click="viewHistory(customer)" class="p-2 text-purple-400 hover:text-purple-600 rounded-lg hover:bg-purple-50" title="Historique">
                                    <ClockIcon class="w-5 h-5" />
                                </button>
                                <button @click="openForm(customer)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                    <PencilIcon class="w-5 h-5" />
                                </button>
                                <button @click="confirmDelete(customer)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredCustomers.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <UserGroupIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            Aucun client trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Customer Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-2xl w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingCustomer ? 'Modifier le client' : 'Nouveau client' }}
                </h3>
                
                <form @submit.prevent="saveCustomer" class="space-y-4">
                    <!-- ID Client (readonly for existing) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID Client</label>
                            <input 
                                :value="editingCustomer ? (editingCustomer.client_id || `CLI-${String(editingCustomer.id).padStart(4, '0')}`) : 'Auto-généré'"
                                type="text" 
                                disabled 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                            <input v-model="form.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom complet">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ICE (Identifiant Commun de l'Entreprise)</label>
                            <input v-model="form.ice" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="000000000000000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                            <input v-model="form.phone" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="+212 600 000 000">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                            <input v-model="form.city" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ville">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                            <input v-model="form.country" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Pays">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                        <textarea v-model="form.address" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Adresse complète"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="form.email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="email@example.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observations</label>
                        <textarea v-model="form.observations" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Notes et observations sur le client..."></textarea>
                    </div>

                    <!-- Fidélité / Remise -->
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                            <GiftIcon class="w-5 h-5 mr-2 text-primary-500" />
                            Fidélité / Remise
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Remise fidélité (%)</label>
                                <input v-model.number="form.loyalty_discount" type="number" min="0" max="100" step="0.5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Points de fidélité</label>
                                <input v-model.number="form.loyalty_points" type="number" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="0">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="form.is_vip" class="mr-2 text-primary-500 rounded">
                                <span class="text-sm text-gray-700">Client VIP</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="button" @click="showForm = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" :disabled="saving" class="flex-1 px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50">
                            Enregistrer
                        </button>
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
                        Historique des Achats - {{ selectedCustomer?.name }}
                    </h3>
                    <button @click="showHistoryModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Customer Summary -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Total Achats</p>
                        <p class="text-xl font-bold text-gray-900">{{ selectedCustomer?.completed_sales_count || 0 }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Montant Total</p>
                        <p class="text-xl font-bold text-green-600">{{ formatCurrency(selectedCustomer?.total_spent || 0) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Points Fidélité</p>
                        <p class="text-xl font-bold text-primary-600">{{ selectedCustomer?.loyalty_points || 0 }}</p>
                    </div>
                </div>

                <!-- Purchase History Table -->
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">N° Transaction</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="purchase in customerHistory" :key="purchase.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(purchase.date) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ purchase.transaction_id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ purchase.items_count }} article(s)</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatCurrency(purchase.total) }}</td>
                        </tr>
                        <tr v-if="customerHistory.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                Aucun achat enregistré
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
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer le client</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer "{{ customerToDelete?.name }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteCustomer" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { customersApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { 
    PlusIcon, 
    PencilIcon, 
    TrashIcon, 
    ClockIcon, 
    UserGroupIcon,
    GiftIcon,
    XMarkIcon,
    EyeIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const customers = ref([])
const search = ref('')
const filterLoyalty = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const showHistoryModal = ref(false)
const editingCustomer = ref(null)
const customerToDelete = ref(null)
const selectedCustomer = ref(null)
const customerHistory = ref([])
const saving = ref(false)

const form = reactive({ 
    name: '', 
    email: '', 
    phone: '', 
    address: '', 
    city: '', 
    country: '', 
    ice: '',
    observations: '',
    loyalty_discount: 0,
    loyalty_points: 0,
    is_vip: false
})

const filteredCustomers = computed(() => {
    let result = customers.value
    
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(c => 
            c.name.toLowerCase().includes(query) ||
            c.email?.toLowerCase().includes(query) ||
            c.phone?.includes(query) ||
            c.ice?.includes(query) ||
            c.client_id?.toLowerCase().includes(query)
        )
    }
    
    if (filterLoyalty.value === 'loyal') {
        result = result.filter(c => c.loyalty_discount > 0 || c.is_vip)
    } else if (filterLoyalty.value === 'new') {
        result = result.filter(c => (c.completed_sales_count || 0) <= 1)
    }
    
    return result
})

const loyalCustomersCount = computed(() => customers.value.filter(c => c.loyalty_discount > 0 || c.is_vip).length)
const totalSpent = computed(() => customers.value.reduce((sum, c) => sum + (c.total_spent || 0), 0))
const averageSpent = computed(() => customers.value.length > 0 ? totalSpent.value / customers.value.length : 0)

function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function openForm(customer = null) {
    editingCustomer.value = customer
    if (customer) {
        form.name = customer.name || ''
        form.email = customer.email || ''
        form.phone = customer.phone || ''
        form.address = customer.address || ''
        form.city = customer.city || ''
        form.country = customer.country || ''
        form.ice = customer.ice || ''
        form.observations = customer.observations || ''
        form.loyalty_discount = customer.loyalty_discount || 0
        form.loyalty_points = customer.loyalty_points || 0
        form.is_vip = customer.is_vip || false
    } else {
        Object.keys(form).forEach(key => {
            if (typeof form[key] === 'boolean') form[key] = false
            else if (typeof form[key] === 'number') form[key] = 0
            else form[key] = ''
        })
    }
    showForm.value = true
}

function viewHistory(customer) {
    selectedCustomer.value = customer
    // Demo history data
    customerHistory.value = [
        { id: 1, date: '2026-02-08', transaction_id: 'TRX-20260208-001', items_count: 5, total: 1250 },
        { id: 2, date: '2026-02-05', transaction_id: 'TRX-20260205-003', items_count: 3, total: 850 },
        { id: 3, date: '2026-01-28', transaction_id: 'TRX-20260128-007', items_count: 8, total: 2100 },
        { id: 4, date: '2026-01-15', transaction_id: 'TRX-20260115-002', items_count: 2, total: 450 },
    ]
    showHistoryModal.value = true
}

function confirmDelete(customer) {
    customerToDelete.value = customer
    showDeleteModal.value = true
}

async function saveCustomer() {
    saving.value = true
    try {
        if (editingCustomer.value) {
            const response = await customersApi.update(editingCustomer.value.id, form)
            const index = customers.value.findIndex(c => c.id === editingCustomer.value.id)
            if (index > -1) customers.value[index] = response.data
        } else {
            const response = await customersApi.create(form)
            customers.value.unshift(response.data)
        }
        showForm.value = false
    } catch (error) {
        alert('Erreur: ' + (error.response?.data?.message || error.message))
    } finally {
        saving.value = false
    }
}

async function deleteCustomer() {
    try {
        await customersApi.delete(customerToDelete.value.id)
        customers.value = customers.value.filter(c => c.id !== customerToDelete.value.id)
        showDeleteModal.value = false
    } catch (error) {
        alert('Erreur lors de la suppression')
    }
}

onMounted(async () => {
    try {
        const response = await customersApi.list({ with_stats: true })
        customers.value = Array.isArray(response.data) ? response.data : response.data.data || []
    } catch (error) {
        console.error('Error loading customers:', error)
        // Demo data
        customers.value = [
            { id: 1, client_id: 'CLI-0001', name: 'Ahmed Benali', ice: '001234567890123', phone: '0612345678', email: 'ahmed@example.com', city: 'Casablanca', country: 'Maroc', loyalty_discount: 10, loyalty_points: 250, is_vip: true, completed_sales_count: 15, total_spent: 45000 },
            { id: 2, client_id: 'CLI-0002', name: 'Sara Mansouri', ice: '', phone: '0698765432', email: 'sara@example.com', city: 'Rabat', country: 'Maroc', loyalty_discount: 5, loyalty_points: 120, is_vip: false, completed_sales_count: 8, total_spent: 22000 },
            { id: 3, client_id: 'CLI-0003', name: 'Mohamed Tazi', ice: '009876543210123', phone: '0655443322', email: 'mohamed@example.com', city: 'Marrakech', country: 'Maroc', loyalty_discount: 0, loyalty_points: 50, is_vip: false, completed_sales_count: 3, total_spent: 8500 },
        ]
    }
})
</script>
