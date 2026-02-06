<template>
    <div class="min-h-screen bg-[#fafafa] flex">
        <!-- Sidebar -->
        <Sidebar :collapsed="sidebarCollapsed" @toggle="toggleSidebar" />
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col" :class="sidebarCollapsed ? 'ml-20' : 'ml-64'">
            <!-- Header -->
            <Header @toggle-sidebar="toggleSidebar" />
            
            <!-- Page Content -->
            <main class="flex-1 p-6 overflow-auto">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Sidebar from './Sidebar.vue'
import Header from './Header.vue'
import { useSettingsStore } from '../../stores/settings'

const sidebarCollapsed = ref(false)
const settingsStore = useSettingsStore()

function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value
}

onMounted(() => {
    settingsStore.fetchSettings()
})
</script>
