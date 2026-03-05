<template>
    <div class="week-view">
        <!-- Time slots and days grid -->
        <div class="grid grid-cols-8 gap-px bg-gray-200">
            <!-- Header Row -->
            <div class="bg-white p-2"></div>
            <div 
                v-for="day in weekDays" 
                :key="day.toISOString()"
                class="bg-white p-3 text-center border-b-2"
                :class="isToday(day) ? 'border-blue-500' : 'border-transparent'"
            >
                <div class="text-xs font-medium text-gray-500 uppercase">
                    {{ formatDayName(day) }}
                </div>
                <div 
                    class="text-lg font-bold mt-1 cursor-pointer hover:text-blue-500"
                    :class="isToday(day) ? 'text-blue-500' : 'text-gray-900'"
                    @click="$emit('day-click', day)"
                >
                    {{ day.getDate() }}
                </div>
            </div>

            <!-- Time slots -->
            <template v-for="hour in hours" :key="hour">
                <!-- Time label -->
                <div class="bg-white p-2 text-xs text-gray-500 text-right pr-3">
                    {{ formatHour(hour) }}
                </div>

                <!-- Day cells -->
                <div 
                    v-for="day in weekDays" 
                    :key="`${day.toISOString()}-${hour}`"
                    class="bg-white p-1 min-h-[60px] relative border-t border-gray-100 cursor-pointer hover:bg-blue-50"
                    @click="handleCellClick(day, hour)"
                >
                    <!-- Appointments and tasks for this time slot -->
                    <div class="space-y-1">
                        <!-- Appointments (blue) -->
                        <div 
                            v-for="appointment in getEventsForSlot(day, hour, 'appointment')" 
                            :key="appointment.id"
                            class="text-xs p-1 rounded bg-blue-100 border-l-2 border-blue-500 cursor-pointer hover:bg-blue-200 truncate"
                            @click.stop="$emit('appointment-click', appointment)"
                        >
                            <div class="font-medium text-blue-900">{{ appointment.time }} - {{ appointment.subject }}</div>
                            <div class="text-blue-700">{{ appointment.customer?.name }}</div>
                        </div>

                        <!-- Tasks (green) -->
                        <div 
                            v-for="task in getEventsForSlot(day, hour, 'task')" 
                            :key="task.id"
                            class="text-xs p-1 rounded bg-green-100 border-l-2 border-green-500 cursor-pointer hover:bg-green-200 truncate"
                            @click.stop="$emit('task-click', task)"
                        >
                            <div class="font-medium text-green-900">{{ task.due_time || 'Toute la journée' }}</div>
                            <div class="text-green-700">{{ task.subject }}</div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    weekStart: {
        type: Date,
        required: true
    },
    appointments: {
        type: Array,
        default: () => []
    },
    tasks: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['appointment-click', 'task-click', 'day-click'])

// Generate week days
const weekDays = computed(() => {
    const days = []
    for (let i = 0; i < 7; i++) {
        const day = new Date(props.weekStart)
        day.setDate(day.getDate() + i)
        days.push(day)
    }
    return days
})

// Hours from 9 AM to 7 PM
const hours = Array.from({ length: 11 }, (_, i) => i + 9)

function formatDayName(date) {
    return date.toLocaleDateString('fr-FR', { weekday: 'short' }).toUpperCase()
}

function formatHour(hour) {
    return `${hour}:00`
}

function isToday(date) {
    const today = new Date()
    return date.toDateString() === today.toDateString()
}

function isSameDay(date1, date2) {
    return date1.toDateString() === date2.toDateString()
}

function getEventsForSlot(day, hour, type) {
    if (type === 'appointment') {
        return props.appointments.filter(apt => {
            const aptDate = new Date(apt.date)
            if (!isSameDay(aptDate, day)) return false

            const [aptHour] = apt.time.split(':').map(Number)
            return aptHour === hour
        })
    } else {
        return props.tasks.filter(task => {
            const taskDate = new Date(task.due_date)
            if (!isSameDay(taskDate, day)) return false

            if (task.due_time) {
                const [taskHour] = task.due_time.split(':').map(Number)
                return taskHour === hour
            }
            
            // Show all-day tasks at 9 AM
            return hour === 9
        })
    }
}

function handleCellClick(day, hour) {
    const datetime = new Date(day)
    datetime.setHours(hour, 0, 0, 0)
    emit('day-click', datetime)
}
</script>

<style scoped>
.week-view {
    overflow-x: auto;
}
</style>
