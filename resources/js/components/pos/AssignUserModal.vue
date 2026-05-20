<template>
    <div class="fixed inset-0 z-[80] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="$emit('close')"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h3 class="text-lg font-bold text-gray-900">Affecter à utilisateur</h3>
                <button type="button" class="rounded-lg p-2 hover:bg-gray-100" @click="$emit('close')">
                    <XMarkIcon class="h-5 w-5 text-gray-500" />
                </button>
            </div>

            <div class="p-5">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Rechercher un employé..."
                    class="mb-3 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <div v-if="loading" class="py-8 text-center text-sm text-gray-500">Chargement...</div>
                <div v-else class="max-h-72 space-y-1 overflow-y-auto">
                    <button
                        v-for="employee in filteredEmployees"
                        :key="employee.id"
                        type="button"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm hover:bg-blue-50"
                        :class="selectedUserId === resolveUserId(employee) ? 'bg-blue-50 ring-1 ring-blue-200' : ''"
                        @click="selectEmployee(employee)"
                    >
                        <span class="font-medium text-gray-800">{{ employee.name }}</span>
                        <span class="text-xs text-gray-500 capitalize">{{ employee.role || 'employé' }}</span>
                    </button>
                    <p v-if="!filteredEmployees.length" class="px-3 py-6 text-center text-sm text-gray-500">
                        Aucun employé disponible.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-200 px-5 py-4">
                <button type="button" class="rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-100" @click="$emit('close')">
                    Annuler
                </button>
                <button
                    type="button"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    :disabled="!selectedUserId || saving"
                    @click="confirmSelection"
                >
                    {{ saving ? 'Enregistrement...' : 'Confirmer' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { employeesApi } from '../../api'

const props = defineProps({
    currentUserId: {
        type: [Number, String],
        default: null,
    },
})

const emit = defineEmits(['close', 'assigned'])

const loading = ref(true)
const saving = ref(false)
const search = ref('')
const employees = ref([])
const selectedUserId = ref(props.currentUserId ? Number(props.currentUserId) : null)

const filteredEmployees = computed(() => {
    const query = search.value.trim().toLowerCase()
    return employees.value.filter((employee) => {
        if (!query) return true
        return String(employee.name || '').toLowerCase().includes(query)
            || String(employee.email || '').toLowerCase().includes(query)
    })
})

function resolveUserId(employee) {
    return Number(employee?.user_id || employee?.user?.id || 0) || null
}

function selectEmployee(employee) {
    selectedUserId.value = resolveUserId(employee)
}

async function fetchEmployees() {
    loading.value = true
    try {
        const response = await employeesApi.list({ status: 'active', paginate: false })
        const rows = Array.isArray(response.data?.data) ? response.data.data : (response.data || [])
        employees.value = rows.filter((employee) => resolveUserId(employee))
    } catch (error) {
        console.error('Failed to load employees:', error)
        employees.value = []
    } finally {
        loading.value = false
    }
}

function confirmSelection() {
    const employee = employees.value.find((row) => resolveUserId(row) === selectedUserId.value)
    if (!employee || !selectedUserId.value) {
        return
    }

    emit('assigned', {
        user_id: selectedUserId.value,
        user_name: employee.user?.name || employee.name,
        employee,
    })
}

onMounted(fetchEmployees)
</script>
