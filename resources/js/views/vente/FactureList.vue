<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Factures</h1>
                <p class="text-gray-500">Gérez vos factures clients</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouvelle Facture
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Factures</p>
                <p class="text-2xl font-bold text-gray-900">{{ facturesList.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">En attente</p>
                <p class="text-2xl font-bold text-yellow-600">{{ enAttenteCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Payées</p>
                <p class="text-2xl font-bold text-green-600">{{ payeesCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Chiffre d'affaires</p>
                <p class="text-2xl font-bold text-primary-600">{{ formatCurrency(chiffreAffaires) }}</p>
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
                <option value="brouillon">Brouillon</option>
                <option value="envoyée">Envoyée</option>
                <option value="payée">Payée</option>
                <option value="partiellement_payée">Partiellement payée</option>
                <option value="en_retard">En retard</option>
                <option value="annulée">Annulée</option>
            </select>
        </div>

        <!-- Factures Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Facture</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Échéance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant HT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant TTC</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="facture in filteredFactures" :key="facture.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ facture.numero }}</td>
                        <td class="px-6 py-4">
                            <p class="text-gray-900">{{ facture.client_name }}</p>
                            <p class="text-sm text-gray-500">{{ facture.client_email }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(facture.date) }}</td>
                        <td class="px-6 py-4 text-sm" :class="isOverdue(facture) ? 'text-red-600 font-medium' : 'text-gray-900'">
                            {{ formatDate(facture.date_echeance) }}
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ formatCurrency(facture.montant_ht) }}</td>
                        <td class="px-6 py-4 font-bold text-gray-900">{{ formatCurrency(facture.montant_ttc) }}</td>
                        <td class="px-6 py-4">
                            <span :class="getStatusClass(facture.statut)" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ getStatusLabel(facture.statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="viewFacture(facture)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Voir">
                                    <EyeIcon class="w-5 h-5" />
                                </button>
                                <button @click="openForm(facture)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                    <PencilIcon class="w-5 h-5" />
                                </button>
                                <button @click="printFacture(facture)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Imprimer">
                                    <PrinterIcon class="w-5 h-5" />
                                </button>
                                <button v-if="facture.statut !== 'payée'" @click="markAsPaid(facture)" class="p-2 text-green-400 hover:text-green-600 rounded-lg hover:bg-green-50" title="Marquer payée">
                                    <CheckCircleIcon class="w-5 h-5" />
                                </button>
                                <button @click="confirmDelete(facture)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredFactures.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <DocumentIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            Aucune facture trouvée
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Facture Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-4xl w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingFacture ? 'Modifier la facture' : 'Nouvelle facture' }}
                </h3>
                
                <form @submit.prevent="saveFacture" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date d'échéance *</label>
                            <input v-model="form.date_echeance" type="date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">TVA (%)</label>
                            <input v-model.number="form.tva" type="number" min="0" max="100" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <!-- Articles -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-900">Lignes de facture</h4>
                            <button type="button" @click="addLine" class="text-sm text-primary-600 hover:text-primary-700">+ Ajouter une ligne</button>
                        </div>
                        <div class="space-y-2">
                            <div v-for="(line, index) in form.lines" :key="index" class="flex items-center gap-2">
                                <input v-model="line.description" type="text" placeholder="Description" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input v-model.number="line.quantity" type="number" min="1" placeholder="Qté" class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input v-model.number="line.price" type="number" step="0.01" placeholder="Prix HT" class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <span class="w-28 text-right font-medium">{{ formatCurrency(line.quantity * line.price) }}</span>
                                <button type="button" @click="removeLine(index)" class="p-2 text-red-400 hover:text-red-600">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200 space-y-1 text-right">
                            <p class="text-gray-600">Total HT: <span class="font-medium">{{ formatCurrency(formTotalHT) }}</span></p>
                            <p class="text-gray-600">TVA ({{ form.tva }}%): <span class="font-medium">{{ formatCurrency(formTVA) }}</span></p>
                            <p class="text-lg font-bold">Total TTC: {{ formatCurrency(formTotalTTC) }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea v-model="form.notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Conditions de paiement, remarques..."></textarea>
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
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer la facture</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer la facture "{{ factureToDelete?.numero }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteFacture" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
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
    DocumentIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const facturesList = ref([])
const customers = ref([])
const search = ref('')
const statusFilter = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const editingFacture = ref(null)
const factureToDelete = ref(null)
const saving = ref(false)

const form = reactive({
    customer_id: '',
    date_echeance: '',
    tva: 20,
    notes: '',
    lines: [{ description: '', quantity: 1, price: 0 }]
})

const filteredFactures = computed(() => {
    let result = facturesList.value
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(f => 
            f.numero.toLowerCase().includes(query) ||
            f.client_name?.toLowerCase().includes(query)
        )
    }
    if (statusFilter.value) {
        result = result.filter(f => f.statut === statusFilter.value)
    }
    return result
})

const enAttenteCount = computed(() => facturesList.value.filter(f => ['envoyée', 'partiellement_payée'].includes(f.statut)).length)
const payeesCount = computed(() => facturesList.value.filter(f => f.statut === 'payée').length)
const chiffreAffaires = computed(() => facturesList.value.filter(f => f.statut === 'payée').reduce((sum, f) => sum + (f.montant_ttc || 0), 0))

const formTotalHT = computed(() => form.lines.reduce((sum, l) => sum + (l.quantity * l.price), 0))
const formTVA = computed(() => formTotalHT.value * (form.tva / 100))
const formTotalTTC = computed(() => formTotalHT.value + formTVA.value)

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function isOverdue(facture) {
    if (facture.statut === 'payée' || facture.statut === 'annulée') return false
    return new Date(facture.date_echeance) < new Date()
}

function getStatusClass(status) {
    const classes = {
        'brouillon': 'bg-gray-100 text-gray-800',
        'envoyée': 'bg-blue-100 text-blue-800',
        'payée': 'bg-green-100 text-green-800',
        'partiellement_payée': 'bg-yellow-100 text-yellow-800',
        'en_retard': 'bg-red-100 text-red-800',
        'annulée': 'bg-gray-100 text-gray-500'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
    const labels = {
        'brouillon': 'Brouillon',
        'envoyée': 'Envoyée',
        'payée': 'Payée',
        'partiellement_payée': 'Partiel',
        'en_retard': 'En retard',
        'annulée': 'Annulée'
    }
    return labels[status] || status
}

function addLine() {
    form.lines.push({ description: '', quantity: 1, price: 0 })
}

function removeLine(index) {
    if (form.lines.length > 1) {
        form.lines.splice(index, 1)
    }
}

function openForm(facture = null) {
    editingFacture.value = facture
    if (facture) {
        form.customer_id = facture.customer_id
        form.date_echeance = facture.date_echeance
        form.tva = facture.tva || 20
        form.notes = facture.notes
        form.lines = facture.lines?.length ? [...facture.lines] : [{ description: '', quantity: 1, price: 0 }]
    } else {
        form.customer_id = ''
        form.date_echeance = ''
        form.tva = 20
        form.notes = ''
        form.lines = [{ description: '', quantity: 1, price: 0 }]
    }
    showForm.value = true
}

function viewFacture(facture) {
    alert('Affichage de la facture: ' + facture.numero)
}

function printFacture(facture) {
    alert('Impression de la facture: ' + facture.numero)
}

function markAsPaid(facture) {
    const index = facturesList.value.findIndex(f => f.id === facture.id)
    if (index > -1) {
        facturesList.value[index].statut = 'payée'
    }
}

function confirmDelete(facture) {
    factureToDelete.value = facture
    showDeleteModal.value = true
}

async function saveFacture() {
    saving.value = true
    try {
        const customer = customers.value.find(c => c.id === form.customer_id)
        const newFacture = {
            id: editingFacture.value?.id || Date.now(),
            numero: editingFacture.value?.numero || `FAC-${Date.now()}`,
            customer_id: form.customer_id,
            client_name: customer?.name || 'Client inconnu',
            client_email: customer?.email || '',
            date: editingFacture.value?.date || new Date().toISOString(),
            date_echeance: form.date_echeance,
            montant_ht: formTotalHT.value,
            tva: form.tva,
            montant_ttc: formTotalTTC.value,
            statut: editingFacture.value?.statut || 'brouillon',
            notes: form.notes,
            lines: [...form.lines]
        }

        if (editingFacture.value) {
            const index = facturesList.value.findIndex(f => f.id === editingFacture.value.id)
            if (index > -1) facturesList.value[index] = newFacture
        } else {
            facturesList.value.unshift(newFacture)
        }
        showForm.value = false
    } catch (error) {
        alert('Erreur: ' + error.message)
    } finally {
        saving.value = false
    }
}

function deleteFacture() {
    facturesList.value = facturesList.value.filter(f => f.id !== factureToDelete.value.id)
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
    facturesList.value = [
        { id: 1, numero: 'FAC-2026-001', customer_id: 1, client_name: 'Ahmed Benali', client_email: 'ahmed@example.com', date: '2026-02-01', date_echeance: '2026-03-01', montant_ht: 12500, tva: 20, montant_ttc: 15000, statut: 'payée', lines: [] },
        { id: 2, numero: 'FAC-2026-002', customer_id: 2, client_name: 'Sara Mansouri', client_email: 'sara@example.com', date: '2026-02-05', date_echeance: '2026-03-05', montant_ht: 7083.33, tva: 20, montant_ttc: 8500, statut: 'envoyée', lines: [] },
        { id: 3, numero: 'FAC-2026-003', customer_id: 3, client_name: 'Mohamed Tazi', client_email: 'mohamed@example.com', date: '2026-01-15', date_echeance: '2026-02-01', montant_ht: 18333.33, tva: 20, montant_ttc: 22000, statut: 'en_retard', lines: [] },
    ]
})
</script>
