<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Fournisseurs</h1>
                <p class="text-gray-500">Gérez vos fournisseurs et leur historique de ventes</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouveau Fournisseur
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Fournisseurs</p>
                <p class="text-2xl font-bold text-gray-900">{{ fournisseurs.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Fournisseurs Actifs</p>
                <p class="text-2xl font-bold text-green-600">{{ activeFournisseurs }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Commandes</p>
                <p class="text-2xl font-bold text-blue-600">{{ totalOrders }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Achats</p>
                <p class="text-2xl font-bold text-primary-600">{{ formatCurrency(totalPurchases) }}</p>
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
            <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les fournisseurs</option>
                <option value="active">Actifs</option>
                <option value="inactive">Inactifs</option>
            </select>
        </div>

        <!-- Fournisseurs Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Fournisseur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ICE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ville</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Ventes</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="fournisseur in filteredFournisseurs" :key="fournisseur.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ fournisseur.fournisseur_id || `FRN-${String(fournisseur.id).padStart(4, '0')}` }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-700 font-medium">{{ getInitials(fournisseur.name) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ fournisseur.name }}</p>
                                    <p class="text-sm text-gray-500">{{ fournisseur.email || '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ fournisseur.ice || '-' }}</td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ fournisseur.phone || '-' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ fournisseur.city || '-' }}</td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ formatCurrency(fournisseur.total_purchases || 0) }}</p>
                            <p class="text-sm text-gray-500">{{ fournisseur.orders_count || 0 }} commandes</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="viewHistory(fournisseur)" class="p-2 text-blue-400 hover:text-blue-600 rounded-lg hover:bg-blue-50" title="Historique">
                                    <ClockIcon class="w-5 h-5" />
                                </button>
                                <button @click="openForm(fournisseur)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                    <PencilIcon class="w-5 h-5" />
                                </button>
                                <button @click="confirmDelete(fournisseur)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredFournisseurs.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <BuildingOfficeIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            Aucun fournisseur trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Fournisseur Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-2xl w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingFournisseur ? 'Modifier le fournisseur' : 'Nouveau fournisseur' }}
                </h3>
                
                <form @submit.prevent="saveFournisseur" class="space-y-4">
                    <!-- ID Fournisseur (readonly for existing) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID Fournisseur</label>
                            <input 
                                :value="editingFournisseur ? (editingFournisseur.fournisseur_id || `FRN-${String(editingFournisseur.id).padStart(4, '0')}`) : 'Auto-généré'"
                                type="text" 
                                disabled 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                            <input v-model="form.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom du fournisseur">
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
                        <input v-model="form.email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="email@fournisseur.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observations</label>
                        <textarea v-model="form.observations" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Notes et observations sur le fournisseur..."></textarea>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center space-x-3">
                        <input v-model="form.is_active" type="checkbox" id="is_active" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <label for="is_active" class="text-sm font-medium text-gray-700">Fournisseur actif</label>
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
                        Historique des Ventes - {{ selectedFournisseur?.name }}
                    </h3>
                    <button @click="showHistoryModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Fournisseur Summary -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Total Commandes</p>
                        <p class="text-xl font-bold text-gray-900">{{ selectedFournisseur?.orders_count || 0 }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Montant Total</p>
                        <p class="text-xl font-bold text-green-600">{{ formatCurrency(selectedFournisseur?.total_purchases || 0) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Dernière Commande</p>
                        <p class="text-xl font-bold text-primary-600">{{ formatDate(selectedFournisseur?.last_order_date) }}</p>
                    </div>
                </div>

                <!-- Purchase History Table -->
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">N° Commande</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="order in fournisseurHistory" :key="order.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(order.date) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ order.order_id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ order.items_count }} article(s)</td>
                            <td class="px-4 py-3">
                                <span :class="getStatusClass(order.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                    {{ getStatusLabel(order.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatCurrency(order.total) }}</td>
                        </tr>
                        <tr v-if="fournisseurHistory.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                Aucune commande enregistrée
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
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer le fournisseur</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer "{{ fournisseurToDelete?.name }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteFournisseur" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useSettingsStore } from '../../stores/settings'
import { 
    PlusIcon, 
    PencilIcon, 
    TrashIcon, 
    ClockIcon, 
    BuildingOfficeIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const fournisseurs = ref([])
const search = ref('')
const filterStatus = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const showHistoryModal = ref(false)
const editingFournisseur = ref(null)
const fournisseurToDelete = ref(null)
const selectedFournisseur = ref(null)
const fournisseurHistory = ref([])
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
    is_active: true
})

const filteredFournisseurs = computed(() => {
    let result = fournisseurs.value
    
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(f => 
            f.name.toLowerCase().includes(query) ||
            f.email?.toLowerCase().includes(query) ||
            f.phone?.includes(query) ||
            f.ice?.includes(query) ||
            f.fournisseur_id?.toLowerCase().includes(query)
        )
    }
    
    if (filterStatus.value === 'active') {
        result = result.filter(f => f.is_active !== false)
    } else if (filterStatus.value === 'inactive') {
        result = result.filter(f => f.is_active === false)
    }
    
    return result
})

const activeFournisseurs = computed(() => fournisseurs.value.filter(f => f.is_active !== false).length)
const totalOrders = computed(() => fournisseurs.value.reduce((sum, f) => sum + (f.orders_count || 0), 0))
const totalPurchases = computed(() => fournisseurs.value.reduce((sum, f) => sum + (f.total_purchases || 0), 0))

function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function getStatusClass(status) {
    const classes = {
        'reçue': 'bg-green-100 text-green-800',
        'en_cours': 'bg-yellow-100 text-yellow-800',
        'envoyée': 'bg-blue-100 text-blue-800',
        'annulée': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
    const labels = {
        'reçue': 'Reçue',
        'en_cours': 'En cours',
        'envoyée': 'Envoyée',
        'annulée': 'Annulée'
    }
    return labels[status] || status
}

function openForm(fournisseur = null) {
    editingFournisseur.value = fournisseur
    if (fournisseur) {
        form.name = fournisseur.name || ''
        form.email = fournisseur.email || ''
        form.phone = fournisseur.phone || ''
        form.address = fournisseur.address || ''
        form.city = fournisseur.city || ''
        form.country = fournisseur.country || ''
        form.ice = fournisseur.ice || ''
        form.observations = fournisseur.observations || ''
        form.is_active = fournisseur.is_active !== false
    } else {
        Object.keys(form).forEach(key => {
            if (key === 'is_active') form[key] = true
            else form[key] = ''
        })
    }
    showForm.value = true
}

function viewHistory(fournisseur) {
    selectedFournisseur.value = fournisseur
    // Demo history data
    fournisseurHistory.value = [
        { id: 1, date: '2026-02-08', order_id: 'BC-2026-015', items_count: 25, status: 'reçue', total: 12500 },
        { id: 2, date: '2026-02-01', order_id: 'BC-2026-012', items_count: 15, status: 'reçue', total: 8500 },
        { id: 3, date: '2026-01-20', order_id: 'BC-2026-008', items_count: 40, status: 'reçue', total: 22000 },
        { id: 4, date: '2026-01-10', order_id: 'BC-2026-003', items_count: 10, status: 'reçue', total: 5500 },
    ]
    showHistoryModal.value = true
}

function confirmDelete(fournisseur) {
    fournisseurToDelete.value = fournisseur
    showDeleteModal.value = true
}

async function saveFournisseur() {
    saving.value = true
    try {
        const newFournisseur = {
            id: editingFournisseur.value?.id || Date.now(),
            fournisseur_id: editingFournisseur.value?.fournisseur_id || `FRN-${String(Date.now()).slice(-4)}`,
            name: form.name,
            email: form.email,
            phone: form.phone,
            address: form.address,
            city: form.city,
            country: form.country,
            ice: form.ice,
            observations: form.observations,
            is_active: form.is_active,
            orders_count: editingFournisseur.value?.orders_count || 0,
            total_purchases: editingFournisseur.value?.total_purchases || 0,
            last_order_date: editingFournisseur.value?.last_order_date || null
        }

        if (editingFournisseur.value) {
            const index = fournisseurs.value.findIndex(f => f.id === editingFournisseur.value.id)
            if (index > -1) fournisseurs.value[index] = newFournisseur
        } else {
            fournisseurs.value.unshift(newFournisseur)
        }
        showForm.value = false
    } catch (error) {
        alert('Erreur: ' + error.message)
    } finally {
        saving.value = false
    }
}

function deleteFournisseur() {
    fournisseurs.value = fournisseurs.value.filter(f => f.id !== fournisseurToDelete.value.id)
    showDeleteModal.value = false
}

onMounted(() => {
    // Demo data
    fournisseurs.value = [
        { id: 1, fournisseur_id: 'FRN-0001', name: 'Fournisseur ABC', ice: '001234567890123', phone: '0522123456', email: 'contact@abc.com', city: 'Casablanca', country: 'Maroc', is_active: true, orders_count: 25, total_purchases: 125000, last_order_date: '2026-02-08' },
        { id: 2, fournisseur_id: 'FRN-0002', name: 'Distributeur XYZ', ice: '009876543210123', phone: '0537654321', email: 'info@xyz.com', city: 'Rabat', country: 'Maroc', is_active: true, orders_count: 18, total_purchases: 85000, last_order_date: '2026-02-05' },
        { id: 3, fournisseur_id: 'FRN-0003', name: 'Import Express', ice: '005555666677778', phone: '0524111222', email: 'commande@importexpress.com', city: 'Marrakech', country: 'Maroc', is_active: true, orders_count: 12, total_purchases: 62000, last_order_date: '2026-01-28' },
        { id: 4, fournisseur_id: 'FRN-0004', name: 'Grossiste du Sud', ice: '', phone: '0528333444', email: 'contact@grossistesud.com', city: 'Agadir', country: 'Maroc', is_active: false, orders_count: 5, total_purchases: 28000, last_order_date: '2025-12-15' },
    ]
})
</script>
