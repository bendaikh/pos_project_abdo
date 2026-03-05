<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Rendez-vous</h1>
                <p class="text-gray-500">Liste de vos rendez-vous programmés</p>
            </div>
            <button @click="openModal()" class="px-4 py-2 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouveau Rendez-vous
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total</p>
                <p class="text-2xl font-bold text-gray-900">{{ statistics.total }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Confirmés</p>
                <p class="text-2xl font-bold text-blue-600">{{ statistics.confirme }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Terminés</p>
                <p class="text-2xl font-bold text-green-600">{{ statistics.termine }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Annulés</p>
                <p class="text-2xl font-bold text-red-600">{{ statistics.annule }}</p>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 space-y-4">
            <div class="flex flex-wrap gap-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Rechercher..."
                    class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les statuts</option>
                    <option value="en_cours">En cours</option>
                    <option value="confirme">Confirmé</option>
                    <option value="termine">Terminé</option>
                    <option value="annule">Annulé</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Objet</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Responsable</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="appointment in filteredAppointments" :key="appointment.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ formatDate(appointment.date) }} {{ appointment.time }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ appointment.subject }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ appointment.customer?.name || '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ appointment.responsible?.name || '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="getStatusClass(appointment.status)">
                                {{ getStatusLabel(appointment.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button @click="openModal(appointment)" class="text-blue-600 hover:text-blue-800 font-medium">Modifier</button>
                            <button @click="deleteAppointment(appointment.id)" class="text-red-600 hover:text-red-800 font-medium">Supprimer</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="filteredAppointments.length === 0" class="px-6 py-12 text-center text-gray-500">
                Aucun rendez-vous trouvé
            </div>
        </div>

        <!-- Modal -->
        <AppointmentModal
            v-if="showModal"
            :appointment="selectedAppointment"
            @close="closeModal"
            @saved="handleSaved"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { PlusIcon } from '@heroicons/vue/24/outline'
import AppointmentModal from './components/AppointmentModal.vue'
import api from '../../api'

const appointments = ref([])
const statistics = ref({})
const search = ref('')
const filterStatus = ref('')
const showModal = ref(false)
const selectedAppointment = ref(null)
const loading = ref(false)

const filteredAppointments = computed(() => {
    return appointments.value.filter(appointment => {
        const matchesSearch = !search.value || 
            appointment.subject.toLowerCase().includes(search.value.toLowerCase()) ||
            appointment.customer?.name?.toLowerCase()?.includes(search.value.toLowerCase())
        
        const matchesStatus = !filterStatus.value || appointment.status === filterStatus.value
        
        return matchesSearch && matchesStatus
    })
})

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function getStatusLabel(status) {
    const labels = {
        'en_cours': 'En cours',
        'confirme': 'Confirmé',
        'termine': 'Terminé',
        'annule': 'Annulé'
    }
    return labels[status] || status
}

function getStatusClass(status) {
    const classes = {
        'en_cours': 'bg-yellow-100 text-yellow-800',
        'confirme': 'bg-blue-100 text-blue-800',
        'termine': 'bg-green-100 text-green-800',
        'annule': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function openModal(appointment = null) {
    selectedAppointment.value = appointment
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    selectedAppointment.value = null
}

function handleSaved() {
    closeModal()
    loadAppointments()
    loadStatistics()
}

async function deleteAppointment(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce rendez-vous ?')) return

    try {
        await api.delete(`/appointments/${id}`)
        loadAppointments()
        loadStatistics()
    } catch (error) {
        console.error('Error deleting appointment:', error)
        alert('Erreur lors de la suppression')
    }
}

async function loadAppointments() {
    try {
        loading.value = true
        const response = await api.get('/appointments')
        appointments.value = response.data.data || response.data
    } catch (error) {
        console.error('Error loading appointments:', error)
        appointments.value = []
    } finally {
        loading.value = false
    }
}

async function loadStatistics() {
    try {
        const response = await api.get('/appointments/statistics')
        statistics.value = response.data
    } catch (error) {
        console.error('Error loading statistics:', error)
    }
}

onMounted(() => {
    loadAppointments()
    loadStatistics()
})
</script>
