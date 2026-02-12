<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Historique d'Achats</h1>
                <p class="text-gray-500">Consultez l'historique complet de vos achats et commandes</p>
            </div>
            <button @click="exportData()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <ArrowDownTrayIcon class="w-5 h-5 mr-2" />
                Exporter
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Transactions</p>
                <p class="text-2xl font-bold text-gray-900">{{ historiqueList.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Montant Total</p>
                <p class="text-2xl font-bold text-primary-600">{{ formatCurrency(totalAmount) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Fournisseurs Actifs</p>
                <p class="text-2xl font-bold text-blue-600">{{ uniqueFournisseurs }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Ce mois</p>
                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(currentMonthAmount) }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input 
                    v-model="search"
                    type="text"
                    placeholder="Rechercher..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                <select v-model="typeFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous les types</option>
                    <option value="bon_commande">Bon de commande</option>
                    <option value="reception">Réception</option>
                    <option value="facture">Facture</option>
                    <option value="paiement">Paiement</option>
                </select>
                <input 
                    v-model="dateDebut"
                    type="date"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Date début"
                >
                <input 
                    v-model="dateFin"
                    type="date"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Date fin"
                >
            </div>
        </div>

        <!-- Historique Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Document</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="item in filteredHistorique" :key="item.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(item.date) }}</td>
                        <td class="px-6 py-4">
                            <span :class="getTypeClass(item.type)" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ getTypeLabel(item.type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ item.numero }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ item.fournisseur_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ item.description }}</td>
                        <td class="px-6 py-4 font-medium" :class="item.type === 'paiement' ? 'text-red-600' : 'text-gray-900'">
                            {{ item.type === 'paiement' ? '-' : '' }}{{ formatCurrency(item.montant) }}
                        </td>
                        <td class="px-6 py-4">
                            <span :class="getStatusClass(item.statut)" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ item.statut }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="viewDocument(item)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Voir">
                                    <EyeIcon class="w-5 h-5" />
                                </button>
                                <button @click="printDocument(item)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Imprimer">
                                    <PrinterIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredHistorique.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <ClockIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            Aucun historique trouvé pour cette période
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-600">
                Affichage de {{ Math.min((currentPage - 1) * itemsPerPage + 1, filteredHistorique.length) }} à {{ Math.min(currentPage * itemsPerPage, filteredHistorique.length) }} sur {{ filteredHistorique.length }} résultats
            </p>
            <div class="flex space-x-2">
                <button 
                    @click="currentPage--" 
                    :disabled="currentPage === 1"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Précédent
                </button>
                <button 
                    @click="currentPage++" 
                    :disabled="currentPage * itemsPerPage >= filteredHistorique.length"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Suivant
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useSettingsStore } from '../../stores/settings'
import { 
    EyeIcon, 
    PrinterIcon,
    ClockIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const historiqueList = ref([])
const search = ref('')
const typeFilter = ref('')
const dateDebut = ref('')
const dateFin = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(20)

const filteredHistorique = computed(() => {
    let result = historiqueList.value
    
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(h => 
            h.numero.toLowerCase().includes(query) ||
            h.fournisseur_name?.toLowerCase().includes(query) ||
            h.description?.toLowerCase().includes(query)
        )
    }
    
    if (typeFilter.value) {
        result = result.filter(h => h.type === typeFilter.value)
    }
    
    if (dateDebut.value) {
        result = result.filter(h => new Date(h.date) >= new Date(dateDebut.value))
    }
    
    if (dateFin.value) {
        result = result.filter(h => new Date(h.date) <= new Date(dateFin.value))
    }
    
    // Pagination
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return result.slice(start, end)
})

const totalAmount = computed(() => historiqueList.value.reduce((sum, h) => {
    return sum + (h.type === 'paiement' ? 0 : h.montant)
}, 0))

const currentMonthAmount = computed(() => {
    const now = new Date()
    const currentMonth = now.getMonth()
    const currentYear = now.getFullYear()
    
    return historiqueList.value
        .filter(h => {
            const date = new Date(h.date)
            return date.getMonth() === currentMonth && date.getFullYear() === currentYear && h.type !== 'paiement'
        })
        .reduce((sum, h) => sum + h.montant, 0)
})

const uniqueFournisseurs = computed(() => {
    const fournisseurs = new Set(historiqueList.value.map(h => h.fournisseur_id))
    return fournisseurs.size
})

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR', { 
        year: 'numeric', 
        month: '2-digit', 
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}

function getTypeClass(type) {
    const classes = {
        'bon_commande': 'bg-blue-100 text-blue-800',
        'reception': 'bg-purple-100 text-purple-800',
        'facture': 'bg-orange-100 text-orange-800',
        'paiement': 'bg-green-100 text-green-800'
    }
    return classes[type] || 'bg-gray-100 text-gray-800'
}

function getTypeLabel(type) {
    const labels = {
        'bon_commande': 'Bon de commande',
        'reception': 'Réception',
        'facture': 'Facture',
        'paiement': 'Paiement'
    }
    return labels[type] || type
}

