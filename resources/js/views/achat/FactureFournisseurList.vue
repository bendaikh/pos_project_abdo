<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Factures Fournisseur</h1>
                <p class="text-gray-500">Gérez vos factures d'achat et paiements fournisseurs</p>
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
                <p class="text-sm text-gray-500">Non payées</p>
                <p class="text-2xl font-bold text-orange-600">{{ nonPayeesCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Payées</p>
                <p class="text-2xl font-bold text-green-600">{{ payeesCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Montant Total</p>
                <p class="text-2xl font-bold text-primary-600">{{ formatCurrency(totalAmount) }}</p>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
            <input 
                v-model="search"
                type="text"
                placeholder="Rechercher par numéro ou fournisseur..."
                class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <select v-model="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="brouillon">Brouillon</option>
                <option value="non_payée">Non payée</option>
                <option value="partiellement_payée">Partiellement payée</option>
                <option value="payée">Payée</option>
                <option value="en_retard">En retard</option>
            </select>
        </div>

        <!-- Factures Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Facture</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date facture</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date échéance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant TTC</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reste à payer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="facture in filteredFactures" :key="facture.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ facture.numero }}</td>
                        <td class="px-6 py-4">
                            <p class="text-gray-900">{{ facture.fournisseur_name }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(facture.date_facture) }}</td>
                        <td class="px-6 py-4 text-sm" :class="isOverdue(facture) ? 'text-red-600 font-medium' : 'text-gray-900'">
                            {{ formatDate(facture.date_echeance) }}
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ formatCurrency(facture.montant_ttc) }}</td>
                        <td class="px-6 py-4 font-medium" :class="facture.reste_a_payer > 0 ? 'text-orange-600' : 'text-green-600'">
                            {{ formatCurrency(facture.reste_a_payer) }}
                        </td>
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
                                <button v-if="facture.reste_a_payer > 0" @click="registerPayment(facture)" class="p-2 text-green-400 hover:text-green-600 rounded-lg hover:bg-green-50" title="Enregistrer paiement">
                                    <BanknotesIcon class="w-5 h-5" />
                                </button>
                                <button @click="printFacture(facture)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Imprimer">
                                    <PrinterIcon class="w-5 h-5" />
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
                            Aucune facture fournisseur trouvée
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Facture Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-4xl w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Nouvelle facture fournisseur</h3>
                
                <form @submit.prevent="saveFacture" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur *</label>
                            <select v-model="form.fournisseur_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Sélectionner un fournisseur</option>
                                <option v-for="fournisseur in fournisseurs" :key="fournisseur.id" :value="fournisseur.id">
                                    {{ fournisseur.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">N° Facture fournisseur</label>
                            <input v-model="form.numero_fournisseur" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ex: FA-2026-123">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date facture *</label>
                            <input v-model="form.date_facture" type="date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date échéance</label>
                            <input v-model="form.date_echeance" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <!-- Articles -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-900">Articles facturés</h4>
                            <button type="button" @click="addLine" class="text-sm text-primary-600 hover:text-primary-700">+ Ajouter un article</button>
                        </div>
                        <div class="space-y-2">
                            <div v-for="(line, index) in form.lines" :key="index" class="flex items-center gap-2">
                                <input v-model="line.description" type="text" placeholder="Description" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input v-model.number="line.quantity" type="number" min="1" placeholder="Qté" class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input v-model.number="line.price" type="number" step="0.01" placeholder="Prix HT" class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input v-model.number="line.tva" type="number" step="0.01" placeholder="TVA %" class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <span class="w-28 text-right font-medium">{{ formatCurrency(calculateLineTTC(line)) }}</span>
                                <button type="button" @click="removeLine(index)" class="p-2 text-red-400 hover:text-red-600">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="text-right space-y-1">
                                <div class="text-sm text-gray-600">Total HT: <span class="font-medium">{{ formatCurrency(totalHT) }}</span></div>
                                <div class="text-sm text-gray-600">Total TVA: <span class="font-medium">{{ formatCurrency(totalTVA) }}</span></div>
                                <div class="text-lg font-bold">Total TTC: {{ formatCurrency(totalTTC) }}</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea v-model="form.notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Notes internes..."></textarea>
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

        <!-- Payment Modal -->
        <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showPaymentModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Enregistrer un paiement</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Facture: <span class="font-medium text-gray-900">{{ paymentFacture?.numero }}</span></p>
                        <p class="text-sm text-gray-600">Reste à payer: <span class="font-medium text-orange-600">{{ formatCurrency(paymentFacture?.reste_a_payer) }}</span></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant payé</label>
                        <input v-model.number="paymentAmount" type="number" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de paiement</label>
                        <input v-model="paymentDate" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Méthode de paiement</label>
                        <select v-model="paymentMethod" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="espèces">Espèces</option>
                            <option value="chèque">Chèque</option>
                            <option value="virement">Virement</option>
                            <option value="carte">Carte bancaire</option>
                        </select>
                    </div>
                </div>
                <div class="flex space-x-3 pt-4">
                    <button @click="showPaymentModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="savePayment" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Valider</button>
                </div>
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
import { 
    PlusIcon, 
    TrashIcon, 
    EyeIcon, 
    PrinterIcon,
    DocumentIcon,
    BanknotesIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const facturesList = ref([])
const fournisseurs = ref([])
const search = ref('')
const statusFilter = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const showPaymentModal = ref(false)
const editingFacture = ref(null)
const factureToDelete = ref(null)
const paymentFacture = ref(null)
const paymentAmount = ref(0)
const paymentDate = ref(new Date().toISOString().split('T')[0])
const paymentMethod = ref('espèces')
const saving = ref(false)

const form = reactive({
    fournisseur_id: '',
    numero_fournisseur: '',
    date_facture: new Date().toISOString().split('T')[0],
    date_echeance: '',
    notes: '',
    lines: [{ description: '', quantity: 1, price: 0, tva: 20 }]
})

const filteredFactures = computed(() => {
    let result = facturesList.value
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(f => 
            f.numero.toLowerCase().includes(query) ||
            f.fournisseur_name?.toLowerCase().includes(query) ||
            f.numero_fournisseur?.toLowerCase().includes(query)
        )
    }
    if (statusFilter.value) {
        result = result.filter(f => f.statut === statusFilter.value)
    }
    return result
})

const nonPayeesCount = computed(() => facturesList.value.filter(f => f.reste_a_payer > 0).length)
const payeesCount = computed(() => facturesList.value.filter(f => f.reste_a_payer === 0).length)
const totalAmount = computed(() => facturesList.value.reduce((sum, f) => sum + (f.montant_ttc || 0), 0))

const totalHT = computed(() => form.lines.reduce((sum, l) => sum + (l.quantity * l.price), 0))
const totalTVA = computed(() => form.lines.reduce((sum, l) => sum + ((l.quantity * l.price * l.tva) / 100), 0))
const totalTTC = computed(() => totalHT.value + totalTVA.value)

function calculateLineTTC(line) {
    const ht = line.quantity * line.price
    const tva = (ht * line.tva) / 100
    return ht + tva
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function isOverdue(facture) {
    if (!facture.date_echeance || facture.reste_a_payer === 0) return false
    return new Date(facture.date_echeance) < new Date()
}

function getStatusClass(status) {
    const classes = {
        'brouillon': 'bg-gray-100 text-gray-800',
        'non_payée': 'bg-orange-100 text-orange-800',
        'partiellement_payée': 'bg-blue-100 text-blue-800',
        'payée': 'bg-green-100 text-green-800',
        'en_retard': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
    const labels = {
        'brouillon': 'Brouillon',
        'non_payée': 'Non payée',
        'partiellement_payée': 'Partiellement payée',
        'payée': 'Payée',
        'en_retard': 'En retard'
    }
    return labels[status] || status
}

function addLine() {
    form.lines.push({ description: '', quantity: 1, price: 0, tva: 20 })
}

function removeLine(index) {
    if (form.lines.length > 1) {
        form.lines.splice(index, 1)
    }
}

function openForm() {
    form.fournisseur_id = ''
    form.numero_fournisseur = ''
    form.date_facture = new Date().toISOString().split('T')[0]
    form.date_echeance = ''
    form.notes = ''
    form.lines = [{ description: '', quantity: 1, price: 0, tva: 20 }]
    showForm.value = true
}

function viewFacture(facture) {
    alert('Affichage de la facture: ' + facture.numero)
}

function registerPayment(facture) {
    paymentFacture.value = facture
    paymentAmount.value = facture.reste_a_payer
    paymentDate.value = new Date().toISOString().split('T')[0]
    paymentMethod.value = 'espèces'
    showPaymentModal.value = true
}

function savePayment() {
    const index = facturesList.value.findIndex(f => f.id === paymentFacture.value.id)
    if (index > -1) {
        const facture = facturesList.value[index]
        facture.reste_a_payer = Math.max(0, facture.reste_a_payer - paymentAmount.value)
        
        if (facture.reste_a_payer === 0) {
            facture.statut = 'payée'
        } else if (facture.reste_a_payer < facture.montant_ttc) {
            facture.statut = 'partiellement_payée'
        }
    }
    showPaymentModal.value = false
}

function printFacture(facture) {
    alert('Impression de la facture: ' + facture.numero)
}

function confirmDelete(facture) {
    factureToDelete.value = facture
    showDeleteModal.value = true
}

async function saveFacture() {
    saving.value = true
    try {
        const fournisseur = fournisseurs.value.find(f => f.id === form.fournisseur_id)
        
        const newFacture = {
            id: Date.now(),
            numero: `FF-${Date.now()}`,
            numero_fournisseur: form.numero_fournisseur,
            fournisseur_id: form.fournisseur_id,
            fournisseur_name: fournisseur?.name || 'Fournisseur inconnu',
            date_facture: form.date_facture,
            date_echeance: form.date_echeance,
            montant_ttc: totalTTC.value,
            reste_a_payer: totalTTC.value,
            statut: 'non_payée',
            notes: form.notes,
            lines: [...form.lines]
        }

        facturesList.value.unshift(newFacture)
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
    // Demo fournisseurs
    fournisseurs.value = [
        { id: 1, name: 'Fournisseur ABC' },
        { id: 2, name: 'Distributeur XYZ' },
        { id: 3, name: 'Import Express' },
    ]

    // Demo data
    facturesList.value = [
        { id: 1, numero: 'FF-2026-001', numero_fournisseur: 'ABC-FA-2026-123', fournisseur_id: 1, fournisseur_name: 'Fournisseur ABC', date_facture: '2026-02-01', date_echeance: '2026-03-01', montant_ttc: 30000, reste_a_payer: 0, statut: 'payée', lines: [] },
        { id: 2, numero: 'FF-2026-002', numero_fournisseur: 'XYZ-456', fournisseur_id: 2, fournisseur_name: 'Distributeur XYZ', date_facture: '2026-02-05', date_echeance: '2026-03-05', montant_ttc: 22200, reste_a_payer: 10000, statut: 'partiellement_payée', lines: [] },
        { id: 3, numero: 'FF-2026-003', numero_fournisseur: 'IMP-789', fournisseur_id: 3, fournisseur_name: 'Import Express', date_facture: '2026-02-08', date_echeance: '2026-02-20', montant_ttc: 54000, reste_a_payer: 54000, statut: 'en_retard', lines: [] },
    ]
})
</script>
