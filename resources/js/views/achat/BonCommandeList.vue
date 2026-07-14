<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Bons d'Achat</h1>
                <p class="text-gray-500">Gérez vos bons d'achat fournisseurs</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouveau Bon Achat
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Commandes</p>
                <p class="text-2xl font-bold text-gray-900">{{ commandesList.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">En attente</p>
                <p class="text-2xl font-bold text-yellow-600">{{ enAttenteCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Reçues</p>
                <p class="text-2xl font-bold text-green-600">{{ recuesCount }}</p>
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
                <option value="envoyée">Envoyée</option>
                <option value="confirmée">Confirmée</option>
                <option value="en_cours">En cours</option>
                <option value="reçue">Reçue</option>
                <option value="annulée">Annulée</option>
            </select>
        </div>

        <!-- Commandes Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Commande</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Livraison prévue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="commande in filteredCommandes" :key="commande.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ commande.numero }}</td>
                        <td class="px-6 py-4">
                            <p class="text-gray-900">{{ commande.fournisseur_name }}</p>
                            <p class="text-sm text-gray-500">{{ commande.fournisseur_email }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(commande.date) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(commande.date_livraison_prevue) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ commande.nb_articles }} article(s)</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ formatCurrency(commande.montant_total) }}</td>
                        <td class="px-6 py-4">
                            <span :class="getStatusClass(commande.statut)" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ getStatusLabel(commande.statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <router-link :to="`/bon-achat/${commande.id}`" class="p-2 text-blue-400 hover:text-blue-600 rounded-lg hover:bg-blue-50" title="Voir détails">
                                    <EyeIcon class="w-5 h-5" />
                                </router-link>
                                <button @click="openForm(commande)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                    <PencilIcon class="w-5 h-5" />
                                </button>
                                <button @click="printCommande(commande)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Imprimer">
                                    <PrinterIcon class="w-5 h-5" />
                                </button>
                                <button v-if="commande.statut !== 'reçue'" @click="markAsReceived(commande)" class="p-2 text-green-400 hover:text-green-600 rounded-lg hover:bg-green-50" title="Marquer reçue">
                                    <CheckCircleIcon class="w-5 h-5" />
                                </button>
                                <button @click="confirmDelete(commande)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredCommandes.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <ShoppingCartIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            Aucun bon de commande trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Commande Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-4xl w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingCommande ? 'Modifier le bon de commande' : 'Nouveau bon de commande' }}
                </h3>
                
                <form @submit.prevent="saveCommande" class="space-y-4">
                    <!-- ID Commande & Date Commande -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID Commande</label>
                            <input 
                                :value="editingCommande ? editingCommande.numero : 'Auto-généré'"
                                type="text" 
                                disabled 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Commande</label>
                            <input v-model="form.date_commande" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <!-- Date Livraison Prévue -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Livraison Prévue</label>
                        <input v-model="form.date_livraison_prevue" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
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
                                    <input v-model="line.nom" type="text" placeholder="Nom de l'Article (recherche)" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
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
                                <input type="radio" v-model="form.statut" value="brouillon" class="mr-2">
                                <span class="text-sm text-gray-700">Brouillon</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" v-model="form.statut" value="envoyée" class="mr-2">
                                <span class="text-sm text-gray-700">Envoyer au fournisseur</span>
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
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer le bon de commande</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer le bon "{{ commandeToDelete?.numero }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteCommande" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
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
    EyeIcon, 
    PrinterIcon,
    ShoppingCartIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const COMMANDES_STORAGE_KEY = 'pos_bon_commandes'

const commandesList = ref([])
const fournisseurs = ref([])
const search = ref('')
const statusFilter = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const editingCommande = ref(null)
const commandeToDelete = ref(null)
const saving = ref(false)

// Function to save commandes to localStorage
function saveCommandesToStorage() {
    try {
        localStorage.setItem(COMMANDES_STORAGE_KEY, JSON.stringify(commandesList.value))
    } catch (error) {
        console.error('Error saving commandes to localStorage:', error)
    }
}

// Function to load commandes from localStorage
function loadCommandesFromStorage() {
    try {
        const stored = localStorage.getItem(COMMANDES_STORAGE_KEY)
        if (stored) {
            commandesList.value = JSON.parse(stored)
            return true
        }
        return false
    } catch (error) {
        console.error('Error loading commandes from localStorage:', error)
        return false
    }
}

const form = reactive({
    fournisseur_id: '',
    date_commande: new Date().toISOString().split('T')[0],
    date_livraison_prevue: '',
    statut: 'brouillon',
    lines: [{ id: '', nom: '', quantite: 1, prix_unitaire: 0 }]
})

const selectedFournisseur = computed(() => {
    if (!form.fournisseur_id) return null
    return fournisseurs.value.find(f => f.id === form.fournisseur_id)
})

const filteredCommandes = computed(() => {
    let result = commandesList.value
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(c => 
            c.numero.toLowerCase().includes(query) ||
            c.fournisseur_name?.toLowerCase().includes(query)
        )
    }
    if (statusFilter.value) {
        result = result.filter(c => c.statut === statusFilter.value)
    }
    return result
})

const enAttenteCount = computed(() => commandesList.value.filter(c => ['envoyée', 'confirmée', 'en_cours'].includes(c.statut)).length)
const recuesCount = computed(() => commandesList.value.filter(c => c.statut === 'reçue').length)
const totalAmount = computed(() => commandesList.value.reduce((sum, c) => sum + (c.montant_total || 0), 0))
const formTotal = computed(() => form.lines.reduce((sum, l) => sum + (l.quantite * l.prix_unitaire), 0))

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function getStatusClass(status) {
    const classes = {
        'brouillon': 'bg-gray-100 text-gray-800',
        'envoyée': 'bg-blue-100 text-blue-800',
        'confirmée': 'bg-purple-100 text-purple-800',
        'en_cours': 'bg-yellow-100 text-yellow-800',
        'reçue': 'bg-green-100 text-green-800',
        'annulée': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
    const labels = {
        'brouillon': 'Brouillon',
        'envoyée': 'Envoyée',
        'confirmée': 'Confirmée',
        'en_cours': 'En cours',
        'reçue': 'Reçue',
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

function openForm(commande = null) {
    editingCommande.value = commande
    if (commande) {
        form.fournisseur_id = commande.fournisseur_id
        form.date_commande = commande.date || new Date().toISOString().split('T')[0]
        form.date_livraison_prevue = commande.date_livraison_prevue || ''
        form.statut = commande.statut || 'brouillon'
        form.lines = commande.lines?.length ? [...commande.lines] : [{ id: '', nom: '', quantite: 1, prix_unitaire: 0 }]
    } else {
        form.fournisseur_id = ''
        form.date_commande = new Date().toISOString().split('T')[0]
        form.date_livraison_prevue = ''
        form.statut = 'brouillon'
        form.lines = [{ id: '', nom: '', quantite: 1, prix_unitaire: 0 }]
    }
    showForm.value = true
}

function viewCommande(commande) {
    alert('Affichage du bon de commande: ' + commande.numero)
}

function printCommande(commande) {
    alert('Impression du bon de commande: ' + commande.numero)
}

function markAsReceived(commande) {
    const index = commandesList.value.findIndex(c => c.id === commande.id)
    if (index > -1) {
        commandesList.value[index].statut = 'reçue'
        saveCommandesToStorage()
    }
}

function confirmDelete(commande) {
    commandeToDelete.value = commande
    showDeleteModal.value = true
}

async function saveCommande() {
    saving.value = true
    try {
        const fournisseur = fournisseurs.value.find(f => f.id === form.fournisseur_id)
        const newCommande = {
            id: editingCommande.value?.id || Date.now(),
            numero: editingCommande.value?.numero || `BC-${Date.now()}`,
            fournisseur_id: form.fournisseur_id,
            fournisseur_name: fournisseur?.name || 'Fournisseur inconnu',
            fournisseur_email: fournisseur?.email || '',
            date: form.date_commande,
            date_livraison_prevue: form.date_livraison_prevue || '',
            nb_articles: form.lines.reduce((sum, l) => sum + l.quantite, 0),
            montant_total: formTotal.value,
            statut: form.statut,
            lines: [...form.lines]
        }

        if (editingCommande.value) {
            const index = commandesList.value.findIndex(c => c.id === editingCommande.value.id)
            if (index > -1) commandesList.value[index] = newCommande
        } else {
            commandesList.value.unshift(newCommande)
        }
        
        // Save to localStorage
        saveCommandesToStorage()
        
        showForm.value = false
    } catch (error) {
        alert('Erreur: ' + error.message)
    } finally {
        saving.value = false
    }
}

function deleteCommande() {
    commandesList.value = commandesList.value.filter(c => c.id !== commandeToDelete.value.id)
    saveCommandesToStorage()
    showDeleteModal.value = false
}

onMounted(async () => {
    // Load real fournisseurs from localStorage
    try {
        const stored = localStorage.getItem('pos_fournisseurs')
        if (stored) {
            const parsed = JSON.parse(stored)
            fournisseurs.value = parsed.map(f => ({
                id: f.id,
                name: f.name || `${f.nom} ${f.prenom}`.trim(),
                email: f.email || '',
                ice: f.ice || '',
                address: f.adresse || f.address || ''
            }))
        } else {
            // Fallback to demo if no localStorage data
            fournisseurs.value = [
                { id: 1, name: 'Fournisseur ABC', email: 'contact@abc.com', ice: '001234567890001', address: '23, Rue de Commerce, Casablanca' },
                { id: 2, name: 'Distributeur XYZ', email: 'info@xyz.com', ice: '001234567890002', address: '45, Avenue Mohammed V, Rabat' },
                { id: 3, name: 'Import Express', email: 'commande@importexpress.com', ice: '001234567890003', address: '78, Boulevard Zerktouni, Casablanca' },
            ]
        }
    } catch (error) {
        console.error('Error loading fournisseurs:', error)
        fournisseurs.value = []
    }

    // Load commandes from localStorage first
    const loadedFromStorage = loadCommandesFromStorage()
    
    // Only use demo data if no stored commandes exist
    if (!loadedFromStorage) {
        commandesList.value = [
            { id: 1, numero: 'BC-2026-001', fournisseur_id: 1, fournisseur_name: 'Fournisseur ABC', fournisseur_email: 'contact@abc.com', date: '2026-02-01', date_livraison_prevue: '2026-02-15', nb_articles: 50, montant_total: 25000, statut: 'reçue', lines: [] },
            { id: 2, numero: 'BC-2026-002', fournisseur_id: 2, fournisseur_name: 'Distributeur XYZ', fournisseur_email: 'info@xyz.com', date: '2026-02-05', date_livraison_prevue: '2026-02-20', nb_articles: 30, montant_total: 18500, statut: 'en_cours', lines: [] },
            { id: 3, numero: 'BC-2026-003', fournisseur_id: 3, fournisseur_name: 'Import Express', fournisseur_email: 'commande@importexpress.com', date: '2026-02-08', date_livraison_prevue: '2026-02-25', nb_articles: 100, montant_total: 45000, statut: 'envoyée', lines: [] },
        ]
        // Save the demo data to localStorage so it persists
        saveCommandesToStorage()
    }
})
</script>
