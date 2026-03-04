<template>
    <div class="min-h-screen bg-gray-50 p-6">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm mb-6">
            <router-link to="/dashboard" class="text-primary-600 hover:text-primary-700 flex items-center">
                <HomeIcon class="w-4 h-4 mr-1" />
                Accueil
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-400" />
            <router-link to="/fournisseurs" class="text-primary-600 hover:text-primary-700">
                Fournisseurs
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-400" />
            <span class="text-gray-600">Fiche fournisseur</span>
        </div>

        <!-- Main Card -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary-50 to-primary-100 border-b border-gray-200 px-6 py-6 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <BuildingOfficeIcon class="w-8 h-8 text-primary-600" />
                    <h1 class="text-2xl font-bold text-gray-900">{{ fournisseur.name || 'Fournisseur' }}</h1>
                </div>
                <div class="flex space-x-3">
                    <button @click="editFournisseur" class="px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition flex items-center">
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
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-start space-x-6">
                    <!-- Logo -->
                    <div class="w-40 h-40 bg-gradient-to-br from-primary-50 to-primary-100 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden border-2 border-primary-200 shadow-sm">
                        <img v-if="fournisseur.logo || fournisseur.photo_url" :src="getSupplierPhotoUrl(fournisseur)" :alt="fournisseur.name" class="w-full h-full object-cover" loading="lazy" />
                        <BuildingOfficeIcon v-else class="w-20 h-20 text-primary-400" />
                    </div>
                    
                    <!-- Info -->
                    <div class="flex-1 space-y-4">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-900">{{ fournisseur.name }}</h2>
                            <p class="text-gray-600 mt-1">ID : <span class="font-mono text-gray-900">#{{ fournisseur.id }}</span></p>
                        </div>
                        
                        <div class="space-y-2">
                            <p class="text-gray-700"><span class="font-medium">Raison sociale :</span> <span v-if="fournisseur.raison_sociale" class="text-gray-900">{{ fournisseur.raison_sociale }}</span><span v-else class="text-gray-400 italic">Non renseignée</span></p>
                            <p class="text-gray-700"><span class="font-medium">Type :</span> <span v-if="fournisseur.type" class="text-gray-900">{{ fournisseur.type }}</span><span v-else class="text-gray-400 italic">Non spécifié</span></p>
                        </div>

                        <div class="flex items-center space-x-3">
                            <span :class="getStatusBadgeClass(fournisseur.statut)" class="px-3 py-1 text-sm font-medium rounded-full">
                                {{ fournisseur.statut }}
                            </span>
                            <span v-if="fournisseur.prefere" class="px-3 py-1 bg-yellow-100 text-yellow-700 text-sm font-medium rounded-full border border-yellow-300">
                                ⭐ Fournisseur Préféré
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
                        @click="activeTab = 'rib'"
                        :class="activeTab === 'rib' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-600 hover:text-gray-900'"
                        class="px-6 py-4 font-medium transition"
                    >
                        Informations Bancaires
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
                            <p class="text-gray-900 font-semibold text-lg"><span v-if="fournisseur.ice">{{ fournisseur.ice }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Reg. Commercial</p>
                            <p class="text-gray-900 font-semibold text-lg"><span v-if="fournisseur.reg_commercial">{{ fournisseur.reg_commercial }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">TVA</p>
                            <p class="text-gray-900 font-semibold text-lg"><span v-if="fournisseur.tva">{{ fournisseur.tva }}</span><span v-else class="text-gray-400 italic">Non renseignée</span></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Total achats</p>
                            <p class="text-primary-600 font-bold text-2xl">{{ formatCurrency(fournisseur.total_purchases || 0) }}</p>
                        </div>
                        <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Nombre de commandes</p>
                            <p class="text-primary-600 font-bold text-2xl">{{ fournisseur.orders_count || 0 }}</p>
                        </div>
                        <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                            <p class="text-gray-600 text-sm mb-1 font-medium">Délai moyen</p>
                            <p class="text-primary-600 font-bold text-2xl">{{ fournisseur.delai_moyen || 0 }} jours</p>
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
                                <p class="text-gray-900 font-medium"><span v-if="fournisseur.telephone">{{ fournisseur.telephone }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <EnvelopeIcon class="w-5 h-5 text-primary-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Email</p>
                                <p class="text-gray-900 font-medium"><span v-if="fournisseur.email">{{ fournisseur.email }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <MapPinIcon class="w-5 h-5 text-primary-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Adresse</p>
                                <p class="text-gray-900 font-medium"><span v-if="fournisseur.adresse">{{ fournisseur.adresse }}</span><span v-else class="text-gray-400 italic">Non renseignée</span></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <BuildingOfficeIcon class="w-5 h-5 text-primary-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Ville</p>
                                <p class="text-gray-900 font-medium"><span v-if="fournisseur.ville">{{ fournisseur.ville }}</span><span v-else class="text-gray-400 italic">Non renseignée</span></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <UserIcon class="w-5 h-5 text-primary-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Contact principal</p>
                                <p class="text-gray-900 font-medium"><span v-if="fournisseur.contact_principal">{{ fournisseur.contact_principal }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <GlobeAltIcon class="w-5 h-5 text-primary-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Site web</p>
                                <p class="text-gray-900 font-medium"><span v-if="fournisseur.site_web">{{ fournisseur.site_web }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIB Tab -->
                <div v-if="activeTab === 'rib'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm font-medium mb-2">Banque</p>
                            <p class="text-gray-900 font-semibold text-lg"><span v-if="fournisseur.banque">{{ fournisseur.banque }}</span><span v-else class="text-gray-400 italic">Non renseignée</span></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-600 text-sm font-medium mb-2">IBAN</p>
                            <p class="text-gray-900 font-semibold text-lg"><span v-if="fournisseur.iban">{{ fournisseur.iban }}</span><span v-else class="text-gray-400 italic">Non renseigné</span></p>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <p class="text-gray-700 font-medium mb-4">Fichier RIB</p>
                        <div v-if="fournisseur.rib_file" class="bg-white rounded border border-gray-300 p-4 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <DocumentIcon class="w-6 h-6 text-primary-600" />
                                <a :href="getFileUrl(fournisseur.rib_file)" target="_blank" rel="noreferrer" class="text-primary-600 hover:text-primary-700 font-medium">
                                    {{ extractFileName(fournisseur.rib_file) }}
                                </a>
                            </div>
                            <button @click="downloadFile(fournisseur.rib_file)" class="text-gray-600 hover:text-gray-900">
                                <ArrowDownTrayIcon class="w-5 h-5" />
                            </button>
                        </div>
                        <div v-else class="bg-white rounded border-2 border-dashed border-gray-300 p-6 text-center">
                            <DocumentIcon class="w-10 h-10 text-gray-400 mx-auto mb-2" />
                            <p class="text-gray-600">Aucun fichier RIB disponible</p>
                        </div>
                        <div v-if="fournisseur.contract_file" class="mt-4 bg-white rounded border border-gray-300 p-4 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <DocumentIcon class="w-6 h-6 text-primary-600" />
                                <a :href="getFileUrl(fournisseur.contract_file)" target="_blank" rel="noreferrer" class="text-primary-600 hover:text-primary-700 font-medium">
                                    {{ extractFileName(fournisseur.contract_file) }}
                                </a>
                            </div>
                            <button @click="downloadFile(fournisseur.contract_file)" class="text-gray-600 hover:text-gray-900">
                                <ArrowDownTrayIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders History -->
            <div class="p-6 border-t border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <ClipboardDocumentListIcon class="w-5 h-5 mr-2 text-primary-600" />
                    Historique des commandes
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">N° Commande</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Montant (Dhs)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="commande in fournisseur.commandes" :key="commande.id" class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-sm font-medium text-primary-600">{{ commande.numero }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(commande.date) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ commande.type }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ formatCurrency(commande.montant_total || 0) }}</td>
                                <td class="px-4 py-3">
                                    <span :class="getCommandeStatusClass(commande.statut)" class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ commande.statut }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!fournisseur.commandes || fournisseur.commandes.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    Aucune commande pour ce fournisseur
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="fournisseur.commandes && fournisseur.commandes.length > 0" class="mt-4 pt-4 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-gray-700 font-medium">Total des achats:</span>
                    <span class="text-primary-600 font-bold text-xl">{{ formatCurrency(fournisseur.total_achats) }}</span>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white border border-gray-200 rounded-xl p-6 max-w-md w-full mx-4 shadow-lg">
                <h3 class="text-xl font-bold text-red-600 mb-3">Supprimer le fournisseur</h3>
                <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir supprimer <span class="font-bold">{{ fournisseur.name }}</span> ? Cette action est irréversible.</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 border border-gray-300 font-medium transition">
                        Annuler
                    </button>
                    <button @click="deleteFournisseur" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition">
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
    BuildingOfficeIcon,
    PencilIcon,
    TrashIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    UserIcon,
    GlobeAltIcon,
    ClipboardDocumentListIcon,
    DocumentIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)
const STORAGE_KEY = 'pos_fournisseurs'

const fournisseur = ref({})
const showDeleteModal = ref(false)
const activeTab = ref('informations')

const defaultFournisseur = () => ({
    id: null,
    name: 'Fournisseur',
    logo: null,
    raison_sociale: '',
    type: '',
    telephone: '',
    email: '',
    adresse: '',
    ville: '',
    contact_principal: '',
    site_web: '',
    ice: '',
    reg_commercial: '',
    tva: '',
    banque: '',
    iban: '',
    rib_file: null,
    contract_file: null,
    statut: 'Actif',
    prefere: false,
    total_achats: 0,
    nombre_commandes: 0,
    delai_moyen: 0,
    commandes: []
})

const readStoredFournisseurs = () => {
    try {
        const stored = localStorage.getItem(STORAGE_KEY)
        return stored ? JSON.parse(stored) : []
    } catch (error) {
        console.error('Unable to parse stored fournisseurs:', error)
        return []
    }
}

const loadFournisseur = () => {
    const suppliers = readStoredFournisseurs()
    const match = suppliers.find(s => String(s.id) === String(route.params.id))
    fournisseur.value = match ? { ...match } : defaultFournisseur()
    
    // Load commandes history from BonCommandeList localStorage
    if (fournisseur.value.id) {
        try {
            const storedCommandes = localStorage.getItem('pos_bon_commandes')
            if (storedCommandes) {
                const commandes = JSON.parse(storedCommandes)
                // Filter commandes for this supplier
                const filteredCommandes = commandes.filter(c => c.fournisseur_id === fournisseur.value.id)
                fournisseur.value.commandes = filteredCommandes
                
                // Calculate total_achats from commandes
                fournisseur.value.total_achats = filteredCommandes.reduce((sum, c) => sum + (c.montant_total || 0), 0)
                
                // Calculate average delivery delay if dates are available
                if (filteredCommandes.length > 0) {
                    const delays = filteredCommandes
                        .map(c => {
                            if (c.date && c.date_livraison_prevue) {
                                const orderDate = new Date(c.date).getTime()
                                const deliveryDate = new Date(c.date_livraison_prevue).getTime()
                                return Math.ceil((deliveryDate - orderDate) / (1000 * 60 * 60 * 24))
                            }
                            return null
                        })
                        .filter(d => d !== null && d > 0)
                    
                    if (delays.length > 0) {
                        fournisseur.value.delai_moyen = Math.round(delays.reduce((a, b) => a + b, 0) / delays.length)
                    }
                }
            }
            
            // Load receptions to calculate total_purchases and orders_count
            const storedReceptions = localStorage.getItem('pos_receptions')
            if (storedReceptions) {
                const receptions = JSON.parse(storedReceptions)
                const filteredReceptions = receptions.filter(r => r.fournisseur_id === fournisseur.value.id)
                fournisseur.value.total_purchases = filteredReceptions.reduce((sum, r) => sum + (r.montant_total || 0), 0)
                fournisseur.value.orders_count = filteredReceptions.length
            }
        } catch (error) {
            console.error('Error loading fournisseur data:', error)
        }
    }
}

function formatDate(date) {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('fr-FR')
}

function getStatusBadgeClass(statut) {
    const classes = {
        'Actif': 'bg-green-100 text-green-800 border border-green-300',
        'Inactif': 'bg-gray-100 text-gray-800 border border-gray-300',
        'Suspendu': 'bg-red-100 text-red-800 border border-red-300'
    }
    return classes[statut] || 'bg-gray-100 text-gray-800 border border-gray-300'
}

function isDataUrl(url) {
    return typeof url === 'string' && (url.startsWith('data:') || url.startsWith('blob:'))
}

function getSupplierPhotoUrl(fournisseur) {
    const url = fournisseur?.logo || fournisseur?.photo_url
    if (!url) {
        return ''
    }
    if (isDataUrl(url)) {
        return url
    }
    const cacheKey = fournisseur.photo_cache_key || 0
    const separator = url.includes('?') ? '&' : '?'
    return `${url}${separator}t=${cacheKey}`
}

function getCommandeStatusClass(statut) {
    const classes = {
        'Reçue': 'bg-green-100 text-green-800',
        'En cours': 'bg-blue-100 text-blue-800',
        'Envoyée': 'bg-yellow-100 text-yellow-800',
        'Annulée': 'bg-red-100 text-red-800'
    }
    return classes[statut] || 'bg-gray-100 text-gray-800'
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

function editFournisseur() {
    router.push(`/fournisseurs/${route.params.id}/edit`)
}

function confirmDelete() {
    showDeleteModal.value = true
}

function deleteFournisseur() {
    // Delete logic here
    showDeleteModal.value = false
    router.push('/fournisseurs')
}

onMounted(() => {
    loadFournisseur()
})

watch(() => route.params.id, () => {
    loadFournisseur()
})
</script>
