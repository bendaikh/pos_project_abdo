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
        
        // Try to load from cache first to ensure something is always visible
        const cachedArticles = await offlineStore.getCachedArticles()
        if (cachedArticles && cachedArticles.length > 0) {
            console.log('Pre-loading articles from cache...')
            articles.value = cachedArticles
        }

        // Only fetch from API if online AND NOT in guest mode
        if (offlineStore.isOnline && !isOfflineGuestMode) {
            try {
                const response = await articlesApi.list({ active: true, in_stock: true, ...params })
                const freshArticles = Array.isArray(response.data) ? response.data : response.data.data || []
                
                if (freshArticles.length > 0) {
                    articles.value = freshArticles
                    await offlineStore.cacheArticles(freshArticles)
                }
            } catch (error) {
                console.error('Failed to fetch articles from API:', error)
            }
        } else {
            console.log('Offline or Guest Mode: Using cached articles only')
        }
        
        loading.value = false
    }

    async function fetchCategories() {
        const offlineStore = useOfflineStore()
        const isOfflineGuestMode = localStorage.getItem('offline_guest_mode') === 'true'
        
        // Pre-load from cache
        const cachedCategories = await offlineStore.getCachedCategories()
        if (cachedCategories && cachedCategories.length > 0) {
            console.log('Pre-loading categories from cache...')
            categories.value = cachedCategories
        }

        if (offlineStore.isOnline && !isOfflineGuestMode) {
            try {
                const response = await categoriesApi.list({ active: true, with_count: true })
                if (response.data && response.data.length > 0) {
                    categories.value = response.data
                    await offlineStore.cacheCategories(categories.value)
                }
            } catch (error) {
                console.error('Failed to fetch categories from API:', error)
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
