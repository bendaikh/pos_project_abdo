<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 p-6">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm mb-6">
            <router-link to="/dashboard" class="text-primary-400 hover:text-primary-300 flex items-center">
                <HomeIcon class="w-4 h-4 mr-1" />
                Accueil
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-500" />
            <router-link to="/customers" class="text-primary-400 hover:text-primary-300">
                Clients
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-500" />
            <span class="text-gray-400">Fiche client</span>
        </div>

        <!-- Main Card -->
        <div class="bg-gray-800 bg-opacity-80 border border-gray-700 rounded-xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <UserIcon class="w-8 h-8 text-primary-400" />
                    <h1 class="text-2xl font-bold text-primary-400">Fiche client</h1>
                </div>
                <div class="flex space-x-3">
                    <button @click="editClient" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                        <PencilIcon class="w-5 h-5 mr-2" />
                        Éditer
                    </button>
                    <button @click="confirmDelete" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 flex items-center">
                        <TrashIcon class="w-5 h-5 mr-2" />
                        Supprimer
                    </button>
                </div>
            </div>

            <!-- Client Profile Section -->
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-start space-x-6">
                    <!-- Avatar -->
                    <div class="w-32 h-32 bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden border-2 border-gray-600">
                        <img v-if="client.avatar" :src="client.avatar" :alt="client.name" class="w-full h-full object-cover" />
                        <UserIcon v-else class="w-16 h-16 text-gray-500" />
                    </div>
                    
                    <!-- Client Info -->
                    <div class="flex-1 space-y-3">
                        <div>
                            <h2 class="text-3xl font-bold text-primary-300">{{ client.name }}</h2>
                            <p class="text-gray-400 mt-1">ID Client : <span class="text-primary-400 font-mono">#{{ client.id }}</span></p>
                        </div>
                        
                        <div v-if="client.entreprise" class="space-y-1">
                            <p class="text-gray-400">Entreprise : <span class="text-white">{{ client.entreprise }}</span></p>
                            <p class="text-gray-400">Raison sociale : <span class="text-white">{{ client.raison_sociale || 'N/A' }}</span></p>
                        </div>

                        <div class="flex items-center space-x-4">
                            <span :class="getStatusBadgeClass(client.statut)" class="px-3 py-1 text-sm font-medium rounded-full">
                                {{ client.statut }}
                            </span>
                            <span v-if="client.fidele" class="px-3 py-1 bg-yellow-900 bg-opacity-50 text-yellow-400 text-sm font-medium rounded-full border border-yellow-700">
                                ⭐ Client Fidèle
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
                            <p class="text-white font-medium">{{ client.telephone || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <EnvelopeIcon class="w-5 h-5 text-primary-400 flex-shrink-0" />
                        <div>
                            <p class="text-gray-400 text-sm">Email:</p>
                            <p class="text-white font-medium">{{ client.email || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <MapPinIcon class="w-5 h-5 text-primary-400 flex-shrink-0" />
                        <div>
                            <p class="text-gray-400 text-sm">Adresse:</p>
                            <p class="text-white font-medium">{{ client.adresse || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <BuildingOfficeIcon class="w-5 h-5 text-primary-400 flex-shrink-0" />
                        <div>
                            <p class="text-gray-400 text-sm">Ville:</p>
                            <p class="text-white font-medium">{{ client.ville || 'N/A' }}</p>
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
                        <p class="text-white font-medium text-lg">{{ client.ice || 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4 border border-gray-600">
                        <p class="text-gray-400 text-sm mb-1">Reg. Commercial</p>
                        <p class="text-white font-medium text-lg">{{ client.reg_commercial || 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4 border border-gray-600">
                        <p class="text-gray-400 text-sm mb-1">TVA</p>
                        <p class="text-white font-medium text-lg">{{ client.tva || 'N/A' }}</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-700 bg-opacity-30 rounded-lg p-4">
                        <p class="text-gray-400 text-sm mb-1">Total des achats</p>
                        <p class="text-primary-300 font-bold text-2xl">{{ formatCurrency(client.total_achats) }}</p>
                    </div>
                    <div class="bg-gray-700 bg-opacity-30 rounded-lg p-4">
                        <p class="text-gray-400 text-sm mb-1">Nombre de commandes</p>
                        <p class="text-primary-300 font-bold text-2xl">{{ client.nombre_commandes || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-xl font-bold text-primary-400 mb-4 flex items-center justify-between">
                    <span>Notes</span>
                    <button @click="addNote" class="text-sm px-3 py-1 bg-primary-600 text-gray-900 rounded-lg hover:bg-primary-500">
                        <PlusIcon class="w-4 h-4 inline mr-1" />
                        Ajouter
                    </button>
                </h3>
                <div class="space-y-3">
                    <div v-for="note in client.notes" :key="note.id" class="bg-gray-700 bg-opacity-30 rounded-lg p-4 border border-gray-600">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <DocumentTextIcon class="w-5 h-5 text-primary-400" />
                                <span class="text-primary-300 text-sm">{{ formatDate(note.date) }}</span>
                            </div>
                            <div class="flex space-x-2">
                                <button @click="editNote(note)" class="text-gray-400 hover:text-primary-400">
                                    <PencilIcon class="w-4 h-4" />
                                </button>
                                <button @click="deleteNote(note)" class="text-gray-400 hover:text-red-400">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <p class="text-gray-300 text-sm">{{ note.content }}</p>
                    </div>
                    <div v-if="!client.notes || client.notes.length === 0" class="text-center py-8 text-gray-500">
                        Aucune note disponible
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
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Total (Dhs)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <tr v-for="commande in client.commandes" :key="commande.id" class="hover:bg-gray-700 hover:bg-opacity-30">
                                <td class="px-4 py-3 text-sm font-medium text-primary-300">#{{ commande.numero }}</td>
                                <td class="px-4 py-3 text-sm text-gray-300">{{ formatDate(commande.date) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-300">{{ commande.type }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-white">{{ formatCurrency(commande.total) }}</td>
                                <td class="px-4 py-3">
                                    <span :class="getCommandeStatusClass(commande.statut)" class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ commande.statut }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!client.commandes || client.commandes.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    Aucune commande pour ce client
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="client.commandes && client.commandes.length > 0" class="mt-4 pt-4 border-t border-gray-700 flex justify-between items-center">
                    <span class="text-gray-400">Total des achats:</span>
                    <span class="text-primary-300 font-bold text-xl">{{ formatCurrency(client.total_achats) }}</span>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
                <h3 class="text-xl font-bold text-red-400 mb-3">Supprimer le client</h3>
                <p class="text-gray-300 mb-6">Êtes-vous sûr de vouloir supprimer le client <span class="font-bold text-white">{{ client.name }}</span> ? Cette action est irréversible.</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 border border-gray-600">
                        Annuler
                    </button>
                    <button @click="deleteClient" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
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
    UserIcon,
    PencilIcon,
    TrashIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
    ClipboardDocumentListIcon,
    PlusIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const client = ref({})
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
        'Livrée': 'bg-green-900 bg-opacity-50 text-green-400',
        'En cours': 'bg-blue-900 bg-opacity-50 text-blue-400',
        'En attente': 'bg-yellow-900 bg-opacity-50 text-yellow-400',
        'Annulée': 'bg-red-900 bg-opacity-50 text-red-400'
    }
    return classes[statut] || 'bg-gray-700 bg-opacity-50 text-gray-400'
}

function editClient() {
    router.push(`/customers/${route.params.id}/edit`)
}

function confirmDelete() {
    showDeleteModal.value = true
}

function deleteClient() {
    // Delete logic here
    showDeleteModal.value = false
    router.push('/customers')
}

function addNote() {
    alert('Ajouter une note')
}

function editNote(note) {
    alert('Éditer la note: ' + note.id)
}

function deleteNote(note) {
    const index = client.value.notes.findIndex(n => n.id === note.id)
    if (index > -1) {
        client.value.notes.splice(index, 1)
    }
}

onMounted(() => {
    // Demo data - In production, fetch from API using route.params.id
    client.value = {
        id: 'C00054',
        name: 'Ahmed Rahmani',
        avatar: null,
        telephone: '+212 665-543210',
        email: 'ahmed.rahmani@email.com',
        entreprise: 'ElectroComp SARL',
        raison_sociale: 'ElectroComp SARL',
        adresse: '56, Rue des Technologies, Casablanca',
        ville: 'Casablanca',
        ice: '87654321',
        reg_commercial: '87654321',
        tva: '1234567890',
        statut: 'Actif',
        fidele: true,
        total_achats: 119150.00,
        nombre_commandes: 8,
        notes: [
            { id: 1, date: '2024-03-05', content: 'Livraison partielle effectuée. Casque sans fil manquant, à recevoir au plus vite selon Ahmed Rahmani.' },
            { id: 2, date: '2024-02-15', content: 'Client préfère les paiements par virement bancaire.' }
        ],
        commandes: [
            { id: 1, numero: '00322', date: '2024-03-14', type: 'Facture', total: 3990.00, statut: 'Livrée' },
            { id: 2, numero: '00316', date: '2024-03-05', type: 'Devis', total: 2720.00, statut: 'En cours' },
            { id: 3, numero: '00305', date: '2024-02-28', type: 'Bon de livraison', total: 5205.00, statut: 'Livrée' },
            { id: 4, numero: '00289', date: '2024-02-15', type: 'Facture', total: 8950.00, statut: 'Livrée' },
            { id: 5, numero: '00267', date: '2024-02-01', type: 'Facture', total: 12400.00, statut: 'Livrée' }
        ]
    }
})
</script>
