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
                    <button @click="openQRScanner" class="p-2 hover:bg-gray-800 rounded-lg" title="Scanner code QR">
                        <QrCodeIcon class="w-5 h-5" />
                    </button>
                    <button @click="toggleDarkMode" class="p-2 hover:bg-gray-800 rounded-lg" title="Mode sombre">
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
            <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
                <h2 class="text-lg font-bold text-gray-900">Ticket</h2>
                <div class="flex items-center space-x-1">
                    <button 
                        @click="showCustomerSelector = !showCustomerSelector" 
                        class="p-2 text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                        title="Sélectionner client"
                    >
                        <UserIcon class="w-5 h-5" />
                    </button>
                    <button 
                        @click="showOptionsMenu = !showOptionsMenu" 
                        class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                        title="Options du ticket"
                    >
                        <Bars3BottomRightIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Options Menu Dropdown -->
            <div v-if="showOptionsMenu" class="bg-gray-50 border-b border-gray-200 p-3 space-y-2">
                <button 
                    @click="showNotesModal = true; showOptionsMenu = false"
                    class="w-full text-left px-3 py-2 text-sm font-medium text-gray-700 hover:bg-white rounded-lg transition-colors"
                >
                    📝 Notes
                </button>
                <button 
                    @click="showDiscountModal = true; showOptionsMenu = false"
                    class="w-full text-left px-3 py-2 text-sm font-medium text-gray-700 hover:bg-white rounded-lg transition-colors"
                >
                    🏷️ Appliquer remise
                </button>
                <button 
                    @click="resetCart; showOptionsMenu = false"
                    class="w-full text-left px-3 py-2 text-sm font-medium text-gray-700 hover:bg-white hover:text-red-600 rounded-lg transition-colors"
                >
                    🔄 Réinitialiser
                </button>
            </div>

            <!-- Customer Selector -->
            <div v-if="showCustomerSelector" class="bg-blue-50 border-b border-blue-200 p-3">
                <input 
                    v-model="customerSearch"
                    type="text"
                    placeholder="Chercher ou créer client..."
                    class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                >
                <div class="mt-2 max-h-32 overflow-y-auto space-y-1">
                    <button 
                        v-for="customer in filteredCustomers"
                        :key="customer.id"
                        @click="selectCustomer(customer)"
                        class="w-full text-left px-3 py-1.5 text-sm rounded hover:bg-blue-100 transition-colors"
                    >
                        {{ customer.name }}
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
                        class="p-3 hover:bg-blue-50 transition-colors border-b border-gray-100 last:border-b-0"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 truncate">{{ item.article_name }}</h4>
                                <div class="text-xs text-gray-600 mt-1">
                                    <span class="font-medium">{{ formatCurrency(item.unit_price) }}</span>
                                    <span v-if="item.variant_price > 0" class="ml-2 text-orange-600 font-semibold">+ Variant: {{ formatCurrency(item.variant_price) }}</span>
                                    <span v-if="item.options_price > 0" class="ml-2 text-primary-600 font-semibold">+ Options: {{ formatCurrency(item.options_price) }}</span>
                                    <span class="ml-2 text-gray-500">=</span>
                                    <span class="ml-2 font-bold text-gray-900">{{ formatCurrency(item.unit_price + (item.variant_price || 0) + (item.options_price || 0)) }}/pcs</span>
                                </div>
                                <div v-if="item.selected_variant?.template_name" class="text-xs text-gray-600 mt-1">
                                    {{ item.selected_variant.template_name }} · {{ item.selected_variant.template_value }}
                                </div>
                                <button
                                    v-if="item.article?.has_options"
                                    type="button"
                                    @click="editItemOptions(index, item)"
                                    class="mt-2 text-xs text-blue-600 hover:text-blue-700 hover:underline font-medium"
                                >
                                    ✏️ Modifier les options
                                </button>
                                <div v-if="item.selected_options && item.selected_options.length" class="mt-2 space-y-0.5 bg-gray-50 p-2 rounded">
                                    <div
                                        v-for="option in item.selected_options"
                                        :key="option.option_id"
                                        class="text-xs text-gray-600"
                                    >
                                        <span class="font-semibold text-gray-700">{{ option.option_name }}:</span>
                                        <span class="ml-1 text-gray-800">
                                            {{ option.variants.map(v => v.name).join(', ') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 mt-2">
                                    <button 
                                        @click="updateQuantity(index, item.quantity - 1)"
                                        class="w-6 h-6 flex items-center justify-center text-gray-600 hover:bg-gray-300 rounded font-bold"
                                    >
                                        −
                                    </button>
                                    <span class="text-sm text-gray-900 font-bold w-6 text-center">{{ item.quantity }}</span>
                                    <button 
                                        @click="updateQuantity(index, item.quantity + 1)"
                                        class="w-6 h-6 flex items-center justify-center text-gray-600 hover:bg-gray-300 rounded font-bold"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>
                            <div class="text-right ml-2">
                                <p class="text-xs text-gray-500 mb-1">{{ item.quantity }}x</p>
                                <p class="text-sm font-bold text-gray-900 bg-primary-100 px-2 py-1 rounded">{{ formatCurrency(getItemLineTotal(item)) }}</p>
                                <button 
                                    @click="removeItem(index)"
                                    class="text-red-500 hover:text-red-700 mt-1 text-lg"
                                    title="Supprimer"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="border-t border-gray-200 p-4 bg-gradient-to-b from-white to-gray-50 space-y-2 text-sm">
                <!-- Subtotal -->
                <div class="flex justify-between text-gray-600">
                    <span class="font-medium">Sous-total:</span>
                    <span class="font-semibold text-gray-900">{{ formatCurrency(cartStore.subtotal) }}</span>
                </div>
                <!-- Discount -->
                <div v-if="cartStore.discountTotal > 0" class="flex justify-between text-orange-600 bg-orange-50 px-2 py-1 rounded">
                    <span class="font-medium">🏷️ Remise:</span>
                    <span class="font-semibold">-{{ formatCurrency(cartStore.discountTotal) }}</span>
                </div>
                <!-- After Discount -->
                <div v-if="cartStore.discountTotal > 0" class="flex justify-between text-gray-600 border-t border-gray-300 pt-2">
                    <span class="font-medium">Après remise:</span>
                    <span class="font-semibold text-gray-900">{{ formatCurrency(cartStore.afterDiscount) }}</span>
                </div>
                <!-- Tax -->
                <div class="flex justify-between text-gray-600">
                    <span class="font-medium">{{ settingsStore.taxName }} ({{ settingsStore.taxRate }}%):</span>
                    <span class="font-semibold text-gray-900">{{ formatCurrency(cartStore.taxAmount) }}</span>
                </div>
                <!-- Total -->
                <div class="border-t border-gray-300 pt-2 flex justify-between text-lg font-bold text-gray-900 bg-primary-100 px-2 py-2 rounded-lg">
                    <span>TOTAL À PAYER:</span>
                    <span class="text-primary-600">{{ formatCurrency(cartStore.total) }}</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="p-4 border-t border-gray-200 space-y-2">
                <button 
                    @click="saveSale"
                    :disabled="cartStore.items.length === 0"
                    class="w-full py-3 px-4 border-2 border-gray-400 text-gray-900 font-bold rounded-lg hover:bg-gray-100 hover:border-gray-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-gray-400"
                >
                    💾 SAUVEGARDER
                </button>
                <button 
                    @click="showPaymentModal = true"
                    :disabled="cartStore.items.length === 0"
                    class="w-full py-3 px-4 bg-gradient-to-r from-primary-500 to-primary-600 text-gray-900 font-bold rounded-lg hover:from-primary-600 hover:to-primary-700 shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:from-primary-500 disabled:hover:to-primary-600 disabled:hover:shadow-lg"
                >
                    💳 PAYER
                </button>
            </div>
        </div>

        <!-- Payment Modal - Multiple Methods -->
        <PaymentMultiModal 
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

        <OptionsModal
            v-if="showOptionsModal && optionsArticle"
            :article="optionsArticle"
            :initial-selections="optionsInitialSelections"
            @close="closeOptionsModal"
            @confirm="handleOptionsConfirm"
        />

        <!-- Selection-First Variants Modal -->
        <SelectVariantsModal
            v-if="showSelectVariantsModal && selectedArticleForVariants"
            :article="selectedArticleForVariants"
            :model-value="selectedVariantId"
            @close="closeSelectVariantsModal"
            @confirm="handleSelectVariantsConfirm"
        />

        <!-- Selection-First Options Modal -->
        <SelectOptionsModal
            v-if="showSelectOptionsModal && optionsArticle"
            :article="optionsArticle"
            :initial-selections="optionsInitialSelections"
            @close="closeSelectOptionsModal"
            @confirm="handleSelectOptionsConfirm"
            @create-option="showCreateOptionForArticle"
        />

        <!-- Need Options Prompt -->
        <div v-if="showNeedOptionsPrompt" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl p-6 max-w-md w-full space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Options manquantes</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Cet article n'a pas encore d'options configurées. Souhaitez-vous en créer une maintenant ?
                    </p>
                </div>
                <div class="flex gap-3 justify-end">
                    <button
                        type="button"
                        @click="cancelNeedOptionsPrompt"
                        class="px-4 py-2 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        @click="promptCreateOptions"
                        class="px-4 py-2 bg-primary-500 text-gray-900 font-semibold rounded-xl hover:bg-primary-600"
                    >
                        Créer une option
                    </button>
                </div>
            </div>
        </div>

        <!-- Notes Modal -->
        <div v-if="showNotesModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-4">📝 Ajouter des notes</h3>
                <textarea 
                    v-model="ticketNotes"
                    placeholder="Notes sur la commande..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 mb-4"
                    rows="4"
                />
                <div class="flex gap-2 justify-end">
                    <button 
                        @click="showNotesModal = false"
                        class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100"
                    >
                        Annuler
                    </button>
                    <button 
                        @click="applyNotes"
                        class="px-4 py-2 bg-primary-500 text-gray-900 font-bold rounded-lg hover:bg-primary-600"
                    >
                        Appliquer
                    </button>
                </div>
            </div>
        </div>

        <!-- Discount Modal -->
        <div v-if="showDiscountModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-4">🏷️ Appliquer une remise</h3>
                <div class="space-y-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant fixe (DHS)</label>
                        <input 
                            v-model.number="discountAmount"
                            type="number"
                            min="0"
                            placeholder="0.00"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pourcentage (%)</label>
                        <input 
                            v-model.number="discountPercent"
                            type="number"
                            min="0"
                            max="100"
                            placeholder="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        />
                    </div>
                </div>
                <div class="flex gap-2 justify-end">
                    <button 
                        @click="showDiscountModal = false"
                        class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100"
                    >
                        Annuler
                    </button>
                    <button 
                        @click="applyDiscount"
                        class="px-4 py-2 bg-primary-500 text-gray-900 font-bold rounded-lg hover:bg-primary-600"
                    >
                        Appliquer
                    </button>
                </div>
            </div>
        </div>

        <!-- Create Option Modal (Comprehensive) -->
        <div v-if="showCreateOptionModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">⚙️ Créer une nouvelle option</h3>
                    <button 
                        type="button"
                        @click="closeCreateOptionModal"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body - Reusable Form Content -->
                <div class="p-6">
                    <OptionFormContent 
                        :form="newOptionForm"
                        :showPriceField="false"
                        :showSettings="true"
                        :showTypeField="false"
                        :currencyCode="settingsStore.currencyCode"
                    />
                </div>

                <!-- Modal Footer -->
                <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end space-x-3">
                    <button 
                        type="button"
                        @click="closeCreateOptionModal"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100"
                    >
                        Annuler
                    </button>
                    <button 
                        type="button"
                        @click="createQuickOption"
                        :disabled="creatingOption || !isNewOptionValid"
                        class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ creatingOption ? 'Création...' : 'Créer l\'option' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCartStore } from '../stores/cart'
import { useArticlesStore } from '../stores/articles'
import { useSettingsStore } from '../stores/settings'
import { useCustomersStore } from '../stores/customers'
import { useOfflineStore } from '../stores/offline'
import { salesApi, optionsApi, articlesApi } from '../api'
import PaymentMultiModal from '../components/pos/PaymentMultiModal.vue'
import CalculatorModal from '../components/pos/CalculatorModal.vue'
import OptionsModal from '../components/pos/OptionsModal.vue'
import SelectOptionsModal from '../components/pos/SelectOptionsModal.vue'
import SelectVariantsModal from '../components/pos/SelectVariantsModal.vue'
import OptionFormContent from '../components/forms/OptionFormContent.vue'
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
const customersStore = useCustomersStore()

const showMenu = ref(false)
const showSearch = ref(false)
const showPaymentModal = ref(false)
const showCalculator = ref(false)
const showCategoriesModal = ref(false)
const showOptionsMenu = ref(false)
const showOptionsModal = ref(false)
const showCustomerSelector = ref(false)
const showNotesModal = ref(false)
const showDiscountModal = ref(false)
const showSelectOptionsModal = ref(false)
const showCreateOptionModal = ref(false)
const showNeedOptionsPrompt = ref(false)
const showSelectVariantsModal = ref(false)
const optionsArticle = ref(null)
const selectedArticleForVariants = ref(null)
const selectedVariantId = ref(null)
const optionsInitialSelections = ref([])
const optionsMode = ref('add')
const variantSelectionMode = ref('add')
const editingCartIndex = ref(null)
const searchQuery = ref('')
const selectedCategoryId = ref(null)
const customerSearch = ref('')
const selectedCustomer = ref(null)
const ticketNotes = ref('')
const discountAmount = ref(0)
const discountPercent = ref(0)
const creatingOption = ref(false)
const newOptionForm = ref({
    name: '',
    type: 'fixed',
    values: [''],
    variantPrices: [0],
    extra_price: 0,
    is_required: false,
    is_active: true,
})

const categories = computed(() => articlesStore.categories)

const filteredCustomers = computed(() => {
    const query = customerSearch.value.toLowerCase()
    if (!query) return customersStore.customers
    return customersStore.customers.filter(c => 
        c.name.toLowerCase().includes(query)
    )
})

const filteredArticles = computed(() => {
    let articles = articlesStore.articles

    // If no category selected (Favoris), filter by is_favorite
    // BUT if there are no favorites at all, show all articles so the screen isn't empty
    if (selectedCategoryId.value === null) {
        const favorites = articles.filter(a => a.is_favorite)
        if (favorites.length > 0) {
            articles = favorites
        } else {
            // If no favorites, default to showing all articles
            // so the user doesn't see an empty screen
        }
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

function getItemLineTotal(item) {
    const unitPrice = Number(item.unit_price) || 0
    const variantPrice = Number(item.variant_price) || 0
    const optionsPrice = Number(item.options_price) || 0
    const quantity = Number(item.quantity) || 0
    const discount = Number(item.discount_amount) || 0
    return (unitPrice + variantPrice + optionsPrice) * quantity - discount
}

function formatOptionsPrice(amount) {
    const numeric = Number(amount || 0)
    if (numeric > 0) {
        return `+${formatCurrency(numeric)}`
    }
    return formatCurrency(numeric)
}

function selectCategory(categoryId) {
    selectedCategoryId.value = categoryId
}

async function addToCart(article) {
    // First, check if article has variants (mandatory single-choice)
    const hasVariants = article.has_variants || (Array.isArray(article.variants) && article.variants.length > 0)
    
    // Then, check if article has options (optional multi-choice)
    const hasOptions = article.has_options || (Array.isArray(article.options) && article.options.length > 0)

    // Load full article data if needed
    let fullArticle = article
    if ((hasVariants || hasOptions) && !article.variants && !article.options) {
        try {
            const response = await articlesApi.get(article.id)
            fullArticle = response.data || article
        } catch (error) {
            console.error('Failed to load article:', error)
        }
    }

    // If article has variants, show variant selection modal first
    if (fullArticle.has_variants || (Array.isArray(fullArticle.variants) && fullArticle.variants.length > 0)) {
        showSelectVariantsModal.value = true
        selectedArticleForVariants.value = fullArticle
        variantSelectionMode.value = 'add'
        return
    }

    // If article has options but no variants, show options selection
    if (fullArticle.has_options || (Array.isArray(fullArticle.options) && fullArticle.options.length > 0)) {
        showSelectOptionsModal.value = true
        optionsArticle.value = fullArticle
        optionsMode.value = 'add'
        editingCartIndex.value = null
        return
    }

    // No variants or options, add directly to cart
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

async function completeSale(payments) {
    const offlineStore = useOfflineStore()
    
    try {
        const data = cartStore.getCartData()
        const saleData = {
            ...data,
            payments: payments, // Multiple payments
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

        // Add all payments
        for (const payment of payments) {
            await salesApi.addPayment(cartStore.currentSaleId, payment)
        }

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

function closeOptionsModal() {
    showOptionsModal.value = false
    optionsArticle.value = null
    optionsInitialSelections.value = []
    optionsMode.value = 'add'
    editingCartIndex.value = null
}

function closeSelectVariantsModal() {
    showSelectVariantsModal.value = false
    selectedArticleForVariants.value = null
    selectedVariantId.value = null
}

function handleSelectVariantsConfirm({ variantId, selectedOptions = [], optionsPrice = 0 }) {
    if (!selectedArticleForVariants.value) return

    const selectedVariant = selectedArticleForVariants.value.variants?.find(v => v.id === variantId)
    if (!selectedVariant) return

    const normalizedOptions = normalizeSelectedOptions(selectedOptions)
    cartStore.addItem(
        selectedArticleForVariants.value,
        1,
        normalizedOptions,
        optionsPrice,
        selectedVariant
    )
    closeSelectVariantsModal()
}

function closeSelectOptionsModal() {
    showSelectOptionsModal.value = false
    optionsArticle.value = null
    optionsMode.value = 'add'
    editingCartIndex.value = null
}

function cancelNeedOptionsPrompt() {
    showNeedOptionsPrompt.value = false
    optionsArticle.value = null
}

function promptCreateOptions() {
    showNeedOptionsPrompt.value = false
    showCreateOptionModal.value = true
}

function handleSelectOptionsConfirm({ selectedOptions, optionsPrice }) {
    if (!optionsArticle.value) return

    if (optionsMode.value === 'edit' && editingCartIndex.value !== null) {
        cartStore.updateItemOptions(editingCartIndex.value, selectedOptions, optionsPrice)
    } else {
        cartStore.addItem(optionsArticle.value, 1, selectedOptions, optionsPrice)
    }

    closeSelectOptionsModal()
}

function showCreateOptionForArticle() {
    showSelectOptionsModal.value = false
    showCreateOptionModal.value = true
}

function handleOptionsConfirm({ selectedOptions, optionsPrice }) {
    if (!optionsArticle.value) return
    const normalized = normalizeSelectedOptions(selectedOptions)
    if (optionsMode.value === 'edit' && editingCartIndex.value !== null) {
        cartStore.updateItemOptions(editingCartIndex.value, normalized, optionsPrice)
    } else {
        cartStore.addItem(optionsArticle.value, 1, normalized, optionsPrice)
    }
    closeOptionsModal()
}

function normalizeSelectedOptions(selectedOptions) {
    if (!Array.isArray(selectedOptions)) return selectedOptions
    return selectedOptions
        .map((option) => ({
            ...option,
            variants: [...(option.variants || [])].sort((a, b) => a.id - b.id),
        }))
        .sort((a, b) => a.option_id - b.option_id)
}

function getActiveOptions(article) {
    return (article.options || [])
        .filter((option) => option.is_active)
        .map((option) => ({
            ...option,
            variants: (option.variants || []).filter((variant) => variant.is_active),
        }))
        .filter((option) => option.variants.length > 0)
}

function openOptionsModal(article, activeOptions, initialSelections, mode, index) {
    optionsArticle.value = {
        ...article,
        options: activeOptions,
    }
    optionsInitialSelections.value = initialSelections || []
    optionsMode.value = mode
    editingCartIndex.value = index
    showOptionsModal.value = true
}

function editItemOptions(index, item) {
    if (!item.article) return
    
    // Check if article has options
    const hasOptions = item.article.options && item.article.options.length > 0
    
    if (hasOptions) {
        // Show selection modal for editing
        showSelectOptionsModal.value = true
        optionsArticle.value = item.article
        optionsMode.value = 'edit'
        editingCartIndex.value = index
        optionsInitialSelections.value = item.selected_options || []
        return
    }
    
    // No options to edit
    console.warn('This article has no options')
}

function openQRScanner() {
    console.log('QR Scanner opened')
    // TODO: Implement QR code scanner using camera
    // Could use a library like jsQR or zxing-js
    alert('Fonctionnalité QR Code à implémenter')
}

function toggleDarkMode() {
    console.log('Dark mode toggle')
    // TODO: Implement dark mode toggle
    alert('Mode sombre à implémenter')
}

function selectCustomer(customer) {
    selectedCustomer.value = customer
    cartStore.setCustomer(customer.id, customer.name)
    showCustomerSelector.value = false
    customerSearch.value = ''
}

function applyNotes() {
    cartStore.setNotes(ticketNotes.value)
    showNotesModal.value = false
}

function applyDiscount() {
    cartStore.setDiscount(discountAmount.value, discountPercent.value)
    showDiscountModal.value = false
}

function resetCart() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser le ticket?')) {
        cartStore.clearCart()
        selectedCustomer.value = null
        ticketNotes.value = ''
        discountAmount.value = 0
        discountPercent.value = 0
    }
}

const isNewOptionValid = computed(() => {
    return newOptionForm.value.name.trim() && 
           newOptionForm.value.values.length > 0 && 
           newOptionForm.value.values.some(v => v.trim())
})

function closeCreateOptionModal() {
    showCreateOptionModal.value = false
    editingCartIndex.value = null
    optionsArticle.value = null
    // Reset form
    newOptionForm.value = {
        name: '',
        type: 'fixed',
        values: [''],
        variantPrices: [0],
        extra_price: 0,
        is_required: false,
        is_active: true,
    }
}

async function createQuickOption() {
    if (!isNewOptionValid.value) return
    
    creatingOption.value = true
    const articleContext = optionsArticle.value
    try {
        // Build value/price pairs and filter empty values
        const valuesWithPrices = newOptionForm.value.values.map((value, index) => ({
            value: value.trim(),
            price: Number(newOptionForm.value.variantPrices?.[index]) || 0,
        })).filter((item) => item.value)

        const validValues = valuesWithPrices.map((item) => item.value)
        const optionData = {
            name: newOptionForm.value.name.trim(),
            type: newOptionForm.value.type,
            values: validValues,
            extra_price: newOptionForm.value.extra_price,
            is_required: newOptionForm.value.is_required,
            is_active: newOptionForm.value.is_active,
        }
        
        const response = await optionsApi.create(optionData)
        console.log('Option créée:', response.data)
        
        // Create variants for each value with corresponding prices
        for (let i = 0; i < valuesWithPrices.length; i++) {
            const variantData = {
                name: valuesWithPrices[i].value,
                price_impact: valuesWithPrices[i].price,
                is_active: true,
            }
            try {
                await optionsApi.createVariant(response.data.id, variantData)
            } catch (error) {
                console.error(`Failed to create variant ${i + 1}:`, error)
            }
        }
        
        // Close modal and reset
        closeCreateOptionModal()
        
        // Refresh article data if we're creating for a specific article
        if (articleContext) {
            try {
                const existingOptionIds = Array.isArray(articleContext.options)
                    ? articleContext.options.map((option) => option.id)
                    : []

                const updatedOptionIds = Array.from(new Set([
                    ...existingOptionIds,
                    response.data.id,
                ]))

                await articlesApi.update(articleContext.id, {
                    options: updatedOptionIds,
                    has_options: true,
                })

                const articleResponse = await articlesApi.get(articleContext.id)
                optionsArticle.value = articleResponse.data
                showSelectOptionsModal.value = true
            } catch (error) {
                console.error('Failed to refresh article:', error)
            }
        }
        
        // Show success message
        alert(`Option "${response.data.name}" créée avec succès!`)
    } catch (error) {
        console.error('Erreur création option:', error)
        alert('Erreur lors de la création de l\'option: ' + (error.response?.data?.message || error.message))
    } finally {
        creatingOption.value = false
    }
}

onMounted(async () => {
    await articlesStore.refresh()
    await customersStore.fetchCustomers()
})
</script>
