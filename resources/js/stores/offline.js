import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { initDB, getAllItems, putItem, addItem, deleteItem, bulkPut, STORES } from '../utils/indexeddb'

export const useOfflineStore = defineStore('offline', () => {
    const isOnline = ref(navigator.onLine)
    const pendingSalesCount = ref(0)
    const isSyncing = ref(false)
    const lastSyncTime = ref(null)
    const syncErrors = ref([])

    // Initialize
    async function init() {
        // Set initial online status
        isOnline.value = navigator.onLine
        
        await initDB()
        await updatePendingSalesCount()
        setupOnlineListener()
        setupServiceWorker()
    }

    // Online/Offline detection
    function setupOnlineListener() {
        window.addEventListener('online', handleOnline)
        window.addEventListener('offline', handleOffline)
    }

    function handleOnline() {
        isOnline.value = true
        console.log('Connection restored - syncing pending data')
        syncPendingData()
    }

    function handleOffline() {
        isOnline.value = false
        console.log('Connection lost - offline mode activated')
    }

    // Service Worker setup
    async function setupServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/service-worker.js')
                console.log('Service Worker registered:', registration)

                // Listen for messages from service worker
                navigator.serviceWorker.addEventListener('message', (event) => {
                    if (event.data.type === 'SYNC_PENDING_TRANSACTIONS') {
                        syncPendingData()
                    }
                })

                // Handle updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            console.log('New service worker available')
                        }
                    })
                })
            } catch (error) {
                console.error('Service Worker registration failed:', error)
            }
        }
    }

    // Cache management
    async function cacheArticles(articles) {
        try {
            await bulkPut(STORES.ARTICLES, articles)
            console.log(`Cached ${articles.length} articles`)
        } catch (error) {
            console.error('Error caching articles:', error)
        }
    }

    async function cacheCategories(categories) {
        try {
            await bulkPut(STORES.CATEGORIES, categories)
            console.log(`Cached ${categories.length} categories`)
        } catch (error) {
            console.error('Error caching categories:', error)
        }
    }

    async function cacheSettings(settings) {
        try {
            for (const [key, value] of Object.entries(settings)) {
                await putItem(STORES.SETTINGS, { key, value })
            }
            console.log('Settings cached')
        } catch (error) {
            console.error('Error caching settings:', error)
        }
    }

    // Get cached data
    async function getCachedArticles() {
        try {
            return await getAllItems(STORES.ARTICLES)
        } catch (error) {
            console.error('Error getting cached articles:', error)
            return []
        }
    }

    async function getCachedCategories() {
        try {
            return await getAllItems(STORES.CATEGORIES)
        } catch (error) {
            console.error('Error getting cached categories:', error)
            return []
        }
    }

    // Pending sales management
    async function savePendingSale(saleData) {
        try {
            const pendingSale = {
                ...saleData,
                created_at: new Date().toISOString(),
                synced: false,
            }
            await addItem(STORES.PENDING_SALES, pendingSale)
            await updatePendingSalesCount()
            console.log('Sale saved to pending queue')
            return { success: true }
        } catch (error) {
            console.error('Error saving pending sale:', error)
            return { success: false, error }
        }
    }

    async function getPendingSales() {
        try {
            const sales = await getAllItems(STORES.PENDING_SALES)
            return sales.filter(sale => !sale.synced)
        } catch (error) {
            console.error('Error getting pending sales:', error)
            return []
        }
    }

    async function updatePendingSalesCount() {
        const sales = await getPendingSales()
        pendingSalesCount.value = sales.length
    }

    // Sync pending data
    async function syncPendingData() {
        if (!isOnline.value || isSyncing.value) {
            return
        }

        isSyncing.value = true
        syncErrors.value = []

        try {
            const pendingSales = await getPendingSales()
            
            if (pendingSales.length === 0) {
                console.log('No pending sales to sync')
                isSyncing.value = false
                return
            }

            console.log(`Syncing ${pendingSales.length} pending sales...`)

            for (const sale of pendingSales) {
                try {
                    // Send to API
                    const response = await fetch('/api/sales', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(sale),
                    })

                    if (response.ok) {
                        // Mark as synced
                        sale.synced = true
                        sale.synced_at = new Date().toISOString()
                        await putItem(STORES.PENDING_SALES, sale)
                        
                        // Delete after successful sync
                        await deleteItem(STORES.PENDING_SALES, sale.temp_id)
                    } else {
                        throw new Error(`Sync failed: ${response.statusText}`)
                    }
                } catch (error) {
                    console.error('Error syncing sale:', error)
                    syncErrors.value.push({
                        sale: sale.temp_id,
                        error: error.message,
                    })
                }
            }

            await updatePendingSalesCount()
            lastSyncTime.value = new Date().toISOString()
            
            console.log('Sync completed')
        } catch (error) {
            console.error('Sync error:', error)
        } finally {
            isSyncing.value = false
        }
    }

    // Force sync
    async function forceSync() {
        await syncPendingData()
    }

    // Clear all offline data
    async function clearOfflineData() {
        try {
            await clearStore(STORES.ARTICLES)
            await clearStore(STORES.CATEGORIES)
            await clearStore(STORES.PENDING_SALES)
            await clearStore(STORES.SETTINGS)
            console.log('Offline data cleared')
        } catch (error) {
            console.error('Error clearing offline data:', error)
        }
    }

    const hasConnection = computed(() => isOnline.value)
    const hasPendingSales = computed(() => pendingSalesCount.value > 0)
    const syncStatus = computed(() => {
        if (isSyncing.value) return 'syncing'
        if (syncErrors.value.length > 0) return 'error'
        if (hasPendingSales.value) return 'pending'
        return 'synced'
    })

    return {
        // State
        isOnline,
        pendingSalesCount,
        isSyncing,
        lastSyncTime,
        syncErrors,
        
        // Computed
        hasConnection,
        hasPendingSales,
        syncStatus,
        
        // Actions
        init,
        cacheArticles,
        cacheCategories,
        cacheSettings,
        getCachedArticles,
        getCachedCategories,
        savePendingSale,
        getPendingSales,
        syncPendingData,
        forceSync,
        clearOfflineData,
    }
})
