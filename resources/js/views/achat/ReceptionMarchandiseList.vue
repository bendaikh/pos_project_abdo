<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Réception de Marchandise</h1>
                <p class="text-gray-500">Gérez les réceptions, validations et retours fournisseurs</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouvelle Réception
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Réceptions</p>
                <p class="text-2xl font-bold text-gray-900">{{ receptionsList.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Validées</p>
                <p class="text-2xl font-bold text-green-600">{{ valideesCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Retours</p>
                <p class="text-2xl font-bold text-orange-600">{{ retoursCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Annulées</p>
                <p class="text-2xl font-bold text-red-600">{{ annuleesCount }}</p>
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
                <option value="en_attente">En attente</option>
                <option value="validée">Validée</option>
                <option value="retour">Retour au fournisseur</option>
                <option value="annulée">Annulée</option>
            </select>
        </div>

        <!-- Réceptions Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Réception</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bon de commande</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date réception</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="reception in filteredReceptions" :key="reception.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ reception.numero }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ reception.bon_commande }}</td>
                        <td class="px-6 py-4">
                            <p class="text-gray-900">{{ reception.fournisseur_name }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(reception.date_reception) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ reception.nb_articles }} article(s)</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ formatCurrency(reception.montant_total) }}</td>
                        <td class="px-6 py-4">
                            <span :class="getStatusClass(reception.statut)" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ getStatusLabel(reception.statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="viewReception(reception)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Voir">
                                    <EyeIcon class="w-5 h-5" />
                                </button>
                                <button v-if="reception.statut === 'en_attente'" @click="validateReception(reception)" class="p-2 text-green-400 hover:text-green-600 rounded-lg hover:bg-green-50" title="Valider">
                                    <CheckCircleIcon class="w-5 h-5" />
                                </button>
                                <button v-if="reception.statut === 'en_attente'" @click="returnToSupplier(reception)" class="p-2 text-orange-400 hover:text-orange-600 rounded-lg hover:bg-orange-50" title="Retour fournisseur">
                                    <ArrowUturnLeftIcon class="w-5 h-5" />
                                </button>
                                <button @click="printReception(reception)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Imprimer">
                                    <PrinterIcon class="w-5 h-5" />
                                </button>
                                <button @click="confirmDelete(reception)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredReceptions.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <TruckIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            Aucune réception de marchandise trouvée
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Reception Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-4xl w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Nouvelle réception de marchandise</h3>
                
                <form @submit.prevent="saveReception" class="space-y-4">
                    <!-- Date Commande -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Commande</label>
                        <input v-model="form.date_commande" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <!-- Fournisseur (recherche) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur (recherche) *</label>
                        <select v-model="form.fournisseur_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">Rechercher un fournisseur...</option>
                            <option v-for="fournisseur in fournisseurs" :key="fournisseur.id" :value="fournisseur.id">
                                {{ fournisseur.name }} - {{ fournisseur.ice }}
                            </option>
                        </select>
                        <!-- Infos Fournisseur -->
                        <div v-if="selectedFournisseur" class="mt-2 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <p class="text-sm font-medium text-gray-900">{{ selectedFournisseur.name }}</p>
                            <p class="text-sm text-gray-600">ICE: {{ selectedFournisseur.ice }}</p>
                            <p class="text-sm text-gray-600">{{ selectedFournisseur.address }}</p>
                        </div>
                    </div>

                    <!-- Articles -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-900">Articles</h4>
                            <button type="button" @click="addLine" class="text-sm text-primary-600 hover:text-primary-700">+ Ajouter un article</button>
                        </div>
                        <div class="space-y-2">
                            <div v-for="(line, index) in form.lines" :key="index" class="grid grid-cols-12 gap-2 items-center">
                                <div class="col-span-1">
                                    <input v-model="line.id" type="text" placeholder="ID" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div class="col-span-4">
                                    <input v-model="line.nom" type="text" placeholder="Nom de l'Article" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div class="col-span-2">
                                    <input v-model.number="line.quantite" type="number" min="1" placeholder="Quantité" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div class="col-span-2">
                                    <input v-model.number="line.prix_unitaire" type="number" step="0.01" placeholder="Prix unitaire" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div class="col-span-2">
                                    <span class="text-right font-medium block">{{ formatCurrency(line.quantite * line.prix_unitaire) }}</span>
                                </div>
                                <div class="col-span-1">
                                    <button type="button" @click="removeLine(index)" class="p-2 text-red-400 hover:text-red-600">
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200 text-right">
                            <div class="text-sm text-gray-600 mb-1">Montant Total de la Commande:</div>
                            <span class="text-2xl font-bold text-gray-900">{{ formatCurrency(formTotal) }}</span>
                        </div>
                    </div>

                    <!-- Statut de la Commande -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut de la Commande</label>
                        <div class="flex space-x-3">
                            <label class="flex items-center">
                                <input type="radio" v-model="form.statut" value="validée" class="mr-2">
                                <span class="text-sm text-gray-700">Validée</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" v-model="form.statut" value="retour" class="mr-2">
                                <span class="text-sm text-gray-700">Retour au fournisseur</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" v-model="form.statut" value="annulée" class="mr-2">
                                <span class="text-sm text-gray-700">Annulée</span>
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

        <!-- Delete Confirmation -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer la réception</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer la réception "{{ receptionToDelete?.numero }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteReception" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
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
    TruckIcon,
    CheckCircleIcon,
    ArrowUturnLeftIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const receptionsList = ref([])
const search = ref('')
const statusFilter = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const editingReception = ref(null)
const receptionToDelete = ref(null)
const saving = ref(false)

const form = reactive({
    fournisseur_id: '',
    date_commande: new Date().toISOString().split('T')[0],
    statut: 'validée',
    lines: [{ id: '', nom: '', quantite: 1, prix_unitaire: 0 }]
})

const fournisseurs = ref([])

const selectedFournisseur = computed(() => {
    if (!form.fournisseur_id) return null
    return fournisseurs.value.find(f => f.id === form.fournisseur_id)
})

const formTotal = computed(() => form.lines.reduce((sum, l) => sum + (l.quantite * l.prix_unitaire), 0))

const filteredReceptions = computed(() => {
    let result = receptionsList.value
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(r => 
            r.numero.toLowerCase().includes(query) ||
            r.fournisseur_name?.toLowerCase().includes(query) ||
            r.bon_commande?.toLowerCase().includes(query)
        )
    }
    if (statusFilter.value) {
        result = result.filter(r => r.statut === statusFilter.value)
    }
    return result
})

const valideesCount = computed(() => receptionsList.value.filter(r => r.statut === 'validée').length)
const retoursCount = computed(() => receptionsList.value.filter(r => r.statut === 'retour').length)
const annuleesCount = computed(() => receptionsList.value.filter(r => r.statut === 'annulée').length)

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function getStatusClass(status) {
    const classes = {
        'en_attente': 'bg-yellow-100 text-yellow-800',
        'validée': 'bg-green-100 text-green-800',
        'retour': 'bg-orange-100 text-orange-800',
        'annulée': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
    const labels = {
        'en_attente': 'En attente',
        'validée': 'Validée',
        'retour': 'Retour fournisseur',
        'annulée': 'Annulée'
    }
    return labels[status] || status
}

function addLine() {
    form.lines.push({ id: '', nom: '', quantite: 1, prix_unitaire: 0 })
}

function removeLine(index) {
    if (form.lines.length > 1) {
        form.lines.splice(index, 1)
    }
}

function openForm() {
    form.fournisseur_id = ''
    form.date_commande = new Date().toISOString().split('T')[0]
    form.statut = 'validée'
    form.lines = [{ id: '', nom: '', quantite: 1, prix_unitaire: 0 }]
    showForm.value = true
}

function viewReception(reception) {
    alert('Affichage de la réception: ' + reception.numero)
}

function validateReception(reception) {
    const index = receptionsList.value.findIndex(r => r.id === reception.id)
    if (index > -1) {
        receptionsList.value[index].statut = 'validée'
    }
}

function returnToSupplier(reception) {
    const index = receptionsList.value.findIndex(r => r.id === reception.id)
    if (index > -1) {
        receptionsList.value[index].statut = 'retour'
    }
}

function printReception(reception) {
    alert('Impression de la réception: ' + reception.numero)
}

function confirmDelete(reception) {
    receptionToDelete.value = reception
    showDeleteModal.value = true
}

async function saveReception() {
    saving.value = true
    try {
        const fournisseur = fournisseurs.value.find(f => f.id === form.fournisseur_id)
        
        const newReception = {
            id: Date.now(),
            numero: `REC-${Date.now()}`,
            fournisseur_id: form.fournisseur_id,
            fournisseur_name: fournisseur?.name || '',
            date_reception: new Date().toISOString().split('T')[0],
            nb_articles: form.lines.reduce((sum, l) => sum + l.quantite, 0),
            montant_total: formTotal.value,
            statut: form.statut,
            lines: [...form.lines]
        }

        receptionsList.value.unshift(newReception)
        showForm.value = false
    } catch (error) {
        alert('Erreur: ' + error.message)
    } finally {
        saving.value = false
    }
}

function deleteReception() {
    receptionsList.value = receptionsList.value.filter(r => r.id !== receptionToDelete.value.id)
    showDeleteModal.value = false
}

onMounted(async () => {
    // Demo fournisseurs
    fournisseurs.value = [
        { id: 1, name: 'Fournisseur ABC', ice: '001234567890001', address: '23, Rue de Commerce, Casablanca' },
        { id: 2, name: 'Distributeur XYZ', ice: '001234567890002', address: '45, Avenue Mohammed V, Rabat' },
        { id: 3, name: 'Import Express', ice: '001234567890003', address: '78, Boulevard Zerktouni, Casablanca' },
    ]

    // Demo data
    receptionsList.value = [
        { id: 1, numero: 'REC-2026-001', bon_commande_id: 1, bon_commande: 'BC-2026-001', fournisseur_name: 'Fournisseur ABC', date_reception: '2026-02-10', nb_articles: 48, montant_total: 24800, statut: 'validée', lines: [] },
        { id: 2, numero: 'REC-2026-002', bon_commande_id: 2, bon_commande: 'BC-2026-002', fournisseur_name: 'Distributeur XYZ', date_reception: '2026-02-11', nb_articles: 30, montant_total: 18500, statut: 'en_attente', lines: [] },
        { id: 3, numero: 'REC-2026-003', bon_commande_id: 3, bon_commande: 'BC-2026-003', fournisseur_name: 'Import Express', date_reception: '2026-02-09', nb_articles: 95, montant_total: 42750, statut: 'retour', lines: [] },
    ]
})
</script>
