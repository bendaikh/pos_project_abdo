import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '../api'
import { putItem, getItem, STORES } from '../utils/indexeddb'
import { useOfflineStore } from './offline'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const token = ref(null)
    const loading = ref(false)
    const offlineGuestMode = ref(false)

    const isAuthenticated = computed(() => !!token.value || offlineGuestMode.value)
    const userName = computed(() => {
        if (offlineGuestMode.value) return 'Utilisateur Hors ligne'
        return user.value?.name || ''
    })
    const userRole = computed(() => {
        if (offlineGuestMode.value) return 'cashier'
        return user.value?.role || ''
    })
    const isSuperAdmin = computed(() => user.value?.role === 'superadmin')
    const isAdmin = computed(() => ['superadmin', 'admin'].includes(user.value?.role))

    async function initAuth() {
        const storedToken = localStorage.getItem('auth_token')
        const storedUser = localStorage.getItem('auth_user')
        const storedOfflineMode = localStorage.getItem('offline_guest_mode')
        
        // Check for offline guest mode FIRST - this takes priority
        if (storedOfflineMode === 'true') {
            offlineGuestMode.value = true
            token.value = storedToken || 'offline_guest_' + Date.now()
            user.value = storedUser ? JSON.parse(storedUser) : {
                id: 0,
                name: 'Utilisateur Hors ligne',
                email: 'offline@local',
                role: 'cashier'
            }
            console.log('Offline guest mode restored')
            return
        }
        
        if (storedToken && storedUser) {
            token.value = storedToken
            user.value = JSON.parse(storedUser)
            
            // Skip verification for offline tokens
            if (storedToken.startsWith('offline_')) {
                console.log('Offline token detected, skipping verification')
                return
            }
            
            const offlineStore = useOfflineStore()
            
            // Verify token is still valid (only if online)
            if (offlineStore.isOnline) {
                try {
                    const response = await authApi.user()
                    user.value = response.data
                    localStorage.setItem('auth_user', JSON.stringify(response.data))
                } catch (error) {
                    // If verification fails and we're online, logout
                    logout()
                }
            } else {
                // If offline, keep using cached credentials
                console.log('Offline mode: Using cached credentials')
            }
        }
    }

    async function login(credentials) {
        loading.value = true
        const offlineStore = useOfflineStore()
        
        try {
            // Try online login first
            const response = await authApi.login(credentials)
            token.value = response.data.token
            user.value = response.data.user
            
            localStorage.setItem('auth_token', response.data.token)
            localStorage.setItem('auth_user', JSON.stringify(response.data.user))
            
            // Cache credentials for offline login (hash password for security)
            await cacheCredentials(credentials.email, credentials.password, response.data.user)
            
            return { success: true }
        } catch (error) {
            // If offline or connection failed, try offline login
            if (!offlineStore.isOnline || error.message === 'Network Error') {
                console.log('Online login failed, trying offline login...')
                return await offlineLogin(credentials)
            }
            
            return { 
                success: false, 
                message: error.response?.data?.message || 'Erreur de connexion'
            }
        } finally {
            loading.value = false
        }
    }
    
    // Cache credentials for offline login
    async function cacheCredentials(email, password, userData) {
        try {
            // Simple hash for password (in production, use better hashing)
            const hashedPassword = btoa(password) // Base64 encode
            
            await putItem(STORES.SETTINGS, {
                key: 'cached_credentials',
                value: {
                    email,
                    password: hashedPassword,
                    user: userData,
                    cached_at: new Date().toISOString()
                }
            })
            console.log('Credentials cached for offline login')
        } catch (error) {
            console.error('Error caching credentials:', error)
        }
    }
    
    // Offline login using cached credentials
    async function offlineLogin(credentials) {
        try {
            const cached = await getItem(STORES.SETTINGS, 'cached_credentials')
            
            if (!cached || !cached.value) {
                return {
                    success: false,
                    message: 'Aucune connexion hors ligne disponible. Connectez-vous en ligne au moins une fois.'
                }
            }
            
            // Verify credentials match
            const hashedPassword = btoa(credentials.password)
            
            if (cached.value.email === credentials.email && cached.value.password === hashedPassword) {
                // Login successful with cached credentials
                token.value = 'offline_token_' + Date.now()
                user.value = cached.value.user
                
                localStorage.setItem('auth_token', token.value)
                localStorage.setItem('auth_user', JSON.stringify(user.value))
                
                console.log('Offline login successful')
                return { 
                    success: true, 
                    offline: true,
                    message: 'Connexion hors ligne réussie'
                }
            } else {
                return {
                    success: false,
                    message: 'Email ou mot de passe incorrect'
                }
            }
        } catch (error) {
            console.error('Offline login error:', error)
            return {
                success: false,
                message: 'Erreur lors de la connexion hors ligne'
            }
        }
    }

    function setOfflineGuestMode() {
        offlineGuestMode.value = true
        token.value = 'offline_guest_' + Date.now()
        user.value = {
            id: 0,
            name: 'Utilisateur Hors ligne',
            email: 'offline@local',
            role: 'cashier'
        }
        localStorage.setItem('offline_guest_mode', 'true')
        console.log('Offline guest mode activated')
    }
    
    function clearOfflineGuestMode() {
        offlineGuestMode.value = false
        localStorage.removeItem('offline_guest_mode')
    }

    async function logout() {
        const offlineStore = useOfflineStore()
        
        try {
            // Only call API if online and not in guest mode
            if (offlineStore.isOnline && !offlineGuestMode.value) {
                await authApi.logout()
            }
        } catch (error) {
            // Ignore logout errors
        } finally {
            token.value = null
            user.value = null
            offlineGuestMode.value = false
            localStorage.removeItem('auth_token')
            localStorage.removeItem('auth_user')
            localStorage.removeItem('offline_guest_mode')
        }
    }

    return {
        user,
        token,
        loading,
        offlineGuestMode,
        isAuthenticated,
        userName,
        userRole,
        isSuperAdmin,
        isAdmin,
        initAuth,
        login,
        logout,
        setOfflineGuestMode,
        clearOfflineGuestMode
    }
})
