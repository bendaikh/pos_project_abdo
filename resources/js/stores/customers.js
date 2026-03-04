import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { customersApi } from '../api'

export const useCustomersStore = defineStore('customers', () => {
    const customers = ref([])
    const loading = ref(false)
    const CUSTOMERS_STORAGE_KEY = 'pos_customers'

    // Load customers from localStorage
    function loadCustomersFromStorage() {
        try {
            const stored = localStorage.getItem(CUSTOMERS_STORAGE_KEY)
            if (stored) {
                customers.value = JSON.parse(stored)
                return true
            }
        } catch (error) {
            console.error('Error loading customers from storage:', error)
        }
        return false
    }

    // Fetch customers from API
    async function fetchCustomers() {
        // Try localStorage first
        if (loadCustomersFromStorage()) {
            console.log(`Loaded ${customers.value.length} customers from localStorage`)
            return
        }

        if (loading.value) return
        if (customers.value.length > 0) return // Already loaded

        loading.value = true
        try {
            const response = await customersApi.list({ limit: 100 })
            const payload = response.data
            customers.value = Array.isArray(payload) ? payload : payload?.data || []
            // Save to localStorage for offline access
            localStorage.setItem(CUSTOMERS_STORAGE_KEY, JSON.stringify(customers.value))
            console.log(`Loaded ${customers.value.length} customers`)
        } catch (error) {
            console.error('Failed to load customers:', error)
            customers.value = []
        } finally {
            loading.value = false
        }
    }

    return {
        customers,
        loading,
        fetchCustomers,
        loadCustomersFromStorage
    }
})
