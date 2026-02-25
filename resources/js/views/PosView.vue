<template>
    <div class="h-screen flex flex-col bg-[#f4f3ef]">
        <header class="flex items-center gap-4 px-4 py-3 bg-gray-900 text-white">
            <div class="flex items-center gap-3">
                <button
                    @click="toggleAppSidebar"
                    class="p-2 bg-white/10 rounded-lg hover:bg-white/20 transition-colors"
                    type="button"
                    title="Afficher/Masquer le menu"
                >
                    <Bars3Icon class="w-5 h-5" />
                </button>
                <div>
                    <p class="text-xs uppercase text-gray-400">Catégorie active</p>
                    <p class="text-lg font-semibold">{{ selectedCategoryName }}</p>
                    <p class="text-xs text-gray-400">{{ totalArticles }} articles</p>
                </div>
            </div>
            <div class="flex-1">
                <div class="relative">
                    <input
                        ref="searchField"
                        v-model="searchQuery"
                        type="text"
                        autofocus
                        placeholder="Rechercher par nom ou code-barres"
                        class="w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-2.5 text-sm text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                    <MagnifyingGlassIcon class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
                </div>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            <transition name="fade">
                <aside
                    v-if="categoriesDisplayMode === 'sidebar'"
                    class="w-64 border-r border-gray-200 bg-white flex flex-col"
                >
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Catégories</p>
                        <h3 class="text-base font-semibold text-gray-900">Navigation</h3>
                    </div>
                    <div class="flex-1 overflow-y-auto px-3 py-3 space-y-2">
                        <button
                            v-for="button in categoryButtons"
                            :key="button.id"
                            @click="selectCategory(button.id)"
                            type="button"
                            class="w-full flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-colors"
                            :class="selectedCategoryId === button.id ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-100'"
                        >
                            <span class="text-2xl">{{ button.icon }}</span>
                            <span class="flex-1 text-left truncate">{{ button.label }}</span>
                        </button>
                    </div>
                </aside>
            </transition>

            <div class="flex-1 flex flex-col overflow-hidden">
                <div class="flex-1 overflow-hidden p-4 bg-[#f8f8f8]">
                    <div class="grid grid-cols-5 grid-rows-4 gap-4 h-full" style="grid-auto-rows: minmax(0, 1fr);">
                        <div
                            v-for="article in paginatedArticles"
                            :key="article.id"
                            class="cursor-pointer rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-lg transition-shadow h-full flex flex-col"
                            @click="addToCart(article)"
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
                                <p class="text-base font-bold text-primary-600">{{ formatCurrency(article.sell_price) }}</p>
                            </div>
                        </div>
                        <div v-if="paginatedArticles.length === 0" class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white/80 p-6 text-center text-sm text-gray-500">
                            Aucun produit ne correspond à cette recherche.
                        </div>
                    </div>
                </div>

                <div v-if="totalArticles > itemsPerPage" class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200">
                    <p class="text-sm text-gray-500">{{ pageSummary }}</p>
                    <div class="flex items-center gap-2">
                        <button
                            @click="prevPage"
                            :disabled="currentPage === 1"
                            class="px-3 py-1 rounded-lg border border-gray-300 text-sm font-semibold disabled:opacity-40"
                        >
                            Précédent
                        </button>
                        <button
                            @click="nextPage"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1 rounded-lg border border-primary-500 text-sm font-semibold text-primary-600 disabled:opacity-40"
                        >
                            Suivant
                        </button>
                    </div>
                </div>

                <div v-if="categoriesDisplayMode === 'bottom'" class="border-t border-gray-200 bg-white px-4 py-3">
                    <div class="flex gap-3 overflow-x-auto">
                        <button
                            v-for="button in categoryButtons"
                            :key="button.id + '-bottom'"
                            @click="selectCategory(button.id)"
                            type="button"
                            class="flex items-center gap-3 rounded-full border px-4 py-3 text-sm font-semibold uppercase tracking-wide transition-colors"
                            :class="selectedCategoryId === button.id ? 'border-primary-500 bg-primary-50 text-primary-600' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                        >
                            <span class="text-2xl">{{ button.icon }}</span>
                            <span>{{ button.label }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <section class="w-[380px] flex-shrink-0 bg-[#f2f2f4] border-l border-gray-200 flex flex-col">
                <div class="p-4 flex-1 overflow-hidden">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 h-full flex flex-col">
                        <div class="p-4 border-b border-gray-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm font-semibold text-gray-800">
                                    <span>Client</span>
                                    <span class="text-gray-400">:</span>
                                </div>
                                <button
                                    @click="showCustomerSelector = !showCustomerSelector"
                                    class="p-1.5 rounded-lg text-blue-600 hover:text-blue-700 hover:bg-blue-50"
                                    type="button"
                                    title="Ajouter client"
                                >
                                    <UserPlusIcon class="w-5 h-5" />
                                </button>
                            </div>
                            <p class="text-xs text-gray-500">
                                {{ cartStore.customerId ? cartStore.customerName : 'Aucun client sélectionné' }}
                            </p>
                            <div v-if="showCustomerSelector" class="space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <input
                                    v-model="customerSearch"
                                    type="text"
                                    placeholder="Chercher un client..."
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                <div class="max-h-32 overflow-y-auto space-y-1">
                                    <button
                                        v-for="customer in filteredCustomers"
                                        :key="customer.id"
                                        @click="selectCustomer(customer)"
                                        class="w-full text-left rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-white transition-colors"
                                        type="button"
                                    >
                                        {{ customer.name }}
                                    </button>
                                    <p v-if="filteredCustomers.length === 0" class="text-xs text-gray-500 px-3 py-2">
                                        Aucun client trouvé.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="mode in serviceModes"
                                    :key="mode.value"
                                    @click="serviceMode = mode.value"
                                    type="button"
                                    class="flex items-center justify-center gap-2 rounded-lg border px-2 py-2 text-xs font-semibold transition-colors"
                                    :class="serviceMode === mode.value ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'"
                                >
                                    <span class="text-base">{{ mode.icon }}</span>
                                    <span>{{ mode.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="px-4 py-2 border-b border-gray-200 flex items-center justify-between text-[11px] uppercase tracking-wide text-gray-500">
                            <span>Articles</span>
                            <span>{{ cartStore.items.length }} articles</span>
                        </div>

                        <div class="flex-1 overflow-auto">
                            <div v-if="cartStore.items.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                                <ShoppingCartIcon class="w-12 h-12 mb-2" />
                                <p class="text-sm">Ticket vide</p>
                            </div>
                            <div v-else class="divide-y divide-dashed divide-gray-200">
                                <div
                                    v-for="(item, index) in cartStore.items"
                                    :key="index"
                                    class="px-4 py-3"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ item.article_name }}</p>
                                            <div v-if="item.selected_variant?.template_name" class="text-xs text-gray-500 mt-1">
                                                {{ item.selected_variant.template_name }} · {{ item.selected_variant.template_value }}
                                            </div>
                                            <button
                                                v-if="item.article?.has_options"
                                                type="button"
                                                @click="editItemOptions(index, item)"
                                                class="mt-1 text-xs text-blue-600 hover:text-blue-700 hover:underline font-medium"
                                            >
                                                Modifier les options
                                            </button>
                                            <div v-if="item.selected_options && item.selected_options.length" class="mt-2 space-y-0.5">
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
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1 rounded-lg bg-gray-100 px-1.5 py-1">
                                                <button
                                                    @click="updateQuantity(index, item.quantity - 1)"
                                                    class="text-gray-600 hover:text-gray-800"
                                                    type="button"
                                                >
                                                    <MinusIcon class="w-4 h-4" />
                                                </button>
                                                <span class="text-xs font-semibold text-gray-700">x{{ item.quantity }}</span>
                                                <button
                                                    @click="updateQuantity(index, item.quantity + 1)"
                                                    class="text-gray-600 hover:text-gray-800"
                                                    type="button"
                                                >
                                                    <PlusIcon class="w-4 h-4" />
                                                </button>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-900">{{ formatCurrency(getItemLineTotal(item)) }}</span>
                                            <button
                                                @click="removeItem(index)"
                                                class="text-red-500 hover:text-red-700"
                                                title="Supprimer"
                                                type="button"
                                            >
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500">
                                        {{ formatCurrency(item.unit_price + (item.variant_price || 0) + (item.options_price || 0)) }}/pcs
                                        <span v-if="item.variant_price > 0" class="ml-2 text-orange-600 font-semibold">
                                            + Variant {{ formatCurrency(item.variant_price) }}
                                        </span>
                                        <span v-if="item.options_price > 0" class="ml-2 text-blue-600 font-semibold">
                                            + Options {{ formatCurrency(item.options_price) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-3 border-t border-gray-200 space-y-2 text-sm">
                            <div class="flex justify-between text-gray-600 border-b border-dashed border-gray-200 pb-2">
                                <span class="font-medium">Total HT :</span>
                                <span class="font-semibold text-gray-900">{{ formatCurrency(cartStore.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 border-b border-dashed border-gray-200 pb-2">
                                <span class="font-medium">TVA :</span>
                                <span class="font-semibold text-gray-900">{{ formatCurrency(cartStore.taxAmount) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 border-b border-dashed border-gray-200 pb-2">
                                <span class="font-medium">Remise :</span>
                                <span class="font-semibold text-gray-900">{{ formatCurrency(cartStore.discountTotal) }}</span>
                            </div>
                            <div class="flex items-baseline justify-between pt-2">
                                <span class="text-lg font-bold text-gray-900">TOTAL TTC :</span>
                                <span class="text-2xl font-bold text-green-600">{{ formatCurrency(cartStore.total) }}</span>
                            </div>
                        </div>

                        <div class="px-4 py-3 border-t border-gray-200 space-y-2">
                            <button
                                @click="showPaymentModal = true"
                                :disabled="cartStore.items.length === 0"
                                class="w-full py-3 px-4 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                type="button"
                            >
                                PASSER AU PAIEMENT
                            </button>
                            <button
                                @click="saveSale"
                                :disabled="cartStore.items.length === 0"
                                class="w-full py-3 px-4 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                type="button"
                            >
                                SAUVEGARDER
                            </button>
                        </div>
                    </div>
                </div>
            </section>
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
                ></textarea>
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
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useCartStore } from '../stores/cart'
import { useArticlesStore } from '../stores/articles'
import { useSettingsStore } from '../stores/settings'
import { useCustomersStore } from '../stores/customers'
import { useOfflineStore } from '../stores/offline'
import { useUiStore } from '../stores/ui'
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
    ShoppingCartIcon,
    MinusIcon,
    PlusIcon,
    TrashIcon,
    UserPlusIcon
} from '@heroicons/vue/24/outline'

const cartStore = useCartStore()
const articlesStore = useArticlesStore()
const settingsStore = useSettingsStore()
const customersStore = useCustomersStore()
const uiStore = useUiStore()
const { posCategoryDisplayMode } = storeToRefs(settingsStore)
const categoriesDisplayMode = posCategoryDisplayMode
const showPaymentModal = ref(false)
const showCalculator = ref(false)
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
const selectedCategoryId = ref('all')
const customerSearch = ref('')
const ticketNotes = ref('')
const discountAmount = ref(0)
const discountPercent = ref(0)
const currentPage = ref(1)
const itemsPerPage = ref(20)
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

const categoryButtons = computed(() => {
    const baseButtons = [
        { id: null, label: 'Favoris', icon: '⭐' },
        { id: 'all', label: 'Tous', icon: '📦' },
    ]

    const dynamicButtons = (categories.value || []).map((category) => ({
        id: category.id,
        label: category.name,
        icon: getCategoryIcon(category.icon),
    }))

    return [...baseButtons, ...dynamicButtons]
})

const serviceModes = [
    { value: 'dine_in', label: 'Sur place', icon: '🍽️' },
    { value: 'pickup', label: 'A emporter', icon: '🥡' },
    { value: 'delivery', label: 'Livraison', icon: '🚚' },
    { value: 'glovo', label: 'Glovo', icon: '🛵' },
]

const serviceMode = computed({
    get: () => cartStore.deliveryMode,
    set: (value) => cartStore.setDeliveryMode(value),
})

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

const totalArticles = computed(() => filteredArticles.value.length)

const totalPages = computed(() => {
    const pages = Math.ceil(totalArticles.value / itemsPerPage.value)
    return pages > 0 ? pages : 1
})

const paginatedArticles = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    return filteredArticles.value.slice(start, start + itemsPerPage.value)
})

const pageSummary = computed(() => {
    if (totalArticles.value === 0) {
        return 'Aucun article'
    }
    const start = (currentPage.value - 1) * itemsPerPage.value + 1
    const end = Math.min(currentPage.value * itemsPerPage.value, totalArticles.value)
    return `Affichage de ${start} à ${end} sur ${totalArticles.value}`
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

function toggleAppSidebar() {
    uiStore.togglePosSidebar()
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

function getActiveVariants(article) {
    return (article?.variants || []).filter(v => v.is_active !== false)
}

function selectCategory(categoryId) {
    selectedCategoryId.value = categoryId
}

function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value += 1
    }
}

function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value -= 1
    }
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

    const activeVariants = getActiveVariants(fullArticle)
    const hasActiveVariants = activeVariants.length > 0

    // If article has active variants, show variant selection modal first
    if (hasActiveVariants) {
        showSelectVariantsModal.value = true
        selectedArticleForVariants.value = fullArticle
        variantSelectionMode.value = 'add'
        return
    }

    // No variants configured -> add directly to cart (options can be edited manually)
    cartStore.addItem(fullArticle)
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

function selectCustomer(customer) {
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
        ticketNotes.value = ''
        discountAmount.value = 0
        discountPercent.value = 0
    }
}

watch([searchQuery, selectedCategoryId], () => {
    currentPage.value = 1
})

watch([filteredArticles, itemsPerPage], () => {
    if (currentPage.value > totalPages.value) {
        currentPage.value = totalPages.value
    }
    if (currentPage.value < 1) {
        currentPage.value = 1
    }
})

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
    await settingsStore.fetchSettings()
    await articlesStore.refresh()
    await customersStore.fetchCustomers()
})
</script>
