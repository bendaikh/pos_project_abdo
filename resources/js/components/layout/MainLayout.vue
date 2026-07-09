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
import { ref, onMounted, onUnmounted, computed, provide } from 'vue'
import { useRoute } from 'vue-router'
import { storeToRefs } from 'pinia'
import Sidebar from './Sidebar.vue'
import Header from './Header.vue'
import { useSettingsStore } from '../../stores/settings'
import { useUiStore } from '../../stores/ui'

const sidebarCollapsed = ref(false)
const isMobile = ref(false)
const settingsStore = useSettingsStore()
const uiStore = useUiStore()
const { posSidebarOpen, appSidebarOpen } = storeToRefs(uiStore)
const route = useRoute()
const isPosRoute = computed(() => route.name === 'pos')
const isDashboardRoute = computed(() => route.name === 'dashboard')
const shouldShowAppSidebar = computed(() => {
    return appSidebarOpen.value
})

const contentInsetLeft = computed(() => {
    if (isMobile.value || !appSidebarOpen.value) {
        return 0
    }
    return sidebarCollapsed.value ? 80 : 256
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
        return 'ml-64'
    }
    return sidebarCollapsed.value ? 'ml-20' : 'ml-64'
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

onMounted(() => {
    settingsStore.fetchSettings()
    updateViewport()
    window.addEventListener('resize', updateViewport)
})

onUnmounted(() => {
    window.removeEventListener('resize', updateViewport)
})
</script>
