<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion de Stock</h1>
                <p class="text-gray-500">Suivez et gérez votre inventaire</p>
            </div>
            <button @click="showMovementForm = true" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Mouvement de Stock
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Articles gérés</p>
                <p class="text-2xl font-bold text-gray-900">{{ stockItems.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Stock bas</p>
                <p class="text-2xl font-bold text-orange-600">{{ lowStockCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Rupture de stock</p>
                <p class="text-2xl font-bold text-red-600">{{ outOfStockCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Valeur du stock</p>
                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(totalStockValue) }}</p>
            </div>
        </div>

        <!-- Stock Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <input 
                    v-model="search"
                    type="text"
                    placeholder="Rechercher un article..."
                    class="w-full max-w-sm px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                >
            </div>
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Article</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seuil</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ item.name }}</p>
                            <p class="text-sm text-gray-500">{{ item.sku || 'N/A' }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ item.category?.name || '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="font-medium" :class="getStockClass(item)">
                                {{ item.stock_quantity }} {{ item.unit }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ item.stock_alert_threshold }}</td>
                        <td class="px-6 py-4">
                            <span 
                                class="px-2 py-1 text-xs font-medium rounded-full"
                                :class="getStatusClass(item)"
                            >
                                {{ getStatusLabel(item) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="openMovementForm(item)" class="text-green-600 hover:text-green-700 text-sm font-medium">
                                Ajuster
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Movement Form Modal -->
        <div v-if="showMovementForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showMovementForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Mouvement de Stock</h3>
                
                <form @submit.prevent="saveMovement" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Article *</label>
                        <select 
                            v-model="movementForm.article_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            <option v-for="item in stockItems" :key="item.id" :value="item.id">{{ item.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select 
                            v-model="movementForm.type"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            <option value="in">Entrée</option>
                            <option value="out">Sortie</option>
                            <option value="adjustment">Ajustement</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantité *</label>
                        <input 
                            v-model.number="movementForm.quantity"
                            type="number"
                            min="1"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Raison</label>
                        <input 
                            v-model="movementForm.reason"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Ex: Réapprovisionnement"
                        >
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="button" @click="showMovementForm = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" :disabled="saving" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { stockApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { PlusIcon } from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const stockItems = ref([])
const search = ref('')
const showMovementForm = ref(false)
const saving = ref(false)

const movementForm = reactive({
    article_id: null,
    type: 'in',
    quantity: 1,
    reason: ''
})

const filteredItems = computed(() => {
    if (!search.value) return stockItems.value
    const query = search.value.toLowerCase()
    return stockItems.value.filter(item => 
        item.name.toLowerCase().includes(query) ||
        item.sku?.toLowerCase().includes(query)
    )
})

const lowStockCount = computed(() => 
    stockItems.value.filter(i => i.stock_quantity <= i.stock_alert_threshold && i.stock_quantity > 0).length
)

const outOfStockCount = computed(() => 
    stockItems.value.filter(i => i.stock_quantity === 0).length
)

const totalStockValue = computed(() => 
    stockItems.value.reduce((sum, item) => sum + (item.stock_quantity * (item.buy_price || item.sell_price)), 0)
)

function getStockClass(item) {
    if (item.stock_quantity === 0) return 'text-red-600'
    if (item.stock_quantity <= item.stock_alert_threshold) return 'text-orange-600'
    return 'text-gray-900'
}

function getStatusClass(item) {
    if (item.stock_quantity === 0) return 'bg-red-100 text-red-700'
    if (item.stock_quantity <= item.stock_alert_threshold) return 'bg-orange-100 text-orange-700'
    return 'bg-green-100 text-green-700'
}

function getStatusLabel(item) {
    if (item.stock_quantity === 0) return 'Rupture'
    if (item.stock_quantity <= item.stock_alert_threshold) return 'Stock bas'
    return 'En stock'
}

function openMovementForm(item = null) {
    movementForm.article_id = item?.id || stockItems.value[0]?.id
    movementForm.type = 'in'
    movementForm.quantity = 1
    movementForm.reason = ''
    showMovementForm.value = true
}

async function saveMovement() {
    saving.value = true
    try {
        await stockApi.recordMovement(movementForm)
        await fetchStock()
        showMovementForm.value = false
    } catch (error) {
        console.error('Failed to save movement:', error)
        alert('Erreur: ' + (error.response?.data?.message || error.message))
    } finally {
        saving.value = false
    }
}

async function fetchStock() {
    try {
        const response = await stockApi.list()
        stockItems.value = Array.isArray(response.data) ? response.data : response.data.data || []
    } catch (error) {
        console.error('Failed to fetch stock:', error)
    }
}

onMounted(fetchStock)
</script>
