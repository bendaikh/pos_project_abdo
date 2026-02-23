<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="relative bg-white rounded-2xl shadow-xl max-w-3xl w-full mx-auto z-10 max-h-[85vh] overflow-y-auto">
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-primary-50 to-blue-50 px-6 py-5 border-b border-gray-200 flex items-center justify-between rounded-t-2xl">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ article?.name }}</h3>
                    <p class="text-sm text-gray-600 mt-1">📏 Sélectionnez une variante (obligatoire)</p>
                </div>
                <button @click="emit('close')" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- No Variants Available -->
                <div v-if="!article?.variants || article.variants.length === 0" class="text-center py-12">
                    <div class="text-6xl mb-4">📭</div>
                    <p class="text-xl font-bold text-gray-900 mb-2">Aucune variante disponible</p>
                    <p class="text-gray-600">Cet article n'a pas de variantes configurées</p>
                </div>

                <!-- Variants Available -->
                <div v-else class="space-y-4">
                    <p class="text-sm text-gray-600 mb-4">
                        Choisissez une variante parmi les options ci-dessous :
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
                                    @change="selectedVariantId = variant.id"
                                >
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-base">{{ variant.name }}</p>
                                <p v-if="Number(variant.price_impact) > 0" class="text-sm font-bold text-green-600 mt-1">
                                    +{{ formatCurrency(variant.price_impact) }}
                                </p>
                            </div>

                            <div v-if="!variant.is_active" class="flex-shrink-0">
                                <span class="px-2 py-1 text-xs font-medium bg-gray-200 text-gray-700 rounded-full">
                                    Inactif
                                </span>
                            </div>
                        </label>
                    </div>

                    <!-- Price Summary -->
                    <div class="bg-gradient-to-r from-primary-50 to-blue-50 rounded-2xl p-4 border border-primary-100 mt-6">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700 font-semibold">💰 Impact sur le prix</span>
                            <span class="text-2xl font-bold text-primary-600">
                                {{ variantPriceImpact > 0 ? '+' : '' }}{{ formatCurrency(variantPriceImpact) }}
                            </span>
                        </div>
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
                    :disabled="!selectedVariantId"
                    class="flex-1 py-3 bg-primary-500 text-gray-900 font-bold rounded-xl hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-lg"
                >
                    ✓ Continuer
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useSettingsStore } from '../../stores/settings'

const props = defineProps({
    article: {
        type: Object,
        required: true
    },
    modelValue: {
        type: [String, Number],
        default: null
    }
})

const emit = defineEmits(['close', 'confirm', 'update:modelValue'])

const settingsStore = useSettingsStore()

const selectedVariantId = ref(props.modelValue)

const activeVariants = computed(() => {
    if (!props.article?.variants) return []
    return props.article.variants.filter(v => v.is_active !== false)
})

const variantPriceImpact = computed(() => {
    if (!selectedVariantId.value) return 0
    const variant = props.article?.variants?.find(v => v.id === selectedVariantId.value)
    return variant ? Number(variant.price_impact) || 0 : 0
})

const formatCurrency = (amount) => {
    return settingsStore.formatCurrency(amount)
}

function handleConfirm() {
    if (!selectedVariantId.value) {
        alert('Veuillez sélectionner une variante')
        return
    }
    emit('confirm', selectedVariantId.value)
}
</script>
