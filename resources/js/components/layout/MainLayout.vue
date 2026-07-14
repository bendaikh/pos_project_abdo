<template>
    <div :class="rootLayoutClass">
        <!-- POS Overlay Sidebar -->
        <div
            v-if="isPosRoute && posSidebarOpen"
            class="fixed inset-0 z-40 bg-black/30"
            @click="uiStore.closePosSidebar"
        ></div>
        <Sidebar v-if="isPosRoute && posSidebarOpen" :collapsed="false" @toggle="uiStore.togglePosSidebar" />

        <!-- Sidebar Overlay (Mobile) -->
        <div
            v-if="isMobile && appSidebarOpen"
            class="fixed inset-0 z-40 bg-black/30"
            @click="closeSidebar"
        ></div>

        <!-- Sidebar -->
        <Sidebar
            v-if="shouldShowAppSidebar"
            :collapsed="sidebarCollapsed"
            :is-mobile="isMobile"
            :mobile-open="appSidebarOpen"
            @toggle="toggleSidebar"
            @close="closeSidebar"
        />
        
        <!-- Main Content -->
        <div
            class="flex-1 flex flex-col min-w-0 min-h-0 h-full"
            :class="layoutOffsetClass"
        >
            <!-- Barre multi-PDV (toujours visible, styles inline) -->
            <div
                v-if="!isPosRoute && authStore.isAuthenticated && !authStore.offlineGuestMode"
                style="z-index: 100; background: #1E2132; color: #FFFFFF; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; font-family: sans-serif; border-bottom: 2px solid #FB923C;"
            >
                <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                    <strong style="font-size: 13px; letter-spacing: 0.04em; color: #22D3EE;">MULTI-PDV</strong>
                    <span style="opacity: 0.85;">|</span>
                    <span style="font-size: 14px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ authStore.currentStore?.name || 'Aucun point de vente sélectionné' }}
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <select
                        :value="authStore.currentStore?.id || ''"
                        @change="onStoreChange"
                        style="min-width: 200px; max-width: 280px; padding: 6px 10px; border-radius: 8px; border: 1px solid rgba(148,163,184,0.2); background: #141625; color: #FFFFFF; font-size: 13px; font-weight: 600;"
                    >
                        <option value="" disabled>Choisir un PDV</option>
                        <option v-for="store in availableStores" :key="store.id" :value="store.id">
                            {{ store.name }}{{ store.code ? ` (${store.code})` : '' }}
                        </option>
                    </select>
                    <router-link
                        to="/fiche-pdv"
                        style="padding: 6px 12px; border-radius: 8px; background: #22D3EE; color: #141625; font-size: 12px; font-weight: 700; text-decoration: none; border: none;"
                    >
                        Gérer les PDV
                    </router-link>
                    <router-link
                        v-if="authStore.canManageUsers"
                        to="/users"
                        style="padding: 6px 12px; border-radius: 8px; background: #FB923C; color: #141625; font-size: 12px; font-weight: 700; text-decoration: none; border: none;"
                    >
                        Utilisateurs
                    </router-link>
                </div>
            </div>

            <!-- Header -->
            <Header v-if="!isPosRoute" @toggle-sidebar="toggleSidebar" />
            
            <!-- Page Content -->
            <main :class="mainContentClass">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, provide, watch } from 'vue'
import { useRoute } from 'vue-router'
import { storeToRefs } from 'pinia'
import Sidebar from './Sidebar.vue'
import Header from './Header.vue'
import { useSettingsStore } from '../../stores/settings'
import { useUiStore } from '../../stores/ui'
import { useAuthStore } from '../../stores/auth'
import { storesApi } from '../../api'

const sidebarCollapsed = ref(false)
const isMobile = ref(false)
const settingsStore = useSettingsStore()
const uiStore = useUiStore()
const authStore = useAuthStore()
const { posSidebarOpen, appSidebarOpen } = storeToRefs(uiStore)
const route = useRoute()
const isPosRoute = computed(() => route.name === 'pos')
const isDashboardRoute = computed(() => route.name === 'dashboard')
const availableStores = ref([])
const shouldShowAppSidebar = computed(() => {
    return appSidebarOpen.value
})

async function loadAvailableStores() {
    if (!authStore.isAuthenticated || authStore.offlineGuestMode) {
        availableStores.value = []
        return
    }
    try {
        const { data } = await storesApi.list()
        availableStores.value = Array.isArray(data) ? data : []
        if (!authStore.currentStore?.name && authStore.currentStore?.id) {
            const match = availableStores.value.find(s => Number(s.id) === Number(authStore.currentStore.id))
            if (match) authStore.setCurrentStore(match)
        }
        if (!authStore.currentStore?.id && availableStores.value.length === 1) {
            authStore.setCurrentStore(availableStores.value[0])
        }
    } catch (error) {
        console.error('Failed to load stores:', error)
        availableStores.value = []
    }
}

async function onStoreChange(event) {
    const storeId = Number(event.target.value)
    const store = availableStores.value.find(s => Number(s.id) === storeId)
    if (!store || Number(authStore.currentStore?.id) === storeId) return
    try {
        await storesApi.select(store.id)
        authStore.setCurrentStore(store)
        window.location.reload()
    } catch (error) {
        alert(error.response?.data?.message || 'Impossible de changer de point de vente')
        await loadAvailableStores()
    }
}

const contentInsetLeft = computed(() => {
    if (isMobile.value || !appSidebarOpen.value) {
        return 0
    }
    return sidebarCollapsed.value ? 80 : 288
})

provide('contentInsetLeft', contentInsetLeft)

const rootLayoutClass = computed(() => {
    if (isDashboardRoute.value) {
        return 'h-screen bg-bg-main flex overflow-hidden'
    }
    return 'min-h-screen bg-bg-main flex'
})
const layoutOffsetClass = computed(() => {
    if (isMobile.value) {
        return ''
    }
    if (!appSidebarOpen.value) {
        return ''
    }
    if (isPosRoute.value) {
        return 'ml-72'
    }
    return sidebarCollapsed.value ? 'ml-20' : 'ml-72'
})

const mainContentClass = computed(() => {
    if (isPosRoute.value) {
        return 'flex-1 overflow-hidden min-w-0'
    }
    if (isDashboardRoute.value) {
        return 'flex-1 overflow-hidden min-w-0 min-h-0 h-full'
    }
    return 'flex-1 p-4 sm:p-6 overflow-auto min-w-0'
})

function toggleSidebar() {
    if (isMobile.value) {
        uiStore.toggleAppSidebar()
        return
    }
    sidebarCollapsed.value = !sidebarCollapsed.value
}

function closeSidebar() {
    uiStore.closeAppSidebar()
}

function updateViewport() {
    isMobile.value = window.innerWidth < 1024
    if (isMobile.value) {
        uiStore.closeAppSidebar()
    }
}

watch(
    () => [authStore.isAuthenticated, authStore.offlineGuestMode, authStore.token],
    ([ready, offline]) => {
        if (ready && !offline) loadAvailableStores()
    },
    { immediate: true }
)

onMounted(() => {
    settingsStore.fetchSettings()
    updateViewport()
    window.addEventListener('resize', updateViewport)
})

onUnmounted(() => {
    window.removeEventListener('resize', updateViewport)
})
</script>
