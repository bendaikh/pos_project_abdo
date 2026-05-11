import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../api'

export const useIncidentTicketsStore = defineStore('incidentTickets', () => {
    const tickets = ref([])
    const incidentTypes = ref([])
    const priorities = ref([])
    const assignments = ref({})
    const employees = ref([])
    const loading = ref(false)
    const error = ref(null)

    const pendingTickets = computed(() => 
        tickets.value.filter(t => ['en_attente', 'en_cours'].includes(t.status))
    )

    const resolvedTickets = computed(() => 
        tickets.value.filter(t => t.status === 'resolu')
    )

    async function fetchTickets(params = {}) {
        loading.value = true
        error.value = null
        try {
            const response = await api.get('/incident-tickets', { params })
            tickets.value = response.data
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Erreur lors du chargement des tickets'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function fetchIncidentTypes() {
        try {
            const response = await api.get('/incident-tickets/types')
            incidentTypes.value = response.data
            return response.data
        } catch (err) {
            console.error('Error fetching incident types:', err)
            return []
        }
    }

    async function fetchPriorities() {
        try {
            const response = await api.get('/incident-tickets/priorities')
            priorities.value = response.data
            return response.data
        } catch (err) {
            console.error('Error fetching priorities:', err)
            return []
        }
    }

    async function fetchAssignmentsWithTypes() {
        try {
            const response = await api.get('/incident-type-assignments/with-types')
            incidentTypes.value = response.data.types
            assignments.value = response.data.assignments
            employees.value = response.data.employees
            return response.data
        } catch (err) {
            console.error('Error fetching assignments:', err)
            return { types: [], assignments: {}, employees: [] }
        }
    }

    async function createTicket(ticketData) {
        loading.value = true
        try {
            const response = await api.post('/incident-tickets', ticketData)
            tickets.value.unshift(response.data.ticket)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Erreur lors de la création du ticket'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function updateTicket(id, ticketData) {
        loading.value = true
        try {
            const response = await api.put(`/incident-tickets/${id}`, ticketData)
            const index = tickets.value.findIndex(t => t.id === id)
            if (index !== -1) {
                tickets.value[index] = response.data.ticket
            }
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Erreur lors de la mise à jour du ticket'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function updateTicketStatus(id, status, resolutionNotes = null) {
        loading.value = true
        try {
            const response = await api.post(`/incident-tickets/${id}/status`, {
                status,
                resolution_notes: resolutionNotes
            })
            const index = tickets.value.findIndex(t => t.id === id)
            if (index !== -1) {
                tickets.value[index] = response.data.ticket
            }
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Erreur lors de la mise à jour du statut'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function deleteTicket(id) {
        loading.value = true
        try {
            await api.delete(`/incident-tickets/${id}`)
            tickets.value = tickets.value.filter(t => t.id !== id)
        } catch (err) {
            error.value = err.response?.data?.message || 'Erreur lors de la suppression du ticket'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function fetchStatistics(params = {}) {
        try {
            const response = await api.get('/incident-tickets/statistics', { params })
            return response.data
        } catch (err) {
            console.error('Error fetching statistics:', err)
            return null
        }
    }

    async function saveAssignments(assignmentsData) {
        try {
            const response = await api.post('/incident-type-assignments/bulk', {
                assignments: assignmentsData
            })
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Erreur lors de la sauvegarde des assignations'
            throw err
        }
    }

    function getResponsibleForType(typeId) {
        const assignment = assignments.value[typeId]
        return assignment?.employee || null
    }

    return {
        tickets,
        incidentTypes,
        priorities,
        assignments,
        employees,
        loading,
        error,
        pendingTickets,
        resolvedTickets,
        fetchTickets,
        fetchIncidentTypes,
        fetchPriorities,
        fetchAssignmentsWithTypes,
        createTicket,
        updateTicket,
        updateTicketStatus,
        deleteTicket,
        fetchStatistics,
        saveAssignments,
        getResponsibleForType
    }
})
