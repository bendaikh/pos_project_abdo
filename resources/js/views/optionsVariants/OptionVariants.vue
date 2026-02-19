<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Options & Variantes d'Article</h1>
                <p class="text-gray-500">Gérez les options (Taille, Couleur, Sauce) et leurs variantes</p>
            </div>
            <button
                type="button"
                @click="router.push('/options/create')"
                class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center"
            >
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouvelle Option
            </button>
        </div>

        <!-- Master-Detail Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Options List -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Rechercher une option..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                        >
                    </div>

                    <div class="max-h-[650px] overflow-y-auto">
                        <div v-if="filteredOptions.length === 0" class="p-6 text-center text-gray-500">
                            <p>Aucune option trouvée</p>
                        </div>
                        <button
                            v-for="option in filteredOptions"
                            :key="option.id"
                            type="button"
                            @click="selectOption(option)"
                            class="w-full text-left p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors"
                            :class="{ 'bg-primary-50 border-l-4 border-l-primary-500': selectedOptionId === option.id }"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ option.name }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded"
                                            :class="option.type === 'fixed' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'"
                                        >
                                            {{ option.type === 'fixed' ? 'Choix unique' : 'Choix multiple' }}
                                        </span>
                                        <span
                                            class="text-xs px-2 py-0.5 rounded"
                                            :class="option.is_active ? 'bg-primary-100 text-gray-900' : 'bg-gray-100 text-gray-600'"
                                        >
                                            {{ option.is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Option Form -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ isCreating ? 'Nouvelle Option' : selectedOption ? 'Détails de l\'option' : 'Sélectionnez une option' }}
                        </h2>
                        <div v-if="selectedOption && !isCreating" class="flex gap-2">
                            <button
                                type="button"
                                @click="deleteOption"
                                class="px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 text-sm font-medium"
                            >
                                Supprimer
                            </button>
                        </div>
                    </div>

                    <div v-if="selectedOption || isCreating" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                            <input
                                v-model="optionForm.name"
                                type="text"
                                placeholder="Ex: Taille, Couleur, Sauce"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="relative flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all"
                                    :class="optionForm.type === 'fixed' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                                    <input
                                        v-model="optionForm.type"
                                        type="radio"
                                        value="fixed"
                                        class="mt-1 w-4 h-4 text-primary-600 border-gray-300"
                                    >
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-900">Choix unique</p>
                                        <p class="text-sm text-gray-500">Une seule variante sélectionnée</p>
                                    </div>
                                </label>
                                <label class="relative flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all"
                                    :class="optionForm.type === 'multiple' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                                    <input
                                        v-model="optionForm.type"
                                        type="radio"
                                        value="multiple"
                                        class="mt-1 w-4 h-4 text-primary-600 border-gray-300"
                                    >
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-900">Choix multiple</p>
                                        <p class="text-sm text-gray-500">Plusieurs variantes possibles</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                v-model="optionForm.is_active"
                                type="checkbox"
                                class="w-4 h-4 text-primary-500 rounded focus:ring-2 focus:ring-primary-500"
                            >
                            <label class="text-sm font-medium text-gray-700">Option active</label>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button
                                type="button"
                                @click="saveOption"
                                :disabled="optionSaving || !optionForm.name.trim()"
                                class="px-4 py-2 bg-primary-500 text-gray-900 rounded-lg hover:bg-primary-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-sm font-medium"
                            >
                                {{ optionSaving ? 'Enregistrement...' : isCreating ? 'Créer l\'option' : 'Mettre à jour' }}
                            </button>
                            <button
                                v-if="isCreating"
                                type="button"
                                @click="cancelCreate"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium"
                            >
                                Annuler
                            </button>
                        </div>
                    </div>

                    <div v-else class="text-sm text-gray-500">
                        Sélectionnez une option à gauche ou créez-en une nouvelle.
                    </div>
                </div>

                <!-- Variants Section -->
                <div v-if="selectedOption && !isCreating" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900">Variantes ({{ variants.length }})</h3>
                            <button
                                type="button"
                                @click="startCreateVariant"
                                class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                            >
                                Nouvelle variante
                            </button>
                        </div>

                        <div v-if="variants.length === 0" class="p-6 text-center text-gray-500">
                            Aucune variante créée
                        </div>
                        <div v-else class="divide-y divide-gray-100 max-h-[420px] overflow-y-auto">
                            <div
                                v-for="variant in variants"
                                :key="variant.id"
                                class="p-4 hover:bg-gray-50"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900">{{ variant.name }}</span>
                                            <span v-if="variant.color" class="w-4 h-4 rounded border border-gray-300" :style="{ backgroundColor: variant.color }"></span>
                                            <span v-if="variant.image" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">Image</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ formatPriceImpact(variant.price_impact) }}
                                        </p>
                                        <p v-if="!variant.is_active" class="text-xs text-gray-400 mt-1">Inactif</p>
                                    </div>
                                    <div class="flex gap-1">
                                        <button
                                            type="button"
                                            @click="editVariant(variant)"
                                            class="p-2 text-blue-600 hover:bg-blue-100 rounded"
                                        >
                                            ✏️
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteVariant(variant)"
                                            class="p-2 text-red-600 hover:bg-red-100 rounded"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">
                            {{ editingVariantId ? 'Modifier variante' : 'Nouvelle variante' }}
                        </h3>

                        <form class="space-y-4" @submit.prevent="saveVariant">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                                <input
                                    v-model="variantForm.name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    required
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Impact prix</label>
                                <div class="flex gap-2">
                                    <input
                                        v-model.number="variantForm.price_impact"
                                        type="number"
                                        step="0.01"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                    <span class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-600">
                                        {{ currencyCode }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Couleur (optionnel)</label>
                                <div class="flex gap-2">
                                    <input
                                        v-model="variantForm.color"
                                        type="text"
                                        placeholder="#FF0000"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                    <div
                                        v-if="variantForm.color"
                                        class="w-10 h-10 rounded-lg border border-gray-300"
                                        :style="{ backgroundColor: variantForm.color }"
                                    ></div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Image (optionnel)</label>
                                <input
                                    ref="variantImageInput"
                                    type="file"
                                    accept="image/*"
                                    @change="handleVariantImageUpload"
                                    class="hidden"
                                >
                                <button
                                    type="button"
                                    @click="triggerVariantImageInput"
                                    class="w-full px-3 py-2 border-2 border-dashed border-gray-300 rounded-lg text-sm text-gray-600 hover:border-primary-500 hover:text-primary-500"
                                >
                                    Ajouter une image
                                </button>
                                <div v-if="variantImagePreview" class="mt-2">
                                    <img :src="variantImagePreview" alt="Preview" class="w-full h-20 object-cover rounded-lg border border-gray-300">
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <input
                                    v-model="variantForm.is_active"
                                    type="checkbox"
                                    class="w-4 h-4 text-primary-500 rounded focus:ring-2 focus:ring-primary-500"
                                >
                                <label class="text-sm font-medium text-gray-700">Variante active</label>
                            </div>

                            <div class="flex gap-2 pt-2">
                                <button
                                    type="submit"
                                    :disabled="variantSaving"
                                    class="flex-1 px-4 py-2 bg-primary-500 text-gray-900 rounded-lg hover:bg-primary-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-sm font-medium"
                                >
                                    {{ variantSaving ? 'Enregistrement...' : editingVariantId ? 'Mettre à jour' : 'Créer' }}
                                </button>
                                <button
                                    v-if="editingVariantId"
                                    type="button"
                                    @click="resetVariantForm"
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium"
                                >
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { PlusIcon } from '@heroicons/vue/24/outline'
import { optionsApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'

const router = useRouter()
const settingsStore = useSettingsStore()
const currencyCode = computed(() => settingsStore.currencyCode)

const options = ref([])
const variants = ref([])
const search = ref('')
const selectedOption = ref(null)
const isCreating = ref(false)
const optionSaving = ref(false)
const variantSaving = ref(false)
const editingVariantId = ref(null)

const optionForm = reactive({
    name: '',
    type: 'fixed',
    is_active: true,
})

const variantForm = reactive({
    name: '',
    price_impact: 0,
    color: '',
    image: '',
    is_active: true,
})

const variantImageInput = ref(null)
const variantImagePreview = ref('')
const selectedOptionId = computed(() => (selectedOption.value ? selectedOption.value.id : null))

const filteredOptions = computed(() => {
    if (!search.value) return options.value
    const query = search.value.toLowerCase()
    return options.value.filter((opt) => opt.name.toLowerCase().includes(query))
})

function formatPriceImpact(value) {
    const amount = Number(value || 0)
    const prefix = amount > 0 ? '+' : ''
    return `${prefix}${amount.toFixed(2)} ${currencyCode.value}`
}

async function fetchOptions() {
    try {
        const response = await optionsApi.list()
        options.value = response.data || []
    } catch (error) {
        console.error('Erreur chargement options:', error)
        options.value = []
    }
}

async function fetchVariants(optionId) {
    try {
        const response = await optionsApi.variants(optionId)
        variants.value = response.data || []
    } catch (error) {
        console.error('Erreur chargement variantes:', error)
        variants.value = []
    }
}

function selectOption(option) {
    isCreating.value = false
    selectedOption.value = option
    optionForm.name = option.name || ''
    optionForm.type = option.type || 'fixed'
    optionForm.is_active = option.is_active ?? true
    editingVariantId.value = null
    resetVariantForm()
    fetchVariants(option.id)
}

function cancelCreate() {
    isCreating.value = false
    optionForm.name = ''
    optionForm.type = 'fixed'
    optionForm.is_active = true
}

async function saveOption() {
    if (!optionForm.name.trim()) return
    optionSaving.value = true
    try {
        if (isCreating.value) {
            const payload = {
                name: optionForm.name.trim(),
                type: optionForm.type,
                is_active: optionForm.is_active,
                values: [],
                is_required: false,
            }
            const response = await optionsApi.create(payload)
            options.value = [...options.value, response.data].sort((a, b) => a.name.localeCompare(b.name))
            selectOption(response.data)
            isCreating.value = false
        } else if (selectedOption.value) {
            const payload = {
                name: optionForm.name.trim(),
                type: optionForm.type,
                is_active: optionForm.is_active,
            }
            const response = await optionsApi.update(selectedOption.value.id, payload)
            const index = options.value.findIndex((opt) => opt.id === selectedOption.value.id)
            if (index > -1) {
                options.value[index] = response.data
                options.value = [...options.value].sort((a, b) => a.name.localeCompare(b.name))
            }
            selectedOption.value = response.data
        }
    } catch (error) {
        console.error('Erreur option:', error)
        alert('Erreur lors de l\'enregistrement de l\'option')
    } finally {
        optionSaving.value = false
    }
}

async function deleteOption() {
    if (!selectedOption.value) return
    if (!confirm(`Supprimer l'option "${selectedOption.value.name}" et toutes ses variantes ?`)) {
        return
    }
    try {
        await optionsApi.delete(selectedOption.value.id)
        options.value = options.value.filter((opt) => opt.id !== selectedOption.value.id)
        selectedOption.value = null
        variants.value = []
    } catch (error) {
        console.error('Erreur suppression option:', error)
        alert('Erreur lors de la suppression')
    }
}

function startCreateVariant() {
    editingVariantId.value = null
    resetVariantForm()
}

function resetVariantForm() {
    variantForm.name = ''
    variantForm.price_impact = 0
    variantForm.color = ''
    variantForm.image = ''
    variantForm.is_active = true
    variantImagePreview.value = ''
}

function editVariant(variant) {
    editingVariantId.value = variant.id
    variantForm.name = variant.name
    variantForm.price_impact = Number(variant.price_impact || 0)
    variantForm.color = variant.color || ''
    variantForm.image = variant.image || ''
    variantForm.is_active = variant.is_active ?? true
    variantImagePreview.value = variant.image || ''
}

function handleVariantImageUpload(event) {
    const file = event.target.files?.[0]
    if (!file) return

    if (!file.type.startsWith('image/')) {
        alert('Veuillez sélectionner une image valide')
        return
    }

    if (file.size > 5 * 1024 * 1024) {
        alert('L\'image ne doit pas dépasser 5 MB')
        return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
        variantForm.image = e.target?.result || ''
        variantImagePreview.value = e.target?.result || ''
    }
    reader.readAsDataURL(file)
}

function triggerVariantImageInput() {
    if (variantImageInput.value) {
        variantImageInput.value.click()
    }
}

async function saveVariant() {
    if (!selectedOption.value) return
    if (!variantForm.name.trim()) return

    variantSaving.value = true
    try {
        const payload = {
            name: variantForm.name.trim(),
            price_impact: Number(variantForm.price_impact || 0),
            color: variantForm.color || null,
            image: variantForm.image || null,
            is_active: variantForm.is_active,
        }

        if (editingVariantId.value) {
            await optionsApi.updateVariant(selectedOption.value.id, editingVariantId.value, payload)
        } else {
            await optionsApi.createVariant(selectedOption.value.id, payload)
        }
        await fetchVariants(selectedOption.value.id)
        resetVariantForm()
        editingVariantId.value = null
    } catch (error) {
        console.error('Erreur variante:', error)
        alert('Erreur lors de l\'enregistrement de la variante')
    } finally {
        variantSaving.value = false
    }
}

async function deleteVariant(variant) {
    if (!selectedOption.value) return
    if (!confirm(`Supprimer la variante "${variant.name}" ?`)) {
        return
    }
    try {
        await optionsApi.deleteVariant(selectedOption.value.id, variant.id)
        await fetchVariants(selectedOption.value.id)
    } catch (error) {
        console.error('Erreur suppression variante:', error)
        alert('Erreur lors de la suppression')
    }
}

onMounted(fetchOptions)
</script>
