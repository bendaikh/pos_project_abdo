<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Unités Mesure</h1>
                <p class="text-gray-500">Gérez les unités de mesure de vos produits</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouvelle unité
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Symbole</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="unit in units" :key="unit.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ unit.code }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ unit.name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ unit.symbol || '—' }}</td>
                        <td class="px-6 py-4">
                            <span
                                class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full"
                                :class="unit.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                            >
                                {{ unit.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex space-x-1">
                                <button @click="openForm(unit)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                    <PencilIcon class="w-4 h-4" />
                                </button>
                                <button @click="confirmDelete(unit)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="units.length === 0 && !loading" class="text-center py-12">
                <ScaleIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900">Aucune unité</h3>
                <p class="text-gray-500">Ajoutez une unité de mesure pour vos produits.</p>
            </div>
        </div>

        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingUnit ? 'Modifier l\'unité' : 'Nouvelle unité' }}
                </h3>
                <form @submit.prevent="saveUnit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
                        <input
                            v-model="form.code"
                            type="text"
                            required
                            placeholder="ex: piece, kg, carton"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="ex: Pièce, Kilogramme"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Symbole</label>
                        <input
                            v-model="form.symbol"
                            type="text"
                            placeholder="ex: kg, L, pce"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                    </div>
                    <div class="flex items-center">
                        <input id="unit-active" v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-primary-500 focus:ring-primary-500">
                        <label for="unit-active" class="ml-2 text-sm text-gray-700">Unité active</label>
                    </div>
                    <div class="flex space-x-3 pt-4">
                        <button type="button" @click="showForm = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600">
                            {{ editingUnit ? 'Enregistrer' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer l'unité</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Confirmer la suppression de « {{ unitToDelete?.name }} » ?
                </p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button @click="deleteUnit" class="flex-1 px-4 py-2 bg-red-500 text-white font-medium rounded-lg hover:bg-red-600">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { PlusIcon, PencilIcon, TrashIcon, ScaleIcon } from '@heroicons/vue/24/outline'
import { measureUnitsApi } from '../../api'

const units = ref([])
const loading = ref(false)
const showForm = ref(false)
const showDeleteModal = ref(false)
const editingUnit = ref(null)
const unitToDelete = ref(null)

const form = reactive({
    code: '',
    name: '',
    symbol: '',
    is_active: true,
})

async function fetchUnits() {
    loading.value = true
    try {
        const { data } = await measureUnitsApi.list()
        units.value = data
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

function openForm(unit = null) {
    editingUnit.value = unit
    form.code = unit?.code || ''
    form.name = unit?.name || ''
    form.symbol = unit?.symbol || ''
    form.is_active = unit?.is_active ?? true
    showForm.value = true
}

async function saveUnit() {
    try {
        const payload = {
            code: form.code,
            name: form.name,
            symbol: form.symbol || null,
            is_active: form.is_active,
        }
        if (editingUnit.value) {
            await measureUnitsApi.update(editingUnit.value.id, payload)
        } else {
            await measureUnitsApi.create(payload)
        }
        showForm.value = false
        await fetchUnits()
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur lors de l\'enregistrement')
    }
}

function confirmDelete(unit) {
    unitToDelete.value = unit
    showDeleteModal.value = true
}

async function deleteUnit() {
    try {
        await measureUnitsApi.delete(unitToDelete.value.id)
        showDeleteModal.value = false
        await fetchUnits()
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur lors de la suppression')
    }
}

onMounted(fetchUnits)
</script>
