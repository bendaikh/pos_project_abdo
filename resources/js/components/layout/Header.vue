<template>
    <header class="h-16 bg-bg-main border-b border-white/10 flex items-center justify-between px-4 sm:px-6 gap-3">
        <!-- Left side -->
        <div class="flex items-center gap-3 min-w-0 flex-1">
            <button 
                @click="$emit('toggle-sidebar')"
                class="p-2 text-text-secondary hover:text-white hover:bg-white/5 rounded-lg flex-shrink-0"
            >
                <Bars3Icon class="w-5 h-5" />
            </button>
            <h1 class="text-lg font-semibold text-white hidden md:block truncate">{{ pageTitle }}</h1>

            <!-- PDV switcher — always visible when logged in -->
            <div v-if="showStoreSwitcher" class="relative ml-1 sm:ml-3" ref="storeSwitcherRef">
                <button
                    type="button"
                    @click="toggleStoreSwitcher"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary-500 text-gray-900 hover:bg-primary-400 transition shadow-sm max-w-[14rem] sm:max-w-[20rem]"
                    :disabled="switchingStore"
                >
                    <BuildingStorefrontIcon class="w-5 h-5 flex-shrink-0" />
                    <div class="min-w-0 text-left">
                        <p class="text-[10px] uppercase tracking-wide font-semibold opacity-80 leading-none mb-0.5">PDV actif</p>
                        <p class="text-sm font-bold truncate leading-tight">
                            {{ currentStoreLabel }}
                        </p>
                    </div>
                    <ChevronDownIcon class="w-4 h-4 flex-shrink-0" :class="{ 'rotate-180': showStoreMenu }" />
                </button>

                <transition
                    enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95"
                    enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100"
                    leave-to-class="transform opacity-0 scale-95"
                >
                    <div
                        v-if="showStoreMenu"
                        class="absolute left-0 mt-2 w-80 bg-bg-card rounded-xl shadow-xl border border-white/10 py-2 z-50"
                    >
                        <div class="px-3 pb-2 border-b border-white/10 mb-1">
                            <p class="text-xs text-text-secondary">Sélectionner le point de vente à afficher</p>
                        </div>

                        <div v-if="loadingStores" class="px-4 py-6 text-sm text-text-secondary text-center">
                            Chargement...
                        </div>
                        <div v-else-if="stores.length === 0" class="px-4 py-6 text-sm text-text-secondary text-center space-y-2">
                            <p>Aucun point de vente disponible</p>
                            <router-link
                                v-if="authStore.isSuperAdmin || authStore.canCreateStore"
                                to="/fiche-pdv"
                                class="text-primary-400 text-sm"
                                @click="showStoreMenu = false"
                            >
                                Créer un PDV
                            </router-link>
                        </div>
                        <button
                            v-for="store in stores"
                            :key="store.id"
                            type="button"
                            @click="selectStore(store)"
                            class="w-full px-4 py-2.5 text-left hover:bg-white/5 flex items-start gap-3 transition"
                            :class="isCurrentStore(store) ? 'bg-primary-500/10' : ''"
                        >
                            <span
                                class="mt-1 w-2 h-2 rounded-full flex-shrink-0"
                                :class="isCurrentStore(store) ? 'bg-primary-400' : 'bg-white/20'"
                            />
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-medium text-white truncate">{{ store.name }}</span>
                                <span class="block text-xs text-text-secondary truncate">
                                    {{ store.code || '—' }}
                                    <template v-if="store.city"> · {{ store.city }}</template>
                                    <template v-if="store.owner?.name"> · {{ store.owner.name }}</template>
                                </span>
                            </span>
                            <CheckIcon v-if="isCurrentStore(store)" class="w-4 h-4 text-primary-400 flex-shrink-0 mt-0.5" />
                        </button>

                        <div class="border-t border-white/10 mt-1 pt-1">
                            <router-link
                                to="/fiche-pdv"
                                class="block px-4 py-2 text-sm text-primary-400 hover:bg-white/5"
                                @click="showStoreMenu = false"
                            >
                                Gérer les points de vente
                            </router-link>
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-2 sm:space-x-4 flex-shrink-0">
            <!-- Dark mode toggle (placeholder) -->
            <button class="p-2 text-text-secondary hover:text-white hover:bg-white/5 rounded-lg hidden sm:inline-flex">
                <MoonIcon class="w-5 h-5" />
            </button>

            <!-- Notifications -->
            <button class="p-2 text-text-secondary hover:text-white hover:bg-white/5 rounded-lg relative">
                <BellIcon class="w-5 h-5" />
                <span v-if="notificationCount > 0" class="absolute top-1 right-1 w-2 h-2 bg-primary-500 rounded-full"></span>
            </button>

            <!-- User dropdown -->
            <div class="relative" ref="dropdownRef">
                <button 
                    @click="showDropdown = !showDropdown; showStoreMenu = false"
                    class="flex items-center space-x-3 p-2 hover:bg-white/5 rounded-lg"
                >
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium text-white">{{ authStore.userName }}</p>
                        <p class="text-xs" :class="authStore.offlineGuestMode ? 'text-primary-400' : 'text-text-secondary'">
                            {{ authStore.offlineGuestMode ? 'Mode Hors ligne' : roleLabel }}
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="authStore.offlineGuestMode ? 'bg-primary-600' : 'bg-primary-500'">
                        <span class="font-semibold text-sm text-white">{{ userInitials }}</span>
                    </div>
                </button>

                <transition
                    enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95"
                    enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100"
                    leave-to-class="transform opacity-0 scale-95"
                >
                    <div 
                        v-if="showDropdown"
                        class="absolute right-0 mt-2 w-48 bg-bg-card rounded-lg shadow-lg border border-white/10 py-1 z-50"
                    >
                        <router-link 
                            to="/settings" 
                            class="block px-4 py-2 text-sm text-text-secondary hover:bg-white/5 hover:text-white"
                            @click="showDropdown = false"
                        >
                            Paramètres
                        </router-link>
                        <hr class="my-1 border-white/10">
                        <button 
                            @click="handleLogout"
                            class="block w-full text-left px-4 py-2 text-sm text-primary-400 hover:bg-primary-500/10"
                        >
                            Déconnexion
                        </button>
                    </div>
                </transition>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { storesApi } from '../../api'
