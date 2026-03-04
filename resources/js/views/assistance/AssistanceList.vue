<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Assistance & Support</h1>
                <p class="text-gray-500">Centre d'aide et support technique</p>
            </div>
            <button @click="openTicketForm" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouveau Ticket
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Tickets total</p>
                <p class="text-2xl font-bold text-gray-900">{{ tickets.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">En attente</p>
                <p class="text-2xl font-bold text-yellow-600">{{ pendingCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Résolu</p>
                <p class="text-2xl font-bold text-green-600">{{ resolvedCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Fermé</p>
                <p class="text-2xl font-bold text-gray-600">{{ closedCount }}</p>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
            <input
                v-model="search"
                type="text"
                placeholder="Rechercher par titre, ID ou contact..."
                class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="pending">En attente</option>
                <option value="in-progress">En cours</option>
                <option value="resolved">Résolu</option>
                <option value="closed">Fermé</option>
            </select>
            <select v-model="filterPriority" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Toutes les priorités</option>
                <option value="low">Basse</option>
                <option value="medium">Moyen</option>
                <option value="high">Haute</option>
            </select>
        </div>

        <!-- Tickets Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticket ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priorité</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="ticket in filteredTickets" :key="ticket.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ ticket.ticket_id }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ ticket.titre }}</p>
                            <p class="text-sm text-gray-600">{{ ticket.description }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ ticket.contact }}</td>
                        <td class="px-6 py-4">
                            <span :class="['px-2 py-1 text-xs font-medium rounded-full', getPriorityClass(ticket.priority)]">
                                {{ getPriorityLabel(ticket.priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(ticket.status)]">
                                {{ getStatusLabel(ticket.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(ticket.date) }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="viewTicket(ticket)" class="p-2 text-blue-400 hover:text-blue-600 rounded-lg hover:bg-blue-50" title="Voir détails">
                                    <EyeIcon class="w-5 h-5" />
                                </button>
                                <button @click="editTicket(ticket)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                    <PencilIcon class="w-5 h-5" />
                                </button>
                                <button @click="closeTicket(ticket)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Fermer">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredTickets.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <QuestionMarkCircleIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            Aucun ticket trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- FAQ Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Questions fréquemment posées</h3>
            <div class="space-y-3">
                <details class="border border-gray-200 rounded-lg p-4">
                    <summary class="font-medium text-gray-900 cursor-pointer flex items-center gap-2">
                        <ChevronDownIcon class="w-5 h-5 text-gray-400" />
                        Comment réinitialiser mon mot de passe?
                    </summary>
                    <p class="mt-3 text-sm text-gray-600">Allez dans Paramètres > Compte > Réinitialiser le mot de passe. Vous recevrez un lien par email.</p>
                </details>
                <details class="border border-gray-200 rounded-lg p-4">
                    <summary class="font-medium text-gray-900 cursor-pointer flex items-center gap-2">
                        <ChevronDownIcon class="w-5 h-5 text-gray-400" />
                        Comment configurer mes préférences de notifications?
                    </summary>
                    <p class="mt-3 text-sm text-gray-600">Accédez à Paramètres > Notifications pour gérer vos préférences d'alerte.</p>
                </details>
                <details class="border border-gray-200 rounded-lg p-4">
                    <summary class="font-medium text-gray-900 cursor-pointer flex items-center gap-2">
                        <ChevronDownIcon class="w-5 h-5 text-gray-400" />
                        Quels sont les horaires d'assistance?
                    </summary>
                    <p class="mt-3 text-sm text-gray-600">Notre équipe d'assistance est disponible de 08:00 à 18:00, du lundi au vendredi.</p>
                </details>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
    PlusIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    QuestionMarkCircleIcon,
    ChevronDownIcon
} from '@heroicons/vue/24/outline'

const search = ref('')
const filterStatus = ref('')
const filterPriority = ref('')

const tickets = ref([
    {
        id: 1,
        ticket_id: 'TK-001',
        titre: 'Bug sur la page Articles',
        description: 'Les images ne se chargent pas correctement',
        contact: 'Ahmed Ben Ahmed',
        priority: 'high',
        status: 'in-progress',
        date: '2026-02-28'
    },
    {
        id: 2,
        ticket_id: 'TK-002',
        titre: 'Question sur la paie',
        description: 'Comment configurer les retenues?',
        contact: 'Fatima Zahra',
        priority: 'medium',
        status: 'pending',
        date: '2026-03-01'
    },
    {
        id: 3,
        ticket_id: 'TK-003',
        titre: 'Problème de synchronisation',
        description: 'Les données ne se synchronisent pas correctement',
        contact: 'Mohamed Ali',
        priority: 'high',
        status: 'pending',
        date: '2026-03-02'
    }
])

const filteredTickets = computed(() => {
    let result = tickets.value

    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(t =>
            t.titre?.toLowerCase().includes(query) ||
            t.ticket_id?.toLowerCase().includes(query) ||
            t.contact?.toLowerCase().includes(query)
        )
    }

    if (filterStatus.value) {
        result = result.filter(t => t.status === filterStatus.value)
    }

    if (filterPriority.value) {
        result = result.filter(t => t.priority === filterPriority.value)
    }

    return result
})

const pendingCount = computed(() => tickets.value.filter(t => t.status === 'pending').length)
const resolvedCount = computed(() => tickets.value.filter(t => t.status === 'resolved').length)
const closedCount = computed(() => tickets.value.filter(t => t.status === 'closed').length)

function getPriorityLabel(priority) {
    const labels = { 'low': 'Basse', 'medium': 'Moyen', 'high': 'Haute' }
    return labels[priority] || 'N/A'
}

function getPriorityClass(priority) {
    const classes = {
        'low': 'bg-blue-100 text-blue-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'high': 'bg-red-100 text-red-800'
    }
    return classes[priority] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
    const labels = {
        'pending': 'En attente',
        'in-progress': 'En cours',
        'resolved': 'Résolu',
        'closed': 'Fermé'
    }
    return labels[status] || 'N/A'
}

function getStatusClass(status) {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'in-progress': 'bg-blue-100 text-blue-800',
        'resolved': 'bg-green-100 text-green-800',
        'closed': 'bg-gray-100 text-gray-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function formatDate(dateString) {
    const date = new Date(dateString)
    return date.toLocaleDateString('fr-FR')
}

function viewTicket(ticket) {
    console.log('View ticket:', ticket)
}

function editTicket(ticket) {
    console.log('Edit ticket:', ticket)
}

function closeTicket(ticket) {
    const index = tickets.value.findIndex(t => t.id === ticket.id)
    if (index > -1) {
        tickets.value.splice(index, 1)
    }
}

function openTicketForm() {
    console.log('Open ticket form')
}
</script>
