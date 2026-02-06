<template>
    <div class="min-h-screen flex items-center justify-center bg-[#fafafa] py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo -->
            <div class="text-center">
                <div class="flex justify-center">
                    <div class="w-16 h-16 bg-gray-900 rounded-2xl flex items-center justify-center">
                        <svg class="w-10 h-10 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <h2 class="mt-6 text-3xl font-bold text-gray-900">GreenPOS</h2>
                <p class="mt-2 text-sm text-gray-600">Connectez-vous à votre compte</p>
            </div>

            <!-- Login Form -->
            <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
                <!-- Offline Notice -->
                <div v-if="!isOnline" class="rounded-md bg-orange-50 p-4 border border-orange-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-orange-800">Mode hors ligne</p>
                            <p class="text-xs text-orange-700 mt-1">Utilisez vos identifiants habituels pour vous connecter</p>
                        </div>
                    </div>
                </div>
                
                <!-- Success Message (offline login) -->
                <div v-if="offlineLoginSuccess" class="rounded-md bg-primary-50 p-4 border border-primary-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Connexion hors ligne réussie!</p>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ error }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input 
                            id="email" 
                            v-model="form.email"
                            type="email" 
                            required 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" 
                            placeholder="votre@email.com"
                        >
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                        <input 
                            id="password" 
                            v-model="form.password"
                            type="password" 
                            required 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" 
                            placeholder="••••••••"
                        >
                    </div>
                </div>

                <div>
                    <button 
                        type="submit" 
                        :disabled="loading"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-gray-900 bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ loading ? 'Connexion...' : 'Se connecter' }}
                    </button>
                </div>
            </form>

            <!-- Demo credentials -->
            <div class="mt-6 text-center space-y-2">
                <p class="text-xs text-gray-500">
                    Demo: superadmin@example.com / Admin@12345
                </p>
                <p v-if="!isOnline" class="text-xs text-orange-600 font-medium">
                    💡 Mode hors ligne: Connectez-vous une fois en ligne pour activer l'accès hors ligne
                </p>
            </div>

            <!-- Offline Quick Access -->
            <div v-if="!isOnline" class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-[#fafafa] text-gray-500">Ou</span>
                    </div>
                </div>
                
                <button
                    @click="continueWithoutLogin"
                    type="button"
                    class="mt-4 w-full py-3 px-4 border-2 border-orange-500 text-orange-600 font-medium rounded-lg hover:bg-orange-50 transition-colors flex items-center justify-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span>Accès rapide au POS (Hors ligne)</span>
                </button>
                <p class="mt-2 text-xs text-center" :class="hasCachedData ? 'text-gray-500' : 'text-orange-600'">
                    <template v-if="hasCachedData">
                        Continuer sans connexion pour traiter les ventes
                    </template>
                    <template v-else>
                        ⚠️ Aucun produit en cache. Connectez-vous en ligne une fois pour activer cette fonctionnalité.
                    </template>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useOfflineStore } from '../stores/offline'

const router = useRouter()
const authStore = useAuthStore()
const offlineStore = useOfflineStore()

const form = reactive({
    email: '',
    password: ''
})
const error = ref('')
const loading = ref(false)
const offlineLoginSuccess = ref(false)
const hasCachedData = ref(false)

const isOnline = computed(() => offlineStore.isOnline)

async function handleLogin() {
    error.value = ''
    offlineLoginSuccess.value = false
    loading.value = true

    const result = await authStore.login(form)
    
    loading.value = false

    if (result.success) {
        if (result.offline) {
            offlineLoginSuccess.value = true
            setTimeout(() => {
                router.push('/dashboard')
            }, 1000)
        } else {
            router.push('/dashboard')
        }
    } else {
        error.value = result.message
    }
}

async function continueWithoutLogin() {
    if (!hasCachedData.value) {
        // Show warning but still allow access
        if (!confirm('Aucun produit en cache. Le POS pourrait être vide. Continuer quand même?')) {
            return
        }
    }
    
    // Set offline guest mode
    authStore.setOfflineGuestMode()
    router.push('/pos')
}

async function checkCachedData() {
    try {
        const articles = await offlineStore.getCachedArticles()
        hasCachedData.value = articles && articles.length > 0
        console.log(`Cached articles found: ${articles?.length || 0}`)
    } catch (error) {
        console.error('Error checking cached data:', error)
        hasCachedData.value = false
    }
}

onMounted(async () => {
    // Initialize offline store if not already initialized
    if (!offlineStore.isOnline && !offlineStore.lastSyncTime) {
        await offlineStore.init()
    }
    
    // Check if we have cached data for offline access
    await checkCachedData()
})
</script>
