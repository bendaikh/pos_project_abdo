import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { articlesApi, categoriesApi } from '../api'
import { useOfflineStore } from './offline'

export const useArticlesStore = defineStore('articles', () => {
    const articles = ref([])
    const categories = ref([])
    const selectedCategoryId = ref(null)
    const searchQuery = ref('')
    const loading = ref(false)

    // Computed
    const filteredArticles = computed(() => {
        let result = articles.value

        // Filter by category
        if (selectedCategoryId.value) {
            result = result.filter(a => a.category_id === selectedCategoryId.value)
        }

        // Filter by search
        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase()
            result = result.filter(a => 
                a.name.toLowerCase().includes(query) ||
                a.sku?.toLowerCase().includes(query)
            )
        }

        return result
    })

    const favoriteArticles = computed(() => {
        return articles.value.filter(a => a.is_favorite)
    })

    // Actions
    async function fetchArticles(params = {}) {
        loading.value = true
        const offlineStore = useOfflineStore()
        const isOfflineGuestMode = localStorage.getItem('offline_guest_mode') === 'true'
        
        // Force offline loading if in guest mode or offline
        if (!offlineStore.isOnline || isOfflineGuestMode) {
            console.log('Guest mode or Offline: Loading articles from cache...')
            articles.value = await offlineStore.getCachedArticles()
            loading.value = false
            return
        }
        
        try {
            const response = await articlesApi.list({ active: true, in_stock: true, ...params })
            articles.value = Array.isArray(response.data) ? response.data : response.data.data || []
            
            // Cache for offline use
            if (offlineStore.isOnline) {
                await offlineStore.cacheArticles(articles.value)
            }
        } catch (error) {
            console.error('Failed to fetch articles:', error)
            
            // Try to load from cache if offline
            if (!offlineStore.isOnline || isOfflineGuestMode) {
                console.log('Loading articles from cache after error...')
                articles.value = await offlineStore.getCachedArticles()
            }
        } finally {
            loading.value = false
        }
    }

    async function fetchCategories() {
        const offlineStore = useOfflineStore()
        const isOfflineGuestMode = localStorage.getItem('offline_guest_mode') === 'true'
        
        // Force offline loading if in guest mode or offline
        if (!offlineStore.isOnline || isOfflineGuestMode) {
            console.log('Guest mode or Offline: Loading categories from cache...')
            categories.value = await offlineStore.getCachedCategories()
            return
        }
        
        try {
            const response = await categoriesApi.list({ active: true, with_count: true })
            categories.value = response.data
            
            // Cache for offline use
            if (offlineStore.isOnline) {
                await offlineStore.cacheCategories(categories.value)
            }
        } catch (error) {
            console.error('Failed to fetch categories:', error)
            
            // Try to load from cache if offline
            if (!offlineStore.isOnline || isOfflineGuestMode) {
                console.log('Loading categories from cache after error...')
                categories.value = await offlineStore.getCachedCategories()
            }
        }
    }

    function setSelectedCategory(categoryId) {
        selectedCategoryId.value = categoryId
    }

    function setSearchQuery(query) {
        searchQuery.value = query
    }

    async function refresh() {
        await Promise.all([fetchArticles(), fetchCategories()])
    }

    return {
        articles,
        categories,
        selectedCategoryId,
        searchQuery,
        loading,
        filteredArticles,
        favoriteArticles,
        fetchArticles,
        fetchCategories,
        setSelectedCategory,
        setSearchQuery,
        refresh
    }
})
