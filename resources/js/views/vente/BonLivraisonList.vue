<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Bons de Livraison</h1>
                <p class="text-gray-500">Gérez vos bons de livraison</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouveau Bon
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Bons</p>
                <p class="text-2xl font-bold text-gray-900">{{ bonsList.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">En cours</p>
                <p class="text-2xl font-bold text-blue-600">{{ enCoursCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Livrés</p>
                <p class="text-2xl font-bold text-green-600">{{ livresCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Ce mois</p>
                <p class="text-2xl font-bold text-primary-600">{{ thisMonthCount }}</p>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
            <input 
                v-model="search"
                type="text"
                placeholder="Rechercher par numéro ou client..."
                class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <select v-model="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="en_preparation">En préparation</option>
                <option value="expédié">Expédié</option>
                <option value="en_cours">En cours de livraison</option>
                <option value="livré">Livré</option>
                <option value="retourné">Retourné</option>
            </select>
        </div>

        <!-- Bons Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Bon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adresse</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="bon in filteredBons" :key="bon.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ bon.numero }}</td>
                        <td class="px-6 py-4">
                            <p class="text-gray-900">{{ bon.client_name }}</p>
                            <p class="text-sm text-gray-500">{{ bon.client_phone }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(bon.date) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ bon.adresse_livraison }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ bon.nb_articles }} article(s)</td>
                        <td class="px-6 py-4">
                            <span :class="getStatusClass(bon.statut)" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ getStatusLabel(bon.statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="viewBon(bon)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Voir">
                                    <EyeIcon class="w-5 h-5" />
                                </button>
                                <button @click="openForm(bon)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                    <PencilIcon class="w-5 h-5" />
                                </button>
                                <button @click="printBon(bon)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Imprimer">
                                    <PrinterIcon class="w-5 h-5" />
                                </button>
                                <button @click="confirmDelete(bon)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredBons.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <TruckIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            Aucun bon de livraison trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Bon Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-4xl w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingBon ? 'Modifier le bon de livraison' : 'Nouveau bon de livraison' }}
                </h3>
                
                <form @submit.prevent="saveBon" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                            <select v-model="form.customer_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Sélectionner un client</option>
                                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                    {{ customer.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date de livraison *</label>
                            <input v-model="form.date_livraison" type="date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse de livraison *</label>
                        <textarea v-model="form.adresse_livraison" rows="2" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>

                    <!-- Articles -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-900">Articles à livrer</h4>
                            <button type="button" @click="addLine" class="text-sm text-primary-600 hover:text-primary-700">+ Ajouter un article</button>
                        </div>
                        <div class="space-y-2">
                            <div v-for="(line, index) in form.lines" :key="index" class="flex items-center gap-2">
                                <input v-model="line.article" type="text" placeholder="Article" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input v-model.number="line.quantity" type="number" min="1" placeholder="Qté" class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <button type="button" @click="removeLine(index)" class="p-2 text-red-400 hover:text-red-600">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea v-model="form.notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Instructions de livraison..."></textarea>
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

        <!-- Delete Confirmation -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer le bon</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer le bon "{{ bonToDelete?.numero }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteBon" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useSettingsStore } from '../../stores/settings'
import { customersApi } from '../../api'
import { 
    PlusIcon, 
    PencilIcon, 
    TrashIcon, 
    EyeIcon, 
    PrinterIcon,
    TruckIcon 
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()

const bonsList = ref([])
const customers = ref([])
const search = ref('')
const statusFilter = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const editingBon = ref(null)
const bonToDelete = ref(null)
const saving = ref(false)

const form = reactive({
    customer_id: '',
    date_livraison: '',
    adresse_livraison: '',
    notes: '',
    lines: [{ article: '', quantity: 1 }]
})

const filteredBons = computed(() => {
    let result = bonsList.value
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(b => 
            b.numero.toLowerCase().includes(query) ||
            b.client_name?.toLowerCase().includes(query)
        )
    }
    if (statusFilter.value) {
        result = result.filter(b => b.statut === statusFilter.value)
    }
    return result
})

const enCoursCount = computed(() => bonsList.value.filter(b => ['en_preparation', 'expédié', 'en_cours'].includes(b.statut)).length)
const livresCount = computed(() => bonsList.value.filter(b => b.statut === 'livré').length)
const thisMonthCount = computed(() => {
    const now = new Date()
    return bonsList.value.filter(b => {
        const date = new Date(b.date)
        return date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear()
    }).length
})

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function getStatusClass(status) {
    const classes = {
        'en_preparation': 'bg-gray-100 text-gray-800',
        'expédié': 'bg-blue-100 text-blue-800',
        'en_cours': 'bg-yellow-100 text-yellow-800',
        'livré': 'bg-green-100 text-green-800',
        'retourné': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
    const labels = {
        'en_preparation': 'En préparation',
        'expédié': 'Expédié',
        'en_cours': 'En cours',
        'livré': 'Livré',
        'retourné': 'Retourné'
    }
    return labels[status] || status
}

function addLine() {
    form.lines.push({ article: '', quantity: 1 })
}

function removeLine(index) {
    if (form.lines.length > 1) {
        form.lines.splice(index, 1)
    }
}

function openForm(bon = null) {
    editingBon.value = bon
    if (bon) {
        form.customer_id = bon.customer_id
        form.date_livraison = bon.date_livraison
        form.adresse_livraison = bon.adresse_livraison
        form.notes = bon.notes
        form.lines = bon.lines?.length ? [...bon.lines] : [{ article: '', quantity: 1 }]
    } else {
        form.customer_id = ''
        form.date_livraison = ''
        form.adresse_livraison = ''
        form.notes = ''
        form.lines = [{ article: '', quantity: 1 }]
    }
    showForm.value = true
}

function viewBon(bon) {
    alert('Affichage du bon: ' + bon.numero)
}

function printBon(bon) {
    alert('Impression du bon: ' + bon.numero)
}

function confirmDelete(bon) {
    bonToDelete.value = bon
    showDeleteModal.value = true
}

async function saveBon() {
    saving.value = true
    try {
        const customer = customers.value.find(c => c.id === form.customer_id)
        const newBon = {
            id: editingBon.value?.id || Date.now(),
            numero: editingBon.value?.numero || `BL-${Date.now()}`,
            customer_id: form.customer_id,
            client_name: customer?.name || 'Client inconnu',
            client_phone: customer?.phone || '',
            date: editingBon.value?.date || new Date().toISOString(),
            date_livraison: form.date_livraison,
            adresse_livraison: form.adresse_livraison,
            nb_articles: form.lines.reduce((sum, l) => sum + l.quantity, 0),
            statut: editingBon.value?.statut || 'en_preparation',
            notes: form.notes,
            lines: [...form.lines]
        }

        if (editingBon.value) {
            const index = bonsList.value.findIndex(b => b.id === editingBon.value.id)
            if (index > -1) bonsList.value[index] = newBon
        } else {
            bonsList.value.unshift(newBon)
        }
        showForm.value = false
    } catch (error) {
        alert('Erreur: ' + error.message)
    } finally {
        saving.value = false
    }
}

function deleteBon() {
    bonsList.value = bonsList.value.filter(b => b.id !== bonToDelete.value.id)
    showDeleteModal.value = false
}

onMounted(async () => {
    try {
        const response = await customersApi.list()
        customers.value = Array.isArray(response.data) ? response.data : response.data.data || []
    } catch (e) {
        console.error('Error loading customers:', e)
    }

    // Demo data
    bonsList.value = [
        { id: 1, numero: 'BL-2026-001', customer_id: 1, client_name: 'Ahmed Benali', client_phone: '0612345678', date: '2026-02-01', date_livraison: '2026-02-03', adresse_livraison: '123 Rue Mohammed V, Casablanca', nb_articles: 5, statut: 'livré', lines: [] },
        { id: 2, numero: 'BL-2026-002', customer_id: 2, client_name: 'Sara Mansouri', client_phone: '0698765432', date: '2026-02-05', date_livraison: '2026-02-08', adresse_livraison: '45 Avenue Hassan II, Rabat', nb_articles: 3, statut: 'en_cours', lines: [] },
        { id: 3, numero: 'BL-2026-003', customer_id: 3, client_name: 'Mohamed Tazi', client_phone: '0655443322', date: '2026-02-08', date_livraison: '2026-02-10', adresse_livraison: '78 Boulevard Zerktouni, Marrakech', nb_articles: 8, statut: 'en_preparation', lines: [] },
    ]
})
</script>
