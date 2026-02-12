<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 p-6">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm mb-6">
            <router-link to="/dashboard" class="text-primary-400 hover:text-primary-300 flex items-center">
                <HomeIcon class="w-4 h-4 mr-1" />
                Accueil
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-500" />
            <router-link to="/fournisseurs" class="text-primary-400 hover:text-primary-300">
                Fournisseurs
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-500" />
            <span class="text-gray-400">Fiche fournisseur</span>
        </div>

        <!-- Main Card -->
        <div class="bg-gray-800 bg-opacity-80 border border-gray-700 rounded-xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <BuildingOfficeIcon class="w-8 h-8 text-primary-400" />
                    <h1 class="text-2xl font-bold text-primary-400">Fiche fournisseur</h1>
                </div>
                <div class="flex space-x-3">
                    <button @click="editFournisseur" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                        <PencilIcon class="w-5 h-5 mr-2" />
                        Éditer
                    </button>
                    <button @click="confirmDelete" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 flex items-center">
                        <TrashIcon class="w-5 h-5 mr-2" />
                        Supprimer
                    </button>
                </div>
            </div>

            <!-- Fournisseur Profile Section -->
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-start space-x-6">
                    <!-- Logo -->
                    <div class="w-32 h-32 bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden border-2 border-gray-600">
                        <img v-if="fournisseur.logo" :src="fournisseur.logo" :alt="fournisseur.name" class="w-full h-full object-cover" />
                        <BuildingOfficeIcon v-else class="w-16 h-16 text-gray-500" />
                    </div>
                    
                    <!-- Fournisseur Info -->
                    <div class="flex-1 space-y-3">
                        <div>
                            <h2 class="text-3xl font-bold text-primary-300">{{ fournisseur.name }}</h2>
                            <p class="text-gray-400 mt-1">ID Fournisseur : <span class="text-primary-400 font-mono">#{{ fournisseur.id }}</span></p>
                        </div>
                        
                        <div class="space-y-1">
                            <p class="text-gray-400">Raison sociale : <span class="text-white">{{ fournisseur.raison_sociale || 'N/A' }}</span></p>
                            <p class="text-gray-400">Type : <span class="text-white">{{ fournisseur.type || 'N/A' }}</span></p>
                        </div>

                        <div class="flex items-center space-x-4">
                            <span :class="getStatusBadgeClass(fournisseur.statut)" class="px-3 py-1 text-sm font-medium rounded-full">
                                {{ fournisseur.statut }}
                            </span>
                            <span v-if="fournisseur.prefere" class="px-3 py-1 bg-yellow-900 bg-opacity-50 text-yellow-400 text-sm font-medium rounded-full border border-yellow-700">
                                ⭐ Fournisseur Préféré
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coordonnées Section -->
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-xl font-bold text-primary-400 mb-4">Coordonnées</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center space-x-3">
                        <PhoneIcon class="w-5 h-5 text-primary-400 flex-shrink-0" />
                        <div>
                            <p class="text-gray-400 text-sm">Téléphone:</p>
                            <p class="text-white font-medium">{{ fournisseur.telephone || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <EnvelopeIcon class="w-5 h-5 text-primary-400 flex-shrink-0" />
                        <div>
                            <p class="text-gray-400 text-sm">Email:</p>
                            <p class="text-white font-medium">{{ fournisseur.email || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <MapPinIcon class="w-5 h-5 text-primary-400 flex-shrink-0" />
                        <div>
                            <p class="text-gray-400 text-sm">Adresse:</p>
                            <p class="text-white font-medium">{{ fournisseur.adresse || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <BuildingOfficeIcon class="w-5 h-5 text-primary-400 flex-shrink-0" />
                        <div>
                            <p class="text-gray-400 text-sm">Ville:</p>
                            <p class="text-white font-medium">{{ fournisseur.ville || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <UserIcon class="w-5 h-5 text-primary-400 flex-shrink-0" />
                        <div>
                            <p class="text-gray-400 text-sm">Contact principal:</p>
                            <p class="text-white font-medium">{{ fournisseur.contact_principal || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <GlobeAltIcon class="w-5 h-5 text-primary-400 flex-shrink-0" />
                        <div>
                            <p class="text-gray-400 text-sm">Site web:</p>
                            <p class="text-white font-medium">{{ fournisseur.site_web || 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations complémentaires -->
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-xl font-bold text-primary-400 mb-4">Informations complémentaires</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4 border border-gray-600">
                        <p class="text-gray-400 text-sm mb-1">ICE</p>
                        <p class="text-white font-medium text-lg">{{ fournisseur.ice || 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4 border border-gray-600">
                        <p class="text-gray-400 text-sm mb-1">Reg. Commercial</p>
                        <p class="text-white font-medium text-lg">{{ fournisseur.reg_commercial || 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4 border border-gray-600">
                        <p class="text-gray-400 text-sm mb-1">TVA</p>
                        <p class="text-white font-medium text-lg">{{ fournisseur.tva || 'N/A' }}</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-700 bg-opacity-30 rounded-lg p-4">
                        <p class="text-gray-400 text-sm mb-1">Total achats</p>
                        <p class="text-primary-300 font-bold text-2xl">{{ formatCurrency(fournisseur.total_achats) }}</p>
                    </div>
                    <div class="bg-gray-700 bg-opacity-30 rounded-lg p-4">
                        <p class="text-gray-400 text-sm mb-1">Nombre de commandes</p>
                        <p class="text-primary-300 font-bold text-2xl">{{ fournisseur.nombre_commandes || 0 }}</p>
                    </div>
                    <div class="bg-gray-700 bg-opacity-30 rounded-lg p-4">
                        <p class="text-gray-400 text-sm mb-1">Délai moyen livraison</p>
                        <p class="text-primary-300 font-bold text-2xl">{{ fournisseur.delai_moyen || 0 }} jours</p>
                    </div>
                </div>
            </div>

            <!-- Historique des commandes -->
            <div class="p-6">
                <h3 class="text-xl font-bold text-primary-400 mb-4 flex items-center">
                    <ClipboardDocumentListIcon class="w-6 h-6 mr-2" />
                    Historique des commandes
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">N° Commande</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Montant (Dhs)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <tr v-for="commande in fournisseur.commandes" :key="commande.id" class="hover:bg-gray-700 hover:bg-opacity-30">
                                <td class="px-4 py-3 text-sm font-medium text-primary-300">{{ commande.numero }}</td>
                                <td class="px-4 py-3 text-sm text-gray-300">{{ formatDate(commande.date) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-300">{{ commande.type }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-white">{{ formatCurrency(commande.montant) }}</td>
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
                <div v-if="fournisseur.commandes && fournisseur.commandes.length > 0" class="mt-4 pt-4 border-t border-gray-700 flex justify-between items-center">
                    <span class="text-gray-400">Total des achats:</span>
                    <span class="text-primary-300 font-bold text-xl">{{ formatCurrency(fournisseur.total_achats) }}</span>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
                <h3 class="text-xl font-bold text-red-400 mb-3">Supprimer le fournisseur</h3>
                <p class="text-gray-300 mb-6">Êtes-vous sûr de vouloir supprimer le fournisseur <span class="font-bold text-white">{{ fournisseur.name }}</span> ? Cette action est irréversible.</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 border border-gray-600">
                        Annuler
                    </button>
                    <button @click="deleteFournisseur" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
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
    ClipboardDocumentListIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const fournisseur = ref({})
const showDeleteModal = ref(false)

function formatDate(date) {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('fr-FR')
}

function getStatusBadgeClass(statut) {
    const classes = {
        'Actif': 'bg-green-900 bg-opacity-50 text-green-400 border border-green-700',
        'Inactif': 'bg-gray-700 bg-opacity-50 text-gray-400 border border-gray-600',
        'Suspendu': 'bg-red-900 bg-opacity-50 text-red-400 border border-red-700'
    }
    return classes[statut] || 'bg-gray-700 bg-opacity-50 text-gray-400 border border-gray-600'
}

function getCommandeStatusClass(statut) {
    const classes = {
        'Reçue': 'bg-green-900 bg-opacity-50 text-green-400',
        'En cours': 'bg-blue-900 bg-opacity-50 text-blue-400',
        'Envoyée': 'bg-yellow-900 bg-opacity-50 text-yellow-400',
        'Annulée': 'bg-red-900 bg-opacity-50 text-red-400'
    }
    return classes[statut] || 'bg-gray-700 bg-opacity-50 text-gray-400'
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
    // Demo data - In production, fetch from API using route.params.id
    fournisseur.value = {
        id: 'F0001',
        name: 'Electronix SARL',
        logo: null,
        raison_sociale: 'Electronix SARL',
        type: 'Grossiste',
        telephone: '+212 522-123456',
        email: 'contact@electronix.ma',
        adresse: '23, Rue de l\'Electronique',
        ville: 'Casablanca',
        contact_principal: 'M. Hassan Benali',
        site_web: 'www.electronix.ma',
        ice: '001234567890001',
        reg_commercial: 'RC-123456',
        tva: 'TVA-123456',
        statut: 'Actif',
        prefere: true,
        total_achats: 250000.00,
        nombre_commandes: 15,
        delai_moyen: 7,
        commandes: [
            { id: 1, numero: 'BC-2026-003', date: '2026-02-08', type: 'Bon de commande', montant: 45000.00, statut: 'Envoyée' },
            { id: 2, numero: 'BC-2026-001', date: '2026-02-01', type: 'Bon de commande', montant: 25000.00, statut: 'Reçue' },
            { id: 3, numero: 'FF-2026-001', date: '2026-02-01', type: 'Facture', montant: 30000.00, statut: 'Reçue' },
            { id: 4, numero: 'BC-2025-125', date: '2025-12-15', type: 'Bon de commande', montant: 38000.00, statut: 'Reçue' }
        ]
    }
})
</script>
