<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm uppercase tracking-widest text-primary-500 font-semibold">Gestion de perte</p>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">Historique des pertes</h1>
                <p class="text-gray-500">Analysez les sorties de stock liées aux pertes, casse, péremptions ou vols.</p>
            </div>
            <RouterLink
                to="/losses"
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-primary-200 text-primary-700 font-medium hover:bg-primary-50"
            >
                Revenir à la déclaration
            </RouterLink>
        </div>

        <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Filtres avancés</h2>
                    <p class="text-sm text-gray-500">Affinez l'historique par période, article, type de perte ou magasin.</p>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg border border-gray-200 hover:border-primary-200 hover:text-primary-600"
                    @click="fetchLosses"
                    :disabled="historyLoading"
                >
                    <ArrowPathIcon class="w-4 h-4 mr-2" :class="{ 'animate-spin': historyLoading }" />
                    Actualiser
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Date début</label>
                    <input v-model="historyFilters.from_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Date fin</label>
                    <input v-model="historyFilters.to_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Article</label>
                    <select v-model="historyFilters.article_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">Tous</option>
                        <option v-for="article in articles" :key="article.id" :value="String(article.id)">{{ article.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Type de perte</label>
                    <select v-model="historyFilters.loss_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">Tous</option>
                        <option v-for="lt in lossTypes" :key="`hist-${lt.value}`" :value="lt.value">{{ lt.label }}</option>
                    </select>
                </div>
                <div v-if="storeSelectionEnabled">
                    <label class="block text-xs text-gray-500 mb-1">Magasin</label>
                    <select v-model="historyFilters.store_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">Tous</option>
                        <option v-for="store in storeOptions" :key="`hist-store-${store.id}`" :value="store.id">{{ store.name }}</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="px-4 py-2 bg-primary-500 text-gray-900 rounded-lg text-sm font-medium hover:bg-primary-600"
                    @click="applyHistoryFilters"
                >
                    Appliquer les filtres
                </button>
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                    @click="resetHistoryFilters"
                >
                    Réinitialiser
                </button>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Article</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Responsable</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Magasin</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Impact</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in historyItems" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(item.loss?.loss_date) }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ item.loss?.reference }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ item.article?.name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ item.quantity }} {{ item.article?.unit || '' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="lossTypeClass(item.loss_type)">
                                    {{ lossTypeLabel(item.loss_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ item.loss?.responsible_employee?.full_name || item.loss?.responsible_name || '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ item.loss?.store_id || '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ formatCurrency(item.total_cost) }}</td>
                        </tr>
                        <tr v-if="!historyItems.length && !historyLoading">
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">Aucune perte trouvée pour les filtres sélectionnés.</td>
                        </tr>
                        <tr v-if="historyLoading">
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">Chargement de l'historique...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="md:hidden space-y-3">
                <div v-if="!historyItems.length && !historyLoading" class="px-4 py-6 text-center text-sm text-gray-500">
                    Aucune perte trouvée pour les filtres sélectionnés.
                </div>
                <div v-if="historyLoading" class="px-4 py-6 text-center text-sm text-gray-500">
                    Chargement de l'historique...
                </div>
                <div v-for="item in historyItems" :key="`card-${item.id}`" class="rounded-xl border border-gray-100 p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-900">{{ item.article?.name || '-' }}</p>
                        <span class="text-xs text-gray-500">{{ formatDate(item.loss?.loss_date) }}</span>
                    </div>
                    <p class="text-xs text-gray-500">Réf: {{ item.loss?.reference || '-' }}</p>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Quantité</span>
                        <span class="text-gray-900">{{ item.quantity }} {{ item.article?.unit || '' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Type</span>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="lossTypeClass(item.loss_type)">
                            {{ lossTypeLabel(item.loss_type) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Responsable</span>
                        <span class="text-gray-900">{{ item.loss?.responsible_employee?.full_name || item.loss?.responsible_name || '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Magasin</span>
                        <span class="text-gray-900">{{ item.loss?.store_id || '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Impact</span>
                        <span class="text-gray-900 font-semibold">{{ formatCurrency(item.total_cost) }}</span>
                    </div>
                </div>
            </div>

            <div v-if="historyMeta.last_page > 1" class="flex items-center justify-between border-t pt-4">
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                    :disabled="historyMeta.current_page <= 1"
                    @click="setHistoryPage(historyMeta.current_page - 1)"
                >
                    Précédent
                </button>
                <p class="text-sm text-gray-600">Page {{ historyMeta.current_page }} / {{ historyMeta.last_page }}</p>
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                    :disabled="historyMeta.current_page >= historyMeta.last_page"
                    @click="setHistoryPage(historyMeta.current_page + 1)"
                >
                    Suivant
                </button>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { lossesApi, articlesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { ArrowPathIcon } from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()

const historyLoading = ref(false)
const articles = ref([])
const historyItems = ref([])
const historyMeta = reactive({ current_page: 1, last_page: 1, total: 0 })

const historyFilters = reactive({
    from_date: '',
    to_date: '',
    article_id: '',
    loss_type: '',
    store_id: '',
    page: 1,
    per_page: 15
})

const lossTypes = [
    { value: 'loss', label: 'Perte' },
    { value: 'breakage', label: 'Casse' },
    { value: 'expiration', label: 'Péremption' },
    { value: 'theft', label: 'Vol' }
]

const formatCurrency = (value) => settingsStore.formatCurrency(Number(value || 0))

const storeOptions = computed(() => {
    const general = settingsStore.settings.general || {}
    if (Array.isArray(general.stores)) {
        return general.stores
    }
    if (general.store_name) {
        return [{ id: general.default_store_id || 1, name: general.store_name }]
    }
    return []
})

const multiStoreEnabled = computed(() => {
    const general = settingsStore.settings.general || {}
    const flag = general.multi_store_enabled
    const enabled = flag === true || flag === 1 || flag === '1'
    return enabled && storeOptions.value.length > 0
})

const storeSelectionEnabled = computed(() => storeOptions.value.length > 0)

const lossTypeMap = lossTypes.reduce((acc, item) => {
    acc[item.value] = item.label
    return acc
}, {})

function lossTypeLabel(type) {
    return lossTypeMap[type] || 'Perte'
}

function lossTypeClass(type) {
    switch (type) {
        case 'breakage':
            return 'bg-orange-100 text-orange-700'
        case 'expiration':
            return 'bg-amber-100 text-amber-700'
        case 'theft':
            return 'bg-red-100 text-red-700'
        default:
            return 'bg-gray-100 text-gray-700'
    }
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

async function fetchArticles() {
    try {
        const response = await articlesApi.list({ manage_stock: true, per_page: 500 })
        articles.value = Array.isArray(response.data) ? response.data : (response.data.data || [])
    } catch (error) {
        console.error('Failed to load articles:', error)
    }
}

async function fetchLosses() {
    historyLoading.value = true
    try {
        const params = { ...historyFilters }
        Object.keys(params).forEach((key) => {
            if (params[key] === '' || params[key] == null) {
                delete params[key]
            }
        })
        const response = await lossesApi.list(params)
        if (Array.isArray(response.data)) {
            historyItems.value = response.data
            historyMeta.current_page = 1
            historyMeta.last_page = 1
            historyMeta.total = response.data.length
        } else {
            historyItems.value = response.data.data || []
            historyMeta.current_page = response.data.meta?.current_page || 1
            historyMeta.last_page = response.data.meta?.last_page || 1
            historyMeta.total = response.data.meta?.total || historyItems.value.length
        }
    } catch (error) {
        console.error('Failed to load loss history', error)
    } finally {
        historyLoading.value = false
    }
}

function applyHistoryFilters() {
    historyFilters.page = 1
    fetchLosses()
}

function resetHistoryFilters() {
    historyFilters.from_date = ''
    historyFilters.to_date = ''
    historyFilters.article_id = ''
    historyFilters.loss_type = ''
    historyFilters.store_id = ''
    historyFilters.page = 1
    fetchLosses()
}

function setHistoryPage(page) {
    if (page < 1 || page > historyMeta.last_page) {
        return
    }
    historyFilters.page = page
    fetchLosses()
}

onMounted(async () => {
    await settingsStore.fetchSettings()
    await Promise.all([
        fetchArticles(),
        fetchLosses()
    ])
})
</script>
