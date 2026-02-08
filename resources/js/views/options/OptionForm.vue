<template>
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Modifier l\'Option' : 'Nouvelle Option' }}</h1>
                <p class="text-gray-500">{{ isEdit ? 'Modifiez les informations de l\'option' : 'Ajoutez une nouvelle option pour vos articles' }}</p>
            </div>
            <router-link to="/options" class="text-gray-500 hover:text-gray-700">
                <XMarkIcon class="w-6 h-6" />
            </router-link>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations de base</h2>
                
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

                    <div>
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

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix supplémentaire</label>
                        <div class="relative">
                            <input 
                                v-model.number="form.extra_price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">{{ settingsStore.currencyCode }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Prix additionnel appliqué lorsque cette option est sélectionnée</p>
                    </div>
                </div>
            </div>

            <!-- Values -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Caractéristiques de l'option</h2>
                
                <div class="space-y-3">
                    <div v-for="(value, index) in form.values" :key="index" class="flex items-center space-x-2">
                        <input 
                            v-model="form.values[index]"
                            type="text"
                            :placeholder="`Valeur ${index + 1} (ex: S, M, L ou sauce haute, sauce bigui...)`"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        <button 
                            type="button"
                            @click="removeValue(index)"
                            v-if="form.values.length > 1"
                            class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50"
                        >
                            <TrashIcon class="w-5 h-5" />
                        </button>
                    </div>
                    <button 
                        type="button"
                        @click="addValue"
                        class="w-full px-4 py-2 border-2 border-dashed border-gray-300 text-gray-600 rounded-lg hover:border-gray-400 hover:bg-gray-50 flex items-center justify-center"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Ajouter une valeur
                    </button>
                </div>
            </div>

            <!-- Settings -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Paramètres</h2>
                
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

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <router-link to="/options" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Annuler
                </router-link>
                <button 
                    type="submit"
                    :disabled="saving || !isFormValid"
                    class="px-6 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50"
                >
                    {{ saving ? 'Enregistrement...' : (isEdit ? 'Mettre à jour' : 'Créer l\'option') }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { optionsApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { XMarkIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const settingsStore = useSettingsStore()

const isEdit = computed(() => !!route.params.id)
const saving = ref(false)

const form = reactive({
    name: '',
    type: 'fixed',
    values: [''],
    extra_price: 0,
    is_required: false,
    is_active: true,
})

const isFormValid = computed(() => {
    return form.name.trim() && 
           form.values.length > 0 && 
           form.values.every(v => v.trim())
})

function addValue() {
    form.values.push('')
}

function removeValue(index) {
    if (form.values.length > 1) {
        form.values.splice(index, 1)
    }
}

async function fetchOption() {
    if (!isEdit.value) return
    
    try {
        const response = await optionsApi.get(route.params.id)
        Object.assign(form, response.data)
    } catch (error) {
        console.error('Failed to fetch option:', error)
        router.push('/options')
    }
}

async function handleSubmit() {
    if (!isFormValid.value) return
    
    saving.value = true
    
    try {
        // Filter out empty values
        const data = {
            ...form,
            values: form.values.filter(v => v.trim())
        }
        
        if (isEdit.value) {
            await optionsApi.update(route.params.id, data)
        } else {
            await optionsApi.create(data)
        }
        router.push('/options')
    } catch (error) {
        console.error('Failed to save option:', error)
        alert('Erreur lors de l\'enregistrement: ' + (error.response?.data?.message || error.message))
    } finally {
        saving.value = false
    }
}

onMounted(fetchOption)
</script>
