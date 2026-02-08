<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dépenses</h1>
                <p class="text-gray-500">Gérez vos dépenses et charges</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouvelle Dépense
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Dépenses</p>
                <p class="text-2xl font-bold text-red-600">{{ formatCurrency(totalDepenses) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Ce mois</p>
                <p class="text-2xl font-bold text-orange-600">{{ formatCurrency(depensesMonth) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">En attente</p>
                <p class="text-2xl font-bold text-yellow-600">{{ enAttenteCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Payées</p>
                <p class="text-2xl font-bold text-green-600">{{ payeesCount }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
            <input 
                v-model="search"
                type="text"
                placeholder="Rechercher..."
                class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <select v-model="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Toutes les catégories</option>
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
            </select>
            <select v-model="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="payee">Payée</option>
                <option value="en_attente">En attente</option>
                <option value="annulee">Annulée</option>
            </select>
        </div>

        <!-- Depenses Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="depense in filteredDepenses" :key="depense.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(depense.date) }}</td>
                        <td class="px-6 py-4">
                            <p class="text-gray-900">{{ depense.description }}</p>
                            <p v-if="depense.reference" class="text-sm text-gray-500">{{ depense.reference }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                {{ depense.categorie }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ depense.fournisseur || '-' }}</td>
                        <td class="px-6 py-4 text-right font-bold text-red-600">{{ formatCurrency(depense.montant) }}</td>
                        <td class="px-6 py-4">
                            <span :class="getStatusClass(depense.statut)" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ getStatusLabel(depense.statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button v-if="depense.statut === 'en_attente'" @click="markAsPaid(depense)" class="p-2 text-green-400 hover:text-green-600 rounded-lg hover:bg-green-50" title="Marquer payée">
                                    <CheckCircleIcon class="w-5 h-5" />
                                </button>
                                <button @click="openForm(depense)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                    <PencilIcon class="w-5 h-5" />
                                </button>
                                <button @click="confirmDelete(depense)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredDepenses.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <CreditCardIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            Aucune dépense trouvée
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Depense Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-lg w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingDepense ? 'Modifier la dépense' : 'Nouvelle dépense' }}
                </h3>
                
                <form @submit.prevent="saveDepense" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <input v-model="form.description" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Description de la dépense">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Montant *</label>
                            <input v-model.number="form.montant" type="number" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                            <input v-model="form.date" type="date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie *</label>
                        <select v-model="form.categorie" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">Sélectionner...</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur</label>
                        <input v-model="form.fournisseur" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom du fournisseur">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Référence</label>
                        <input v-model="form.reference" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="N° facture, reçu, etc.">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea v-model="form.notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
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
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer la dépense</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer cette dépense ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteDepense" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
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
    CreditCardIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const depensesList = ref([])
const search = ref('')
const categoryFilter = ref('')
const statusFilter = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const editingDepense = ref(null)
const depenseToDelete = ref(null)
const saving = ref(false)

const categories = [
    'Fournitures',
    'Loyer',
    'Électricité',
    'Eau',
    'Internet/Téléphone',
    'Salaires',
    'Transport',
    'Marketing',
    'Maintenance',
    'Autres'
]

const form = reactive({
    description: '',
    montant: 0,
    date: new Date().toISOString().split('T')[0],
    categorie: '',
    fournisseur: '',
    reference: '',
    notes: ''
})

const filteredDepenses = computed(() => {
    let result = depensesList.value
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(d => 
            d.description.toLowerCase().includes(query) ||
            d.fournisseur?.toLowerCase().includes(query)
        )
    }
    if (categoryFilter.value) {
        result = result.filter(d => d.categorie === categoryFilter.value)
    }
    if (statusFilter.value) {
        result = result.filter(d => d.statut === statusFilter.value)
    }
    return result
})

const totalDepenses = computed(() => depensesList.value.reduce((sum, d) => sum + d.montant, 0))

const depensesMonth = computed(() => {
    const now = new Date()
    return depensesList.value
        .filter(d => {
            const date = new Date(d.date)
            return date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear()
        })
        .reduce((sum, d) => sum + d.montant, 0)
})

const enAttenteCount = computed(() => depensesList.value.filter(d => d.statut === 'en_attente').length)
const payeesCount = computed(() => depensesList.value.filter(d => d.statut === 'payee').length)

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function getStatusClass(status) {
    const classes = {
        'payee': 'bg-green-100 text-green-800',
        'en_attente': 'bg-yellow-100 text-yellow-800',
        'annulee': 'bg-gray-100 text-gray-500'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
    const labels = {
        'payee': 'Payée',
        'en_attente': 'En attente',
        'annulee': 'Annulée'
    }
    return labels[status] || status
}

function openForm(depense = null) {
    editingDepense.value = depense
    if (depense) {
        form.description = depense.description
        form.montant = depense.montant
        form.date = depense.date
        form.categorie = depense.categorie
        form.fournisseur = depense.fournisseur || ''
        form.reference = depense.reference || ''
        form.notes = depense.notes || ''
    } else {
        form.description = ''
        form.montant = 0
        form.date = new Date().toISOString().split('T')[0]
        form.categorie = ''
        form.fournisseur = ''
        form.reference = ''
        form.notes = ''
    }
    showForm.value = true
}

function confirmDelete(depense) {
    depenseToDelete.value = depense
    showDeleteModal.value = true
}

function markAsPaid(depense) {
    const index = depensesList.value.findIndex(d => d.id === depense.id)
    if (index > -1) {
        depensesList.value[index].statut = 'payee'
    }
}

async function saveDepense() {
    saving.value = true
    try {
        const newDepense = {
            id: editingDepense.value?.id || Date.now(),
            description: form.description,
            montant: form.montant,
            date: form.date,
            categorie: form.categorie,
            fournisseur: form.fournisseur,
            reference: form.reference,
            notes: form.notes,
            statut: editingDepense.value?.statut || 'en_attente'
        }

        if (editingDepense.value) {
            const index = depensesList.value.findIndex(d => d.id === editingDepense.value.id)
            if (index > -1) depensesList.value[index] = newDepense
        } else {
            depensesList.value.unshift(newDepense)
        }
        showForm.value = false
    } catch (error) {
        alert('Erreur: ' + error.message)
    } finally {
        saving.value = false
    }
}

function deleteDepense() {
    depensesList.value = depensesList.value.filter(d => d.id !== depenseToDelete.value.id)
    showDeleteModal.value = false
}

onMounted(() => {
    // Demo data
    depensesList.value = [
        { id: 1, description: 'Loyer mensuel', montant: 15000, date: '2026-02-01', categorie: 'Loyer', fournisseur: 'Propriétaire', reference: 'LOY-202602', statut: 'payee' },
        { id: 2, description: 'Facture électricité', montant: 2500, date: '2026-02-05', categorie: 'Électricité', fournisseur: 'ONE', reference: 'ELEC-202602', statut: 'payee' },
        { id: 3, description: 'Fournitures de bureau', montant: 800, date: '2026-02-07', categorie: 'Fournitures', fournisseur: 'Office Pro', reference: 'FB-2026-045', statut: 'en_attente' },
        { id: 4, description: 'Internet et téléphone', montant: 1200, date: '2026-02-08', categorie: 'Internet/Téléphone', fournisseur: 'Maroc Telecom', reference: 'INT-202602', statut: 'en_attente' },
        { id: 5, description: 'Maintenance climatisation', montant: 3500, date: '2026-02-06', categorie: 'Maintenance', fournisseur: 'ClimService', reference: 'MAINT-2026-012', statut: 'payee' },
    ]
})
</script>
