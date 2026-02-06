<template>
    <div class="h-[calc(100vh-7rem)] flex">
        <!-- Left Panel - Products -->
        <div class="flex-1 flex flex-col bg-[#fafafa] -m-6 mr-0">
            <!-- Header -->
            <div class="bg-gray-900 text-white px-4 py-3 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button @click="showMenu = !showMenu" class="p-2 hover:bg-gray-800 rounded-lg">
                        <Bars3Icon class="w-5 h-5" />
                    </button>
                    <span class="font-semibold">{{ selectedCategoryName }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="showSearch = !showSearch" class="p-2 hover:bg-gray-800 rounded-lg">
                        <MagnifyingGlassIcon class="w-5 h-5" />
                    </button>
                    <button class="p-2 hover:bg-gray-800 rounded-lg">
                        <QrCodeIcon class="w-5 h-5" />
                    </button>
                    <button class="p-2 hover:bg-gray-800 rounded-lg">
                        <MoonIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div v-if="showSearch" class="p-4 bg-white border-b">
                <input 
                    v-model="searchQuery"
                    type="text"
                    placeholder="Rechercher un article..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
            </div>

            <!-- Product Grid -->
            <div class="flex-1 overflow-auto p-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <div 
                        v-for="article in filteredArticles" 
                        :key="article.id"
                        @click="addToCart(article)"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden cursor-pointer hover:shadow-md transition-shadow"
                    >
                        <div class="aspect-square bg-gray-100 flex items-center justify-center">
                            <img 
                                v-if="article.photo"
                                :src="article.photo" 
                                :alt="article.name"
                                class="w-full h-full object-cover"
                            >
                            <span v-else class="text-4xl">📦</span>
                        </div>
                        <div class="p-3">
                            <h3 class="text-sm font-medium text-gray-900 truncate">{{ article.name }}</h3>
                            <p class="text-sm font-semibold text-secondary-600">
                                {{ formatCurrency(article.sell_price) }}<span v-if="article.unit !== 'piece'">/{{ article.unit }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Special Items -->
                    <div 
                        @click="showCategoriesModal = true"
                        class="bg-primary-500 rounded-xl shadow-sm overflow-hidden cursor-pointer hover:bg-primary-600 transition-colors"
                    >
                        <div class="aspect-square flex flex-col items-center justify-center text-gray-900">
                            <TagIcon class="w-8 h-8 mb-2" />
                            <span class="font-medium">Catégories</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Tabs -->
            <div class="bg-white border-t border-gray-200 px-2 py-3">
                <div class="flex space-x-1 overflow-x-auto">
                    <button 
                        @click="selectCategory(null)"
                        class="flex flex-col items-center justify-center px-4 py-2 rounded-lg transition-colors min-w-[70px]"
                        :class="!selectedCategoryId ? 'bg-primary-500 text-gray-900' : 'text-gray-500 hover:bg-gray-100'"
                    >
                        <HeartIcon class="w-5 h-5" />
                        <span class="text-xs">FAVORIS</span>
                    </button>
                    <button 
                        v-for="category in categories"
                        :key="category.id"
                        @click="selectCategory(category.id)"
                        class="flex flex-col items-center justify-center px-4 py-2 rounded-lg transition-colors min-w-[70px]"
                        :class="selectedCategoryId === category.id ? 'bg-primary-500 text-gray-900' : 'text-gray-500 hover:bg-gray-100'"
                    >
                        <span class="text-lg">{{ getCategoryIcon(category.icon) }}</span>
                        <span class="text-xs uppercase">{{ category.name }}</span>
                    </button>
                    <button 
                        @click="selectCategory('all')"
                        class="flex flex-col items-center justify-center px-4 py-2 rounded-lg transition-colors min-w-[70px]"
                        :class="selectedCategoryId === 'all' ? 'bg-primary-500 text-gray-900' : 'text-gray-500 hover:bg-gray-100'"
                    >
                        <Squares2X2Icon class="w-5 h-5" />
                        <span class="text-xs">TOUS</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Panel - Cart/Ticket -->
        <div class="w-80 bg-white border-l border-gray-200 flex flex-col">
            <!-- Ticket Header -->
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Ticket</h2>
                <div class="flex items-center space-x-2">
                    <button class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <UserIcon class="w-5 h-5" />
                    </button>
                    <button @click="showOptionsMenu = !showOptionsMenu" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <Bars3BottomRightIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-auto">
                <div v-if="cartStore.items.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                    <ShoppingCartIcon class="w-12 h-12 mb-2" />
                    <p class="text-sm">Ticket vide</p>
                </div>
                <div v-else class="divide-y divide-gray-100">
                    <div 
                        v-for="(item, index) in cartStore.items" 
                        :key="index"
                        class="p-4 hover:bg-gray-50"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900">{{ item.article_name }}</h4>
                                <div class="flex items-center space-x-2 mt-1">
                                    <button 
                                        @click="updateQuantity(index, item.quantity - 1)"
                                        class="w-6 h-6 flex items-center justify-center text-gray-500 hover:bg-gray-200 rounded"
                                    >
                                        <MinusIcon class="w-4 h-4" />
                                    </button>
                                    <span class="text-sm text-gray-900 font-medium">x {{ item.quantity }}</span>
                                    <button 
                                        @click="updateQuantity(index, item.quantity + 1)"
                                        class="w-6 h-6 flex items-center justify-center text-gray-500 hover:bg-gray-200 rounded"
                                    >
                                        <PlusIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(item.total) }}</p>
                                <button 
                                    @click="removeItem(index)"
                                    class="text-red-500 hover:text-red-700 mt-1"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="border-t border-gray-200 p-4 space-y-2">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Sous-total:</span>
                    <span>{{ formatCurrency(cartStore.subtotal) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Remise:</span>
                    <span>{{ formatCurrency(cartStore.discountTotal) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-500">
                    <span>{{ settingsStore.taxName }} ({{ settingsStore.taxRate }}%):</span>
                    <span>{{ formatCurrency(cartStore.taxAmount) }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t">
                    <span>Total:</span>
                    <span>{{ formatCurrency(cartStore.total) }}</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="p-4 border-t border-gray-200 grid grid-cols-2 gap-3">
                <button 
                    @click="saveSale"
                    :disabled="cartStore.items.length === 0"
                    class="py-3 px-4 border-2 border-primary-600 text-gray-900 font-medium rounded-lg hover:bg-primary-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    SAUVEGARDER
                </button>
                <button 
                    @click="showPaymentModal = true"
                    :disabled="cartStore.items.length === 0"
                    class="py-3 px-4 bg-primary-500 text-gray-900 font-bold rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    PAYER
                </button>
            </div>
        </div>

        <!-- Payment Modal -->
        <PaymentModal 
            v-if="showPaymentModal"
            :total="cartStore.total"
            @close="showPaymentModal = false"
            @complete="completeSale"
        />

        <!-- Calculator Modal -->
        <CalculatorModal 
            v-if="showCalculator"
            @close="showCalculator = false"
            @result="handleCalculatorResult"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCartStore } from '../stores/cart'
import { useArticlesStore } from '../stores/articles'
import { useSettingsStore } from '../stores/settings'
import { useOfflineStore } from '../stores/offline'
import { salesApi } from '../api'
import PaymentModal from '../components/pos/PaymentModal.vue'
import CalculatorModal from '../components/pos/CalculatorModal.vue'
import {
    Bars3Icon,
    MagnifyingGlassIcon,
    QrCodeIcon,
    MoonIcon,
    HeartIcon,
    Squares2X2Icon,
    TagIcon,
    UserIcon,
    Bars3BottomRightIcon,
    ShoppingCartIcon,
    MinusIcon,
    PlusIcon,
    TrashIcon
} from '@heroicons/vue/24/outline'

const cartStore = useCartStore()
const articlesStore = useArticlesStore()
const settingsStore = useSettingsStore()

const showMenu = ref(false)
const showSearch = ref(false)
const showPaymentModal = ref(false)
const showCalculator = ref(false)
const showCategoriesModal = ref(false)
const showOptionsMenu = ref(false)
const searchQuery = ref('')
const selectedCategoryId = ref(null)

const categories = computed(() => articlesStore.categories)

const filteredArticles = computed(() => {
    let articles = articlesStore.articles

    // Filter by favorites if no category selected
    if (selectedCategoryId.value === null) {
        articles = articles.filter(a => a.is_favorite)
    } else if (selectedCategoryId.value !== 'all') {
        articles = articles.filter(a => a.category_id === selectedCategoryId.value)
    }

    // Filter by search
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        articles = articles.filter(a => 
            a.name.toLowerCase().includes(query) ||
            a.sku?.toLowerCase().includes(query)
        )
    }

    return articles
})

const selectedCategoryName = computed(() => {
    if (selectedCategoryId.value === null) return 'Favoris'
    if (selectedCategoryId.value === 'all') return 'Tous les Articles'
    const category = categories.value.find(c => c.id === selectedCategoryId.value)
    return category?.name || 'Articles'
})

const categoryIcons = {
    apple: '🍎',
    cup: '🥤',
    bread: '🥖',
    home: '🏠',
    cookie: '🍪',
    meat: '🥩',
}

function getCategoryIcon(icon) {
    return categoryIcons[icon] || '📦'
}

function formatCurrency(amount) {
    return settingsStore.formatCurrency(amount)
}

function selectCategory(categoryId) {
    selectedCategoryId.value = categoryId
}

function addToCart(article) {
    cartStore.addItem(article)
}

function updateQuantity(index, quantity) {
    cartStore.updateItemQuantity(index, quantity)
}

function removeItem(index) {
    cartStore.removeItem(index)
}

async function saveSale() {
    try {
        const data = cartStore.getCartData()
        const response = await salesApi.create(data)
        cartStore.setSaleId(response.data.id)
        alert('Vente sauvegardée!')
    } catch (error) {
        console.error('Failed to save sale:', error)
        alert('Erreur lors de la sauvegarde')
    }
}

async function completeSale(paymentData) {
    const offlineStore = useOfflineStore()
    
    try {
        const data = cartStore.getCartData()
        const saleData = {
            ...data,
            payment: paymentData,
            status: 'completed',
        }

        // If offline, save to pending queue
        if (!offlineStore.isOnline) {
            const result = await offlineStore.savePendingSale(saleData)
            if (result.success) {
                cartStore.clearCart()
                showPaymentModal.value = false
                alert('Vente sauvegardée hors ligne! Elle sera synchronisée automatiquement.')
            } else {
                throw new Error('Erreur lors de la sauvegarde hors ligne')
            }
            return
        }

        // If online, process normally
        if (!cartStore.currentSaleId) {
            const response = await salesApi.create(data)
            cartStore.setSaleId(response.data.id)
        }

        // Add payment
        await salesApi.addPayment(cartStore.currentSaleId, paymentData)

        // Complete sale
        await salesApi.complete(cartStore.currentSaleId)

        // Clear cart
        cartStore.clearCart()
        showPaymentModal.value = false

        alert('Vente complétée avec succès!')
    } catch (error) {
        console.error('Failed to complete sale:', error)
        alert('Erreur lors du paiement: ' + (error.response?.data?.message || error.message))
    }
}

function handleCalculatorResult(result) {
    // Handle calculator result
    showCalculator.value = false
}

onMounted(async () => {
    await articlesStore.refresh()
})
</script>

