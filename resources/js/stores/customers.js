import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { customersApi } from '../api'

export const useCustomersStore = defineStore('customers', () => {
    const customers = ref([])
    const loading = ref(false)
    const CUSTOMERS_STORAGE_KEY = 'pos_customers'

    function normalizeCustomer(customer) {
        if (!customer) return null
        const fullName = `${customer.nom || ''} ${customer.prenom || ''}`.trim()
        return {
            ...customer,
            id: customer.id,
            name: customer.name || fullName || customer.raison_sociale || 'Client',
            phone: customer.phone || customer.telephone || customer.mobile || '',
        }
    }

    function saveCustomersToStorage() {
        try {
            localStorage.setItem(CUSTOMERS_STORAGE_KEY, JSON.stringify(customers.value))
        } catch (error) {
            console.error('Error saving customers to storage:', error)
        }
    }

    // Load customers from localStorage
    function loadCustomersFromStorage() {
        try {
            const stored = localStorage.getItem(CUSTOMERS_STORAGE_KEY)
            if (stored) {
                const parsed = JSON.parse(stored)
                customers.value = Array.isArray(parsed) ? parsed.map(normalizeCustomer).filter(Boolean) : []
                return true
            }
        } catch (error) {
            console.error('Error loading customers from storage:', error)
        }
        return false
    }

    // Fetch customers from API
    async function fetchCustomers({ force = false } = {}) {
        if (loading.value) return
        if (!force && customers.value.length > 0) return

        loading.value = true
        try {
            const response = await customersApi.list({ paginate: false, active: true })
            const payload = response.data
            const rawCustomers = Array.isArray(payload) ? payload : payload?.data || []
            customers.value = rawCustomers.map(normalizeCustomer).filter(Boolean)
            saveCustomersToStorage()
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
        loadCustomersFromStorage,
        normalizeCustomer,
    }
})
