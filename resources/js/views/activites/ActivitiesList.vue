<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Activités & Rendez-vous</h1>
                <p class="text-gray-500">Gestion de votre calendrier et rendez-vous</p>
            </div>
            <button @click="openActivityForm" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouveau Rendez-vous
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Rendez-vous total</p>
                <p class="text-2xl font-bold text-gray-900">{{ activities.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">À venir</p>
                <p class="text-2xl font-bold text-blue-600">{{ upcomingCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Complétés</p>
                <p class="text-2xl font-bold text-green-600">{{ completedCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Annulés</p>
                <p class="text-2xl font-bold text-red-600">{{ cancelledCount }}</p>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
            <input
                v-model="search"
                type="text"
                placeholder="Rechercher par titre, description ou contact..."
                class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="planned">Planifié</option>
                <option value="completed">Complété</option>
                <option value="cancelled">Annulé</option>
            </select>
        </div>

        <!-- Activities Calendar View -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Calendar -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Calendrier</h3>
                <div class="space-y-3">
                    <div v-if="activities.length === 0" class="text-center py-12">
                        <CalendarDaysIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                        <p class="text-gray-500">Aucun rendez-vous programmé</p>
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="activity in filteredActivities" :key="activity.id" class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex-shrink-0">
                                <div :class="['w-12 h-12 rounded-lg flex items-center justify-center text-white', getStatusColor(activity.status)]">
                                    <CalendarDaysIcon class="w-6 h-6" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900">{{ activity.title }}</h4>
                                <p class="text-sm text-gray-600">{{ activity.description }}</p>
                                <div class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                                    <ClockIcon class="w-4 h-4" />
                                    <span>{{ formatDate(activity.date) }} à {{ activity.time }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="editActivity(activity)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                    <PencilIcon class="w-4 h-4" />
                                </button>
                                <button @click="deleteActivity(activity)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Activities Sidebar -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">À venir</h3>
                <div class="space-y-3">
                    <div v-if="upcomingActivities.length === 0" class="text-center py-8">
                        <CheckIcon class="w-12 h-12 mx-auto text-gray-300 mb-2" />
                        <p class="text-sm text-gray-500">Aucun rendez-vous à venir</p>
                    </div>
                    <div v-else v-for="activity in upcomingActivities.slice(0, 5)" :key="activity.id" class="p-3 border border-blue-200 bg-blue-50 rounded-lg">
                        <p class="font-medium text-gray-900 text-sm">{{ activity.title }}</p>
                        <p class="text-xs text-gray-600 mt-1">{{ formatDate(activity.date) }} à {{ activity.time }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Form Modal -->
    <div v-if="showActivityForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ editingActivity ? 'Modifier' : 'Nouveau' }} Rendez-vous</h3>
                <button @click="closeActivityForm" class="text-gray-400 hover:text-gray-600">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>
            <form @submit.prevent="saveActivity" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                    <input v-model="activityForm.title" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ex: Réunion avec client" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea v-model="activityForm.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Détails du rendez-vous..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                        <input v-model="activityForm.date" type="date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heure *</label>
                        <input v-model="activityForm.time" type="time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" @click="closeActivityForm" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    CalendarDaysIcon,
    ClockIcon,
    CheckIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const search = ref('')
const filterStatus = ref('')
const showActivityForm = ref(false)
const editingActivity = ref(null)

const defaultActivityForm = () => ({
    title: '',
    description: '',
    date: new Date().toISOString().split('T')[0],
    time: '10:00',
    status: 'planned'
})

const activityForm = reactive({...defaultActivityForm()})

const activities = ref([
    {
        id: 1,
        title: 'Réunion avec client ABC',
        description: 'Discussion sur la nouvelle commande',
        date: '2026-03-05',
        time: '10:00',
        status: 'planned'
    },
    {
        id: 2,
        title: 'Livraison produits',
        description: 'Réception de marchandise fournisseur',
        date: '2026-03-06',
        time: '14:00',
        status: 'planned'
    },
    {
        id: 3,
        title: 'Inventaire mensuel',
        description: 'Vérification stock',
        date: '2026-03-10',
        time: '09:00',
        status: 'planned'
    }
])

const filteredActivities = computed(() => {
    let result = activities.value

    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(a =>
            a.title?.toLowerCase().includes(query) ||
            a.description?.toLowerCase().includes(query)
        )
    }

    if (filterStatus.value) {
        result = result.filter(a => a.status === filterStatus.value)
    }

    return result
})

const upcomingActivities = computed(() =>
    activities.value.filter(a => a.status === 'planned')
)

const upcomingCount = computed(() => upcomingActivities.value.length)
const completedCount = computed(() => activities.value.filter(a => a.status === 'completed').length)
const cancelledCount = computed(() => activities.value.filter(a => a.status === 'cancelled').length)

function getStatusColor(status) {
    const colors = {
        'planned': 'bg-blue-500',
        'completed': 'bg-green-500',
        'cancelled': 'bg-red-500'
    }
    return colors[status] || 'bg-gray-500'
}

function formatDate(dateString) {
    const date = new Date(dateString)
    return date.toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' })
}

function openActivityForm() {
    editingActivity.value = null
    Object.assign(activityForm, defaultActivityForm())
    showActivityForm.value = true
}

function closeActivityForm() {
    showActivityForm.value = false
    editingActivity.value = null
}

function saveActivity() {
    if (editingActivity.value) {
        const index = activities.value.findIndex(a => a.id === editingActivity.value.id)
        if (index > -1) {
            activities.value[index] = {
                ...activities.value[index],
                title: activityForm.title,
                description: activityForm.description,
                date: activityForm.date,
                time: activityForm.time
            }
        }
    } else {
        const newActivity = {
            id: Date.now(),
            title: activityForm.title,
            description: activityForm.description,
            date: activityForm.date,
            time: activityForm.time,
            status: 'planned'
        }
        activities.value.unshift(newActivity)
    }
    closeActivityForm()
}

function editActivity(activity) {
    console.log('Edit activity:', activity)
}

function deleteActivity(activity) {
    const index = activities.value.findIndex(a => a.id === activity.id)
    if (index > -1) {
        activities.value.splice(index, 1)
    }
}
</script>
