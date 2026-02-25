<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="relative bg-white rounded-2xl shadow-xl max-w-3xl w-full mx-auto z-10 max-h-[85vh] overflow-y-auto">
            <div class="sticky top-0 bg-gradient-to-r from-primary-50 to-blue-50 px-6 py-5 border-b border-gray-200 flex items-center justify-between rounded-t-2xl">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ article?.name }}</h3>
                    <p class="text-base text-gray-600 mt-1">Sélectionnez la variante et les options simultanément pour aller vite.</p>
                </div>
                <button @click="emit('close')" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <div v-if="!article?.variants || article.variants.length === 0" class="text-center py-12">
                    <div class="text-6xl mb-4">📭</div>
                    <p class="text-xl font-bold text-gray-900 mb-2">Aucune variante disponible</p>
                    <p class="text-gray-600">Cet article n'a pas de variantes configurées</p>
                </div>

                <div v-else class="space-y-6">
                    <p class="text-base text-gray-600 mb-4">
                        Choisissez la variante et les options ci-dessous pour aller vite.
                    </p>

                    <div class="space-y-2">
                        <label
                            v-for="variant in activeVariants"
                            :key="variant.id"
                            class="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all"
                            :class="[
                                selectedVariantId === variant.id
                                    ? 'border-primary-500 bg-primary-50 shadow-md'
                                    : 'border-gray-200 bg-white hover:border-primary-300'
                            ]"
                        >
                            <div class="flex-shrink-0">
                                <input
                                    type="radio"
                                    :name="'variant'"
                                    :value="variant.id"
                                    class="w-5 h-5 text-primary-600 border-gray-300 cursor-pointer"
                                    :checked="selectedVariantId === variant.id"
                                    @change="updateSelectedVariant(variant.id)"
                                >
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-base">
                                    {{ variant.template_name ? `${variant.template_name} · ${variant.template_value}` : variant.name }}
                                </p>
                                <div class="flex flex-wrap gap-2 items-center mt-1 text-sm text-gray-600">
                                    <span v-if="Number(variant.price_impact) > 0" class="text-green-600 font-semibold">
                                        +{{ formatCurrency(variant.price_impact) }}
                                    </span>
                                    <span v-if="variant.cost_price > 0" class="text-orange-600 font-semibold">
                                        Coût: {{ formatCurrency(variant.cost_price) }}
                                    </span>
                                    <span v-if="variant.sku" class="text-gray-500">SKU: {{ variant.sku }}</span>
                                </div>
                            </div>

                            <div v-if="!variant.is_active" class="flex-shrink-0">
                                <span class="px-2 py-1 text-xs font-medium bg-gray-200 text-gray-700 rounded-full">
                                    Inactif
                                </span>
                            </div>
                        </label>
                    </div>

                    <div class="bg-gradient-to-r from-primary-50 to-blue-50 rounded-2xl p-4 border border-primary-100 mt-6">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700 font-semibold">💰 Impact sur le prix</span>
                            <span class="text-2xl font-bold text-primary-600">
                                {{ variantPriceImpact > 0 ? '+' : '' }}{{ formatCurrency(variantPriceImpact) }}
                            </span>
                        </div>
                    </div>

                    <div v-if="hasOptions" class="mt-6 space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-gray-900">Options disponibles</h4>
                            <span class="text-sm text-gray-500">Choisissez vos variantes</span>
                        </div>

                        <div v-for="option in selectableOptions" :key="option.id" class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
                            <div class="mb-4 flex items-center justify-between gap-4">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">{{ option.name }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <span v-if="option.is_required" class="text-orange-600 font-medium">✱ Obligatoire</span>
                                        <span v-else class="text-gray-500">Optionnel</span>
                                    </p>
                                </div>
                            </div>

                            <div v-if="activeOptionVariants(option).length === 0" class="text-sm text-gray-500 py-4">
                                Aucune variante disponible pour cette option.
                            </div>

                            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <label
                                    v-for="variant in activeOptionVariants(option)"
                                    :key="variant.id"
                                    class="flex items-center gap-4 p-4 bg-white border-2 rounded-xl transition-all"
                                    :class="[
                                        isVariantSelected(option, variant)
                                            ? 'border-primary-500 shadow-md bg-primary-50'
                                            : 'border-gray-200 hover:border-primary-400'
                                    ]"
                                >
                                    <div class="flex-shrink-0">
                                        <input
                                            type="checkbox"
                                            :name="'option-' + option.id"
                                            :value="variant.id"
                                            class="w-5 h-5 text-primary-600 border-gray-300 cursor-pointer"
                                            :checked="isVariantSelected(option, variant)"
                                            @change="selectOptionVariant(option, variant.id)"
                                        >
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 text-base">{{ variant.name }}</p>
                                        <p class="text-xs text-gray-500 mt-1">Prix: {{ formatVariantPrice(variant) }}</p>
                                        <p v-if="Number(variant.price_impact) !== 0" class="text-sm font-bold text-primary-600 mt-1">
                                            {{ formatPriceImpact(variant.price_impact) }}
                                        </p>
                                    </div>
                                </label>
                            </div>

                            <p v-if="option.is_required && !hasSelection(option.id)" class="text-sm text-orange-600 mt-3">
                                Choisissez une variante pour continuer.
                            </p>
                        </div>

                        <div class="bg-gradient-to-r from-primary-50 to-blue-50 rounded-2xl p-4 border border-primary-100 flex items-center justify-between">
                            <span class="text-gray-700 font-semibold">💰 Coût des options</span>
                            <span class="text-2xl font-bold text-primary-600">
                                {{ formatPriceImpact(optionsPrice) }}
                            </span>
                        </div>
                        <p v-if="missingRequiredOptions" class="text-sm text-orange-600 font-medium">
                            Sélectionnez toutes les variantes obligatoires pour continuer.
                        </p>
                    </div>
                </div>
            </div>

            <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex gap-3 rounded-b-2xl">
                <button
                    type="button"
                    @click="emit('close')"
                    class="flex-1 py-3 border-2 border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors text-lg"
                >
                    Annuler
                </button>
                <button
                    type="button"
                    @click="handleConfirm"
                    :disabled="!canConfirm"
                    class="flex-1 py-3 bg-primary-500 text-gray-900 font-bold rounded-xl hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-lg"
                >
                    ✓ Ajouter au ticket
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useSettingsStore } from '../../stores/settings'

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: [String, Number],
        default: null,
    },
})

