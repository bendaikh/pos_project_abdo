<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Journal de Caisse</h1>
                <p class="text-gray-500">Suivi des entrées et sorties de caisse</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouvelle Opération
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Solde Actuel</p>
                <p class="text-2xl font-bold" :class="soldeActuel >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ formatCurrency(soldeActuel) }}
                </p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Entrées (ce mois)</p>
                <p class="text-2xl font-bold text-green-600">+{{ formatCurrency(entreesMonth) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Sorties (ce mois)</p>
                <p class="text-2xl font-bold text-red-600">-{{ formatCurrency(sortiesMonth) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Opérations</p>
                <p class="text-2xl font-bold text-gray-900">{{ operationsList.length }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
            <input 
                v-model="search"
                type="text"
                placeholder="Rechercher par description..."
                class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <select v-model="typeFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les types</option>
                <option value="entree">Entrées</option>
                <option value="sortie">Sorties</option>
            </select>
            <input 
                v-model="dateFilter"
                type="date"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
        </div>

        <!-- Operations Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mode</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Solde</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="operation in filteredOperations" :key="operation.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(operation.date) }}</td>
                        <td class="px-6 py-4">
                            <p class="text-gray-900">{{ operation.description }}</p>
                            <p v-if="operation.reference" class="text-sm text-gray-500">Réf: {{ operation.reference }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span :class="operation.type === 'entree' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ operation.type === 'entree' ? 'Entrée' : 'Sortie' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ getModeLabel(operation.mode) }}</td>
                        <td class="px-6 py-4 text-right font-medium" :class="operation.type === 'entree' ? 'text-green-600' : 'text-red-600'">
                            {{ operation.type === 'entree' ? '+' : '-' }}{{ formatCurrency(operation.montant) }}
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-gray-900">{{ formatCurrency(operation.solde_apres) }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="openForm(operation)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                    <PencilIcon class="w-5 h-5" />
                                </button>
                                <button @click="confirmDelete(operation)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredOperations.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <BookOpenIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            Aucune opération trouvée
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Operation Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-lg w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingOperation ? 'Modifier l\'opération' : 'Nouvelle opération' }}
                </h3>
                
                <form @submit.prevent="saveOperation" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type d'opération *</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" v-model="form.type" value="entree" class="mr-2 text-primary-500">
                                <span class="text-green-600 font-medium">Entrée</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" v-model="form.type" value="sortie" class="mr-2 text-primary-500">
                                <span class="text-red-600 font-medium">Sortie</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant *</label>
                        <input v-model.number="form.montant" type="number" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement *</label>
                        <select v-model="form.mode" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="especes">Espèces</option>
                            <option value="carte">Carte bancaire</option>
                            <option value="virement">Virement</option>
                            <option value="cheque">Chèque</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <input v-model="form.description" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Description de l'opération">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Référence</label>
                        <input v-model="form.reference" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="N° facture, ticket, etc.">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                        <input v-model="form.date" type="date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
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
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer l'opération</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer cette opération ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteOperation" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
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
    BookOpenIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const operationsList = ref([])
const search = ref('')
const typeFilter = ref('')
const dateFilter = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const editingOperation = ref(null)
const operationToDelete = ref(null)
const saving = ref(false)

const form = reactive({
    type: 'entree',
    montant: 0,
    mode: 'especes',
    description: '',
    reference: '',
    date: new Date().toISOString().split('T')[0]
})

const filteredOperations = computed(() => {
    let result = operationsList.value
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(o => o.description.toLowerCase().includes(query))
    }
    if (typeFilter.value) {
        result = result.filter(o => o.type === typeFilter.value)
    }
    if (dateFilter.value) {
        result = result.filter(o => o.date === dateFilter.value)
    }
    return result
})

const soldeActuel = computed(() => {
    if (operationsList.value.length === 0) return 0
    return operationsList.value[0].solde_apres
})

const entreesMonth = computed(() => {
    const now = new Date()
    return operationsList.value
        .filter(o => {
            const date = new Date(o.date)
            return o.type === 'entree' && date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear()
        })
        .reduce((sum, o) => sum + o.montant, 0)
})

const sortiesMonth = computed(() => {
    const now = new Date()
    return operationsList.value
        .filter(o => {
            const date = new Date(o.date)
            return o.type === 'sortie' && date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear()
        })
        .reduce((sum, o) => sum + o.montant, 0)
})

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function getModeLabel(mode) {
    const labels = {
        'especes': 'Espèces',
        'carte': 'Carte bancaire',
        'virement': 'Virement',
        'cheque': 'Chèque'
    }
    return labels[mode] || mode
}

function openForm(operation = null) {
    editingOperation.value = operation
    if (operation) {
        form.type = operation.type
        form.montant = operation.montant
        form.mode = operation.mode
        form.description = operation.description
        form.reference = operation.reference || ''
        form.date = operation.date
    } else {
        form.type = 'entree'
        form.montant = 0
        form.mode = 'especes'
        form.description = ''
        form.reference = ''
        form.date = new Date().toISOString().split('T')[0]
    }
    showForm.value = true
}

function confirmDelete(operation) {
    operationToDelete.value = operation
    showDeleteModal.value = true
}

function calculateSolde() {
    let solde = 0
    const sorted = [...operationsList.value].sort((a, b) => new Date(a.date) - new Date(b.date))
    sorted.forEach(op => {
        if (op.type === 'entree') {
            solde += op.montant
        } else {
            solde -= op.montant
        }
        op.solde_apres = solde
    })
    operationsList.value = sorted.reverse()
}

async function saveOperation() {
    saving.value = true
    try {
        const newOperation = {
            id: editingOperation.value?.id || Date.now(),
            type: form.type,
            montant: form.montant,
            mode: form.mode,
            description: form.description,
            reference: form.reference,
            date: form.date,
            solde_apres: 0
        }

        if (editingOperation.value) {
            const index = operationsList.value.findIndex(o => o.id === editingOperation.value.id)
            if (index > -1) operationsList.value[index] = newOperation
        } else {
            operationsList.value.push(newOperation)
        }
        calculateSolde()
        showForm.value = false
    } catch (error) {
        alert('Erreur: ' + error.message)
    } finally {
        saving.value = false
    }
}

function deleteOperation() {
    operationsList.value = operationsList.value.filter(o => o.id !== operationToDelete.value.id)
    calculateSolde()
    showDeleteModal.value = false
}

onMounted(() => {
    // Demo data
    operationsList.value = [
        { id: 1, type: 'entree', montant: 50000, mode: 'especes', description: 'Ventes du jour', reference: 'V-2026-001', date: '2026-02-08', solde_apres: 85000 },
        { id: 2, type: 'sortie', montant: 5000, mode: 'especes', description: 'Achat fournitures', reference: 'A-2026-015', date: '2026-02-07', solde_apres: 35000 },
        { id: 3, type: 'entree', montant: 25000, mode: 'carte', description: 'Ventes du jour', reference: 'V-2026-002', date: '2026-02-07', solde_apres: 40000 },
        { id: 4, type: 'sortie', montant: 15000, mode: 'virement', description: 'Paiement fournisseur ABC', reference: 'F-2026-008', date: '2026-02-06', solde_apres: 15000 },
        { id: 5, type: 'entree', montant: 30000, mode: 'especes', description: 'Encaissement client', reference: 'C-2026-003', date: '2026-02-05', solde_apres: 30000 },
    ]
})
</script>
