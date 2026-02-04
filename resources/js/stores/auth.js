import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '../api'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const token = ref(null)
    const loading = ref(false)

    const isAuthenticated = computed(() => !!token.value)
    const userName = computed(() => user.value?.name || '')
    const userRole = computed(() => user.value?.role || '')
    const isSuperAdmin = computed(() => user.value?.role === 'superadmin')
    const isAdmin = computed(() => ['superadmin', 'admin'].includes(user.value?.role))

    async function initAuth() {
        const storedToken = localStorage.getItem('auth_token')
        const storedUser = localStorage.getItem('auth_user')
        
        if (storedToken && storedUser) {
            token.value = storedToken
            user.value = JSON.parse(storedUser)
            
            // Verify token is still valid
            try {
                const response = await authApi.user()
                user.value = response.data
                localStorage.setItem('auth_user', JSON.stringify(response.data))
            } catch (error) {
                logout()
            }
        }
    }

    async function login(credentials) {
        loading.value = true
        try {
            const response = await authApi.login(credentials)
            token.value = response.data.token
            user.value = response.data.user
            
            localStorage.setItem('auth_token', response.data.token)
            localStorage.setItem('auth_user', JSON.stringify(response.data.user))
            
            return { success: true }
        } catch (error) {
            return { 
                success: false, 
                message: error.response?.data?.message || 'Erreur de connexion'
            }
        } finally {
            loading.value = false
        }
    }

    async function logout() {
        try {
            await authApi.logout()
        } catch (error) {
            // Ignore logout errors
        } finally {
            token.value = null
            user.value = null
            localStorage.removeItem('auth_token')
            localStorage.removeItem('auth_user')
        }
    }

    return {
        user,
        token,
        loading,
        isAuthenticated,
        userName,
        userRole,
        isSuperAdmin,
        isAdmin,
        initAuth,
        login,
        logout
    }
})
