<template>
    <div class="grid gap-2">
        <div class="grid grid-cols-7 gap-1 text-center text-xs uppercase text-gray-500">
            <div v-for="day in weekdays" :key="day">{{ day }}</div>
        </div>
        <div class="grid grid-cols-7 gap-2">
            <div 
                v-for="day in days"
                :key="day.date.toISOString()"
                class="p-3 rounded-lg border border-gray-100 cursor-pointer"
                :class="{
                    'bg-blue-50 border-blue-200': day.isCurrentMonth && day.isToday,
                    'bg-white border-gray-100': day.isCurrentMonth && !day.isToday,
                    'bg-gray-50 text-gray-400 border-transparent': !day.isCurrentMonth
                }"
                @click="$emit('day-click', day.date)"
            >
                <div class="text-sm font-semibold" :class="{ 'text-blue-600': day.isToday }">{{ day.date.getDate() }}</div>
                <div class="mt-1 space-y-1 text-xs">
                    <div 
                        v-for="appointment in day.appointments" 
                        :key="`apt-${appointment.id}`"
                        class="truncate text-blue-700"
                        @click.stop="$emit('appointment-click', appointment)"
                    >
                        {{ appointment.time }} - {{ appointment.subject }}
                    </div>
                    <div 
                        v-for="task in day.tasks" 
                        :key="`task-${task.id}`"
                        class="truncate text-green-700"
                        @click.stop="$emit('task-click', task)"
                    >
                        {{ task.due_time || 'Toute la journée' }} - {{ task.subject }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    month: {
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

const weekdays = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']

const days = computed(() => {
    const month = props.month.getMonth()
    const year = props.month.getFullYear()
    const firstDay = new Date(year, month, 1)
    const startDay = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1
    const totalDays = new Date(year, month + 1, 0).getDate()

    const result = []
    for (let i = 0; i < 42; i++) {
        const date = new Date(year, month, i + 1 - startDay)
        const isCurrentMonth = date.getMonth() === month
        const dayAppointments = props.appointments.filter(app => new Date(app.date).toDateString() === date.toDateString())
        const dayTasks = props.tasks.filter(task => new Date(task.due_date).toDateString() === date.toDateString())

        result.push({
            date,
            isCurrentMonth,
            isToday: date.toDateString() === new Date().toDateString(),
            appointments: dayAppointments,
            tasks: dayTasks
        })
    }

    return result
})
</script>
