<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tâches</h1>
                <p class="text-gray-500">Gestion de vos tâches</p>
            </div>
            <button @click="openModal()" class="px-4 py-2 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouvelle Tâche
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total</p>
                <p class="text-2xl font-bold text-gray-900">{{ statistics.total }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Urgentes</p>
                <p class="text-2xl font-bold text-red-600">{{ statistics.urgente }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">En cours</p>
                <p class="text-2xl font-bold text-blue-600">{{ statistics.en_cours }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Terminées</p>
                <p class="text-2xl font-bold text-green-600">{{ statistics.termine }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">En retard</p>
                <p class="text-2xl font-bold text-orange-600">{{ statistics.overdue }}</p>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 space-y-4">
            <div class="flex flex-wrap gap-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Rechercher..."
                    class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                />
                <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente">En attente</option>
                    <option value="en_cours">En cours</option>
                    <option value="termine">Terminé</option>
                    <option value="annule">Annulé</option>
                </select>
                <select v-model="filterPriority" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Toutes les priorités</option>
                    <option value="faible">Faible</option>
                    <option value="moyenne">Moyenne</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Date d'échéance</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Objet</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Employé</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Priorité</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="task in filteredTasks" :key="task.id" class="hover:bg-gray-50" :class="{ 'bg-red-50': isOverdue(task) }">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ formatDate(task.due_date) }}
                            <span v-if="isOverdue(task)" class="ml-2 text-xs text-red-600 font-semibold">EN RETARD</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ task.subject }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ task.employee?.name || '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="getPriorityClass(task.priority)">
                                {{ getPriorityLabel(task.priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="getStatusClass(task.status)">
                                {{ getStatusLabel(task.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2 flex flex-wrap gap-2">
                            <button v-if="task.status !== 'termine'" @click="markCompleted(task.id)" class="px-3 py-1 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 font-medium text-xs">Marquer terminée</button>
                            <button @click="openModal(task)" class="px-3 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-medium text-xs">Modifier</button>
                            <button @click="deleteTask(task.id)" class="px-3 py-1 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 font-medium text-xs">Supprimer</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="filteredTasks.length === 0" class="px-6 py-12 text-center text-gray-500">
                Aucune tâche trouvée
            </div>
        </div>

        <!-- Modal -->
        <TaskModal
            v-if="showModal"
            :task="selectedTask"
            @close="closeModal"
            @saved="handleSaved"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { PlusIcon } from '@heroicons/vue/24/outline'
import TaskModal from './components/TaskModal.vue'
import api from '../../api'

const tasks = ref([])
const statistics = ref({})
const search = ref('')
const filterStatus = ref('')
const filterPriority = ref('')
const showModal = ref(false)
const selectedTask = ref(null)
const loading = ref(false)

const filteredTasks = computed(() => {
    return tasks.value.filter(task => {
        const matchesSearch = !search.value || 
            task.subject.toLowerCase().includes(search.value.toLowerCase()) ||
            task.employee?.name?.toLowerCase()?.includes(search.value.toLowerCase())
        
        const matchesStatus = !filterStatus.value || task.status === filterStatus.value
        const matchesPriority = !filterPriority.value || task.priority === filterPriority.value
        
        return matchesSearch && matchesStatus && matchesPriority
    }).sort((a, b) => {
        // Sort by priority and date
        const priorityOrder = { 'urgente': 0, 'moyenne': 1, 'faible': 2 }
        if (priorityOrder[a.priority] !== priorityOrder[b.priority]) {
            return priorityOrder[a.priority] - priorityOrder[b.priority]
        }
        return new Date(a.due_date) - new Date(b.due_date)
    })
})

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function isOverdue(task) {
    if (task.status === 'termine' || task.status === 'annule') return false
    const now = new Date()
    const dueDate = new Date(task.due_date)
    return now > dueDate
}

function getStatusLabel(status) {
    const labels = {
        'en_attente': 'En attente',
        'en_cours': 'En cours',
        'termine': 'Terminée',
        'annule': 'Annulée'
    }
    return labels[status] || status
}

function getPriorityLabel(priority) {
    const labels = {
        'faible': 'Faible',
        'moyenne': 'Moyenne',
        'urgente': 'Urgente'
    }
    return labels[priority] || priority
}

function getStatusClass(status) {
    const classes = {
        'en_attente': 'bg-gray-100 text-gray-800',
        'en_cours': 'bg-blue-100 text-blue-800',
        'termine': 'bg-green-100 text-green-800',
        'annule': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function getPriorityClass(priority) {
    const classes = {
        'faible': 'bg-green-100 text-green-800',
        'moyenne': 'bg-yellow-100 text-yellow-800',
        'urgente': 'bg-red-100 text-red-800'
    }
    return classes[priority] || 'bg-gray-100 text-gray-800'
}

function openModal(task = null) {
    selectedTask.value = task
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    selectedTask.value = null
}

function handleSaved() {
    closeModal()
    loadTasks()
    loadStatistics()
}

async function markCompleted(id) {
    try {
        await api.post(`/tasks/${id}/mark-completed`)
        loadTasks()
        loadStatistics()
    } catch (error) {
        console.error('Error marking task as completed:', error)
        alert('Erreur lors de la mise à jour')
    }
}

async function deleteTask(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) return

    try {
        await api.delete(`/tasks/${id}`)
        loadTasks()
        loadStatistics()
    } catch (error) {
        console.error('Error deleting task:', error)
        alert('Erreur lors de la suppression')
    }
}

async function loadTasks() {
    try {
        loading.value = true
        const response = await api.get('/tasks')
        tasks.value = response.data.data || response.data
    } catch (error) {
        console.error('Error loading tasks:', error)
        tasks.value = []
    } finally {
        loading.value = false
    }
}

async function loadStatistics() {
    try {
        const response = await api.get('/tasks/statistics')
        statistics.value = response.data
    } catch (error) {
        console.error('Error loading statistics:', error)
    }
}

onMounted(() => {
    loadTasks()
    loadStatistics()
})
</script>
