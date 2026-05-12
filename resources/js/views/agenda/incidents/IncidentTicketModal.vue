<template>
    <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 py-10">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl mx-4 flex flex-col md:flex-row">
            <!-- Left side - Form -->
            <div class="flex-1 p-6 border-r border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ isEditing ? 'Modifier le ticket' : 'Nouveau ticket incident' }}
                    </h2>
                    <button @click="$emit('close')" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <!-- Type d'incident -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type d'incident *</label>
                        <select 
                            v-model="form.incident_type_id" 
                            @change="onTypeChange"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white"
                            required
                        >
                            <option value="">Sélectionner un type</option>
                            <option v-for="type in incidentTypes" :key="type.id" :value="type.id">
                                {{ type.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Responsable -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Responsable *</label>
                        <select 
                            v-model="form.responsible_id" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white"
                            required
                        >
                            <option value="">Sélectionner un responsable</option>
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.name }}
                            </option>
                        </select>
                        <p v-if="autoAssignedResponsible" class="mt-1 text-xs text-green-600">
                            Assigné automatiquement selon le type d'incident
                        </p>
                    </div>

                    <!-- Titre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                        <input 
                            v-model="form.title" 
                            type="text" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Titre de l'incident"
                            required
                        />
                    </div>

                    <!-- Priorité -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priorité *</label>
                        <select 
                            v-model="form.priority_id" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white"
                            required
                        >
                            <option value="">Sélectionner une priorité</option>
                            <option v-for="priority in priorities" :key="priority.id" :value="priority.id">
                                {{ priority.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea 
                            v-model="form.description" 
                            rows="4"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Décrivez l'incident en détail..."
                        ></textarea>
                    </div>

                    <!-- Reported by (optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Signalé par (optionnel)</label>
                        <select 
                            v-model="form.reported_by_id" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white"
                        >
                            <option value="">Sélectionner un employé</option>
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Status (only when editing) -->
                    <div v-if="isEditing">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select 
                            v-model="form.status" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white"
                        >
                            <option value="en_attente">En attente</option>
                            <option value="en_cours">En cours</option>
                            <option value="resolu">Résolu</option>
                            <option value="abandonne">Abandonné</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button 
                            type="button" 
                            @click="$emit('close')" 
                            class="px-5 py-2.5 border border-red-300 text-red-600 font-medium rounded-lg hover:bg-red-50 transition-colors"
                        >
                            Annuler
                        </button>
                        <button 
                            type="submit" 
                            :disabled="saving"
                            class="px-5 py-2.5 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50 flex items-center"
                        >
                            <CheckIcon class="w-5 h-5 mr-2" />
                            {{ isEditing ? 'Mettre à jour' : 'Créer ticket' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right side - Details preview -->
            <div class="w-full md:w-72 bg-gray-50 p-6 rounded-r-xl">
                <h3 class="font-semibold text-gray-900 mb-4">Détails du ticket</h3>
                
                <!-- Ticket number (if editing) -->
                <div v-if="isEditing && ticket?.ticket_number" class="mb-4">
                    <p class="text-sm text-gray-500">N° Ticket</p>
                    <p class="text-lg font-bold text-blue-600">#{{ ticket.ticket_number }}</p>
                </div>

                <!-- Responsible preview -->
                <div v-if="selectedResponsible" class="mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-green-700 font-semibold text-sm">
                                {{ selectedResponsible.name?.charAt(0)?.toUpperCase() }}
                            </span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ selectedResponsible.name }}</p>
                            <p class="text-xs text-green-600 flex items-center">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                                {{ selectedResponsible.role || 'Employé' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Priority preview -->
                <div v-if="selectedPriority" class="mb-4">
                    <p class="text-sm text-gray-500 mb-1">Priorité</p>
                    <span 
                        class="px-3 py-1 rounded-full text-sm font-semibold"
                        :style="getPriorityStyle(selectedPriority)"
                    >
                        {{ selectedPriority.label }}
                    </span>
                </div>

                <!-- Status preview -->
                <div v-if="form.status" class="mb-4">
                    <p class="text-sm text-gray-500 mb-1">Statut</p>
                    <span 
                        class="px-3 py-1 rounded-full text-sm font-semibold"
                        :class="getStatusClass(form.status)"
                    >
                        {{ getStatusLabel(form.status) }}
                    </span>
                </div>

                <!-- Type preview -->
                <div v-if="selectedType" class="mb-4">
                    <p class="text-sm text-gray-500 mb-1">Type</p>
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-medium">
                        {{ selectedType.label }}
                    </span>
                </div>

                <!-- Date -->
                <div class="mb-4">
                    <p class="text-sm text-gray-500 mb-1">Date</p>
                    <p class="text-sm text-gray-900">{{ formatDate(new Date()) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { XMarkIcon, CheckIcon } from '@heroicons/vue/24/outline'
import api from '../../../api'

const props = defineProps({
    ticket: Object,
    incidentTypes: Array,
    priorities: Array,
    employees: Array,
    assignments: Object
})

const emit = defineEmits(['close', 'saved'])

const form = ref({
    incident_type_id: '',
    title: '',
    description: '',
    priority_id: '',
    responsible_id: '',
    reported_by_id: '',
    status: 'en_attente'
})

const saving = ref(false)
const autoAssignedResponsible = ref(false)

const isEditing = computed(() => !!props.ticket)

const selectedType = computed(() => {
    if (!form.value.incident_type_id) return null
    return props.incidentTypes?.find(t => t.id == form.value.incident_type_id)
})

const selectedPriority = computed(() => {
    if (!form.value.priority_id) return null
    return props.priorities?.find(p => p.id == form.value.priority_id)
})

const selectedResponsible = computed(() => {
    if (!form.value.responsible_id) return null
    return props.employees?.find(e => e.id == form.value.responsible_id)
})

function onTypeChange() {
    autoAssignedResponsible.value = false
    
    if (form.value.incident_type_id && props.assignments) {
        const assignedEmployees = props.assignments[form.value.incident_type_id]
        if (assignedEmployees && assignedEmployees.length > 0) {
            // Auto-select the first assigned employee
            form.value.responsible_id = assignedEmployees[0].id
            autoAssignedResponsible.value = true
        }
    }
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

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

async function handleSubmit() {
    saving.value = true
    
    try {
        const data = {
            incident_type_id: form.value.incident_type_id,
            title: form.value.title,
            description: form.value.description || null,
            priority_id: form.value.priority_id,
            responsible_id: form.value.responsible_id,
            reported_by_id: form.value.reported_by_id || null,
        }

        if (isEditing.value) {
            data.status = form.value.status
            await api.put(`/incident-tickets/${props.ticket.id}`, data)
        } else {
            await api.post('/incident-tickets', data)
        }
        
        emit('saved')
    } catch (error) {
        console.error('Error saving ticket:', error)
        const message = error.response?.data?.errors 
            ? Object.values(error.response.data.errors).flat().join('\n')
            : 'Erreur lors de l\'enregistrement du ticket'
        alert(message)
    } finally {
        saving.value = false
    }
}

onMounted(() => {
    if (props.ticket) {
        form.value = {
            incident_type_id: props.ticket.incident_type_id,
            title: props.ticket.title,
            description: props.ticket.description || '',
            priority_id: props.ticket.priority_id,
            responsible_id: props.ticket.responsible_id,
            reported_by_id: props.ticket.reported_by_id || '',
            status: props.ticket.status
        }
    }
})
</script>
