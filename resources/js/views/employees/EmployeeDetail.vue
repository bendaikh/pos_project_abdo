<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 p-6">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm mb-6">
            <router-link to="/dashboard" class="text-primary-400 hover:text-primary-300 flex items-center">
                <HomeIcon class="w-4 h-4 mr-1" />
                Accueil
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-500" />
            <router-link to="/employees" class="text-primary-400 hover:text-primary-300">
                Employés
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-500" />
            <span class="text-gray-400">Fiche employé</span>
        </div>

        <!-- Main Card -->
        <div class="bg-gray-800 bg-opacity-80 border border-gray-700 rounded-xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <UsersIcon class="w-8 h-8 text-primary-400" />
                    <h1 class="text-2xl font-bold text-primary-400">Fiche employé</h1>
                </div>
                <div class="flex space-x-3">
                    <button @click="editEmployee" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                        <PencilIcon class="w-5 h-5 mr-2" />
                        Éditer
                    </button>
                    <button @click="confirmDelete" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 flex items-center">
                        <TrashIcon class="w-5 h-5 mr-2" />
                        Supprimer
                    </button>
                </div>
            </div>

            <!-- Employee Profile Section -->
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-start space-x-6">
                    <!-- Avatar -->
                    <div class="w-32 h-32 bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden border-2 border-gray-600">
                        <img v-if="employee.avatar" :src="employee.avatar" :alt="employee.name" class="w-full h-full object-cover" />
                        <UserCircleIcon v-else class="w-20 h-20 text-gray-500" />
                    </div>
                    
                    <!-- Employee Info -->
                    <div class="flex-1 space-y-3">
                        <div>
                            <h2 class="text-3xl font-bold text-primary-300">{{ employee.prenom }} {{ employee.nom }}</h2>
                            <p class="text-gray-400 mt-1">ID Employé : <span class="text-primary-400 font-mono">#{{ employee.id }}</span></p>
                        </div>
                        
                        <div class="space-y-1">
                            <p class="text-gray-400">Rôle : <span class="text-white font-medium">{{ employee.role }}</span></p>
                            <p class="text-gray-400">Raison sociale : <span class="text-white">{{ employee.raison_sociale || 'N/A' }}</span></p>
                        </div>

                        <div class="flex items-center space-x-4">
                            <span :class="getStatusBadgeClass(employee.statut)" class="px-3 py-1 text-sm font-medium rounded-full">
                                {{ employee.statut }}
                            </span>
                            <span v-if="employee.type_contrat" class="px-3 py-1 bg-blue-900 bg-opacity-50 text-blue-400 text-sm font-medium rounded-full border border-blue-700">
                                {{ employee.type_contrat }}
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
                            <p class="text-white font-medium">{{ employee.telephone || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <EnvelopeIcon class="w-5 h-5 text-primary-400 flex-shrink-0" />
                        <div>
                            <p class="text-gray-400 text-sm">Email:</p>
                            <p class="text-white font-medium">{{ employee.email || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <MapPinIcon class="w-5 h-5 text-primary-400 flex-shrink-0" />
                        <div>
                            <p class="text-gray-400 text-sm">Adresse:</p>
                            <p class="text-white font-medium">{{ employee.adresse || 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <IdentificationIcon class="w-5 h-5 text-primary-400 flex-shrink-0" />
                        <div>
                            <p class="text-gray-400 text-sm">CIN:</p>
                            <p class="text-white font-medium">{{ employee.cin || 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations complémentaires -->
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-xl font-bold text-primary-400 mb-4">Informations complémentaires</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="bg-gray-700 bg-opacity-30 rounded-lg p-4">
                        <p class="text-gray-400 text-sm mb-1">Date d'embauche</p>
                        <p class="text-white font-medium">{{ formatDate(employee.date_embauche) }}</p>
                    </div>
                    <div class="bg-gray-700 bg-opacity-30 rounded-lg p-4">
                        <p class="text-gray-400 text-sm mb-1">Département</p>
                        <p class="text-white font-medium">{{ employee.departement || 'N/A' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4 border border-gray-600">
                        <p class="text-gray-400 text-sm mb-1">Salaire mensuel</p>
                        <p class="text-primary-300 font-bold text-xl">{{ formatCurrency(employee.salaire) }}</p>
                    </div>
                    <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4 border border-gray-600">
                        <p class="text-gray-400 text-sm mb-1">Total ventes</p>
                        <p class="text-green-400 font-bold text-xl">{{ formatCurrency(employee.total_ventes) }}</p>
                    </div>
                    <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4 border border-gray-600">
                        <p class="text-gray-400 text-sm mb-1">Commissions</p>
                        <p class="text-yellow-400 font-bold text-xl">{{ formatCurrency(employee.commissions) }}</p>
                    </div>
                </div>
            </div>

            <!-- Historique des Entrées/Sorties -->
            <div class="p-6">
                <h3 class="text-xl font-bold text-primary-400 mb-4 flex items-center">
                    <ClockIcon class="w-6 h-6 mr-2" />
                    Historique des Entrées/Sorties
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Heure</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <tr v-for="entry in employee.historique" :key="entry.id" class="hover:bg-gray-700 hover:bg-opacity-30">
                                <td class="px-4 py-3 text-sm text-gray-300">{{ formatDate(entry.date) }}</td>
                                <td class="px-4 py-3">
                                    <span :class="entry.type === 'Entrée' ? 'text-green-400' : 'text-orange-400'" class="text-sm font-medium">
                                        {{ entry.type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-white">{{ entry.heure }}</td>
                            </tr>
                            <tr v-if="!employee.historique || employee.historique.length === 0">
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                                    Aucun historique disponible
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
                <h3 class="text-xl font-bold text-red-400 mb-3">Supprimer l'employé</h3>
                <p class="text-gray-300 mb-6">Êtes-vous sûr de vouloir supprimer l'employé <span class="font-bold text-white">{{ employee.prenom }} {{ employee.nom }}</span> ? Cette action est irréversible.</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 border border-gray-600">
                        Annuler
                    </button>
                    <button @click="deleteEmployee" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
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
    UsersIcon,
    UserCircleIcon,
    PencilIcon,
    TrashIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    IdentificationIcon,
    ClockIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const employee = ref({})
const showDeleteModal = ref(false)

function formatDate(date) {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('fr-FR')
}

function getStatusBadgeClass(statut) {
    const classes = {
        'Actif': 'bg-green-900 bg-opacity-50 text-green-400 border border-green-700',
        'Inactif': 'bg-gray-700 bg-opacity-50 text-gray-400 border border-gray-600',
        'Suspendu': 'bg-red-900 bg-opacity-50 text-red-400 border border-red-700',
        'En congé': 'bg-yellow-900 bg-opacity-50 text-yellow-400 border border-yellow-700'
    }
    return classes[statut] || 'bg-gray-700 bg-opacity-50 text-gray-400 border border-gray-600'
}

function editEmployee() {
    router.push(`/employees/${route.params.id}/edit`)
}

function confirmDelete() {
    showDeleteModal.value = true
}

function deleteEmployee() {
    // Delete logic here
    showDeleteModal.value = false
    router.push('/employees')
}

onMounted(() => {
    // Demo data - In production, fetch from API using route.params.id
    employee.value = {
        id: 'EMP0047',
        nom: 'Dubois',
        prenom: 'Émilie',
        avatar: null,
        role: 'Responsable RH',
        raison_sociale: 'TechnoPro Solutions SARL',
        telephone: '+212 655-332211',
        email: 'emilie.dubois@mail.com',
        adresse: '45, Rue des Entreprises, Casablanca',
        cin: 'AB123456',
        statut: 'Actif',
        type_contrat: 'CDI',
        date_embauche: '2023-01-15',
        departement: 'Ressources Humaines',
        salaire: 15000.00,
        total_ventes: 0,
        commissions: 0,
        historique: [
            { id: 1, date: '2024-04-22', type: 'Sortie', heure: '17:38' },
            { id: 2, date: '2024-04-19', type: 'Entrée', heure: '08:46' },
            { id: 3, date: '2024-04-19', type: 'Sortie', heure: '17:22' },
            { id: 4, date: '2024-04-18', type: 'Entrée', heure: '08:51' },
            { id: 5, date: '2024-04-18', type: 'Sortie', heure: '17:15' }
        ]
    }
})
</script>
