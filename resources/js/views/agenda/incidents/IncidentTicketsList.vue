<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tickets Incidents</h1>
                <p class="text-gray-500">Gestion des incidents et demandes internes</p>
            </div>
            <button @click="openModal()" class="px-4 py-2 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouveau ticket
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total</p>
                <p class="text-2xl font-bold text-gray-900">{{ statistics.total || 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">En attente</p>
                <p class="text-2xl font-bold text-orange-600">{{ statistics.en_attente || 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">En cours</p>
                <p class="text-2xl font-bold text-blue-600">{{ statistics.en_cours || 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Résolus</p>
                <p class="text-2xl font-bold text-green-600">{{ statistics.resolu || 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Abandonnés</p>
                <p class="text-2xl font-bold text-gray-500">{{ statistics.abandonne || 0 }}</p>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 space-y-4">
            <div class="flex flex-wrap gap-4">
                <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Statut</option>
                    <option value="en_attente">En attente</option>
                    <option value="en_cours">En cours</option>
                    <option value="resolu">Résolu</option>
                    <option value="abandonne">Abandonné</option>
                </select>
                <select v-model="filterType" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Type</option>
                    <option v-for="type in incidentTypes" :key="type.id" :value="type.id">{{ type.label }}</option>
                </select>
                <select v-model="filterPriority" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Priorité</option>
                    <option v-for="priority in priorities" :key="priority.id" :value="priority.id">{{ priority.label }}</option>
                </select>
                <select v-model="filterDate" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Date</option>
                    <option value="today">Aujourd'hui</option>
                    <option value="week">Cette semaine</option>
                    <option value="month">Ce mois</option>
                </select>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Recherche..."
                    class="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                />
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">N° Ticket</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Titre</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Employé</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Responsable</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Priorité</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="ticket in filteredTickets" :key="ticket.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ formatDate(ticket.created_at) }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded font-semibold">
                                {{ ticket.ticket_number }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-medium">
                                {{ ticket.incident_type?.label || '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                            {{ ticket.title }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ ticket.reported_by?.name || '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ ticket.responsible?.name || '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span 
                                class="px-3 py-1 rounded-full text-xs font-semibold"
                                :style="getPriorityStyle(ticket.priority)"
                            >
                                {{ ticket.priority?.label || '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="getStatusClass(ticket.status)">
                                {{ getStatusLabel(ticket.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button 
                                    v-if="ticket.status === 'en_attente'" 
                                    @click="updateStatus(ticket.id, 'en_cours')" 
                                    class="px-2 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-medium text-xs"
                                    title="Démarrer"
                                >
                                    En cours
                                </button>
                                <button 
                                    v-if="ticket.status === 'en_cours'" 
                                    @click="openResolveModal(ticket)" 
                                    class="px-2 py-1 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 font-medium text-xs"
                                    title="Résoudre"
                                >
                                    Résolu
                                </button>
                                <button 
                                    @click="openModal(ticket)" 
                                    class="p-1.5 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100"
                                    title="Modifier"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                </button>
                                <button 
                                    @click="deleteTicket(ticket.id)" 
                                    class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100"
                                    title="Supprimer"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="loading" class="px-6 py-12 text-center text-gray-500">
                <svg class="animate-spin h-8 w-8 mx-auto text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-2">Chargement...</p>
            </div>

            <div v-else-if="filteredTickets.length === 0" class="px-6 py-12 text-center text-gray-500">
                <TicketIcon class="w-12 h-12 mx-auto text-gray-300 mb-4" />
                <p>Aucun ticket trouvé</p>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <IncidentTicketModal
            v-if="showModal"
            :ticket="selectedTicket"
            :incident-types="incidentTypes"
            :priorities="priorities"
            :employees="employees"
            :assignments="assignments"
            @close="closeModal"
            @saved="handleSaved"
        />

        <!-- Resolve Modal -->
        <div v-if="showResolveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Résoudre le ticket</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes de résolution</label>
                        <textarea 
                            v-model="resolutionNotes" 
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Décrivez comment l'incident a été résolu..."
                        ></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button @click="closeResolveModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button @click="resolveTicket" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                            Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { PlusIcon, PencilIcon, TrashIcon, TicketIcon } from '@heroicons/vue/24/outline'
import IncidentTicketModal from './IncidentTicketModal.vue'
import api from '../../../api'

const tickets = ref([])
const incidentTypes = ref([])
const priorities = ref([])
const employees = ref([])
const assignments = ref({})
const statistics = ref({})
const search = ref('')
const filterStatus = ref('')
const filterType = ref('')
const filterPriority = ref('')
const filterDate = ref('')
const showModal = ref(false)
const selectedTicket = ref(null)
const showResolveModal = ref(false)
const ticketToResolve = ref(null)
const resolutionNotes = ref('')
const loading = ref(false)

const filteredTickets = computed(() => {
    return tickets.value.filter(ticket => {
        const matchesSearch = !search.value || 
            ticket.ticket_number?.toLowerCase().includes(search.value.toLowerCase()) ||
            ticket.title?.toLowerCase().includes(search.value.toLowerCase()) ||
            ticket.responsible?.name?.toLowerCase()?.includes(search.value.toLowerCase())
        
        const matchesStatus = !filterStatus.value || ticket.status === filterStatus.value
        const matchesType = !filterType.value || ticket.incident_type_id == filterType.value
        const matchesPriority = !filterPriority.value || ticket.priority_id == filterPriority.value
        
        let matchesDate = true
        if (filterDate.value) {
            const ticketDate = new Date(ticket.created_at)
            const today = new Date()
            today.setHours(0, 0, 0, 0)
            
            if (filterDate.value === 'today') {
                matchesDate = ticketDate >= today
            } else if (filterDate.value === 'week') {
                const weekAgo = new Date(today)
                weekAgo.setDate(weekAgo.getDate() - 7)
                matchesDate = ticketDate >= weekAgo
            } else if (filterDate.value === 'month') {
                const monthAgo = new Date(today)
                monthAgo.setMonth(monthAgo.getMonth() - 1)
                matchesDate = ticketDate >= monthAgo
            }
        }
        
        return matchesSearch && matchesStatus && matchesType && matchesPriority && matchesDate
    }).sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function getStatusLabel(status) {
    const labels = {
        'en_attente': 'En attente',
        'en_cours': 'En cours',
        'resolu': 'Résolu',
        'abandonne': 'Abandonné'
    }
    return labels[status] || status
}

function getStatusClass(status) {
    const classes = {
        'en_attente': 'bg-orange-100 text-orange-800',
        'en_cours': 'bg-blue-100 text-blue-800',
        'resolu': 'bg-green-100 text-green-800',
        'abandonne': 'bg-gray-100 text-gray-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function getPriorityStyle(priority) {
    if (!priority?.metadata) {
        return { backgroundColor: '#F3F4F6', color: '#374151' }
    }
    const meta = typeof priority.metadata === 'string' ? JSON.parse(priority.metadata) : priority.metadata
    return {
        backgroundColor: meta.bg_color || '#F3F4F6',
        color: meta.color || '#374151'
    }
}

function openModal(ticket = null) {
    selectedTicket.value = ticket
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    selectedTicket.value = null
}

function openResolveModal(ticket) {
    ticketToResolve.value = ticket
    resolutionNotes.value = ''
    showResolveModal.value = true
}

function closeResolveModal() {
    showResolveModal.value = false
    ticketToResolve.value = null
    resolutionNotes.value = ''
}

function handleSaved() {
    closeModal()
    loadTickets()
    loadStatistics()
}

async function updateStatus(id, status) {
    try {
        await api.post(`/incident-tickets/${id}/status`, { status })
        loadTickets()
        loadStatistics()
    } catch (error) {
        console.error('Error updating status:', error)
        alert('Erreur lors de la mise à jour du statut')
    }
}

async function resolveTicket() {
    if (!ticketToResolve.value) return
    
    try {
        await api.post(`/incident-tickets/${ticketToResolve.value.id}/status`, {
            status: 'resolu',
            resolution_notes: resolutionNotes.value
        })
        closeResolveModal()
        loadTickets()
        loadStatistics()
    } catch (error) {
        console.error('Error resolving ticket:', error)
        alert('Erreur lors de la résolution du ticket')
    }
}

async function deleteTicket(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?')) return

    try {
        await api.delete(`/incident-tickets/${id}`)
        loadTickets()
        loadStatistics()
    } catch (error) {
        console.error('Error deleting ticket:', error)
        alert('Erreur lors de la suppression')
    }
}

async function loadTickets() {
    try {
        loading.value = true
        const response = await api.get('/incident-tickets')
        tickets.value = response.data.data || response.data
    } catch (error) {
        console.error('Error loading tickets:', error)
        tickets.value = []
    } finally {
        loading.value = false
    }
}

async function loadStatistics() {
    try {
        const response = await api.get('/incident-tickets/statistics')
        statistics.value = response.data
    } catch (error) {
        console.error('Error loading statistics:', error)
    }
}

async function loadIncidentTypes() {
    try {
        const response = await api.get('/incident-tickets/types')
        incidentTypes.value = response.data
    } catch (error) {
        console.error('Error loading incident types:', error)
    }
}

async function loadPriorities() {
    try {
        const response = await api.get('/incident-tickets/priorities')
        priorities.value = response.data
    } catch (error) {
        console.error('Error loading priorities:', error)
    }
}

async function loadAssignments() {
    try {
        const response = await api.get('/incident-type-assignments/with-types')
        assignments.value = response.data.assignments || {}
        employees.value = response.data.employees || []
    } catch (error) {
        console.error('Error loading assignments:', error)
    }
}

onMounted(() => {
    loadTickets()
    loadStatistics()
    loadIncidentTypes()
    loadPriorities()
    loadAssignments()
})
</script>
