<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Agenda - Calendrier</h1>
                <p class="text-gray-500">Visualisez tous vos rendez-vous et tâches</p>
            </div>
            <div class="flex gap-2">
                <button @click="openAppointmentModal()" class="px-4 py-2 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 flex items-center">
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Rendez-vous
                </button>
                <button @click="openTaskModal()" class="px-4 py-2 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 flex items-center">
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Tâche
                </button>
            </div>
        </div>

        <!-- View Switcher -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center justify-between">
            <div class="flex gap-2">
                <button 
                    v-for="view in ['jour', 'mois']" 
                    :key="view"
                    @click="currentView = view"
                    class="px-4 py-2 rounded-lg font-medium transition-colors"
                    :class="currentView === view ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                >
                    {{ view.charAt(0).toUpperCase() + view.slice(1) }}
                </button>
            </div>

            <!-- Date Navigation -->
            <div class="flex items-center gap-4">
                <button @click="previousPeriod" class="p-2 rounded-lg hover:bg-gray-100">
                    <ChevronLeftIcon class="w-5 h-5" />
                </button>
                <div class="text-lg font-semibold">
                    {{ formattedPeriod }}
                </div>
                <button @click="nextPeriod" class="p-2 rounded-lg hover:bg-gray-100">
                    <ChevronRightIcon class="w-5 h-5" />
                </button>
                <button @click="goToToday" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Aujourd'hui
                </button>
            </div>
        </div>

        <!-- Calendar View -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Day View -->
            <div v-if="currentView === 'jour'" class="p-4">
                <DayView 
                    :date="currentDate" 
                    :appointments="filteredAppointments"
                    :tasks="filteredTasks"
                    @appointment-click="openAppointmentDetail"
                    @task-click="openTaskDetail"
                    @time-slot-click="handleTimeSlotClick"
                />
            </div>

            <!-- Month View -->
            <div v-else class="p-4">
                <MonthView 
                    :month="currentDate" 
                    :appointments="filteredAppointments"
                    :tasks="filteredTasks"
                    @appointment-click="openAppointmentDetail"
                    @task-click="openTaskDetail"
                    @day-click="handleDayClick"
                />
            </div>
        </div>

        <!-- Quick Action Modal -->
        <teleport to="body">
            <div v-if="showQuickActions" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" @click="showQuickActions = false">
                <div class="bg-white rounded-xl p-6 shadow-xl max-w-xs w-full mx-4" @click.stop>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ajouter pour {{ formatDate(quickActionDate) }}</h3>
                    <div class="space-y-2">
                        <button @click="confirmAppointment" class="w-full px-4 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center justify-center font-medium">
                            <CalendarIcon class="w-5 h-5 mr-2" />
                            Rendez-vous
                        </button>
                        <button @click="confirmTask" class="w-full px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center justify-center font-medium">
                            <ClipboardDocumentCheckIcon class="w-5 h-5 mr-2" />
                            Tâche
                        </button>
                    </div>
                    <button @click="showQuickActions = false" class="w-full mt-3 px-4 py-2 text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                </div>
            </div>
        </teleport>

        <!-- Appointment Modal -->
        <AppointmentModal
            v-if="showAppointmentModal"
            :appointment="selectedAppointment"
            :initial-date="appointmentInitialDate"
            @close="closeAppointmentModal"
            @saved="handleAppointmentSaved"
        />

        <!-- Task Modal -->
        <TaskModal
            v-if="showTaskModal"
            :task="selectedTask"
            :initial-date="taskInitialDate"
            @close="closeTaskModal"
            @saved="handleTaskSaved"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { PlusIcon, ChevronLeftIcon, ChevronRightIcon, CalendarIcon, ClipboardDocumentCheckIcon } from '@heroicons/vue/24/outline'
import DayView from './components/DayView.vue'
import MonthView from './components/MonthView.vue'
import AppointmentModal from './components/AppointmentModal.vue'
import TaskModal from './components/TaskModal.vue'
import axios from 'axios'

const currentView = ref('mois')
const currentDate = ref(new Date())
const appointments = ref([])
const tasks = ref([])
const loading = ref(false)

