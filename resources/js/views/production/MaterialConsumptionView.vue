<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Consommation MP</h1>
                <p class="text-gray-500">Enregistrez manuellement les consommations de matières premières.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 max-w-3xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Matière première *</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher une matière première..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                    <div v-if="search" class="mt-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg bg-white">
                        <div v-if="filteredMaterials.length">
                            <button
                                v-for="material in filteredMaterials"
                                :key="material.id"
                                @click="selectMaterial(material)"
                                type="button"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center justify-between gap-3"
                            >
                                <span>{{ material.name }} ({{ material.unit || 'piece' }})</span>
                                <span class="text-gray-400 text-xs">Stock: {{ material.stock_quantity }}</span>
                            </button>
                        </div>
                        <div v-else-if="noMaterialResults" class="px-4 py-3 text-sm text-gray-500 space-y-1">
                            <p>Aucun résultat. Créez la matière première si elle n'existe pas encore.</p>
                            <router-link
                                to="/articles/create"
                                class="text-primary-600 hover:underline text-sm"
                            >
                                Ajouter une nouvelle matière première
                            </router-link>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantité consommée *</label>
                    <input
                        v-model.number="quantity"
                        type="number"
                        min="1"
                        step="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motif *</label>
                    <select
                        v-model="reason"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="production">Production</option>
                        <option value="loss">Perte</option>
                        <option value="adjustment">Ajustement</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input
                        v-model="consumedAt"
                        type="date"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsable</label>
                    <input
                        :value="authStore.userName"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50"
                        disabled
                    >
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea
                        v-model="notes"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="Ajouter un commentaire..."
                    ></textarea>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mt-6">
                <div v-if="selectedMaterial" class="space-y-1 text-sm text-gray-600">
                    <div>
                        Sélectionné: <span class="font-semibold">{{ selectedMaterial.name }}</span>
                    </div>
                    <div class="text-xs text-gray-500">
                        Stock disponible: <span class="font-medium">{{ selectedMaterial.stock_quantity || 0 }} {{ selectedMaterial.unit || 'piece' }}</span>
                    </div>
                    <div class="text-xs text-gray-500">
                        Prix d'achat unitaire: <span class="font-medium">{{ formatCurrency(selectedMaterial.buy_price) }}</span>
                    </div>
                </div>
                <button
                    @click="submitConsumption"
                    :disabled="saving || !selectedMaterial"
                    type="button"
                    class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50"
                >
                    Enregistrer
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { articlesApi, consumptionsApi } from '../../api'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()

const search = ref('')
const materials = ref([])
const selectedMaterial = ref(null)
const quantity = ref(1)
const reason = ref('production')
const consumedAt = ref(new Date().toISOString().split('T')[0])
const notes = ref('')
const saving = ref(false)

const filteredMaterials = computed(() => {
    if (!search.value) return []
    const query = search.value.toLowerCase()
    return materials.value.filter(material =>
        material.name.toLowerCase().includes(query) ||
        material.sku?.toLowerCase().includes(query)
    )
})

const noMaterialResults = computed(() => search.value && filteredMaterials.value.length === 0)

async function fetchMaterials() {
    try {
        const response = await articlesApi.list({ manage_stock: true })
        materials.value = Array.isArray(response.data) ? response.data : (response.data.data || [])
    } catch (error) {
        console.error('Failed to fetch materials:', error)
    }
}

function formatCurrency(value) {
    if (value === null || value === undefined || isNaN(Number(value))) {
        return '-'
    }
    return Number(value)
        .toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        + ' DH'
}

function selectMaterial(material) {
    selectedMaterial.value = material
    search.value = ''
}

async function submitConsumption() {
    if (!selectedMaterial.value) {
        alert('Veuillez sélectionner une matière première.')
        return
    }

    saving.value = true
    try {
        await consumptionsApi.create({
            article_id: selectedMaterial.value.id,
            quantity: quantity.value,
            reason: reason.value,
            consumed_at: consumedAt.value,
            notes: notes.value,
        })

        alert('Consommation enregistrée.')
        quantity.value = 1
        notes.value = ''
        selectedMaterial.value = null
        search.value = ''
        await fetchMaterials()
    } catch (error) {
        const message = error.response?.data?.message || error.response?.data?.errors
        alert('Erreur: ' + (message ? JSON.stringify(message) : error.message))
    } finally {
        saving.value = false
    }
}

onMounted(async () => {
    await authStore.initAuth()
    await fetchMaterials()
})
</script>
