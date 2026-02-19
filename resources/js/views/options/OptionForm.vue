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
            <!-- Reusable Option Form Content -->
            <OptionFormContent 
                :form="form"
                :showPriceField="true"
                :showSettings="true"
                :currencyCode="settingsStore.currencyCode"
            />

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
import { XMarkIcon } from '@heroicons/vue/24/outline'
import OptionFormContent from '../../components/forms/OptionFormContent.vue'

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
