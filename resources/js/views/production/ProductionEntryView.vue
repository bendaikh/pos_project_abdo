<template>
    <div class="min-h-[calc(100vh-64px)] bg-[#f4f3ef] p-4 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Entrée de Production</h1>
                <p class="text-sm text-gray-500">Menu de production type POS pour ajouter des articles fabriqués.</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
            <div class="flex flex-wrap items-center gap-4 px-4 py-3 border-b border-gray-200">
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Date:</span>
                        <input
                            v-model="productionDate"
                            type="date"
                            class="px-3 py-1.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Responsable:</span>
                        <input
                            :value="authStore.userName"
                            type="text"
                            class="px-3 py-1.5 border border-gray-200 rounded-lg bg-gray-50"
                            disabled
                        >
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">Référence:</span>
                        <input
                            :value="currentReference || 'Auto-générée à la validation'"
                            type="text"
                            class="px-3 py-1.5 border border-gray-200 rounded-lg bg-gray-50"
                            disabled
                        >
                    </div>
                </div>
            </div>

            <div class="px-4 py-3 border-b border-gray-200">
                <div class="relative">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher un article"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                    <MagnifyingGlassIcon class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
                </div>
            </div>

            <div class="px-4 py-3 border-b border-gray-200">
                <div class="flex gap-3 overflow-x-auto">
                    <button
                        v-for="tab in categoryTabs"
                        :key="tab.id"
                        @click="selectedCategoryId = tab.id"
                        type="button"
                        class="flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-wide transition-colors"
                        :class="selectedCategoryId === tab.id ? 'border-primary-500 bg-primary-50 text-primary-600' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                    >
                        <span>{{ tab.label }}</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-[2fr_1fr] gap-4 p-4">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <button
                            v-for="article in filteredArticles"
                            :key="article.id"
                            @click="addItem(article)"
                            type="button"
                            class="cursor-pointer rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-lg transition-shadow h-full flex flex-col text-left"
                        >
                            <div class="flex-1 rounded-t-2xl bg-gray-100 flex items-center justify-center overflow-hidden">
                                <img
                                    v-if="article.photo"
                                    :src="article.photo"
                                    :alt="article.name"
                                    class="h-full w-full object-cover"
                                >
                                <span v-else class="text-3xl">📦</span>
                            </div>
                            <div class="p-3 space-y-1">
                                <h3 class="text-sm font-semibold text-gray-900 truncate">{{ article.name }}</h3>
                                <p class="text-xs text-gray-500">{{ article.unit || 'Pièce' }}</p>
                            </div>
                        </button>
                        <div v-if="filteredArticles.length === 0" class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white/80 p-6 text-center text-sm text-gray-500">
                            Aucun article composite ne correspond à la recherche.
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200 p-4">
                        <h3 class="text-sm font-semibold text-gray-800 mb-3">Coût de production (MP)</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-gray-600 uppercase text-[11px]">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Produit</th>
                                        <th class="px-3 py-2 text-left">Quantité</th>
                                        <th class="px-3 py-2 text-left">Coût total MP</th>
                                        <th class="px-3 py-2 text-left">Coût unitaire</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="row in productionCostRows" :key="row.article_id">
                                        <td class="px-3 py-2">{{ row.name }}</td>
                                        <td class="px-3 py-2">{{ row.quantity }}</td>
                                        <td class="px-3 py-2 font-semibold text-gray-900">{{ formatCurrency(row.totalCost) }}</td>
                                        <td class="px-3 py-2">{{ formatCurrency(row.unitCost) }}</td>
                                    </tr>
                                    <tr v-if="productionCostRows.length === 0">
                                        <td colspan="4" class="px-3 py-4 text-center text-sm text-gray-500">
                                            Ajoutez des articles pour calculer le coût.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex items-center justify-between text-sm text-gray-700 mt-3">
                            <span>Total MP</span>
                            <span class="font-semibold text-primary-600">{{ formatCurrency(estimatedTotalCost) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-4 flex flex-col">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-800">Articles Produits</h3>
                        <span class="text-xs text-gray-500">{{ productionItems.length }} article(s)</span>
                    </div>

                    <div class="flex-1 overflow-y-auto">
                        <div v-if="productionItems.length === 0" class="text-sm text-gray-400 text-center py-10">
                            Aucun article sélectionné.
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="item in productionItems"
                                :key="item.article_id"
                                class="flex items-center gap-3 border border-gray-200 rounded-xl p-3"
                            >
                                <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center">
                                    <img v-if="item.photo" :src="item.photo" :alt="item.name" class="w-full h-full object-cover">
                                    <span v-else>📦</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-900">{{ item.name }}</p>
                                    <p class="text-xs text-gray-500">Quantité produite</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button
                                        type="button"
                                        class="w-7 h-7 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50"
                                        @click="adjustQuantity(item, -1)"
                                    >
                                        <MinusIcon class="w-4 h-4 mx-auto" />
                                    </button>
                                    <input
                                        v-model.number="item.quantity"
                                        type="number"
                                        min="1"
                                        step="1"
                                        class="w-14 text-center px-2 py-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                    <button
                                        type="button"
                                        class="w-7 h-7 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50"
                                        @click="adjustQuantity(item, 1)"
                                    >
                                        <PlusIcon class="w-4 h-4 mx-auto" />
                                    </button>
                                </div>
                                <button
                                    type="button"
                                    class="text-gray-400 hover:text-red-600 ml-2"
                                    @click="removeItem(item.article_id)"
                                >
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-3 mt-3 text-sm text-gray-600 space-y-2">
                        <div class="flex items-center justify-between">
                            <span>Total articles produits</span>
                            <span class="font-semibold text-gray-900">{{ totalQuantity }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Coût total MP</span>
                            <span class="font-semibold text-primary-600">{{ formatCurrency(estimatedTotalCost) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 mt-4">
                        <button
                            type="button"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                            @click="resetForm"
                        >
                            Annuler
                        </button>
                        <button
                            type="button"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                            :disabled="saving || productionItems.length === 0"
                            @click="validateProduction"
                        >
                            Valider Production
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-4 py-3 border-t border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea
                    v-model="notes"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Informations complémentaires sur cette production..."
                ></textarea>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { articlesApi, productionApi, categoriesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { useAuthStore } from '../../stores/auth'
import { MagnifyingGlassIcon, PlusIcon, MinusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const authStore = useAuthStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const search = ref('')
const allArticles = ref([])
const categories = ref([])
const selectedCategoryId = ref('all')
const productionItems = ref([])
const productionDate = ref(new Date().toISOString().split('T')[0])
const notes = ref('')
const saving = ref(false)
const currentReference = ref('')
const currentEntryId = ref(null)
const articleDetails = ref({})

const categoryTabs = computed(() => [
    { id: 'all', label: 'Tous' },
    ...categories.value.map((cat) => ({ id: cat.id, label: cat.name })),
])

const filteredArticles = computed(() => {
    let result = allArticles.value
    if (selectedCategoryId.value !== 'all') {
        result = result.filter(article => article.category_id === selectedCategoryId.value)
    }
    if (!search.value) return result
    const query = search.value.toLowerCase()
    return result.filter(article =>
        article.name.toLowerCase().includes(query) ||
        article.sku?.toLowerCase().includes(query)
    )
})

const totalQuantity = computed(() =>
    productionItems.value.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0)
)

const estimatedTotalCost = computed(() =>
    productionItems.value.reduce((sum, item) => sum + getEstimatedLineCost(item), 0)
)

const productionCostRows = computed(() =>
    productionItems.value.map((item) => ({
        article_id: item.article_id,
        name: item.name,
        quantity: Number(item.quantity) || 0,
        unitCost: getEstimatedUnitCost(item),
        totalCost: getEstimatedLineCost(item),
    }))
)

function getEstimatedUnitCost(item) {
    const detail = articleDetails.value[item.article_id]
    if (!detail || !Array.isArray(detail.bom_items)) return 0
    return detail.bom_items.reduce((sum, bom) => {
        const unitCost = Number(bom.unit_cost ?? bom.component?.buy_price ?? 0)
        return sum + (Number(bom.quantity) * unitCost)
    }, 0)
}

function getEstimatedLineCost(item) {
    return getEstimatedUnitCost(item) * (Number(item.quantity) || 0)
}

async function fetchCompositeArticles() {
    try {
        const response = await articlesApi.list({ is_composite: true })
        allArticles.value = Array.isArray(response.data) ? response.data : (response.data.data || [])
    } catch (error) {
        console.error('Failed to load composite articles:', error)
    }
}

async function fetchCategories() {
    try {
        const response = await categoriesApi.list({ active: true })
        categories.value = Array.isArray(response.data) ? response.data : (response.data.data || [])
    } catch (error) {
        console.error('Failed to load categories:', error)
    }
}

async function loadArticleDetails(articleId) {
    if (articleDetails.value[articleId]) return
    try {
        const response = await articlesApi.get(articleId)
        articleDetails.value = {
            ...articleDetails.value,
            [articleId]: response.data,
        }
    } catch (error) {
        console.error('Failed to load article details:', error)
    }
}

function addItem(article) {
    const existing = productionItems.value.find(item => item.article_id === article.id)
    if (existing) {
        existing.quantity += 1
        return
    }

    productionItems.value.push({
        article_id: article.id,
        name: article.name,
        unit: article.unit || 'piece',
        photo: article.photo || null,
        quantity: 1,
    })

    loadArticleDetails(article.id)
}

function removeItem(articleId) {
    productionItems.value = productionItems.value.filter(item => item.article_id !== articleId)
}

function adjustQuantity(item, delta) {
    const next = Number(item.quantity) + delta
    item.quantity = Math.max(1, next)
}

function resetForm() {
    productionItems.value = []
    notes.value = ''
    currentReference.value = ''
    currentEntryId.value = null
    productionDate.value = new Date().toISOString().split('T')[0]
}

async function validateProduction() {
    if (productionItems.value.length === 0) {
        alert('Veuillez ajouter des produits à produire.')
        return
    }

    saving.value = true
    try {
        if (currentEntryId.value) {
            const response = await productionApi.validate(currentEntryId.value)
            currentReference.value = response.data.reference
        } else {
            const response = await productionApi.create({
                produced_at: productionDate.value,
                notes: notes.value,
                status: 'validated',
                items: productionItems.value.map(item => ({
                    article_id: item.article_id,
                    quantity: item.quantity,
                })),
            })
            currentEntryId.value = response.data.id
            currentReference.value = response.data.reference
        }

        alert('Production validée. Les stocks ont été mis à jour.')
        resetForm()
    } catch (error) {
        const message = error.response?.data?.message || error.response?.data?.errors
        alert('Erreur: ' + (message ? JSON.stringify(message) : error.message))
    } finally {
        saving.value = false
    }
}

onMounted(async () => {
    await authStore.initAuth()
    await settingsStore.fetchSettings()
    await fetchCategories()
    await fetchCompositeArticles()
})
</script>
