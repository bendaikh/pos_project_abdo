<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Paramètres</h1>
            <p class="text-gray-500">Configurez les paramètres de votre point de vente</p>
        </div>

        <!-- Tabs Navigation -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex border-b border-gray-200 overflow-x-auto">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors"
                    :class="activeTab === tab.id ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                >
                    {{ tab.label }}
                </button>
            </div>

            <div class="p-6">
                <!-- Infos Générales -->
                <div v-show="activeTab === 'general'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <BuildingStorefrontIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Infos Générales
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du magasin *</label>
                            <input v-model="settings.store_name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom de votre magasin">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                            <input v-model="settings.store_city" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ville">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                            <input v-model="settings.store_address" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Adresse complète">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                            <input v-model="settings.store_country" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Pays">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                            <input v-model="settings.store_phone" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="+212 600 000 000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="settings.store_email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="contact@magasin.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ICE (Identifiant Commun de l'Entreprise)</label>
                            <input v-model="settings.store_ice" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="000000000000000">
                        </div>
                    </div>
                </div>

                <!-- Matériel -->
                <div v-show="activeTab === 'material'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <ComputerDesktopIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Matériel
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Imprimante</label>
                            <select v-model="settings.printer_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Sélectionner une imprimante</option>
                                <option value="thermal_80">Thermique 80mm</option>
                                <option value="thermal_58">Thermique 58mm</option>
                                <option value="a4">A4 Standard</option>
                                <option value="none">Aucune</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom/Port de l'imprimante</label>
                            <input v-model="settings.printer_name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="USB001, COM1, etc.">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Scanner</label>
                            <select v-model="settings.scanner_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Sélectionner un scanner</option>
                                <option value="usb">USB (Code-barres)</option>
                                <option value="bluetooth">Bluetooth</option>
                                <option value="camera">Caméra</option>
                                <option value="none">Aucun</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tiroir-caisse</label>
                            <select v-model="settings.cash_drawer" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Sélectionner</option>
                                <option value="connected">Connecté à l'imprimante</option>
                                <option value="usb">USB indépendant</option>
                                <option value="manual">Manuel</option>
                                <option value="none">Aucun</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Écran client</label>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" v-model="settings.customer_display" value="enabled" class="mr-2 text-primary-500">
                                    <span class="text-sm">Activé</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" v-model="settings.customer_display" value="disabled" class="mr-2 text-primary-500">
                                    <span class="text-sm">Désactivé</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Devise -->
                <div v-show="activeTab === 'currency'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <CurrencyDollarIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Devise
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pays de devise</label>
                            <select v-model="settings.currency_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="MA">Maroc</option>
                                <option value="FR">France</option>
                                <option value="US">États-Unis</option>
                                <option value="GB">Royaume-Uni</option>
                                <option value="AE">Émirats Arabes Unis</option>
                                <option value="SA">Arabie Saoudite</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Code de devise</label>
                            <input v-model="settings.currency_code" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="MAD, EUR, USD...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Symbole</label>
                            <input v-model="settings.currency_symbol" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="DH, €, $...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Position du symbole</label>
                            <select v-model="settings.currency_position" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="before">Avant le montant ($ 100)</option>
                                <option value="after">Après le montant (100 DH)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Format du Reçu -->
                <div v-show="activeTab === 'receipt'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <DocumentTextIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Format du Reçu
                    </h2>
                    
                    <!-- Logo -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3">Logo</h3>
                        <div class="flex items-center space-x-4">
                            <div class="w-24 h-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50">
                                <img v-if="settings.receipt_logo" :src="settings.receipt_logo" alt="Logo" class="max-w-full max-h-full object-contain">
                                <PhotoIcon v-else class="w-8 h-8 text-gray-400" />
                            </div>
                            <div class="flex-1">
                                <input type="file" ref="logoInput" @change="handleLogoUpload" accept="image/*" class="hidden">
                                <button @click="$refs.logoInput.click()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                                    Choisir un logo
                                </button>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG jusqu'à 2MB. Recommandé: 200x200px</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="settings.receipt_show_logo" class="mr-2 text-primary-500 rounded">
                                <span class="text-sm text-gray-700">Afficher le logo sur le reçu</span>
                            </label>
                        </div>
                    </div>

                    <!-- En-tête -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">En-tête</label>
                        <textarea v-model="settings.receipt_header" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Texte affiché en haut du reçu..."></textarea>
                    </div>

                    <!-- Pied de page -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pied de page</label>
                        <textarea v-model="settings.receipt_footer" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Merci pour votre visite!"></textarea>
                    </div>

                    <!-- Note -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                        <textarea v-model="settings.receipt_note" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Note additionnelle sur le reçu..."></textarea>
                    </div>

                    <!-- QR Code -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3">QR Code</h3>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="settings.receipt_show_qr" class="mr-2 text-primary-500 rounded">
                                <span class="text-sm text-gray-700">Afficher un QR code sur le reçu</span>
                            </label>
                            <div v-if="settings.receipt_show_qr">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contenu du QR code</label>
                                <input v-model="settings.receipt_qr_content" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="URL, numéro de transaction, etc.">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Taxes -->
                <div v-show="activeTab === 'taxes'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <ReceiptPercentIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Taxes
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <input v-model="settings.tax_enabled" type="checkbox" id="tax_enabled" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <label for="tax_enabled" class="text-sm font-medium text-gray-700">Activer les taxes</label>
                        </div>
                        <div v-if="settings.tax_enabled" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom de la taxe</label>
                                <input v-model="settings.tax_name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="TVA">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Taux de taxe (%)</label>
                                <input v-model.number="settings.tax_rate" type="number" min="0" max="100" step="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commissions -->
                <div v-show="activeTab === 'commissions'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <BanknotesIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Commissions
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <input v-model="settings.commission_enabled" type="checkbox" id="commission_enabled" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <label for="commission_enabled" class="text-sm font-medium text-gray-700">Activer les commissions</label>
                        </div>
                        <div v-if="settings.commission_enabled" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ID de Commission</label>
                                <input v-model="settings.commission_id" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="COM-001">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Montant / Pourcentage</label>
                                <div class="flex space-x-2">
                                    <input v-model.number="settings.commission_amount" type="number" min="0" step="0.01" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <select v-model="settings.commission_type" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="percentage">%</option>
                                        <option value="fixed">Fixe</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Abonnement -->
                <div v-show="activeTab === 'subscription'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <CreditCardIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Abonnement
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type d'Abonnement</label>
                            <select v-model="settings.subscription_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="free">Gratuit</option>
                                <option value="basic">Basic</option>
                                <option value="pro">Professionnel</option>
                                <option value="enterprise">Entreprise</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Montant</label>
                            <input v-model.number="settings.subscription_amount" type="number" min="0" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Durée</label>
                            <select v-model="settings.subscription_duration" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="monthly">Mensuel</option>
                                <option value="quarterly">Trimestriel</option>
                                <option value="yearly">Annuel</option>
                                <option value="lifetime">À vie</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date d'expiration</label>
                            <input v-model="settings.subscription_expiry" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <!-- Subscription Status Card -->
                    <div class="mt-4 p-4 rounded-lg" :class="subscriptionStatusClass">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium">Statut de l'abonnement</p>
                                <p class="text-sm opacity-80">{{ subscriptionStatusText }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-medium" :class="subscriptionBadgeClass">
                                {{ settings.subscription_type === 'free' ? 'Gratuit' : 'Actif' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end space-x-3">
            <button 
                @click="resetSettings"
                class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
            >
                Réinitialiser
            </button>
            <button 
                @click="saveSettings"
                :disabled="saving"
                class="px-6 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50"
            >
                {{ saving ? 'Enregistrement...' : 'Enregistrer les modifications' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { settingsApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import {
    BuildingStorefrontIcon,
    ComputerDesktopIcon,
    CurrencyDollarIcon,
    DocumentTextIcon,
    PhotoIcon,
    ReceiptPercentIcon,
    BanknotesIcon,
    CreditCardIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const saving = ref(false)
const activeTab = ref('general')

const tabs = [
    { id: 'general', label: 'Infos Générales' },
    { id: 'material', label: 'Matériel' },
    { id: 'currency', label: 'Devise' },
    { id: 'receipt', label: 'Format du Reçu' },
    { id: 'taxes', label: 'Taxes' },
    { id: 'commissions', label: 'Commissions' },
    { id: 'subscription', label: 'Abonnement' },
]

const settings = reactive({
    // Infos Générales
    store_name: '',
    store_city: '',
    store_address: '',
    store_country: 'Maroc',
    store_phone: '',
    store_email: '',
    store_ice: '',
    
    // Matériel
    printer_type: '',
    printer_name: '',
    scanner_type: '',
    cash_drawer: '',
    customer_display: 'disabled',
    
    // Devise
    currency_country: 'MA',
    currency_code: 'MAD',
    currency_symbol: 'DH',
    currency_position: 'after',
    
    // Format du Reçu
    receipt_logo: '',
    receipt_show_logo: true,
    receipt_header: '',
    receipt_footer: 'Merci pour votre visite!',
    receipt_note: '',
    receipt_show_qr: false,
    receipt_qr_content: '',
    
    // Taxes
    tax_enabled: true,
    tax_name: 'TVA',
    tax_rate: 20,
    
    // Commissions
    commission_enabled: false,
    commission_id: '',
    commission_amount: 0,
    commission_type: 'percentage',
    
    // Abonnement
    subscription_type: 'free',
    subscription_amount: 0,
    subscription_duration: 'monthly',
    subscription_expiry: '',
})

const subscriptionStatusClass = computed(() => {
    if (settings.subscription_type === 'free') return 'bg-gray-100 text-gray-800'
    return 'bg-green-100 text-green-800'
})

const subscriptionBadgeClass = computed(() => {
    if (settings.subscription_type === 'free') return 'bg-gray-200 text-gray-700'
    return 'bg-green-200 text-green-700'
})

const subscriptionStatusText = computed(() => {
    if (settings.subscription_type === 'free') return 'Vous utilisez la version gratuite'
    if (settings.subscription_expiry) {
        const expiry = new Date(settings.subscription_expiry)
        return `Expire le ${expiry.toLocaleDateString('fr-FR')}`
    }
    return 'Abonnement actif'
})

function handleLogoUpload(event) {
    const file = event.target.files[0]
    if (file) {
        const reader = new FileReader()
        reader.onload = (e) => {
            settings.receipt_logo = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

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

function resetSettings() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser les paramètres ?')) {
        loadSettings()
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
            else if (key.startsWith('printer_') || key.startsWith('scanner_') || key.startsWith('cash_') || key.startsWith('customer_')) group = 'material'
            else if (key.startsWith('commission_')) group = 'commission'
            else if (key.startsWith('subscription_')) group = 'subscription'
            
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