const showAppointmentModal = ref(false)
const showTaskModal = ref(false)
const showQuickActions = ref(false)
const selectedAppointment = ref(null)
const selectedTask = ref(null)
const appointmentInitialDate = ref(null)
const taskInitialDate = ref(null)
const quickActionDate = ref(null)

// Computed
const formattedPeriod = computed(() => {
    const date = currentDate.value
    const options = { year: 'numeric', month: 'long' }
    
    if (currentView.value === 'jour') {
        return date.toLocaleDateString('fr-FR', { ...options, day: 'numeric' })
    } else {
        return date.toLocaleDateString('fr-FR', options)
    }
})

const filteredAppointments = computed(() => {
    return appointments.value.filter(apt => {
        const aptDate = new Date(apt.date)
        
        if (currentView.value === 'jour') {
            return aptDate.toDateString() === currentDate.value.toDateString()
        } else {
            return aptDate.getMonth() === currentDate.value.getMonth() &&
                   aptDate.getFullYear() === currentDate.value.getFullYear()
        }
    })
})

const filteredTasks = computed(() => {
    return tasks.value.filter(task => {
        const taskDate = new Date(task.due_date)
        
        if (currentView.value === 'jour') {
            return taskDate.toDateString() === currentDate.value.toDateString()
        } else {
            return taskDate.getMonth() === currentDate.value.getMonth() &&
                   taskDate.getFullYear() === currentDate.value.getFullYear()
        }
    })
})

// Methods
function previousPeriod() {
    const date = new Date(currentDate.value)
    
    if (currentView.value === 'jour') {
        date.setDate(date.getDate() - 1)
    } else {
        date.setMonth(date.getMonth() - 1)
    }
    
    currentDate.value = date
}

function nextPeriod() {
    const date = new Date(currentDate.value)
    
    if (currentView.value === 'jour') {
        date.setDate(date.getDate() + 1)
    } else {
        date.setMonth(date.getMonth() + 1)
    }
    
    currentDate.value = date
}

function goToToday() {
    currentDate.value = new Date()
}

function formatDate(date) {
    if (!date) return ''
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function handleDayClick(date) {
    quickActionDate.value = date
    showQuickActions.value = true
}

function handleTimeSlotClick(datetime) {
    quickActionDate.value = datetime
    showQuickActions.value = true
}

function confirmAppointment() {
    showQuickActions.value = false
    appointmentInitialDate.value = quickActionDate.value
    selectedAppointment.value = null
    showAppointmentModal.value = true
}

function confirmTask() {
    showQuickActions.value = false
    taskInitialDate.value = quickActionDate.value
    selectedTask.value = null
    showTaskModal.value = true
}

function openAppointmentModal(date = null) {
    appointmentInitialDate.value = date || null
    selectedAppointment.value = null
    showAppointmentModal.value = true
}

function openTaskModal(date = null) {
    taskInitialDate.value = date || null
    selectedTask.value = null
    showTaskModal.value = true
}

function openAppointmentDetail(appointment) {
    selectedAppointment.value = appointment
    showAppointmentModal.value = true
}

function openTaskDetail(task) {
    selectedTask.value = task
    showTaskModal.value = true
}

function closeAppointmentModal() {
    showAppointmentModal.value = false
    selectedAppointment.value = null
    appointmentInitialDate.value = null
}

function closeTaskModal() {
    showTaskModal.value = false
    selectedTask.value = null
    taskInitialDate.value = null
}

function handleAppointmentSaved() {
    closeAppointmentModal()
    loadAppointments()
}

function handleTaskSaved() {
    closeTaskModal()
    loadTasks()
}

async function loadAppointments() {
    try {
        loading.value = true
        const response = await axios.get('/api/appointments')
        appointments.value = response.data.data || response.data
    } catch (error) {
        console.error('Error loading appointments:', error)
        appointments.value = []
    } finally {
        loading.value = false
    }
}

async function loadTasks() {
    try {
        loading.value = true
        const response = await axios.get('/api/tasks')
        tasks.value = response.data.data || response.data
    } catch (error) {
        console.error('Error loading tasks:', error)
        tasks.value = []
    } finally {
        loading.value = false
    }
}

// Lifecycle
onMounted(() => {
    loadAppointments()
    loadTasks()
})

// Watch for view changes to reload data
watch(currentDate, () => {
    loadAppointments()
    loadTasks()
})
</script>