const emit = defineEmits(['close', 'confirm', 'update:modelValue'])

const settingsStore = useSettingsStore()

const selectedVariantId = ref(props.modelValue)
const selectedOptions = ref([])
const optionsPrice = ref(0)

const currencyFormatter = new Intl.NumberFormat('fr-DZ', {
    style: 'currency',
    currency: 'DZD',
    minimumFractionDigits: 2,
})

const activeVariants = computed(() => {
    if (!props.article?.variants) return []
    return props.article.variants.filter(v => v.is_active !== false)
})

const selectedVariant = computed(() => {
    if (!selectedVariantId.value) return null
    return props.article?.variants?.find(v => v.id === selectedVariantId.value) || null
})

const variantPriceImpact = computed(() => {
    return selectedVariant.value ? Number(selectedVariant.value.price_impact) || 0 : 0
})

const selectableOptions = computed(() => {
    return (props.article?.options || [])
        .filter(option => option.is_active)
        .map(option => {
            const activeVariants = (option.variants || []).filter(variant => variant.is_active)
            const fallbackVariants = !activeVariants.length && Array.isArray(option.values)
                ? option.values.map((value, index) => ({
                    id: `value-${option.id}-${index}`,
                    name: value,
                    price_impact: Number(option.extra_price) || 0,
                    is_active: true,
                }))
                : []

            return {
                ...option,
                variants: activeVariants.length > 0 ? activeVariants : fallbackVariants,
            }
        })
        .filter(option => option.variants && option.variants.length > 0)
})

const hasOptions = computed(() => selectableOptions.value.length > 0)

const missingRequiredOptions = computed(() => {
    return selectableOptions.value.some(option => option.is_required && !hasSelection(option.id))
})

const canConfirm = computed(() => {
    return !!selectedVariantId.value && !missingRequiredOptions.value
})

const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const formatPriceImpact = (price) => {
    const amount = Number(price) || 0
    if (amount === 0) return 'Gratuit'
    return amount > 0 ? `+ ${currencyFormatter.format(amount)}` : `- ${currencyFormatter.format(Math.abs(amount))}`
}

const formatVariantPrice = (variant) => {
    const base = Number(props.article?.sell_price) || 0
    const impact = Number(variant.price_impact) || 0
    return currencyFormatter.format(base + impact)
}

function activeOptionVariants(option) {
    return (option.variants || []).filter(variant => variant.is_active)
}

function selectOptionVariant(option, variantId) {
    const variant = option.variants.find(v => v.id === variantId)
    if (!variant) return

    let optionSelection = selectedOptions.value.find(sel => sel.option_id === option.id)
    if (!optionSelection) {
        optionSelection = {
            option_id: option.id,
            option_name: option.name,
            type: option.type,
            variants: [],
        }
        selectedOptions.value.push(optionSelection)
    }

    const variantIndex = optionSelection.variants.findIndex(v => v.id === variantId)
    if (variantIndex >= 0) {
        optionSelection.variants.splice(variantIndex, 1)
    } else {
        optionSelection.variants.push({
            id: variant.id,
            name: variant.name,
            price_impact: variant.price_impact,
        })
    }

    updateOptionsPrice()
}

function isVariantSelected(option, variant) {
    return selectedOptions.value.some(sel => sel.option_id === option.id && sel.variants.some(v => v.id === variant.id))
}

function hasSelection(optionId) {
    return selectedOptions.value.some(sel => sel.option_id === optionId && sel.variants.length > 0)
}

function updateOptionsPrice() {
    optionsPrice.value = selectedOptions.value.reduce((total, optionSelection) => {
        const variantsTotal = optionSelection.variants.reduce((sum, variant) => {
            return sum + (Number(variant.price_impact) || 0)
        }, 0)
        return total + variantsTotal
    }, 0)
}

function updateSelectedVariant(id) {
    selectedVariantId.value = id
    emit('update:modelValue', id)
}

function handleConfirm() {
    if (!selectedVariantId.value) {
        alert('Veuillez sélectionner une variante')
        return
    }
    if (missingRequiredOptions.value) {
        alert('Sélectionnez toutes les variantes obligatoires')
        return
    }

    emit('confirm', {
        variantId: selectedVariantId.value,
        selectedOptions: selectedOptions.value.map(option => ({
            ...option,
            variants: option.variants.map(variant => ({ ...variant })),
        })),
        optionsPrice: optionsPrice.value,
    })
}

watch(
    selectableOptions,
    () => {
        selectedOptions.value = []
        optionsPrice.value = 0
    },
    { immediate: true }
)

watch(
    () => props.modelValue,
    value => {
        selectedVariantId.value = value
    },
    { immediate: true }
)
</script>
