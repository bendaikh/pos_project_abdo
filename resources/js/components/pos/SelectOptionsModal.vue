<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="relative bg-white rounded-2xl shadow-xl max-w-4xl w-full mx-auto z-10 max-h-[85vh] overflow-y-auto">
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-primary-50 to-cyan-50 px-6 py-5 border-b border-gray-200 flex items-center justify-between rounded-t-2xl">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ article?.name }}</h3>
                    <p class="text-sm text-gray-600 mt-1">✓ Sélectionnez les options et variantes disponibles</p>
                </div>
                <button @click="emit('close')" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">
                <div class="space-y-6" v-if="selectableOptions.length > 0">
                    <!-- Option Cards -->
                    <div v-for="option in selectableOptions" :key="option.id" class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
                        <!-- Option Header -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-bold text-gray-900">{{ option.name }}</h4>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">
                                <span v-if="option.is_required" class="text-orange-600 font-medium">✱ Obligatoire</span>
                                <span v-else class="text-gray-500">Optionnel</span>
                            </p>
                        </div>

                        <!-- Variants Grid -->
                        <div v-if="activeVariants(option).length === 0" class="text-sm text-gray-500 py-4">
                            Aucune variante disponible pour cette option
                        </div>

                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <label
                                v-for="variant in activeVariants(option)"
                                :key="variant.id"
                                class="flex items-center gap-4 p-4 bg-white border-2 rounded-xl transition-all"
                                :class="isVariantSelected(option, variant) ? 'border-primary-500 shadow-md bg-primary-50' : 'border-gray-200'"
                            >
                                <div class="flex-shrink-0">
                                    <input
                                        type="checkbox"
                                        :name="`option-${option.id}`"
                                        :value="variant.id"
                                        class="w-5 h-5 text-primary-600 border-gray-300 cursor-pointer"
                                        :checked="isVariantSelected(option, variant)"
                                        @change="selectVariant(option, variant.id)"
                                    >
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 text-base">{{ variant.name }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Supplément: {{ formatOptionPrice(variant.price_impact) }}
                                    </p>
                                    <p v-if="Number(variant.price_impact) !== 0" class="text-sm font-bold text-primary-600 mt-1">
                                        {{ formatPriceImpact(variant.price_impact) }}
                                    </p>
                                </div>

                                <!-- Color & Image Preview -->
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span v-if="variant.color" class="w-6 h-6 rounded-lg border-2 border-gray-200" :style="{ backgroundColor: variant.color }" :title="variant.color"></span>
                                    <img v-if="variant.image" :src="variant.image" alt="" class="w-10 h-10 rounded-lg object-cover border border-gray-200">
                                </div>
                            </label>
                        </div>
                        <p v-if="option.is_required && !hasSelection(option.id)" class="text-xs text-orange-600 mt-3">
                            Choisissez une variante pour continuer.
                        </p>
                    </div>

                    <!-- Options Total -->
                    <div class="bg-gradient-to-r from-primary-50 to-cyan-50 rounded-2xl p-4 border border-primary-100 flex items-center justify-between">
                        <span class="text-gray-700 font-semibold">💰 Coût des options</span>
                        <span class="text-2xl font-bold text-primary-600">
                            {{ formatPriceImpact(optionsPrice) }}
                        </span>
                    </div>

                </div>
            </div>

            <!-- Footer -->
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
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
    initialSelections: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['close', 'confirm'])

// Reactive state for selections
const selectedOptions = ref(Array.isArray(props.initialSelections) ? [...props.initialSelections] : [])
const optionsPrice = ref(0)

// Computed properties
const selectableOptions = computed(() => {
    return (props.article?.options || [])
        .map((option) => {
            const activeVariants = (option.variants || []).filter((variant) => variant.is_active !== false)
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
                is_active: option.is_active !== false,
                variants: activeVariants.length > 0 ? activeVariants : fallbackVariants,
            }
        })
        .filter((option) => option.variants && option.variants.length > 0)
})

// Methods
function activeVariants(option) {
    return (option.variants || []).filter((variant) => variant.is_active !== false)
}

const currencyFormatter = new Intl.NumberFormat('fr-DZ', {
    style: 'currency',
    currency: 'DZD',
    minimumFractionDigits: 2,
})

function formatPriceImpact(price) {
    const amount = Number(price) || 0
    if (amount === 0) return 'Gratuit'
    return amount > 0 ? `+ ${currencyFormatter.format(amount)}` : `- ${currencyFormatter.format(Math.abs(amount))}`
}

function formatOptionPrice(price) {
    const amount = Number(price) || 0
    if (amount === 0) return 'Gratuit'
    return `+ ${currencyFormatter.format(amount)}`
}

function isVariantSelected(option, variant) {
    return selectedOptions.value.some(
        (sel) => sel.option_id === option.id && sel.variants.some((v) => v.id === variant.id)
    )
}

function selectVariant(option, variantId) {
    const variant = option.variants.find((v) => v.id === variantId)
    if (!variant) return

    // Get or create option selection
    let optionSelection = selectedOptions.value.find((sel) => sel.option_id === option.id)
    
    if (!optionSelection) {
        // Create new option selection
        optionSelection = {
            option_id: option.id,
            option_name: option.name,
            type: option.type,
            variants: [],
        }
        selectedOptions.value.push(optionSelection)
    }

    // Toggle variant selection (add if not present, remove if present)
    const variantIndex = optionSelection.variants.findIndex((v) => v.id === variantId)
    
    if (variantIndex >= 0) {
        // Remove variant
        optionSelection.variants.splice(variantIndex, 1)
    } else {
        // Add variant
        optionSelection.variants.push({
            id: variant.id,
            name: variant.name,
            price_impact: variant.price_impact
        })
    }

    updateOptionsPrice()
}

function hasSelection(optionId) {
    return selectedOptions.value.some((sel) => sel.option_id === optionId && sel.variants.length > 0)
}

function updateOptionsPrice() {
    optionsPrice.value = selectedOptions.value.reduce((total, optionSelection) => {
        // Sum price for all selected variants in this option
        const variantsTotal = optionSelection.variants.reduce((sum, variant) => {
            const priceImpact = Number(variant.price_impact) || 0
            return sum + priceImpact
        }, 0)
        return total + variantsTotal
    }, 0)
}

const canConfirm = computed(() => {
    return selectableOptions.value.every((option) => {
        if (!option.is_required) return true
        return hasSelection(option.id)
    })
})

onMounted(() => {
    if (selectedOptions.value.length > 0) {
        updateOptionsPrice()
    }
})

function handleConfirm() {
    emit('confirm', {
        selectedOptions: selectedOptions.value,
        optionsPrice: optionsPrice.value,
    })
}
</script>
