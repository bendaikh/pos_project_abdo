<template>
    <div class="min-h-screen bg-gray-50 p-6">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm mb-6">
            <router-link to="/dashboard" class="text-primary-600 hover:text-primary-700 flex items-center">
                <HomeIcon class="w-4 h-4 mr-1" />
                Accueil
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-400" />
            <router-link to="/customers" class="text-primary-600 hover:text-primary-700">
                Clients
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-400" />
            <span class="text-gray-600">Fiche client</span>
        </div>

        <!-- Main Card -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary-50 to-primary-100 border-b border-gray-200 px-6 py-6 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <UserCircleIcon class="w-8 h-8 text-primary-600" />
                    <h1 class="text-2xl font-bold text-gray-900">{{ client.name || 'Client' }}</h1>
                </div>
                <div class="flex space-x-3">
                    <button @click="editClient" class="px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition flex items-center">
                        <PencilIcon class="w-5 h-5 mr-2" />
                        Éditer
                    </button>
                    <button @click="confirmDelete" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition flex items-center">
                        <TrashIcon class="w-5 h-5 mr-2" />
                        Supprimer
                    </button>
                </div>
            </div>

            <!-- Profile Section -->
            <div v-if="client.id" class="p-6 border-b border-gray-200">
                <div class="flex items-start space-x-6">
                    <!-- Avatar -->
                    <div class="w-40 h-40 bg-gradient-to-br from-primary-50 to-primary-100 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden border-2 border-primary-200 shadow-sm">
                        <img v-if="client.photo_url || client.avatar" :key="`${client.id}-${client.photo_url}`" :src="resolveCustomerPhotoUrl(client.photo_url || client.avatar)" :alt="client.name" class="w-full h-full object-cover" onerror="console.log('Image failed to load:', this.src)" />
                        <UserCircleIcon v-else class="w-20 h-20 text-primary-400" />
                    </div>
                    
                    <!-- Info -->
                    <div class="flex-1 space-y-4">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-900">{{ client.name }}</h2>
                            <p class="text-gray-600 mt-1">ID : <span class="font-mono text-gray-900">#{{ client.id }}</span></p>
                        </div>
                        
                        <div class="space-y-2">
                            <p class="text-gray-700"><span class="font-medium">Type :</span> <span v-if="client.type_client" class="text-gray-900">{{ client.type_client === 'entreprise' ? 'Entreprise' : 'Particulier' }}</span><span v-else class="text-gray-400 italic">Non spécifié</span></p>
                            <p v-if="client.raison_sociale" class="text-gray-700"><span class="font-medium">Raison sociale :</span> <span class="text-gray-900">{{ client.raison_sociale }}</span></p>
                        </div>

                        <div class="flex items-center space-x-3">
                            <span :class="getStatusBadgeClass(client.status)" class="px-3 py-1 text-sm font-medium rounded-full">
                                {{ getStatusLabel(client.status) }}
                            </span>
                            <span v-if="client.is_vip || client.loyalty_discount > 0" class="px-3 py-1 bg-yellow-100 text-yellow-700 text-sm font-medium rounded-full border border-yellow-300">
                                ⭐ Client Fidèle
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <div class="flex">
                    <button
                        @click="activeTab = 'informations'"
                        :class="activeTab === 'informations' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-600 hover:text-gray-900'"
                        class="px-6 py-4 font-medium transition"
                    >
                        Informations
                    </button>
                    <button
                        @click="activeTab = 'coordonnees'"
                        :class="activeTab === 'coordonnees' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-600 hover:text-gray-900'"
                        class="px-6 py-4 font-medium transition"
                    >
                        Coordonnées
                    </button>
                    <button
                        @click="activeTab = 'documents'"
                        :class="activeTab === 'documents' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-600 hover:text-gray-900'"
                        class="px-6 py-4 font-medium transition"
                    >
                        Documents
                    </button>
                    <button
                        @click="activeTab = 'achats'"
                        :class="activeTab === 'achats' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-600 hover:text-gray-900'"
                        class="px-6 py-4 font-medium transition"
                    >
                        Historique des Achats
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Informations Tab -->
                <div v-if="activeTab === 'informations'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">ICE</p>
                            <p class="text-gray-900 font-semibold text-lg"><span v-if="client.ice">{{ client.ice }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">IF</p>
                            <p class="text-gray-900 font-semibold text-lg"><span v-if="client.if">{{ client.if }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Activité</p>
                            <p class="text-gray-900 font-semibold text-lg"><span v-if="client.activite">{{ client.activite }}</span><span v-else class="text-gray-400 italic">Non renseignée</span></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Total des achats</p>
                            <p class="text-primary-600 font-bold text-2xl">{{ formatCurrency(client.total_achats || 0) }}</p>
                        </div>
                        <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Nombre d'achats</p>
                            <p class="text-primary-600 font-bold text-2xl">{{ client.nombre_commandes || 0 }}</p>
                        </div>
                        <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Remise fidélité</p>
                            <p class="text-primary-600 font-bold text-2xl">{{ client.loyalty_discount || 0 }}%</p>
                        </div>
                    </div>
                </div>

                <!-- Coordonnées Tab -->
                <div v-if="activeTab === 'coordonnees'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-start space-x-3">
                            <PhoneIcon class="w-5 h-5 text-primary-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Téléphone</p>
                                <p class="text-gray-900 font-medium"><span v-if="client.phone">{{ client.phone }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <EnvelopeIcon class="w-5 h-5 text-primary-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Email</p>
                                <p class="text-gray-900 font-medium"><span v-if="client.email">{{ client.email }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <MapPinIcon class="w-5 h-5 text-primary-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Adresse</p>
                                <p class="text-gray-900 font-medium"><span v-if="client.address || client.adresse">{{ client.address || client.adresse }}</span><span v-else class="text-gray-400 italic">Non renseignée</span></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <BuildingOfficeIcon class="w-5 h-5 text-primary-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Ville</p>
                                <p class="text-gray-900 font-medium"><span v-if="client.city || client.ville">{{ client.city || client.ville }}</span><span v-else class="text-gray-400 italic">Non renseignée</span></p>
                            </div>
                        </div>
                    </div>

                    <div v-if="client.note_interne" class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <p class="text-gray-600 text-sm font-medium mb-2">Notes internes</p>
                        <p class="text-gray-900">{{ client.note_interne }}</p>
                    </div>
                </div>

                <!-- Documents Tab -->
                <div v-if="activeTab === 'documents'" class="space-y-6">
                    <!-- CIN Document -->
                    <div>
                        <p class="text-gray-700 font-medium mb-3">Carte d'Identité Nationale (CIN)</p>
                        <div v-if="client.documents?.cin" class="bg-white rounded border border-gray-300 p-4 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <DocumentIcon class="w-6 h-6 text-primary-600" />
                                <a :href="getFileUrl(client.documents.cin)" target="_blank" rel="noreferrer" class="text-primary-600 hover:text-primary-700 font-medium">
                                    {{ extractFileName(client.documents.cin) }}
                                </a>
                            </div>
                            <button @click="downloadFile(client.documents.cin)" class="text-gray-600 hover:text-gray-900 transition">
                                <ArrowDownTrayIcon class="w-5 h-5" />
                            </button>
                        </div>
                        <div v-else class="bg-white rounded border-2 border-dashed border-gray-300 p-6 text-center">
                            <DocumentIcon class="w-10 h-10 text-gray-400 mx-auto mb-2" />
                            <p class="text-gray-600">Aucun document CIN disponible</p>
                        </div>
                    </div>

                    <!-- Registre de Commerce -->
                    <div>
                        <p class="text-gray-700 font-medium mb-3">Registre de Commerce</p>
                        <div v-if="client.documents?.registre_commerce" class="bg-white rounded border border-gray-300 p-4 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <DocumentIcon class="w-6 h-6 text-primary-600" />
                                <a :href="getFileUrl(client.documents.registre_commerce)" target="_blank" rel="noreferrer" class="text-primary-600 hover:text-primary-700 font-medium">
                                    {{ extractFileName(client.documents.registre_commerce) }}
                                </a>
                            </div>
                            <button @click="downloadFile(client.documents.registre_commerce)" class="text-gray-600 hover:text-gray-900 transition">
                                <ArrowDownTrayIcon class="w-5 h-5" />
                            </button>
                        </div>
                        <div v-else class="bg-white rounded border-2 border-dashed border-gray-300 p-6 text-center">
                            <DocumentIcon class="w-10 h-10 text-gray-400 mx-auto mb-2" />
                            <p class="text-gray-600">Aucun registre de commerce disponible</p>
                        </div>
                    </div>

                    <!-- Attestation TVA -->
                    <div>
                        <p class="text-gray-700 font-medium mb-3">Attestation TVA</p>
                        <div v-if="client.documents?.attestation_tva" class="bg-white rounded border border-gray-300 p-4 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <DocumentIcon class="w-6 h-6 text-primary-600" />
                                <a :href="getFileUrl(client.documents.attestation_tva)" target="_blank" rel="noreferrer" class="text-primary-600 hover:text-primary-700 font-medium">
                                    {{ extractFileName(client.documents.attestation_tva) }}
                                </a>
                            </div>
                            <button @click="downloadFile(client.documents.attestation_tva)" class="text-gray-600 hover:text-gray-900 transition">
                                <ArrowDownTrayIcon class="w-5 h-5" />
                            </button>
                        </div>
                        <div v-else class="bg-white rounded border-2 border-dashed border-gray-300 p-6 text-center">
                            <DocumentIcon class="w-10 h-10 text-gray-400 mx-auto mb-2" />
                            <p class="text-gray-600">Aucune attestation TVA disponible</p>
                        </div>
                    </div>
                </div>

                <!-- Historique des Achats Tab -->
                <div v-if="activeTab === 'achats'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Total des achats</p>
                            <p class="text-gray-900 font-bold text-2xl">{{ formatCurrency(client.total_achats || 0) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Nombre d'achats</p>
                            <p class="text-gray-900 font-bold text-2xl">{{ client.nombre_commandes || 0 }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Moyenne par achat</p>
                            <p class="text-gray-900 font-bold text-2xl">{{ formatCurrency(client.nombre_commandes > 0 ? client.total_achats / client.nombre_commandes : 0) }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">N° Transaction</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Articles</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Montant</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="commande in client.commandes || []" :key="commande.id" class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(commande.date) }}</td>
                                    <td class="px-4 py-3 text-sm font-mono text-primary-600">{{ commande.numero }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ commande.items_count || 0 }} article(s)</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ formatCurrency(commande.total || 0) }}</td>
                                    <td class="px-4 py-3">
                                        <span :class="getCommandeStatusClass(commande.statut)" class="px-2 py-1 text-xs font-medium rounded-full">
                                            {{ commande.statut }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!client.commandes || client.commandes.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        Aucun achat enregistré
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white border border-gray-200 rounded-xl p-6 max-w-md w-full mx-4 shadow-lg">
                <h3 class="text-xl font-bold text-red-600 mb-3">Supprimer le client</h3>
                <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir supprimer <span class="font-bold">{{ client.name }}</span> ? Cette action est irréversible.</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 border border-gray-300 font-medium transition">
                        Annuler
                    </button>
                    <button @click="deleteClient" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useSettingsStore } from '../../stores/settings'
import {
    HomeIcon,
    ChevronRightIcon,
    UserCircleIcon,
    PencilIcon,
    TrashIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    BuildingOfficeIcon,
    DocumentIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const CUSTOMERS_STORAGE_KEY = 'pos_customers'
const SALES_STORAGE_KEY = 'pos_sales'

const client = ref({})
const showDeleteModal = ref(false)
const activeTab = ref('informations')

function loadCustomersFromStorage() {
    const stored = localStorage.getItem(CUSTOMERS_STORAGE_KEY)
    return stored ? JSON.parse(stored) : []
}

function loadSalesFromStorage() {
    const stored = localStorage.getItem(SALES_STORAGE_KEY)
    return stored ? JSON.parse(stored) : []
}

function formatDate(date) {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('fr-FR')
}

function resolveCustomerPhotoUrl(value) {
    const url = String(value || '').trim()
    if (!url) return ''
    if (
        url.startsWith('data:image/')
        || url.startsWith('blob:')
        || url.startsWith('http://')
        || url.startsWith('https://')
        || url.startsWith('//')
    ) {
        return url
    }
    if (url.startsWith('/')) {
        return `${window.location.origin}${url}`
    }
    if (url.startsWith('storage/')) {
        return `${window.location.origin}/${url}`
    }
    return `${window.location.origin}/storage/${url}`
}

function extractFileName(path) {
    if (!path) return 'document'
    if (typeof path === 'object') {
        return path.name || 'document'
    }
    return path.split('/').pop().replace(/\.pdf|\.doc|\.docx/i, '')
}

function downloadFile(url) {
    const source = typeof url === 'string' ? url : url?.url
    const filename = typeof url === 'object' ? url?.name || extractFileName(source) : extractFileName(source)
    if (source) {
        const link = document.createElement('a')
        link.href = source
        link.download = filename
        link.click()
    }
}

function getFileUrl(file) {
    if (!file) return ''
    return typeof file === 'string' ? file : file?.url || ''
}

function getStatusBadgeClass(status) {
    const classes = {
        'active': 'bg-green-100 text-green-800 border border-green-300',
        'inactive': 'bg-gray-100 text-gray-800 border border-gray-300',
        'suspended': 'bg-red-100 text-red-800 border border-red-300',
        'Actif': 'bg-green-100 text-green-800 border border-green-300',
        'Inactif': 'bg-gray-100 text-gray-800 border border-gray-300',
        'Suspendu': 'bg-red-100 text-red-800 border border-red-300'
    }
    return classes[status] || 'bg-gray-100 text-gray-800 border border-gray-300'
}

function getStatusLabel(status) {
    const labels = {
        'active': 'Actif',
        'inactive': 'Inactif',
        'suspended': 'Suspendu'
    }
    return labels[status] || status
}

function getCommandeStatusClass(statut) {
    const classes = {
        'Livrée': 'bg-green-100 text-green-800',
        'En cours': 'bg-blue-100 text-blue-800',
        'En attente': 'bg-yellow-100 text-yellow-800',
        'Annulée': 'bg-red-100 text-red-800'
    }
    return classes[statut] || 'bg-gray-100 text-gray-800'
}

function editClient() {
    router.push(`/customers/${route.params.id}/edit`)
}

function confirmDelete() {
    showDeleteModal.value = true
}

function deleteClient() {
    try {
        const storedCustomers = loadCustomersFromStorage()
        const filteredCustomers = storedCustomers.filter(c => String(c.id) !== String(route.params.id))
        localStorage.setItem(CUSTOMERS_STORAGE_KEY, JSON.stringify(filteredCustomers))
        router.push('/customers')
    } catch (error) {
        console.error('Error deleting customer:', error)
    }
}

function loadCustomerData() {
    const customerId = parseInt(route.params.id) || route.params.id
    const customers = loadCustomersFromStorage()
    const sales = loadSalesFromStorage()
    
    const foundCustomer = customers.find(c => String(c.id) === String(customerId) || String(c.client_id) === String(route.params.id))
    
    if (foundCustomer) {
        // Use the actual customer ID for matching, not the modified one
        const actualCustomerId = foundCustomer.id
        const customerSales = sales.filter(sale => String(sale.customer_id) === String(actualCustomerId))
        const totalSpent = customerSales.reduce((sum, sale) => sum + (sale.total || 0), 0)
        
        console.log('DEBUG CustomerDetail:', { customerId, actualCustomerId, foundCustomerName: foundCustomer.name, salesCount: customerSales.length, totalSpent, sales: customerSales })
        
        client.value = {
            ...foundCustomer,
            id: actualCustomerId,
            name: foundCustomer.name || `${foundCustomer.nom || ''} ${foundCustomer.prenom || ''}`.trim(),
            total_achats: totalSpent,
            nombre_commandes: customerSales.length,
            commandes: customerSales.map((sale, idx) => ({
                id: sale.id || idx + 1,
                numero: String(idx + 1).padStart(5, '0'),
                date: sale.date,
                type: 'Facture',
                items_count: sale.items_count || 0,
                total: sale.total,
                statut: sale.status || 'Livrée'
            }))
        }
    } else {
        client.value = {}
        console.log('DEBUG CustomerDetail: Customer not found', { customerId, availableCustomerIds: customers.map(c => c.id) })
    }
}

onMounted(() => {
    loadCustomerData()
})

watch(() => route.params.id, () => {
    loadCustomerData()
})
</script>
