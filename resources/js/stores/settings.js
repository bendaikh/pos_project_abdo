import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { settingsApi } from '../api'
import { useOfflineStore } from './offline'

export const useSettingsStore = defineStore('settings', () => {
    const settings = ref({
        general: {
            store_name: 'GreenPOS',
            store_address: '',
            store_phone: '',
            store_country: 'Morocco'
        },
        currency: {
            currency_code: 'DHS',
            currency_symbol: 'DHS',
            currency_position: 'after'
        },
        tax: {
            tax_enabled: true,
            tax_rate: 20,
            tax_name: 'TVA'
        },
        receipt: {
            receipt_header: 'Merci pour votre achat!',
            receipt_footer: 'À bientôt!',
            receipt_show_logo: true
        }
    })
    const loading = ref(false)
    const loaded = ref(false)

    // Computed getters
    const storeName = computed(() => settings.value.general?.store_name || 'GreenPOS')
    const currencyCode = computed(() => settings.value.currency?.currency_code || 'DHS')
    const currencySymbol = computed(() => settings.value.currency?.currency_symbol || 'DHS')
    const currencyPosition = computed(() => settings.value.currency?.currency_position || 'after')
    const taxEnabled = computed(() => settings.value.tax?.tax_enabled ?? true)
    const taxRate = computed(() => settings.value.tax?.tax_rate || 0)
    const taxName = computed(() => settings.value.tax?.tax_name || 'TVA')

    // Format currency
    function formatCurrency(amount) {
        const formatted = Number(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
        if (currencyPosition.value === 'before') {
            return `${currencySymbol.value} ${formatted}`
        }
        return `${formatted} ${currencySymbol.value}`
    }

    // Fetch settings
    async function fetchSettings() {
        if (loaded.value) return
        
        loading.value = true
        const offlineStore = useOfflineStore()
        
        try {
            const response = await settingsApi.all()
            settings.value = response.data
            loaded.value = true
            
            // Cache settings for offline use
            if (offlineStore.isOnline) {
                await offlineStore.cacheSettings(settings.value)
            }
        } catch (error) {
            console.error('Failed to load settings:', error)
            
            // Try to load from cache if offline
            if (!offlineStore.isOnline) {
                console.log('Loading settings from cache...')
                // Settings are already set to defaults, so offline mode will work
            }
        } finally {
            loading.value = false
        }
    }

    // Update settings
    async function updateSettings(newSettings) {
        loading.value = true
        try {
            await settingsApi.update(newSettings)
            await fetchSettings()
            return { success: true }
        } catch (error) {
            return { 
                success: false, 
                message: error.response?.data?.message || 'Erreur lors de la mise à jour' 
            }
        } finally {
            loading.value = false
        }
    }

    return {
        settings,
        loading,
        loaded,
        storeName,
        currencyCode,
        currencySymbol,
        currencyPosition,
        taxEnabled,
        taxRate,
        taxName,
        formatCurrency,
        fetchSettings,
        updateSettings
    }
})
