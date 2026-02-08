<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Options</h1>
                <p class="text-gray-500">Gérez les options d'articles (tailles, couleurs, sauces, etc.)</p>
            </div>
            <router-link to="/options/create" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouvelle Option
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input 
                        v-model="search"
                        type="text"
                        placeholder="Rechercher une option..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                </div>
                <select 
                    v-model="typeFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <option value="">Tous les types</option>
                    <option value="fixed">Options uniques (choix unique)</option>
                    <option value="multiple">Options multiples</option>
                </select>
            </div>
        </div>

        <!-- Options Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valeurs</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix Extra</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="option in filteredOptions" :key="option.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ option.name }}</p>
                                <p v-if="option.is_required" class="text-xs text-orange-600">Requis</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span 
                                class="px-2 py-1 text-xs font-medium rounded-full"
                                :class="option.type === 'fixed' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'"
                            >
                                {{ option.type === 'fixed' ? 'Choix unique' : 'Choix multiples' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <div class="flex flex-wrap gap-1">
                                <span 
                                    v-for="(value, idx) in option.values.slice(0, 3)" 
                                    :key="idx"
                                    class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded"
                                >
                                    {{ value }}
                                </span>
                                <span v-if="option.values.length > 3" class="px-2 py-1 text-xs text-gray-500">
                                    +{{ option.values.length - 3 }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ option.extra_price > 0 ? formatCurrency(option.extra_price) : '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <span 
                                class="px-2 py-1 text-xs font-medium rounded-full"
                                :class="option.is_active ? 'bg-primary-100 text-gray-900' : 'bg-gray-100 text-gray-700'"
                            >
                                {{ option.is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <router-link :to="`/options/${option.id}/edit`" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                    <PencilIcon class="w-5 h-5" />
                                </router-link>
                                <button @click="confirmDelete(option)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredOptions.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            Aucune option trouvée
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer l'option</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer "{{ optionToDelete?.name }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button @click="deleteOption" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { optionsApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const options = ref([])
const search = ref('')
const typeFilter = ref('')
const showDeleteModal = ref(false)
const optionToDelete = ref(null)

const filteredOptions = computed(() => {
    let result = options.value

    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(o => 
            o.name.toLowerCase().includes(query) ||
            o.values.some(v => v.toLowerCase().includes(query))
        )
    }

    if (typeFilter.value) {
        result = result.filter(o => o.type === typeFilter.value)
    }

    return result
})

function confirmDelete(option) {
    optionToDelete.value = option
    showDeleteModal.value = true
}

async function deleteOption() {
    try {
        await optionsApi.delete(optionToDelete.value.id)
        options.value = options.value.filter(o => o.id !== optionToDelete.value.id)
        showDeleteModal.value = false
        optionToDelete.value = null
    } catch (error) {
        console.error('Failed to delete option:', error)
        alert('Erreur lors de la suppression')
    }
}

async function fetchOptions() {
    try {
        const response = await optionsApi.list()
        options.value = response.data
    } catch (error) {
        console.error('Failed to fetch options:', error)
    }
}

onMounted(fetchOptions)
</script>
