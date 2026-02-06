<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Articles</h1>
                <p class="text-gray-500">Gérez vos articles et leur inventaire</p>
            </div>
            <div class="flex space-x-3">
                <router-link to="/categories" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Catégories
                </router-link>
                <router-link to="/articles/create" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Nouvel Article
                </router-link>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input 
                        v-model="search"
                        type="text"
                        placeholder="Rechercher un article..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                </div>
                <select 
                    v-model="selectedCategory"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <option value="">Toutes les catégories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
                <select 
                    v-model="stockFilter"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <option value="">Tous les stocks</option>
                    <option value="low">Stock bas</option>
                    <option value="out">Rupture de stock</option>
                </select>
            </div>
        </div>

        <!-- Articles Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Article</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="article in filteredArticles" :key="article.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                                    <img v-if="article.photo" :src="article.photo" class="w-full h-full object-cover">
                                    <span v-else class="text-2xl">📦</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ article.name }}</p>
                                    <p class="text-sm text-gray-500">{{ article.sku || 'N/A' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ article.category?.name || 'Non catégorisé' }}
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ formatCurrency(article.sell_price) }}</p>
                            <p class="text-sm text-gray-500">Achat: {{ formatCurrency(article.buy_price || 0) }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span v-if="article.manage_stock" class="text-sm" :class="getStockClass(article)">
                                {{ article.stock_quantity }} {{ article.unit }}
                            </span>
                            <span v-else class="text-sm text-gray-400">Non géré</span>
                        </td>
                        <td class="px-6 py-4">
                            <span 
                                class="px-2 py-1 text-xs font-medium rounded-full"
                                :class="article.is_active ? 'bg-primary-100 text-gray-900' : 'bg-gray-100 text-gray-700'"
                            >
                                {{ article.is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <router-link :to="`/articles/${article.id}/edit`" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                    <PencilIcon class="w-5 h-5" />
                                </router-link>
                                <button @click="confirmDelete(article)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredArticles.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            Aucun article trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer l'article</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer "{{ articleToDelete?.name }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button @click="deleteArticle" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { articlesApi, categoriesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const articles = ref([])
const categories = ref([])
const search = ref('')
const selectedCategory = ref('')
const stockFilter = ref('')
const showDeleteModal = ref(false)
const articleToDelete = ref(null)

const filteredArticles = computed(() => {
    let result = articles.value

    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(a => 
            a.name.toLowerCase().includes(query) ||
            a.sku?.toLowerCase().includes(query)
        )
    }

    if (selectedCategory.value) {
        result = result.filter(a => a.category_id === parseInt(selectedCategory.value))
    }

    if (stockFilter.value === 'low') {
        result = result.filter(a => a.manage_stock && a.stock_quantity <= a.stock_alert_threshold && a.stock_quantity > 0)
    } else if (stockFilter.value === 'out') {
        result = result.filter(a => a.manage_stock && a.stock_quantity === 0)
    }

    return result
})

function getStockClass(article) {
    if (article.stock_quantity === 0) return 'text-red-600 font-medium'
    if (article.stock_quantity <= article.stock_alert_threshold) return 'text-orange-600 font-medium'
    return 'text-gray-900'
}

function confirmDelete(article) {
    articleToDelete.value = article
    showDeleteModal.value = true
}

async function deleteArticle() {
    try {
        await articlesApi.delete(articleToDelete.value.id)
        articles.value = articles.value.filter(a => a.id !== articleToDelete.value.id)
        showDeleteModal.value = false
        articleToDelete.value = null
    } catch (error) {
        console.error('Failed to delete article:', error)
        alert('Erreur lors de la suppression')
    }
}

async function fetchData() {
    try {
        const [articlesRes, categoriesRes] = await Promise.all([
            articlesApi.list(),
            categoriesApi.list()
        ])
        articles.value = Array.isArray(articlesRes.data) ? articlesRes.data : articlesRes.data.data || []
        categories.value = categoriesRes.data
    } catch (error) {
        console.error('Failed to fetch data:', error)
    }
}

onMounted(fetchData)
</script>
