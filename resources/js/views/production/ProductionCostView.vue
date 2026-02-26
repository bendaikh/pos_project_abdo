<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Coût de production</h1>
            <p class="text-gray-500">Résumé des coûts MP par production validée.</p>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 grid grid-cols-1 md:grid-cols-5 gap-3">
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
                <label class="block text-xs text-gray-500 mb-1">Référence</label>
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="PRD-2024..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
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
                    @click="fetchEntries"
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit / Quantité</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Coût total MP</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Coût unitaire</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Responsable</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date / Magasin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="row in costRows" :key="row.key" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ row.reference }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <div class="font-medium text-gray-900">{{ row.product }}</div>
                            <div class="text-xs text-gray-500">Quantité: {{ row.quantity }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 font-semibold">{{ formatCurrency(row.totalCost) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ formatCurrency(row.unitCost) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ row.responsable }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <div>{{ row.date }}</div>
                            <div class="text-xs text-gray-500">Magasin: {{ row.store }}</div>
                        </td>
                    </tr>
                    <tr v-if="costRows.length === 0">
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Aucune production trouvée.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { productionApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const entries = ref([])

const filters = reactive({
    from_date: '',
    to_date: '',
    search: '',
    store_id: '',
})

const costRows = computed(() => {
    const rows = []
    entries.value.forEach((entry) => {
        const base = {
            reference: entry.reference,
            responsable: entry.user?.name || '-',
            date: entry.produced_at ? new Date(entry.produced_at).toLocaleDateString('fr-FR') : '-',
            store: entry.store_id || '-',
        }
        ;(entry.items || []).forEach((item) => {
            const unitCost = Number(item.unit_cost) || 0
            const totalCost = Number(item.total_cost) || unitCost * Number(item.quantity || 0)
            rows.push({
                key: `${entry.id}-${item.id}`,
                ...base,
                product: item.article?.name || '-',
                quantity: item.quantity || 0,
                unitCost,
                totalCost,
            })
        })
    })
    return rows
})

async function fetchEntries() {
    try {
        const response = await productionApi.list({
            ...filters,
            status: 'validated',
        })
        entries.value = Array.isArray(response.data) ? response.data : (response.data.data || [])
    } catch (error) {
        console.error('Failed to load production costs:', error)
    }
}

onMounted(async () => {
    await settingsStore.fetchSettings()
    await fetchEntries()
})
</script>
