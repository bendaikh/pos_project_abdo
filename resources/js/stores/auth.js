import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '../api'
import { putItem, getItem, STORES } from '../utils/indexeddb'
import { useOfflineStore } from './offline'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const token = ref(null)
    const currentStore = ref(null)
    const needsStoreSetup = ref(false)
    const loading = ref(false)
    const offlineGuestMode = ref(false)
    const initialized = ref(false)
    let initPromise = null

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
    const isOwner = computed(() => user.value?.role === 'owner' || !!user.value?.owned_stores?.length)
    const isAdmin = computed(() => ['superadmin', 'owner', 'admin'].includes(user.value?.role))
    const canManageUsers = computed(() => ['superadmin', 'owner', 'admin'].includes(user.value?.role))
    const canCreateStore = computed(() => !!user.value?.can_create_store || isSuperAdmin.value || user.value?.role === 'owner')

    function applyAuthPayload(payload) {
        const nextUser = payload?.user || payload
        user.value = nextUser
        currentStore.value = payload?.current_store || nextUser?.default_store || null
        needsStoreSetup.value = !!payload?.needs_store_setup

        if (currentStore.value?.id) {
            localStorage.setItem('current_store_id', String(currentStore.value.id))
        }

        localStorage.setItem('auth_user', JSON.stringify(nextUser))
    }

    function setCurrentStore(store) {
        currentStore.value = store
        if (store?.id) {
            localStorage.setItem('current_store_id', String(store.id))
            if (user.value) {
                user.value = { ...user.value, default_store_id: store.id }
                localStorage.setItem('auth_user', JSON.stringify(user.value))
            }
        }
    }

    async function initAuth() {
        if (initialized.value) return
        if (initPromise) return initPromise

        initPromise = (async () => {
            const storedToken = localStorage.getItem('auth_token')
            const storedUser = localStorage.getItem('auth_user')
            const storedOfflineMode = localStorage.getItem('offline_guest_mode')
            const storedStoreId = localStorage.getItem('current_store_id')

            if (storedOfflineMode === 'true') {
                offlineGuestMode.value = true
                token.value = storedToken || 'offline_guest_' + Date.now()
                user.value = storedUser ? JSON.parse(storedUser) : {
                    id: 0,
                    name: 'Utilisateur Hors ligne',
                    email: 'offline@local',
                    role: 'cashier'
                }
                return
            }

            if (storedToken && storedUser) {
                token.value = storedToken
                user.value = JSON.parse(storedUser)
                if (storedStoreId) {
                    currentStore.value = { id: Number(storedStoreId) }
                }

                if (storedToken.startsWith('offline_')) {
                    return
                }

                const offlineStore = useOfflineStore()

                if (offlineStore.isOnline) {
                    try {
                        const response = await authApi.user()
                        applyAuthPayload(response.data)
                    } catch (error) {
                        logout()
                    }
                }
            }
        })()

        try {
            await initPromise
        } finally {
            initialized.value = true
            initPromise = null
        }
    }

    async function login(credentials) {
        loading.value = true
        const offlineStore = useOfflineStore()

        try {
            const response = await authApi.login(credentials)
            token.value = response.data.token
            localStorage.setItem('auth_token', response.data.token)
            applyAuthPayload(response.data)

            await cacheCredentials(credentials.email, credentials.password, response.data.user)

            return {
                success: true,
                needs_store_setup: needsStoreSetup.value,
            }
        } catch (error) {
            if (!offlineStore.isOnline || error.message === 'Network Error') {
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

    async function cacheCredentials(email, password, userData) {
        try {
            const hashedPassword = btoa(password)

            await putItem(STORES.SETTINGS, {
                key: 'cached_credentials',
                value: {
                    email,
                    password: hashedPassword,
                    user: userData,
                    cached_at: new Date().toISOString()
                }
            })
        } catch (error) {
            console.error('Error caching credentials:', error)
        }
    }

    async function offlineLogin(credentials) {
        try {
            const cached = await getItem(STORES.SETTINGS, 'cached_credentials')

            if (!cached || !cached.value) {
                return {
                    success: false,
                    message: 'Aucune connexion hors ligne disponible. Connectez-vous en ligne au moins une fois.'
                }
            }

            const hashedPassword = btoa(credentials.password)

            if (cached.value.email === credentials.email && cached.value.password === hashedPassword) {
                token.value = 'offline_token_' + Date.now()
                user.value = cached.value.user

                localStorage.setItem('auth_token', token.value)
                localStorage.setItem('auth_user', JSON.stringify(user.value))

                return {
                    success: true,
                    offline: true,
                    message: 'Connexion hors ligne réussie'
                }
            }

            return {
                success: false,
                message: 'Email ou mot de passe incorrect'
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
    }

    function clearOfflineGuestMode() {
        offlineGuestMode.value = false
        localStorage.removeItem('offline_guest_mode')
    }

    async function logout() {
        const offlineStore = useOfflineStore()

        try {
            if (offlineStore.isOnline && !offlineGuestMode.value) {
                await authApi.logout()
            }
        } catch (error) {
            // Ignore logout errors
        } finally {
            token.value = null
            user.value = null
            currentStore.value = null
            needsStoreSetup.value = false
            offlineGuestMode.value = false
            localStorage.removeItem('auth_token')
            localStorage.removeItem('auth_user')
            localStorage.removeItem('current_store_id')
            localStorage.removeItem('offline_guest_mode')
        }
    }

    return {
        user,
        token,
        currentStore,
        needsStoreSetup,
        loading,
        offlineGuestMode,
        isAuthenticated,
        userName,
        userRole,
        isSuperAdmin,
        isOwner,
        isAdmin,
        canManageUsers,
        canCreateStore,
        initAuth,
        login,
        logout,
        setCurrentStore,
        applyAuthPayload,
        setOfflineGuestMode,
        clearOfflineGuestMode,
        initialized
    }
})
