<template>
    <div class="min-h-screen bg-gray-50 p-4 lg:p-8">
        <div class="max-w-5xl mx-auto space-y-6">
            <header class="flex flex-col gap-1">
                <p class="text-xs uppercase tracking-wide text-primary-600">Options central</p>
                <h1 class="text-3xl font-bold text-gray-900">🎁 Options & variantes</h1>
                <p class="text-sm text-gray-600">Créez rapidement des options réutilisables et ajoutez des variantes pour vos articles.</p>
            </header>

            <section class="grid gap-4 lg:grid-cols-2">
                <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Nouvelle option</h2>
                        <span class="text-xs text-gray-500">Vue rapide</span>
                    </div>
                    <div class="space-y-3">
                        <label class="text-xs font-semibold text-gray-600">Nom de l’option</label>
                        <input 
                            v-model="form.optionName"
                            type="text"
                            placeholder="Ex: Sauce, Couleur, Taille"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-600">1ère valeur</label>
                            <input 
                                ref="variantNameInput"
                                v-model="form.variantName"
                                type="text"
                                placeholder="Ex: Piquante, Rouge"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-600">Prix ({{ currencyCode }})</label>
                            <input 
                                ref="variantPriceInput"
                                v-model.number="form.variantPrice"
                                type="number"
                                step="0.01"
                                placeholder="0.00"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                        </div>
                    </div>
                    <button 
                        @click="createOption"
                        :disabled="isCreating || !form.optionName.trim() || !form.variantName.trim() || form.variantPrice === null"
                        class="w-full py-2 rounded-xl bg-primary-500 text-gray-900 font-semibold hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isCreating ? 'Création...' : '+ Créer l’option' }}
                    </button>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Variantes d’article</h2>
                        <span class="text-xs text-gray-500">Article linked</span>
                    </div>
                    <p class="text-sm text-gray-600">Les variantes d’article se créent depuis ici puis reçoivent la sélection.</p>
                    <button 
                        @click="showArticleVariantForm = true"
                        class="w-full py-2 rounded-xl border border-primary-500 text-primary-600 font-semibold hover:bg-primary-50"
                    >
                        + Créer une variante d’article
                    </button>
                </div>
            </section>

            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Vos options</h2>
                    <p class="text-sm text-gray-500">{{ options.length }} options actives</p>
                </div>
                <div v-if="options.length === 0" class="bg-white rounded-2xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500">
                    <div class="text-3xl mb-2">⚙️</div>
                    Créez votre première option pour commencer à ajouter des variantes.
                </div>
                <div v-else class="space-y-3">
                    <div
                        v-for="option in options"
                        :key="option.id"
                        class="bg-white border border-gray-100 rounded-2xl shadow-sm p-4 space-y-3"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-lg font-semibold text-gray-900">{{ option.name }}</p>
                                <p class="text-xs text-gray-500">{{ option.variants.length }} valeur(s) disponible(s)</p>
                            </div>
                                <div class="flex items-center gap-2">
                                    <button 
                                        @click="editingOptionId === option.id ? editingOptionId = null : editingOptionId = option.id"
                                        class="flex items-center gap-2 px-4 py-2 rounded-2xl border border-primary-200 text-sm font-semibold text-primary-600 bg-primary-50 hover:bg-primary-100"
                                    >
                                        <span class="text-base">+</span>
                                        <span>{{ editingOptionId === option.id ? 'Fermer' : 'Ajouter' }}</span>
                                    </button>
                                    <button 
                                        @click="confirmDeleteOption(option)"
                                        class="flex items-center gap-2 px-4 py-2 rounded-2xl border border-red-200 text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100"
                                    >
                                        <span>🗑️</span>
                                        <span>Supprimer</span>
                                    </button>
                                </div>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span 
                                v-for="variant in option.variants"
                                :key="variant.id"
                                class="px-3 py-1 rounded-full bg-gray-100 text-gray-700"
                            >
                                {{ variant.name }} (+{{ formatPrice(variant.price_impact) }})
                            </span>
                        </div>
                        <transition name="expand">
                            <div v-if="editingOptionId === option.id" class="border border-dashed border-gray-200 rounded-2xl p-3 bg-gray-50 space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-[2fr,1fr] gap-3">
                                    <div class="space-y-1">
                                        <input 
                                            v-model="variantForms[option.id].name"
                                            type="text"
                                            placeholder="Nouvelle valeur"
                                            class="w-full text-sm px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        >
                                        <input 
                                            v-model.number="variantForms[option.id].price"
                                            type="number"
                                            step="0.01"
                                            placeholder="Prix"
                                            class="w-full text-sm px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        >
                                    </div>
                                    <button 
                                        @click="addVariant(option.id)"
                                        :disabled="!variantForms[option.id].name.trim() || variantForms[option.id].price === null"
                                        class="flex items-center justify-center gap-2 rounded-2xl bg-primary-600 text-white font-semibold hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm px-4 py-3"
                                    >
                                        <span class="text-lg">+</span>
                                        Ajouter
                                    </button>
                                </div>
                                <div class="space-y-2 text-xs">
                                    <p class="font-semibold text-gray-700">Valeurs existantes</p>
                                    <div class="grid gap-2">
                                        <div 
                                            v-for="variant in option.variants" 
                                            :key="variant.id"
                                            class="flex items-center justify-between bg-white border border-gray-200 rounded-xl px-3 py-2"
                                        >
                                            <div>
                                                <p class="text-sm text-gray-900">{{ variant.name }}</p>
                                                <p class="text-xs text-gray-500">+{{ formatPrice(variant.price_impact) }}</p>
                                            </div>
                                            <button 
                                                class="text-xs text-red-600"
                                                @click="confirmDeleteVariant(option.id, variant)"
                                            >
                                                Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </section>

        <!-- Create Article Variant Modal -->
        <transition name="fade">
            <div v-if="showArticleVariantForm" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-xl max-w-md w-full space-y-6 p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-gray-900">📏 Créer Variante</h3>
                        <button 
                            @click="showArticleVariantForm = false"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <p class="text-sm text-gray-600">
                        Choisissez un article pour créer sa variante ici.
                    </p>

                    <!-- Form -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Article *</label>
                            <select
                                v-model="variantForm.article_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium"
                            >
                                <option value="" disabled>Sélectionner un article</option>
                                <option v-for="article in articles" :key="article.id" :value="article.id">
                                    {{ article.name }}
                                </option>
                            </select>
                            <p v-if="articlesLoading" class="text-xs text-gray-500 mt-1">Chargement des articles...</p>
                        </div>

                        <div class="space-y-2 text-sm text-gray-700">
                            <p class="text-xs font-semibold text-gray-600 uppercase">Variantes existantes</p>
                            <div v-if="!variantForm.article_id" class="text-xs text-gray-500">Sélectionnez un article pour voir ses variantes.</div>
                            <div v-else-if="variantsLoading" class="text-xs text-gray-500">Chargement des variantes...</div>
                            <div v-else class="space-y-2 max-h-40 overflow-y-auto">
                                <p v-if="selectedArticleVariants.length === 0" class="text-xs text-gray-500">Aucune variante enregistrée pour cet article.</p>
                                <div
                                    v-for="variant in selectedArticleVariants"
                                    :key="variant.id"
                                    class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3 py-2"
                                >
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900">{{ variant.name }}</span>
                                        <span
                                            class="text-xs"
                                            :class="variant.is_active ? 'text-green-600' : 'text-gray-400'"
                                        >
                                            {{ variant.is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-semibold text-primary-600">+{{ formatPrice(variant.price_impact) }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de la variante *</label>
                            <input 
                                v-model="variantForm.name"
                                type="text"
                                placeholder="Petit, Moyen, Grand, etc."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Impact sur le prix ({{ currencyCode }})</label>
                            <input 
                                v-model.number="variantForm.price_impact"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-bold text-lg"
                            >
                            <p class="text-xs text-gray-500 mt-1">Prix supplémentaire pour cette variante (optionnel)</p>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Variante active</label>
                                <p class="text-xs text-gray-500 mt-1">Disponible à la sélection</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    v-model="variantForm.is_active"
                                    type="checkbox"
                                    class="sr-only peer"
                                >
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                        <button 
                            @click="showArticleVariantForm = false"
                            class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Annuler
                        </button>
                        <button 
                            @click="saveArticleVariant"
                            :disabled="!variantForm.article_id || !variantForm.name.trim()"
                            class="flex-1 px-4 py-3 bg-primary-500 text-gray-900 font-semibold rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ savingVariant ? 'Création...' : '✓ Créer' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Delete Confirmation Modal -->
        <div v-if="deleteModal.show" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-xl">
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ deleteModal.title }}</h3>
                <p class="text-gray-600 mb-6">{{ deleteModal.message }}</p>
                <div class="flex gap-3">
                    <button 
                        @click="deleteModal.show = false"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button 
                        @click="executeDelete"
                        class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </div>

        <!-- Variant Table -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Variantes d'article</p>
                    <p class="text-xs text-gray-500">Liste des variantes créées pour chaque article</p>
                </div>
                <router-link
                    to="/options-variants"
                    class="text-xs font-semibold text-primary-600 hover:text-primary-500"
                >Page variants</router-link>
            </div>

            <div v-if="articlesLoading" class="p-6 text-center text-xs text-gray-500">
                Chargement des variantes...
            </div>
            <div v-else-if="articleVariantsSummary.length === 0" class="p-6 text-center text-xs text-gray-500">
                Aucune variante créée pour le moment.
            </div>

            <div v-else class="divide-y divide-gray-100">
                <div
                    v-for="variant in articleVariantsSummary"
                    :key="variant.id"
                    class="grid grid-cols-12 gap-3 px-4 py-3 text-xs items-center"
                >
                    <span class="col-span-4 font-semibold text-gray-900 truncate">{{ variant.articleName }}</span>
                    <span class="col-span-5 text-gray-600 truncate">{{ variant.name }}</span>
                    <span class="col-span-2 font-semibold text-primary-600">+{{ formatPrice(variant.priceImpact) }}</span>
                    <span
                        class="col-span-1 text-right"
                        :class="variant.isActive ? 'text-green-600' : 'text-gray-400'"
                    >
                        {{ variant.isActive ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { optionsApi, articlesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'

const settingsStore = useSettingsStore()

const options = ref([])
const isCreating = ref(false)
const showArticleVariantForm = ref(false)
const editingOptionId = ref(null)
const variantForms = reactive({})
const articles = ref([])
const articlesLoading = ref(false)
const selectedArticleVariants = ref([])
const variantsLoading = ref(false)

const currencyCode = computed(() => settingsStore.currencyCode || 'DHS')

const articleVariantsSummary = computed(() => {
    return articles.value.flatMap(article => {
        const variants = Array.isArray(article.variants) ? article.variants : []
        return variants.map((variant, idx) => ({
            id: `${article.id}-${variant.id ?? idx}`,
            articleName: article.name,
            name: variant.name,
            priceImpact: variant.price_impact ?? 0,
            isActive: variant.is_active ?? true,
        }))
    })
})

const form = reactive({
    optionName: '',
    variantName: '',
    variantPrice: null,
})

const variantForm = reactive({
    article_id: '',
    name: '',
    price_impact: 0,
    is_active: true,
})

const savingVariant = ref(false)

const deleteModal = reactive({
    show: false,
    title: '',
    message: '',
    type: null,
    optionId: null,
    variant: null,
})

const variantNameInput = ref(null)
const variantPriceInput = ref(null)

const formatPrice = (price) => {
    if (!price && price !== 0) return '0.00'
    return parseFloat(price).toFixed(2)
}

function focusNextField(field) {
    if (field === 'variantName') variantNameInput.value?.focus()
    if (field === 'variantPrice') variantPriceInput.value?.focus()
}

async function createOption() {
    const optionName = form.optionName.trim()
    const variantName = form.variantName.trim()
    const variantPrice = form.variantPrice

    if (!optionName || !variantName || variantPrice === null) {
        alert('Remplissez tous les champs')
        return
    }

    isCreating.value = true
    try {
        const response = await optionsApi.create({
            name: optionName,
            type: 'fixed',
            values: [variantName],
            extra_price: variantPrice,
            is_active: true,
            is_required: false
        })

        const newOption = response.data
        newOption.variants = [{
            id: 1,
            name: variantName,
            price_impact: variantPrice
        }]
        options.value.push(newOption)
        variantForms[newOption.id] = { name: '', price: null }

        // Reset
        form.optionName = ''
        form.variantName = ''
        form.variantPrice = null
    } catch (error) {
        console.error('Error creating option:', error)
        alert('Erreur lors de la création')
    } finally {
        isCreating.value = false
    }
}

async function addVariant(optionId) {
    const form = variantForms[optionId]
    if (!form.name.trim() || form.price === null) {
        alert('Remplissez nom et prix')
        return
    }

    try {
        const option = options.value.find(o => o.id === optionId)
        if (!option) return

        // Add to UI
        option.variants.push({
            id: Date.now(),
            name: form.name.trim(),
            price_impact: form.price
        })

        // Update backend
        const values = option.variants.map(v => v.name)
        await optionsApi.update(optionId, {
            name: option.name,
            type: 'fixed',
            values,
            extra_price: form.price,
            is_active: true,
            is_required: false
        })

        // Reset
        variantForms[optionId].name = ''
        variantForms[optionId].price = null
    } catch (error) {
        console.error('Error adding variant:', error)
        alert('Erreur')
    }
}

function confirmDeleteVariant(optionId, variant) {
    deleteModal.show = true
    deleteModal.type = 'variant'
    deleteModal.optionId = optionId
    deleteModal.variant = variant
    deleteModal.title = 'Supprimer la variante'
    deleteModal.message = `Supprimer "${variant.name}" ?`
}

function confirmDeleteOption(option) {
    deleteModal.show = true
    deleteModal.type = 'option'
    deleteModal.optionId = option.id
    deleteModal.title = 'Supprimer l\'option'
    deleteModal.message = `Supprimer "${option.name}" et ses variantes ?`
}

async function executeDelete() {
    try {
        if (deleteModal.type === 'variant') {
            const option = options.value.find(o => o.id === deleteModal.optionId)
            if (!option) return

            const idx = option.variants.findIndex(v => v.id === deleteModal.variant.id)
            if (idx > -1) option.variants.splice(idx, 1)

            const values = option.variants.map(v => v.name)
            const price = option.variants.length ? option.variants[0].price_impact : 0
            
            await optionsApi.update(deleteModal.optionId, {
                name: option.name,
                type: 'fixed',
                values: values.length ? values : [''],
                extra_price: price,
                is_active: true,
                is_required: false
            })
        } else if (deleteModal.type === 'option') {
            await optionsApi.delete(deleteModal.optionId)
            options.value = options.value.filter(o => o.id !== deleteModal.optionId)
            delete variantForms[deleteModal.optionId]
        }
    } catch (error) {
        console.error('Error:', error)
        alert('Erreur')
    } finally {
        deleteModal.show = false
    }
}

async function fetchOptions() {
    try {
        const response = await optionsApi.list()
        options.value = response.data.map(opt => ({
            ...opt,
            variants: opt.values?.map((val, idx) => ({
                id: idx,
                name: val,
                price_impact: opt.extra_price || 0
            })) || []
        }))

        options.value.forEach(opt => {
            if (!variantForms[opt.id]) {
                variantForms[opt.id] = { name: '', price: null }
            }
        })
    } catch (error) {
        console.error('Error fetching options:', error)
    }
}

async function fetchArticles() {
    articlesLoading.value = true
    try {
        const response = await articlesApi.list({ active: true })
        const data = Array.isArray(response.data?.data) ? response.data.data : response.data
        articles.value = Array.isArray(data) ? data : []
    } catch (error) {
        console.error('Error fetching articles:', error)
        articles.value = []
    } finally {
        articlesLoading.value = false
    }
}

async function fetchArticleVariants(articleId) {
    if (!articleId) {
        selectedArticleVariants.value = []
        return
    }

    variantsLoading.value = true
    try {
        const response = await articlesApi.listVariants(articleId)
        const payload = response.data
        const data = Array.isArray(payload) ? payload : Array.isArray(payload?.data) ? payload.data : []
        selectedArticleVariants.value = data
    } catch (error) {
        console.error('Error fetching article variants:', error)
        selectedArticleVariants.value = []
    } finally {
        variantsLoading.value = false
    }
}

async function saveArticleVariant() {
    if (!variantForm.article_id) {
        alert('Veuillez sélectionner un article')
        return
    }
    if (!variantForm.name.trim()) {
        alert('Veuillez entrer un nom pour la variante')
        return
    }

    savingVariant.value = true
    try {
        const articleId = variantForm.article_id
        await articlesApi.createVariant(articleId, {
            name: variantForm.name.trim(),
            price_impact: variantForm.price_impact || 0,
            is_active: variantForm.is_active,
        })

        alert('✅ Variante créée avec succès')
        await fetchArticleVariants(articleId)
        variantForm.name = ''
        variantForm.price_impact = 0
        variantForm.is_active = true
    } catch (error) {
        console.error('Error saving variant:', error)
        alert('Erreur lors de la création de la variante')
    } finally {
        savingVariant.value = false
    }
}

watch(() => variantForm.article_id, (articleId) => {
    if (articleId) {
        fetchArticleVariants(articleId)
    } else {
        selectedArticleVariants.value = []
    }
})

watch(showArticleVariantForm, (visible) => {
    if (!visible) {
        variantForm.article_id = ''
        selectedArticleVariants.value = []
    }
})

onMounted(() => {
    fetchOptions()
    fetchArticles()
})
</script>

<style scoped>
button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.expand-enter-active,
.expand-leave-active {
    transition: all 0.3s ease;
}

.expand-enter-from {
    opacity: 0;
    max-height: 0;
}

.expand-leave-to {
    opacity: 0;
    max-height: 0;
}
</style>
