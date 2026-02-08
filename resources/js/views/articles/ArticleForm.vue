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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU / Code</label>
                        <input 
                            v-model="form.sku"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <select 
                            v-model="form.category_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                            <option :value="null">Sélectionner une catégorie</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unité</label>
                        <select 
                            v-model="form.unit"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
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
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Seuil d'alerte</label>
                        <input 
                            v-model.number="form.stock_alert_threshold"
                            type="number"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
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
                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                        >
                        <label for="is_active" class="text-sm font-medium text-gray-700">
                            Article actif
                        </label>
                    </div>
                    <div class="flex items-center space-x-3">
                        <input 
                            v-model="form.is_on_sale"
                            type="checkbox"
                            id="is_on_sale"
                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                        >
                        <label for="is_on_sale" class="text-sm font-medium text-gray-700">
                            Article mis en vente
                        </label>
                    </div>
                    <div class="flex items-center space-x-3">
                        <input 
                            v-model="form.is_favorite"
                            type="checkbox"
                            id="is_favorite"
                            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                        >
                        <label for="is_favorite" class="text-sm font-medium text-gray-700">
                            Marquer comme favori (apparaît en premier dans le POS)
                        </label>
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Options d'articles</h2>
                        <p class="text-sm text-gray-500">Activez les options pour ce produit (taille, couleur, etc.)</p>
                    </div>
                    <router-link to="/options/create" target="_blank" class="text-sm text-primary-600 hover:text-primary-700">
                        + Créer une option
                    </router-link>
                </div>
                
                <div class="flex items-center space-x-3 mb-4">
                    <input 
                        v-model="form.has_options"
                        type="checkbox"
                        id="has_options"
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                    >
                    <label for="has_options" class="text-sm font-medium text-gray-700">
                        Activer l'option d'articles pour ce produit
                    </label>
                </div>

                <div v-if="form.has_options" class="space-y-2">
                    <p class="text-sm text-gray-600 mb-3">Sélectionnez les options disponibles pour cet article :</p>
                    <div v-if="options.length === 0" class="text-sm text-gray-500 italic p-4 bg-gray-50 rounded-lg">
                        Aucune option disponible. <router-link to="/options/create" target="_blank" class="text-primary-600 hover:underline">Créez-en une</router-link> d'abord.
                    </div>
                    <div v-else class="space-y-2 max-h-64 overflow-y-auto">
                        <label 
                            v-for="option in options" 
                            :key="option.id"
                            class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                            :class="selectedOptions.includes(option.id) ? 'border-primary-500 bg-primary-50' : 'border-gray-200'"
                        >
                            <input 
                                v-model="selectedOptions"
                                type="checkbox"
                                :value="option.id"
                                class="mt-1 w-4 h-4 text-primary-600 border-gray-300 rounded"
                            >
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-medium text-gray-900">{{ option.name }}</p>
                                    <span 
                                        class="px-2 py-0.5 text-xs rounded-full"
                                        :class="option.type === 'fixed' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'"
                                    >
                                        {{ option.type === 'fixed' ? 'Unique' : 'Multiple' }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">{{ option.values.join(', ') }}</p>
                                <p v-if="option.extra_price > 0" class="text-xs text-gray-400 mt-1">
                                    +{{ formatCurrency(option.extra_price) }}
                                </p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Photos -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Photos</h2>
                
                <div class="space-y-4">
                    <div v-for="(photo, index) in form.photos" :key="index" class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg">
                        <div class="flex-1">
                            <input 
                                v-model="form.photos[index].photo_url"
                                type="text"
                                placeholder="URL de la photo"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="flex items-center space-x-2 text-sm text-gray-600">
                                <input 
                                    v-model="form.photos[index].is_primary"
                                    type="radio"
                                    :name="'primary_photo'"
                                    :value="true"
                                    @change="setPrimaryPhoto(index)"
                                    class="w-4 h-4 text-primary-600"
                                >
                                <span>Principale</span>
                            </label>
                            <button 
                                type="button"
                                @click="removePhoto(index)"
                                v-if="form.photos.length > 1"
                                class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50"
                            >
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                    <button 
                        type="button"
                        @click="addPhoto"
                        class="w-full px-4 py-2 border-2 border-dashed border-gray-300 text-gray-600 rounded-lg hover:border-gray-400 hover:bg-gray-50 flex items-center justify-center"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Ajouter une photo
                    </button>
                    <p class="text-xs text-gray-500">Vous pouvez ajouter plusieurs photos. La première photo sera utilisée par défaut.</p>
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
                    class="px-6 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50"
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
import { articlesApi, categoriesApi, optionsApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { XMarkIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const settingsStore = useSettingsStore()

const isEdit = computed(() => !!route.params.id)
const categories = ref([])
const options = ref([])
const selectedOptions = ref([])
const saving = ref(false)

const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

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
    has_options: false,
    is_on_sale: true,
    photos: [],
})

async function fetchCategories() {
    try {
        const response = await categoriesApi.list()
        categories.value = response.data
    } catch (error) {
        console.error('Failed to fetch categories:', error)
    }
}

async function fetchOptions() {
    try {
        const response = await optionsApi.list({ active: true })
        options.value = response.data
    } catch (error) {
        console.error('Failed to fetch options:', error)
    }
}

async function fetchArticle() {
    if (!isEdit.value) return
    
    try {
        const response = await articlesApi.get(route.params.id)
        const article = response.data
        Object.assign(form, article)
        
        // Set selected options
        if (article.options) {
            selectedOptions.value = article.options.map(o => o.id)
        }
        
        // Set photos
        if (article.photos && article.photos.length > 0) {
            form.photos = article.photos.map(p => ({
                photo_url: p.photo_url,
                is_primary: p.is_primary,
                sort_order: p.sort_order
            }))
        } else if (article.photo) {
            form.photos = [{ photo_url: article.photo, is_primary: true, sort_order: 0 }]
        }
    } catch (error) {
        console.error('Failed to fetch article:', error)
        router.push('/articles')
    }
}

function addPhoto() {
    form.photos.push({ photo_url: '', is_primary: false, sort_order: form.photos.length })
}

function removePhoto(index) {
    if (form.photos.length > 1) {
        const wasMain = form.photos[index].is_primary
        form.photos.splice(index, 1)
        
        // If we removed the main photo, make the first one main
        if (wasMain && form.photos.length > 0) {
            form.photos[0].is_primary = true
        }
        
        // Update sort order
        form.photos.forEach((p, i) => p.sort_order = i)
    }
}

function setPrimaryPhoto(index) {
    form.photos.forEach((p, i) => {
        p.is_primary = i === index
    })
}

async function handleSubmit() {
    saving.value = true
    
    try {
        const data = { ...form }
        
        // Add selected options
        if (form.has_options) {
            data.options = selectedOptions.value
        } else {
            data.options = []
        }
        
        // Filter out empty photo URLs
        if (data.photos && data.photos.length > 0) {
            data.photos = data.photos
                .filter(p => p.photo_url.trim())
                .map((p, i) => ({
                    ...p,
                    sort_order: i,
                    is_primary: i === 0 ? true : p.is_primary
                }))
            
            // Set primary photo as the main photo field (for backwards compatibility)
            const primaryPhoto = data.photos.find(p => p.is_primary)
            if (primaryPhoto) {
                data.photo = primaryPhoto.photo_url
            }
        }
        
        if (isEdit.value) {
            await articlesApi.update(route.params.id, data)
        } else {
            await articlesApi.create(data)
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
    await fetchOptions()
    await fetchArticle()
})
</script>
