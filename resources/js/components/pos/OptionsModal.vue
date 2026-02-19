<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="emit('close')"></div>

            <div class="relative bg-white rounded-2xl shadow-xl max-w-3xl w-full mx-auto z-10">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ article?.name }}</h3>
                        <p class="text-sm text-gray-500">Sélectionnez les variantes souhaitées</p>
                    </div>
                    <button @click="emit('close')" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                    <div v-if="selectableOptions.length === 0" class="text-sm text-gray-500">
                        Aucune option disponible pour cet article.
                    </div>

                    <div v-for="option in selectableOptions" :key="option.id" class="border border-gray-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ option.name }}</h4>
                                <p class="text-xs text-gray-500">
                                    {{ option.type === 'fixed' ? 'Choix unique' : 'Choix multiple' }}
                                    <span v-if="option.is_required" class="ml-2 text-orange-600">Obligatoire</span>
                                </p>
                            </div>
                        </div>

                        <div v-if="activeVariants(option).length === 0" class="text-sm text-gray-400">
                            Aucune variante active
                        </div>

                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label
                                v-for="variant in activeVariants(option)"
                                :key="variant.id"
                                class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-colors"
                                :class="isVariantSelected(option, variant) ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <input
                                    v-if="option.type === 'fixed'"
                                    type="radio"
                                    :name="`option-${option.id}`"
                                    :value="variant.id"
                                    class="w-4 h-4 text-primary-600 border-gray-300"
                                    :checked="isVariantSelected(option, variant)"
                                    @change="selectSingle(option, variant.id)"
                                >
                                <input
                                    v-else
                                    type="checkbox"
                                    class="w-4 h-4 text-primary-600 border-gray-300 rounded"
                                    :checked="isVariantSelected(option, variant)"
                                    @change="toggleMultiple(option, variant.id)"
                                >
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ variant.name }}</p>
                                    <p v-if="Number(variant.price_impact) !== 0" class="text-xs text-gray-500">
                                        {{ formatPriceImpact(variant.price_impact) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span v-if="variant.color" class="w-4 h-4 rounded border border-gray-300" :style="{ backgroundColor: variant.color }"></span>
                                    <img v-if="variant.image" :src="variant.image" alt="" class="w-10 h-10 rounded object-cover border border-gray-200">
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm text-gray-600 pt-2 border-t border-gray-200">
                        <span>Total options</span>
                        <span class="font-semibold text-gray-900">
                            {{ formatPriceImpact(optionsPrice) }}
                        </span>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex gap-3">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="flex-1 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        :disabled="!canConfirm"
                        @click="handleConfirm"
                        class="flex-1 py-3 bg-primary-500 text-gray-900 font-bold rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Ajouter au ticket
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
