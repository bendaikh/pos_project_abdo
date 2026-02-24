<template>
    <div class="min-h-screen bg-[#fafafa] flex">
        <!-- POS Overlay Sidebar -->
        <div
            v-if="isPosRoute && posSidebarOpen"
            class="fixed inset-0 z-40 bg-black/30"
            @click="uiStore.closePosSidebar"
        ></div>
        <Sidebar v-if="isPosRoute && posSidebarOpen" :collapsed="false" @toggle="uiStore.togglePosSidebar" />

        <!-- Sidebar -->
        <Sidebar v-if="!isPosRoute" :collapsed="sidebarCollapsed" @toggle="toggleSidebar" />
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col" :class="!isPosRoute ? (sidebarCollapsed ? 'ml-20' : 'ml-64') : ''">
            <!-- Header -->
            <Header v-if="!isPosRoute" @toggle-sidebar="toggleSidebar" />
            
            <!-- Page Content -->
            <main :class="isPosRoute ? 'flex-1 overflow-hidden' : 'flex-1 p-6 overflow-auto'">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { storeToRefs } from 'pinia'
import Sidebar from './Sidebar.vue'
import Header from './Header.vue'
import { useSettingsStore } from '../../stores/settings'
import { useUiStore } from '../../stores/ui'

const sidebarCollapsed = ref(false)
const settingsStore = useSettingsStore()
const uiStore = useUiStore()
const { posSidebarOpen } = storeToRefs(uiStore)
const route = useRoute()
const isPosRoute = computed(() => route.name === 'pos')

function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value
}

onMounted(() => {
    settingsStore.fetchSettings()
})
</script>
