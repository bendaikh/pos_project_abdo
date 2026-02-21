<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="emit('close')"></div>

            <div class="relative bg-white rounded-2xl shadow-xl max-w-4xl w-full mx-auto z-10">
                <div class="sticky top-0 bg-gradient-to-r from-primary-50 to-cyan-50 px-6 py-5 border-b border-gray-200 flex items-center justify-between rounded-t-2xl">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ article?.name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">💡 Sélectionnez les options et variantes</p>
                    </div>
                    <button @click="emit('close')" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                    <div v-if="selectableOptions.length === 0" class="text-center py-12">
                        <div class="text-4xl mb-3">⚙️</div>
                        <p class="text-gray-600 font-medium">Aucune option disponible</p>
                        <p class="text-gray-400 text-sm">Cet article n'a pas d'options configurées</p>
                    </div>

                    <div v-for="option in selectableOptions" :key="option.id" class="bg-gray-50 rounded-2xl p-5 border border-gray-200">
                        <!-- Option Header -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-bold text-gray-900">{{ option.name }}</h4>
                                <span 
                                    class="px-3 py-1 text-xs font-semibold rounded-full"
                                    :class="option.type === 'fixed' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'"
                                >
                                    {{ option.type === 'fixed' ? 'Un choix' : 'Plusieurs' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">
                                <span v-if="option.is_required" class="text-orange-600 font-medium">✱ Obligatoire</span>
                                <span v-else class="text-gray-500">Optionnel</span>
                            </p>
                        </div>

                        <!-- Variants Grid -->
                        <div v-if="activeVariants(option).length === 0" class="text-sm text-gray-500 py-4">
                            Aucune variante disponible
                        </div>

                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <label
                                v-for="variant in activeVariants(option)"
                                :key="variant.id"
                                class="flex items-center gap-4 p-4 bg-white border-2 rounded-xl cursor-pointer transition-all hover:border-primary-400"
                                :class="isVariantSelected(option, variant) ? 'border-primary-500 shadow-md bg-primary-50' : 'border-gray-200'"
                            >
                                <div class="flex-shrink-0">
                                    <input
                                        v-if="option.type === 'fixed'"
                                        type="radio"
                                        :name="`option-${option.id}`"
                                        :value="variant.id"
                                        class="w-5 h-5 text-primary-600 border-gray-300 cursor-pointer"
                                        :checked="isVariantSelected(option, variant)"
                                        @change="selectSingle(option, variant.id)"
                                    >
                                    <input
                                        v-else
                                        type="checkbox"
                                        class="w-5 h-5 text-primary-600 border-gray-300 rounded cursor-pointer"
                                        :checked="isVariantSelected(option, variant)"
                                        @change="toggleMultiple(option, variant.id)"
                                    >
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 text-base">{{ variant.name }}</p>
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
                    </div>

                    <!-- Options Total -->
                    <div class="bg-gradient-to-r from-primary-50 to-cyan-50 rounded-2xl p-4 border border-primary-100 flex items-center justify-between">
                        <span class="text-gray-700 font-semibold">Coût des options</span>
                        <span class="text-2xl font-bold text-primary-600">
                            {{ formatPriceImpact(optionsPrice) }}
                        </span>
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
                        :disabled="!canConfirm"
                        @click="handleConfirm"
                        class="flex-1 py-3 bg-primary-500 text-gray-900 font-bold rounded-xl hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-lg"
                    >
                        ✓ Ajouter au ticket
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useSettingsStore } from '../../stores/settings'
import { XMarkIcon } from '@heroicons/vue/24/outline'

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

const settingsStore = useSettingsStore()
const currencyCode = computed(() => settingsStore.currencyCode)

const selections = ref({})

const selectableOptions = computed(() => {
    return (props.article?.options || []).filter((option) => option.is_active)
})

function buildSelections() {
    const initial = {}
    selectableOptions.value.forEach((option) => {
        initial[option.id] = option.type === 'multiple' ? [] : null
    })

    if (Array.isArray(props.initialSelections)) {
        props.initialSelections.forEach((selection) => {
            const optionId = selection.option_id
            const option = selectableOptions.value.find((opt) => opt.id === optionId)
            if (!option) return

            const availableIds = activeVariants(option).map((variant) => variant.id)
            const selectedIds = (selection.variants || [])
                .map((variant) => variant.id)
                .filter((id) => availableIds.includes(id))

            if (option.type === 'multiple') {
                initial[optionId] = selectedIds
            } else {
                initial[optionId] = selectedIds.length > 0 ? selectedIds[0] : null
            }
        })
    }

    selections.value = initial
}

watch(
    () => [props.article, props.initialSelections],
    buildSelections,
    { immediate: true, deep: true }
)

function activeVariants(option) {
    return (option.variants || []).filter((variant) => variant.is_active)
}

function isVariantSelected(option, variant) {
    const selected = selections.value[option.id]
    if (option.type === 'multiple') {
        return Array.isArray(selected) && selected.includes(variant.id)
    }
    return selected === variant.id
}

function selectSingle(option, variantId) {
    selections.value[option.id] = variantId
}

function toggleMultiple(option, variantId) {
    const selected = selections.value[option.id] || []
    if (selected.includes(variantId)) {
        selections.value[option.id] = selected.filter((id) => id !== variantId)
    } else {
        selections.value[option.id] = [...selected, variantId]
    }
}

const optionsPrice = computed(() => {
    let total = 0
    selectableOptions.value.forEach((option) => {
        const selectedIds = option.type === 'multiple'
            ? selections.value[option.id] || []
            : selections.value[option.id] ? [selections.value[option.id]] : []

        const variants = activeVariants(option).filter((variant) => selectedIds.includes(variant.id))
        variants.forEach((variant) => {
            total += Number(variant.price_impact || 0)
        })
    })
    return total
})

const canConfirm = computed(() => {
    return selectableOptions.value.every((option) => {
        if (!option.is_required) return true
        if (option.type === 'multiple') {
            return (selections.value[option.id] || []).length > 0
        }
        return !!selections.value[option.id]
    })
})

function buildSelectedOptions() {
    const payload = []
    selectableOptions.value.forEach((option) => {
        const selectedIds = option.type === 'multiple'
            ? selections.value[option.id] || []
            : selections.value[option.id] ? [selections.value[option.id]] : []

        if (selectedIds.length === 0) {
            return
        }

        const selectedVariants = activeVariants(option)
            .filter((variant) => selectedIds.includes(variant.id))
            .map((variant) => ({
                id: variant.id,
                name: variant.name,
                price_impact: Number(variant.price_impact || 0),
                color: variant.color || null,
                image: variant.image || null,
            }))
            .sort((a, b) => a.id - b.id)

        payload.push({
            option_id: option.id,
            option_name: option.name,
            type: option.type,
            variants: selectedVariants,
        })
    })

    return payload.sort((a, b) => a.option_id - b.option_id)
}

function formatPriceImpact(value) {
    const amount = Number(value || 0)
    const prefix = amount > 0 ? '+' : ''
    return `${prefix}${amount.toFixed(2)} ${currencyCode.value}`
}

function handleConfirm() {
    emit('confirm', {
        selectedOptions: buildSelectedOptions(),
        optionsPrice: Number(optionsPrice.value || 0),
    })
}
</script>