function getStatusClass(status) {
    const classes = {
        'Brouillon': 'bg-gray-100 text-gray-800',
        'Envoyée': 'bg-blue-100 text-blue-800',
        'Confirmée': 'bg-purple-100 text-purple-800',
        'Reçue': 'bg-green-100 text-green-800',
        'Validée': 'bg-green-100 text-green-800',
        'Payée': 'bg-green-100 text-green-800',
        'Non payée': 'bg-orange-100 text-orange-800',
        'Annulée': 'bg-red-100 text-red-800',
        'Retour': 'bg-red-100 text-red-800',
        'Complété': 'bg-green-100 text-green-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function viewDocument(item) {
    alert(`Affichage du document ${item.type}: ${item.numero}`)
}

function printDocument(item) {
    alert(`Impression du document: ${item.numero}`)
}

function exportData() {
    alert('Export des données en cours...')
}

onMounted(() => {
    // Demo data - mixed history from all purchase operations
    historiqueList.value = [
        // Bon de commande
        { id: 1, date: '2026-02-01T10:30:00', type: 'bon_commande', numero: 'BC-2026-001', fournisseur_id: 1, fournisseur_name: 'Fournisseur ABC', description: 'Commande de matériel informatique (50 articles)', montant: 25000, statut: 'Reçue' },
        { id: 2, date: '2026-02-05T14:15:00', type: 'bon_commande', numero: 'BC-2026-002', fournisseur_id: 2, fournisseur_name: 'Distributeur XYZ', description: 'Commande de fournitures de bureau (30 articles)', montant: 18500, statut: 'En cours' },
        { id: 3, date: '2026-02-08T09:45:00', type: 'bon_commande', numero: 'BC-2026-003', fournisseur_id: 3, fournisseur_name: 'Import Express', description: 'Commande de produits électroniques (100 articles)', montant: 45000, statut: 'Envoyée' },
        
        // Réception
        { id: 4, date: '2026-02-10T11:20:00', type: 'reception', numero: 'REC-2026-001', fournisseur_id: 1, fournisseur_name: 'Fournisseur ABC', description: 'Réception BC-2026-001 (48/50 articles)', montant: 24800, statut: 'Validée' },
        { id: 5, date: '2026-02-11T16:30:00', type: 'reception', numero: 'REC-2026-002', fournisseur_id: 2, fournisseur_name: 'Distributeur XYZ', description: 'Réception partielle BC-2026-002', montant: 18500, statut: 'En attente' },
        { id: 6, date: '2026-02-09T13:00:00', type: 'reception', numero: 'REC-2026-003', fournisseur_id: 3, fournisseur_name: 'Import Express', description: 'Réception BC-2026-003 avec anomalies', montant: 42750, statut: 'Retour' },
        
        // Facture
        { id: 7, date: '2026-02-01T15:45:00', type: 'facture', numero: 'FF-2026-001', fournisseur_id: 1, fournisseur_name: 'Fournisseur ABC', description: 'Facture ABC-FA-2026-123', montant: 30000, statut: 'Payée' },
        { id: 8, date: '2026-02-05T10:00:00', type: 'facture', numero: 'FF-2026-002', fournisseur_id: 2, fournisseur_name: 'Distributeur XYZ', description: 'Facture XYZ-456', montant: 22200, statut: 'Non payée' },
        { id: 9, date: '2026-02-08T14:30:00', type: 'facture', numero: 'FF-2026-003', fournisseur_id: 3, fournisseur_name: 'Import Express', description: 'Facture IMP-789', montant: 54000, statut: 'Non payée' },
        
        // Paiement
        { id: 10, date: '2026-02-03T09:15:00', type: 'paiement', numero: 'PAY-2026-001', fournisseur_id: 1, fournisseur_name: 'Fournisseur ABC', description: 'Paiement facture FF-2026-001 par virement', montant: 30000, statut: 'Complété' },
        { id: 11, date: '2026-02-07T11:45:00', type: 'paiement', numero: 'PAY-2026-002', fournisseur_id: 2, fournisseur_name: 'Distributeur XYZ', description: 'Acompte facture FF-2026-002 par chèque', montant: 12200, statut: 'Complété' },
        
        // Older entries
        { id: 12, date: '2026-01-25T10:00:00', type: 'bon_commande', numero: 'BC-2026-000', fournisseur_id: 1, fournisseur_name: 'Fournisseur ABC', description: 'Commande précédente', montant: 15000, statut: 'Reçue' },
        { id: 13, date: '2026-01-28T14:00:00', type: 'reception', numero: 'REC-2026-000', fournisseur_id: 1, fournisseur_name: 'Fournisseur ABC', description: 'Réception BC-2026-000', montant: 15000, statut: 'Validée' },
        { id: 14, date: '2026-01-30T16:00:00', type: 'facture', numero: 'FF-2026-000', fournisseur_id: 1, fournisseur_name: 'Fournisseur ABC', description: 'Facture janvier', montant: 18000, statut: 'Payée' },
        { id: 15, date: '2026-02-02T10:30:00', type: 'paiement', numero: 'PAY-2026-000', fournisseur_id: 1, fournisseur_name: 'Fournisseur ABC', description: 'Paiement facture janvier', montant: 18000, statut: 'Complété' },
    ].sort((a, b) => new Date(b.date) - new Date(a.date))
})
</script>
