<template>
    <div class="border border-gray-200 rounded-lg overflow-hidden">
        <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50">
            <div>
                <p class="text-xs font-medium text-gray-500">Jour</p>
                <p class="text-xl font-semibold text-gray-900">{{ formattedDate }}</p>
            </div>
            <div class="flex flex-col items-end">
                <button @click="emitDayClick" class="text-sm text-primary-600 font-medium hover:underline">Ajouter un RDV ou une tâche</button>
            </div>
        </div>
        <div class="divide-y divide-gray-100">
            <div 
                v-for="slot in slots" 
                :key="slot"
                class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 cursor-pointer"
                @click="emitTimeSlotClick(slot)"
            >
                <span class="text-xs text-gray-500">{{ slot }}h</span>
                <div class="flex-1 flex gap-2 overflow-hidden">
                    <template v-for="appointment in getAppointmentsForSlot(slot)" :key="`apt-${appointment.id}-${slot}`">
                        <div class="bg-blue-100 border border-blue-200 rounded-lg px-2 py-1 text-xs text-blue-800 truncate" @click.stop="$emit('appointment-click', appointment)">
                            <strong>{{ appointment.time }}</strong> - {{ appointment.subject }}
                        </div>
                    </template>
                    <template v-for="task in getTasksForSlot(slot)" :key="`task-${task.id}-${slot}`">
                        <div class="bg-green-100 border border-green-200 rounded-lg px-2 py-1 text-xs text-green-800 truncate" @click.stop="$emit('task-click', task)">
                            <strong>{{ task.due_time || 'Toute la journée' }}</strong> - {{ task.subject }}
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    date: {
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

const emit = defineEmits(['appointment-click', 'task-click', 'time-slot-click'])

const formattedDate = computed(() => props.date.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }))

const slots = Array.from({ length: 11 }, (_, index) => 9 + index)

function getAppointmentsForSlot(slot) {
    return props.appointments.filter(appointment => {
        const aptDate = new Date(appointment.date)
        if (aptDate.toDateString() !== props.date.toDateString()) return false

        const [hour] = appointment.time.split(':').map(Number)
        return hour === slot
    })
}

function getTasksForSlot(slot) {
    return props.tasks.filter(task => {
        const taskDate = new Date(task.due_date)
        if (taskDate.toDateString() !== props.date.toDateString()) return false

        if (task.due_time) {
            const [hour] = task.due_time.split(':').map(Number)
            return hour === slot
        }
        return slot === 9
    })
}

function emitDayClick() {
    emit('day-click', props.date)
}

function emitTimeSlotClick(slot) {
    const dateTime = new Date(props.date)
    dateTime.setHours(slot, 0, 0, 0)
    emit('time-slot-click', dateTime)
}
</script>
