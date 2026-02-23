<template>
    <div class="min-h-screen bg-gray-50 p-4 lg:p-8 text-base">
        <div class="max-w-5xl mx-auto space-y-6">
            <header class="flex flex-col gap-1">
                <p class="text-sm uppercase tracking-wide text-primary-600">Options central</p>
                <h1 class="text-3xl font-bold text-gray-900">🎁 Options & variantes</h1>
                <p class="text-sm text-gray-600">Créez rapidement des options réutilisables et ajoutez des variantes pour vos articles.</p>
            </header>

            <section class="space-y-6">
                <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="flex items-center justify-start bg-gray-50 border-b border-gray-100">
                        <button
                            @click="activeTab = 'options'"
                            class="flex-1 py-3 text-center text-sm font-semibold transition-colors"
                            :class="activeTab === 'options' ? 'text-primary-600 border-b-2 border-primary-500 bg-white' : 'text-gray-500 hover:bg-gray-100'"
                        >
                            Options
                        </button>
                        <button
                            @click="activeTab = 'variants'"
                            class="flex-1 py-3 text-center text-sm font-semibold transition-colors"
                            :class="activeTab === 'variants' ? 'text-primary-600 border-b-2 border-primary-500 bg-white' : 'text-gray-500 hover:bg-gray-100'"
                        >
                            Variantes
                        </button>
                    </div>
                    <div v-if="activeTab === 'options'" class="p-6 space-y-6">
                        <div class="grid gap-4 lg:grid-cols-2">
                            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5 space-y-4">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-semibold text-gray-900">Nouvelle option</h2>
                                    <span class="text-sm text-gray-500">Vue rapide</span>
                                </div>
                                <div class="space-y-3">
                                    <label class="text-sm font-semibold text-gray-600">Nom de l’option</label>
                                    <input 
                                        v-model="form.optionName"
                                        type="text"
                                        placeholder="Ex: Sauce, Couleur, Taille"
                                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-sm font-semibold text-gray-600">1ère valeur</label>
                                        <input 
                                            v-model="form.variantName"
                                            type="text"
                                            placeholder="Ex: Piquante, Rouge"
                                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        >
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-sm font-semibold text-gray-600">Prix ({{ currencyCode }})</label>
                                        <input 
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
                            <div class="bg-primary-50 border border-primary-100 rounded-2xl p-5 space-y-3">
                                <div>
                                    <h2 class="text-lg font-semibold text-primary-700">Variantes (sous-produits)</h2>
                                    <p class="text-sm text-primary-600">Définissez les noms et valeurs qui seront liés aux articles.</p>
                                </div>
                                <button
                                    @click="activeTab = 'variants'"
                                    class="w-full rounded-2xl border border-primary-200 bg-white text-primary-600 font-semibold px-4 py-2 hover:bg-primary-50"
                                >
                                    Voir les variantes
                                </button>
                                <p class="text-base text-primary-600">Lien direct avec le POS et le formulaire article.</p>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Vos options</h3>
                                <p class="text-sm text-gray-500">{{ options.length }} options actives</p>
                            </div>
                            <div v-if="options.length === 0" class="mt-4 bg-white rounded-2xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500">
                                <div class="text-3xl mb-2">⚙️</div>
                                Créez votre première option pour commencer à ajouter des variantes.
                            </div>
                            <div v-else class="mt-3 space-y-3">
                                <div
                                    v-for="option in options"
                                    :key="option.id"
                                    class="bg-white border border-gray-100 rounded-2xl shadow-sm p-4 space-y-3"
                                >
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-lg font-semibold text-gray-900">{{ option.name }}</p>
                                            <p class="text-sm text-gray-500">{{ option.variants.length }} valeur(s) disponible(s)</p>
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
                                    <div class="flex flex-wrap gap-2 text-sm">
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
                                            <div class="space-y-2 text-sm">
                                                <p class="font-semibold text-gray-700">Valeurs existantes</p>
                                                <div class="grid gap-2">
                                                    <div 
                                                        v-for="variant in option.variants" 
                                                        :key="variant.id"
                                                        class="flex items-center justify-between bg-white border border-gray-200 rounded-xl px-3 py-2"
                                                    >
                                                        <div>
                                                            <p class="text-sm text-gray-900">{{ variant.name }}</p>
                                                            <p class="text-sm text-gray-500">+{{ formatPrice(variant.price_impact) }}</p>
                                                        </div>
                                                        <button 
                                                            class="text-sm text-red-600"
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
                        </div>
                    </div>

                    <div v-else class="p-6 space-y-6">
                        <div class="space-y-4 bg-white border border-gray-100 rounded-2xl shadow-sm p-5">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Variantes (sous-produits)</h2>
                                <p class="text-sm text-gray-600">Déclarez des variantes globales et faites-les chevaucher sur vos articles.</p>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-[1fr,1fr,auto] items-end">
                                <div>
                                    <label class="text-sm font-semibold text-gray-600">Nom de la variante</label>
                                    <input
                                        v-model="variantTemplateForm.name"
                                        type="text"
                                        placeholder="e.g. Taille, Format"
                                        class="w-full text-sm px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-gray-600">Valeur</label>
                                    <input
                                        v-model="variantTemplateForm.value"
                                        type="text"
                                        placeholder="Petit, Moyen, Grand..."
                                        class="w-full text-sm px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                </div>
                                <button
                                    @click="addVariantTemplateValue"
                                    :disabled="!variantTemplateForm.name.trim() || !variantTemplateForm.value.trim()"
                                    class="rounded-2xl bg-primary-600 text-white font-semibold px-4 py-3 hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Ajouter
                                </button>
                            </div>
                            <p class="text-base text-gray-500">Ces variantes seront disponibles dans les formulaires d’articles sans prix ni code-barres.</p>
                        </div>

                        <div v-if="variantTemplates.length === 0" class="bg-white border border-dashed border-gray-200 rounded-2xl p-6 text-center text-sm text-gray-500">
                            <div class="text-3xl mb-2">🎯</div>
                            Créez vos premières variantes pour pouvoir les attacher aux articles plus tard.
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="template in variantTemplates"
                                :key="template.id"
                                class="bg-white border border-gray-100 rounded-2xl shadow-sm p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ template.name }}</p>
                                        <p class="text-sm text-gray-500">{{ template.values.length }} valeur(s)</p>
                                    </div>
                                    <button
                                        @click="removeVariantTemplate(template.id)"
                                        class="text-sm text-red-600 hover:underline"
                                    >
                                        Supprimer la variante
                                    </button>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        v-for="value in template.values"
                                        :key="value"
                                        class="flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700"
                                    >
                                        <span>{{ value }}</span>
                                        <button type="button" @click="removeVariantTemplateValue(template.id, value)" class="text-gray-500">×</button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Variantes d'article</p>
                                    <p class="text-sm text-gray-500">Liste des variantes créées pour chaque article</p>
                                </div>
                                <router-link
                                    to="/options-variants"
                                    class="text-sm font-semibold text-primary-600 hover:text-primary-500"
                                >Page variants</router-link>
                            </div>

                            <div v-if="articlesLoading" class="p-6 text-center text-sm text-gray-500">
                                Chargement des variantes...
                            </div>
                            <div v-else-if="articleVariantsSummary.length === 0" class="p-6 text-center text-sm text-gray-500">
                                Aucune variante créée pour le moment.
                            </div>

                            <div v-else class="divide-y divide-gray-100">
                                <div
                                    v-for="variant in articleVariantsSummary"
                                    :key="variant.id"
                                    class="grid grid-cols-12 gap-3 px-4 py-3 text-sm items-center"
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
            </section>

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

    </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { optionsApi, articlesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { useVariantTemplatesStore } from '../../stores/variantTemplates'

const settingsStore = useSettingsStore()
const variantTemplatesStore = useVariantTemplatesStore()

const options = ref([])
const isCreating = ref(false)
const activeTab = ref('options')
const editingOptionId = ref(null)
const variantForms = reactive({})
const articles = ref([])
const articlesLoading = ref(false)

const currencyCode = computed(() => settingsStore.currencyCode || 'DHS')
const variantTemplates = computed(() => variantTemplatesStore.templates)

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

const variantTemplateForm = reactive({
    name: '',
    value: '',
})

const deleteModal = reactive({
    show: false,
    title: '',
    message: '',
    type: null,
    optionId: null,
    variant: null,
})

const formatPrice = (price) => {
    if (!price && price !== 0) return '0.00'
    return parseFloat(price).toFixed(2)
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

function addVariantTemplateValue() {
    const template = variantTemplatesStore.addTemplateValue(variantTemplateForm.name, variantTemplateForm.value)
    if (!template) return
    variantTemplateForm.name = template.name
    variantTemplateForm.value = ''
}

function removeVariantTemplate(templateId) {
    variantTemplatesStore.removeTemplate(templateId)
}

function removeVariantTemplateValue(templateId, value) {
    variantTemplatesStore.removeTemplateValue(templateId, value)
}

onMounted(() => {
    fetchOptions()
    fetchArticles()
    variantTemplatesStore.loadTemplates()
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
