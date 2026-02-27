<template>
    <div class="min-h-screen bg-[#fafafa] flex">
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
            class="flex-1 flex flex-col min-w-0"
            :class="layoutOffsetClass"
        >
            <!-- Header -->
            <Header v-if="!isPosRoute" @toggle-sidebar="toggleSidebar" />
            
            <!-- Page Content -->
            <main :class="isPosRoute ? 'flex-1 overflow-hidden' : 'flex-1 p-4 sm:p-6 overflow-auto min-w-0'">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
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
const shouldShowAppSidebar = computed(() => {
    return appSidebarOpen.value
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
