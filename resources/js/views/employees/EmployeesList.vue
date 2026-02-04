<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Employés</h1>
                <p class="text-gray-500">Gérez votre équipe et leurs accès</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouvel Employé
            </button>
        </div>

        <!-- Employees Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
                v-for="employee in employees" 
                :key="employee.id"
                class="bg-white rounded-xl p-6 shadow-sm border border-gray-100"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-green-600 font-semibold">{{ getInitials(employee.name) }}</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ employee.name }}</h3>
                            <p class="text-sm text-gray-500">{{ getRoleLabel(employee.role) }}</p>
                        </div>
                    </div>
                    <span 
                        class="px-2 py-1 text-xs font-medium rounded-full"
                        :class="getStatusClass(employee.status)"
                    >
                        {{ getStatusLabel(employee.status) }}
                    </span>
                </div>
                
                <div class="mt-4 space-y-2 text-sm">
                    <p v-if="employee.email" class="text-gray-500">{{ employee.email }}</p>
                    <p v-if="employee.phone" class="text-gray-500">{{ employee.phone }}</p>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end space-x-2">
                    <button @click="openForm(employee)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <PencilIcon class="w-5 h-5" />
                    </button>
                    <button @click="confirmDelete(employee)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50">
                        <TrashIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="employees.length === 0" class="text-center py-12">
            <UsersIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900">Aucun employé</h3>
            <p class="text-gray-500">Ajoutez des employés pour gérer les accès au système.</p>
        </div>

        <!-- Employee Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingEmployee ? 'Modifier l\'employé' : 'Nouvel employé' }}
                </h3>
                
                <form @submit.prevent="saveEmployee" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input v-model="form.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="form.email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input v-model="form.phone" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rôle *</label>
                        <select v-model="form.role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="admin">Administrateur</option>
                            <option value="manager">Manager</option>
                            <option value="cashier">Caissier</option>
                            <option value="vendor">Vendeur</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select v-model="form.status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                            <option value="suspended">Suspendu</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="button" @click="showForm = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                        <button type="submit" :disabled="saving" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer l'employé</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer "{{ employeeToDelete?.name }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteEmployee" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { employeesApi } from '../../api'
import { PlusIcon, PencilIcon, TrashIcon, UsersIcon } from '@heroicons/vue/24/outline'

const employees = ref([])
const showForm = ref(false)
const showDeleteModal = ref(false)
const editingEmployee = ref(null)
const employeeToDelete = ref(null)
const saving = ref(false)

const form = reactive({ name: '', email: '', phone: '', role: 'cashier', status: 'active' })

function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function getRoleLabel(role) {
    const labels = { admin: 'Administrateur', manager: 'Manager', cashier: 'Caissier', vendor: 'Vendeur' }
    return labels[role] || role
}

function getStatusClass(status) {
    const classes = { active: 'bg-green-100 text-green-700', inactive: 'bg-gray-100 text-gray-700', suspended: 'bg-red-100 text-red-700' }
    return classes[status] || 'bg-gray-100 text-gray-700'
}

function getStatusLabel(status) {
    const labels = { active: 'Actif', inactive: 'Inactif', suspended: 'Suspendu' }
    return labels[status] || status
}

function openForm(employee = null) {
    editingEmployee.value = employee
    if (employee) {
        form.name = employee.name
        form.email = employee.email || ''
        form.phone = employee.phone || ''
        form.role = employee.role
        form.status = employee.status
    } else {
        form.name = ''
        form.email = ''
        form.phone = ''
        form.role = 'cashier'
        form.status = 'active'
    }
    showForm.value = true
}

function confirmDelete(employee) {
    employeeToDelete.value = employee
    showDeleteModal.value = true
}

async function saveEmployee() {
    saving.value = true
    try {
        if (editingEmployee.value) {
            const response = await employeesApi.update(editingEmployee.value.id, form)
            const index = employees.value.findIndex(e => e.id === editingEmployee.value.id)
            if (index > -1) employees.value[index] = response.data
        } else {
            const response = await employeesApi.create(form)
            employees.value.unshift(response.data)
        }
        showForm.value = false
    } catch (error) {
        alert('Erreur: ' + (error.response?.data?.message || error.message))
    } finally {
        saving.value = false
    }
}

async function deleteEmployee() {
    try {
        await employeesApi.delete(employeeToDelete.value.id)
        employees.value = employees.value.filter(e => e.id !== employeeToDelete.value.id)
        showDeleteModal.value = false
    } catch (error) {
        alert('Erreur lors de la suppression')
    }
}

onMounted(async () => {
    const response = await employeesApi.list()
    employees.value = Array.isArray(response.data) ? response.data : response.data.data || []
})
</script>
