import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { customersApi } from '../api'

export const useCustomersStore = defineStore('customers', () => {
    const customers = ref([])
    const loading = ref(false)

    // Fetch customers from API
    async function fetchCustomers() {
        if (loading.value) return
        if (customers.value.length > 0) return // Already loaded

        loading.value = true
        try {
            const response = await customersApi.list({ limit: 100 })
            const payload = response.data
            customers.value = Array.isArray(payload) ? payload : payload?.data || []
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
        fetchCustomers
    }
})
