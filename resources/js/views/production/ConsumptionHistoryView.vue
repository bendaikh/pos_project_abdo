<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Historique de consommation MP</h1>
            <p class="text-gray-500">Traçabilité complète des matières premières consommées.</p>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 grid grid-cols-1 md:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Date début</label>
                <input
                    v-model="filters.from_date"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Date fin</label>
                <input
                    v-model="filters.to_date"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Produit</label>
                <select
                    v-model="filters.produced_article_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <option value="">Tous</option>
                    <option v-for="article in compositeArticles" :key="article.id" :value="article.id">
                        {{ article.name }}
                    </option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Matière première</label>
                <select
                    v-model="filters.article_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <option value="">Toutes</option>
                    <option v-for="article in materials" :key="article.id" :value="article.id">
                        {{ article.name }}
                    </option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Magasin (ID)</label>
                <input
                    v-model="filters.store_id"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
            </div>
            <div class="flex items-end">
                <button
                    @click="fetchConsumptions"
                    type="button"
                    class="w-full px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600"
                >
                    Filtrer
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence production</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Responsable</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit fabriqué</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matière première</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Magasin</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock avant</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock après</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="consumption in consumptions" :key="consumption.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(consumption.consumed_at) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ consumption.production_entry?.reference || '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ consumption.user?.name || '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ consumption.produced_article?.name || '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ consumption.article?.name || '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ consumption.quantity }} {{ consumption.unit || '' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ consumption.store_id || '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ consumption.stock_before }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ consumption.stock_after }}</td>
                    </tr>
                    <tr v-if="consumptions.length === 0">
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">Aucune consommation trouvée.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { articlesApi, consumptionsApi } from '../../api'

const consumptions = ref([])
const allArticles = ref([])

const filters = reactive({
    from_date: '',
    to_date: '',
    produced_article_id: '',
    article_id: '',
    store_id: '',
})

const compositeArticles = computed(() => allArticles.value.filter(article => article.is_composite))
const materials = computed(() => allArticles.value.filter(article => article.manage_stock))

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

async function fetchArticles() {
    try {
        const response = await articlesApi.list()
        allArticles.value = Array.isArray(response.data) ? response.data : (response.data.data || [])
    } catch (error) {
        console.error('Failed to load articles:', error)
    }
}

async function fetchConsumptions() {
    try {
        const response = await consumptionsApi.list({ ...filters })
        consumptions.value = Array.isArray(response.data) ? response.data : (response.data.data || [])
    } catch (error) {
        console.error('Failed to load consumptions:', error)
    }
}

onMounted(async () => {
    await fetchArticles()
    await fetchConsumptions()
})
</script>