import {
    Bars3Icon,
    BellIcon,
    MoonIcon,
    BuildingStorefrontIcon,
    ChevronDownIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline'

defineEmits(['toggle-sidebar'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const showDropdown = ref(false)
const showStoreMenu = ref(false)
const dropdownRef = ref(null)
const storeSwitcherRef = ref(null)
const notificationCount = ref(0)
const stores = ref([])
const loadingStores = ref(false)
const switchingStore = ref(false)
const storesLoaded = ref(false)

const showStoreSwitcher = computed(() => {
    return authStore.isAuthenticated && !authStore.offlineGuestMode
})

const currentStoreLabel = computed(() => {
    return authStore.currentStore?.name || 'Choisir un PDV'
})

const pageTitle = computed(() => {
    const titles = {
        '/dashboard': 'Aperçu du Dashboard',
        '/pos': 'Menu POS',
        '/fiche-produit': 'Fiche produit',
        '/articles': 'Fiche produit',
        '/famille-produit': 'Famille Produit',
        '/categories': 'Famille Produit',
        '/unites-mesure': 'Unités Mesure',
        '/stock': 'Gestion de stock',
        '/customers': 'Gestion des clients',
        '/employees': 'Gestion des employés',
        '/reports': 'Rapports',
        '/settings': 'Paramètres',
        '/magasins': 'Fiche PDV',
        '/fiche-pdv': 'Fiche PDV',
        '/users': 'Utilisateurs',
        '/store-setup': 'Créer mon PDV',
    }
    return titles[route.path] || 'Dashboard'
})

const roleLabel = computed(() => {
    const roles = {
        superadmin: 'Super Admin',
        owner: 'Propriétaire PDV',
        admin: 'Administrateur',
        manager: 'Manager',
        cashier: 'Caissier'
    }
    return roles[authStore.userRole] || authStore.userRole
})

const userInitials = computed(() => {
    const name = authStore.userName || ''
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

function isCurrentStore(store) {
    return Number(authStore.currentStore?.id) === Number(store.id)
}

async function loadStores() {
    if (authStore.offlineGuestMode || !authStore.isAuthenticated) return

    loadingStores.value = true
    try {
        const { data } = await storesApi.list()
        stores.value = Array.isArray(data) ? data : []
        storesLoaded.value = true

        if (authStore.currentStore?.id && !authStore.currentStore?.name) {
            const match = stores.value.find(s => Number(s.id) === Number(authStore.currentStore.id))
            if (match) authStore.setCurrentStore(match)
        } else if (!authStore.currentStore?.id && stores.value.length === 1) {
            authStore.setCurrentStore(stores.value[0])
        }
    } catch (error) {
        console.error('Failed to load stores for switcher:', error)
        stores.value = []
    } finally {
        loadingStores.value = false
    }
}

async function toggleStoreSwitcher() {
    showDropdown.value = false
    showStoreMenu.value = !showStoreMenu.value
    if (showStoreMenu.value && !storesLoaded.value) {
        await loadStores()
    }
}

async function selectStore(store) {
    if (isCurrentStore(store) || switchingStore.value) {
        showStoreMenu.value = false
        return
    }

    switchingStore.value = true
    try {
        await storesApi.select(store.id)
        authStore.setCurrentStore(store)
        showStoreMenu.value = false
        window.location.reload()
    } catch (error) {
        alert(error.response?.data?.message || 'Impossible de changer de point de vente')
    } finally {
        switchingStore.value = false
    }
}

function handleClickOutside(event) {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        showDropdown.value = false
    }
    if (storeSwitcherRef.value && !storeSwitcherRef.value.contains(event.target)) {
        showStoreMenu.value = false
    }
}

async function handleLogout() {
    showDropdown.value = false
    showStoreMenu.value = false
    await authStore.logout()
    router.push('/login')
}

watch(
    () => [authStore.isAuthenticated, authStore.offlineGuestMode, authStore.token],
    ([ready, offline]) => {
        if (ready && !offline) loadStores()
    },
    { immediate: true }
)

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>
