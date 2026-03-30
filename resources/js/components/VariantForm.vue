<template>
    <div class="space-y-6">
        <!-- Variant Name -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de la variante *</label>
            <input
                v-model="formData.name"
                type="text"
                placeholder="Exemple: Petit, Moyen, Grand, Rouge, Bleu..."
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <p class="text-xs text-gray-500 mt-1">Nom visible aux clients</p>
        </div>

        <!-- Variant Price -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Prix de la variante ({{ currencyCode }})</label>
            <input
                v-model.number="formData.price_impact"
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <p class="text-xs text-gray-500 mt-1">Prix final de cette variante. Elle remplace le prix de base du produit.</p>
        </div>

        <!-- Active Status -->
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
            <div>
                <label class="text-sm font-medium text-gray-700">Variante active</label>
                <p class="text-xs text-gray-500 mt-1">Disponible à la sélection</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input 
                    v-model="formData.is_active"
                    type="checkbox"
                    class="sr-only peer"
                >
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
            </label>
        </div>

        <!-- Sort Order (hidden in most cases, but available if needed) -->
        <div v-if="showAdvanced" class="pt-2 border-t border-gray-200">
            <label class="block text-sm font-medium text-gray-700 mb-2">Ordre d'affichage</label>
            <input
                v-model.number="formData.sort_order"
                type="number"
                min="0"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <p class="text-xs text-gray-500 mt-1">0 = premier, 1 = deuxième, etc.</p>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { useSettingsStore } from '../../stores/settings'

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
        validator: (obj) => {
            return 'name' in obj && 'price_impact' in obj && 'is_active' in obj && 'sort_order' in obj
        }
    },
    showAdvanced: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:modelValue'])

const settingsStore = useSettingsStore()

const currencyCode = computed(() => settingsStore.currencyCode || 'USD')

// Create local copy of formData
const formData = reactive({...props.modelValue})

// Watch for updates and emit them
import { watch } from 'vue'
watch(formData, (newVal) => {
    emit('update:modelValue', { ...newVal })
}, { deep: true })

// Watch for prop changes to update local data
watch(() => props.modelValue, (newVal) => {
    Object.assign(formData, newVal)
}, { deep: true })
</script>
