<template>
    <!-- Basic Info Section -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">📋 Informations de base</h2>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'option *</label>
                <input 
                    v-model="form.name"
                    type="text"
                    required
                    placeholder="ex: Taille, Couleur, Sauce..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                <p class="text-xs text-gray-500 mt-1">Le titre de l'option (ex: "Taille", "Sauce", "Couleur")</p>
            </div>

            <div v-if="showTypeField">
                <label class="block text-sm font-medium text-gray-700 mb-1">Type d'option *</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all" :class="form.type === 'fixed' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                        <input 
                            v-model="form.type"
                            type="radio"
                            value="fixed"
                            class="mt-1 w-4 h-4 text-primary-600 border-gray-300"
                        >
                        <div class="ml-3">
                            <p class="font-medium text-gray-900">Option unique</p>
                            <p class="text-sm text-gray-500">L'utilisateur choisit une seule valeur (ex: Taille S, M ou L)</p>
                        </div>
                    </label>
                    <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all" :class="form.type === 'multiple' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                        <input 
                            v-model="form.type"
                            type="radio"
                            value="multiple"
                            class="mt-1 w-4 h-4 text-primary-600 border-gray-300"
                        >
                        <div class="ml-3">
                            <p class="font-medium text-gray-900">Choix multiples</p>
                            <p class="text-sm text-gray-500">L'utilisateur peut sélectionner plusieurs valeurs (ex: plusieurs sauces)</p>
                        </div>
                    </label>
                </div>
            </div>

            <div v-if="showPriceField">
                <label class="block text-sm font-medium text-gray-700 mb-1">Prix supplémentaire</label>
                <div class="relative">
                    <input 
                        v-model.number="form.extra_price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">{{ currencyCode }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Prix additionnel appliqué lorsque cette option est sélectionnée</p>
            </div>
        </div>
    </div>

    <!-- Values Section -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">🎨 Variantes</h2>
        
        <div class="space-y-3">
            <div v-for="(value, index) in form.values" :key="index" class="p-4 border border-gray-200 rounded-lg space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                    <input 
                        v-model="form.values[index]"
                        type="text"
                        :placeholder="`Variante ${index + 1}`"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prix impact ({{ currencyCode }})</label>
                    <input 
                        :value="(form.variantPrices && form.variantPrices[index]) || 0"
                        @input="updateVariantPrice(index, $event)"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                </div>
                <button 
                    type="button"
                    @click="removeVariant(index)"
                    class="w-full p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50 text-sm font-medium"
                >
                    <TrashIcon class="w-4 h-4 inline mr-2" /> Supprimer
                </button>
            </div>
        </div>
        <button 
            type="button"
            @click="addVariant"
            class="w-full px-4 py-2 border-2 border-dashed border-gray-300 text-gray-600 rounded-lg hover:border-gray-400 hover:bg-gray-50 flex items-center justify-center font-medium mt-4"
        >
            <PlusIcon class="w-5 h-5 mr-2" />
            Ajouter une variante
        </button>
    </div>

    <!-- Settings Section (Optional) -->
    <div v-if="showSettings" class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">⚙️ Paramètres</h2>
        
        <div class="space-y-3">
            <div class="flex items-center space-x-3">
                <input 
                    v-model="form.is_required"
                    type="checkbox"
                    id="is_required"
                    class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                >
                <label for="is_required" class="text-sm font-medium text-gray-700">
                    Option obligatoire (le client doit choisir une valeur)
                </label>
            </div>
            <div class="flex items-center space-x-3">
                <input 
                    v-model="form.is_active"
                    type="checkbox"
                    id="is_active"
                    class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                >
                <label for="is_active" class="text-sm font-medium text-gray-700">
                    Option active
                </label>
            </div>
        </div>
    </div>
</template>

<script setup>
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    form: {
        type: Object,
        required: true
    },
    showPriceField: {
        type: Boolean,
        default: false
    },
    showSettings: {
        type: Boolean,
        default: false
    },
    showTypeField: {
        type: Boolean,
        default: true
    },
    currencyCode: {
        type: String,
        default: 'DZD'
    }
})

function updateVariantPrice(index, event) {
    if (!props.form.variantPrices) {
        props.form.variantPrices = props.form.values.map(() => 0)
    }
    props.form.variantPrices[index] = Number(event.target.value) || 0
}

function removeVariant(index) {
    props.form.values.splice(index, 1)
    if (props.form.variantPrices) {
        props.form.variantPrices.splice(index, 1)
    }
}

function addVariant() {
    props.form.values.push('')
    if (!props.form.variantPrices) {
        props.form.variantPrices = props.form.values.map(() => 0)
    } else {
        props.form.variantPrices.push(0)
    }
}
</script>
