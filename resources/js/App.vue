<template>
    <router-view />
    <OfflineIndicator />
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
import { useOfflineStore } from './stores/offline'
import OfflineIndicator from './components/OfflineIndicator.vue'

const authStore = useAuthStore()
const offlineStore = useOfflineStore()

onMounted(async () => {
    // Initialize offline functionality FIRST (before auth)
    await offlineStore.init()
    
    // Then try to restore auth state from localStorage
    await authStore.initAuth()
})
</script>
