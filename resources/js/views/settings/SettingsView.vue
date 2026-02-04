<template>
    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Paramètres</h1>
            <p class="text-gray-500">Configurez les paramètres de votre point de vente</p>
        </div>

        <!-- General Settings -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations générales</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du point de vente</label>
                    <input v-model="settings.store_name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input v-model="settings.store_address" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input v-model="settings.store_phone" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                        <input v-model="settings.store_country" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Currency Settings -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Devise</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Code de devise</label>
                    <input v-model="settings.currency_code" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="DHS">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Symbole</label>
                    <input v-model="settings.currency_symbol" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="DHS">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position du symbole</label>
                    <select v-model="settings.currency_position" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="before">Avant le montant</option>
                        <option value="after">Après le montant</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tax Settings -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Taxes</h2>
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <input v-model="settings.tax_enabled" type="checkbox" id="tax_enabled" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                    <label for="tax_enabled" class="text-sm font-medium text-gray-700">Activer les taxes</label>
                </div>
                <div v-if="settings.tax_enabled" class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom de la taxe</label>
                        <input v-model="settings.tax_name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="TVA">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Taux (%)</label>
                        <input v-model.number="settings.tax_rate" type="number" min="0" max="100" step="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipt Settings -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Reçu</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">En-tête du reçu</label>
                    <input v-model="settings.receipt_header" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pied de page du reçu</label>
                    <input v-model="settings.receipt_footer" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="flex items-center space-x-3">
                    <input v-model="settings.receipt_show_logo" type="checkbox" id="receipt_show_logo" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                    <label for="receipt_show_logo" class="text-sm font-medium text-gray-700">Afficher le logo sur le reçu</label>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button 
                @click="saveSettings"
                :disabled="saving"
                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
            >
                {{ saving ? 'Enregistrement...' : 'Enregistrer les modifications' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { settingsApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'

const settingsStore = useSettingsStore()
const saving = ref(false)

const settings = reactive({
    store_name: '',
    store_address: '',
    store_phone: '',
    store_country: '',
    currency_code: 'DHS',
    currency_symbol: 'DHS',
    currency_position: 'after',
    tax_enabled: true,
    tax_name: 'TVA',
    tax_rate: 20,
    receipt_header: '',
    receipt_footer: '',
    receipt_show_logo: true,
})

async function loadSettings() {
    try {
        const response = await settingsApi.all()
        const data = response.data
        
        // Flatten the grouped settings
        Object.entries(data).forEach(([group, values]) => {
            Object.entries(values).forEach(([key, value]) => {
                if (key in settings) {
                    settings[key] = value
                }
            })
        })
    } catch (error) {
        console.error('Failed to load settings:', error)
    }
}

async function saveSettings() {
    saving.value = true
    try {
        const settingsArray = Object.entries(settings).map(([key, value]) => {
            let type = 'string'
            let group = 'general'
            
            if (typeof value === 'boolean') type = 'boolean'
            else if (typeof value === 'number') type = 'number'
            
            if (key.startsWith('currency_')) group = 'currency'
            else if (key.startsWith('tax_')) group = 'tax'
            else if (key.startsWith('receipt_')) group = 'receipt'
            else if (key.startsWith('store_')) group = 'general'
            
            return { key, value, type, group }
        })
        
        await settingsApi.update(settingsArray)
        settingsStore.loaded = false
        await settingsStore.fetchSettings()
        alert('Paramètres enregistrés avec succès!')
    } catch (error) {
        console.error('Failed to save settings:', error)
        alert('Erreur lors de l\'enregistrement')
    } finally {
        saving.value = false
    }
}

onMounted(loadSettings)
</script>
