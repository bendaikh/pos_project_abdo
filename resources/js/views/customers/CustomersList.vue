<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Clients</h1>
                <p class="text-gray-500">Gérez vos clients et leur historique d'achats</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouveau Client
            </button>
        </div>

        <!-- Search -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <input 
                v-model="search"
                type="text"
                placeholder="Rechercher par nom, email ou téléphone..."
                class="w-full max-w-md px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
            >
        </div>

        <!-- Customers Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Achats</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total dépensé</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="customer in filteredCustomers" :key="customer.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 font-medium">{{ getInitials(customer.name) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ customer.name }}</p>
                                    <p class="text-sm text-gray-500">{{ customer.city || 'Pas de ville' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ customer.email || '-' }}</p>
                            <p class="text-sm text-gray-500">{{ customer.phone || '-' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ customer.completed_sales_count || 0 }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-green-600">
                            {{ formatCurrency(customer.total_spent || 0) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="openForm(customer)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                    <PencilIcon class="w-5 h-5" />
                                </button>
                                <button @click="confirmDelete(customer)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredCustomers.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            Aucun client trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Customer Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingCustomer ? 'Modifier le client' : 'Nouveau client' }}
                </h3>
                
                <form @submit.prevent="saveCustomer" class="space-y-4">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                        <textarea v-model="form.address" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                            <input v-model="form.city" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                            <input v-model="form.country" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea v-model="form.notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="button" @click="showForm = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" :disabled="saving" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer le client</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer "{{ customerToDelete?.name }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteCustomer" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { customersApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const customers = ref([])
const search = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const editingCustomer = ref(null)
const customerToDelete = ref(null)
const saving = ref(false)

const form = reactive({ name: '', email: '', phone: '', address: '', city: '', country: '', notes: '' })

const filteredCustomers = computed(() => {
    if (!search.value) return customers.value
    const query = search.value.toLowerCase()
    return customers.value.filter(c => 
        c.name.toLowerCase().includes(query) ||
        c.email?.toLowerCase().includes(query) ||
        c.phone?.includes(query)
    )
})

function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function openForm(customer = null) {
    editingCustomer.value = customer
    if (customer) {
        Object.assign(form, customer)
    } else {
        Object.keys(form).forEach(key => form[key] = '')
    }
    showForm.value = true
}

function confirmDelete(customer) {
    customerToDelete.value = customer
    showDeleteModal.value = true
}

async function saveCustomer() {
    saving.value = true
    try {
        if (editingCustomer.value) {
            const response = await customersApi.update(editingCustomer.value.id, form)
            const index = customers.value.findIndex(c => c.id === editingCustomer.value.id)
            if (index > -1) customers.value[index] = response.data
        } else {
            const response = await customersApi.create(form)
            customers.value.unshift(response.data)
        }
        showForm.value = false
    } catch (error) {
        alert('Erreur: ' + (error.response?.data?.message || error.message))
    } finally {
        saving.value = false
    }
}

async function deleteCustomer() {
    try {
        await customersApi.delete(customerToDelete.value.id)
        customers.value = customers.value.filter(c => c.id !== customerToDelete.value.id)
        showDeleteModal.value = false
    } catch (error) {
        alert('Erreur lors de la suppression')
    }
}

onMounted(async () => {
    const response = await customersApi.list({ with_stats: true })
    customers.value = Array.isArray(response.data) ? response.data : response.data.data || []
})
</script>
