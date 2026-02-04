<template>
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Modifier l\'Article' : 'Nouvel Article' }}</h1>
                <p class="text-gray-500">{{ isEdit ? 'Modifiez les informations de l\'article' : 'Ajoutez un nouveau produit à votre inventaire' }}</p>
            </div>
            <router-link to="/articles" class="text-gray-500 hover:text-gray-700">
                <XMarkIcon class="w-6 h-6" />
            </router-link>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations de base</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'article *</label>
                        <input 
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU / Code</label>
                        <input 
                            v-model="form.sku"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <select 
                            v-model="form.category_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            <option :value="null">Sélectionner une catégorie</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unité</label>
                        <select 
                            v-model="form.unit"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            <option value="piece">Pièce</option>
                            <option value="kg">Kilogramme (kg)</option>
                            <option value="lb">Livre (lb)</option>
                            <option value="g">Gramme (g)</option>
                            <option value="l">Litre (L)</option>
                            <option value="ml">Millilitre (ml)</option>
                            <option value="doz">Douzaine</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea 
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Prix</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix de vente *</label>
                        <div class="relative">
                            <input 
                                v-model.number="form.sell_price"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            >
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">{{ settingsStore.currencyCode }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix d'achat</label>
                        <div class="relative">
                            <input 
                                v-model.number="form.buy_price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            >
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">{{ settingsStore.currencyCode }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Gestion du stock</h2>
                
                <div class="flex items-center space-x-3 mb-4">
                    <input 
                        v-model="form.manage_stock"
                        type="checkbox"
                        id="manage_stock"
                        class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                    >
                    <label for="manage_stock" class="text-sm font-medium text-gray-700">
                        Activer la gestion de stock
                    </label>
                </div>

                <div v-if="form.manage_stock" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantité en stock</label>
                        <input 
                            v-model.number="form.stock_quantity"
                            type="number"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Seuil d'alerte</label>
                        <input 
                            v-model.number="form.stock_alert_threshold"
                            type="number"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Statut</h2>
                
                <div class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <input 
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                        >
                        <label for="is_active" class="text-sm font-medium text-gray-700">
                            Article actif
                        </label>
                    </div>
                    <div class="flex items-center space-x-3">
                        <input 
                            v-model="form.is_favorite"
                            type="checkbox"
                            id="is_favorite"
                            class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                        >
                        <label for="is_favorite" class="text-sm font-medium text-gray-700">
                            Marquer comme favori (apparaît en premier dans le POS)
                        </label>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <router-link to="/articles" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Annuler
                </router-link>
                <button 
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                >
                    {{ saving ? 'Enregistrement...' : (isEdit ? 'Mettre à jour' : 'Créer l\'article') }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { articlesApi, categoriesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const settingsStore = useSettingsStore()

const isEdit = computed(() => !!route.params.id)
const categories = ref([])
const saving = ref(false)

const form = reactive({
    name: '',
    sku: '',
    description: '',
    category_id: null,
    subcategory_id: null,
    sell_price: 0,
    buy_price: 0,
    unit: 'piece',
    manage_stock: false,
    stock_quantity: 0,
    stock_alert_threshold: 10,
    photo: '',
    is_favorite: false,
    is_active: true,
})

async function fetchCategories() {
    try {
        const response = await categoriesApi.list()
        categories.value = response.data
    } catch (error) {
        console.error('Failed to fetch categories:', error)
    }
}

async function fetchArticle() {
    if (!isEdit.value) return
    
    try {
        const response = await articlesApi.get(route.params.id)
        Object.assign(form, response.data)
    } catch (error) {
        console.error('Failed to fetch article:', error)
        router.push('/articles')
    }
}

async function handleSubmit() {
    saving.value = true
    
    try {
        if (isEdit.value) {
            await articlesApi.update(route.params.id, form)
        } else {
            await articlesApi.create(form)
        }
        router.push('/articles')
    } catch (error) {
        console.error('Failed to save article:', error)
        alert('Erreur lors de l\'enregistrement: ' + (error.response?.data?.message || error.message))
    } finally {
        saving.value = false
    }
}

onMounted(async () => {
    await fetchCategories()
    await fetchArticle()
})
</script>
