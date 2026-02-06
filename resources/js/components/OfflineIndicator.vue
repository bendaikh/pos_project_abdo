<template>
    <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="transform translate-y-full opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-full opacity-0"
    >
        <div 
            v-if="!offlineStore.isOnline || offlineStore.hasPendingSales"
            class="fixed bottom-4 right-4 z-50"
        >
            <!-- Offline Badge -->
            <div 
                v-if="!offlineStore.isOnline"
                class="bg-orange-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center space-x-3 mb-2"
            >
                <WifiIcon class="w-5 h-5" />
                <div>
                    <p class="font-medium text-sm">Mode Hors ligne</p>
                    <p class="text-xs opacity-90">Les ventes seront synchronisées automatiquement</p>
                </div>
            </div>

            <!-- Pending Sales Badge -->
            <div 
                v-if="offlineStore.hasPendingSales"
                @click="offlineStore.forceSync"
                class="bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center space-x-3 cursor-pointer hover:bg-blue-600 transition-colors"
            >
                <div class="relative">
                    <CloudArrowUpIcon class="w-5 h-5" :class="{ 'animate-bounce': offlineStore.isSyncing }" />
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                </div>
                <div>
                    <p class="font-medium text-sm">
                        {{ offlineStore.isSyncing ? 'Synchronisation...' : `${offlineStore.pendingSalesCount} vente(s) en attente` }}
                    </p>
                    <p class="text-xs opacity-90">
                        {{ offlineStore.isSyncing ? 'Envoi des données...' : 'Cliquez pour synchroniser' }}
                    </p>
                </div>
            </div>

            <!-- Sync Success -->
            <transition
                enter-active-class="transition ease-out duration-300"
                leave-active-class="transition ease-in duration-200"
            >
                <div 
                    v-if="showSyncSuccess"
                    class="bg-primary-500 text-gray-900 px-4 py-3 rounded-lg shadow-lg flex items-center space-x-3 mt-2"
                >
                    <CheckCircleIcon class="w-5 h-5" />
                    <p class="font-medium text-sm">Synchronisation réussie!</p>
                </div>
            </transition>

            <!-- Sync Errors -->
            <div 
                v-if="offlineStore.syncErrors.length > 0"
                class="bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg mt-2"
            >
                <div class="flex items-start space-x-3">
                    <ExclamationCircleIcon class="w-5 h-5 flex-shrink-0" />
                    <div>
                        <p class="font-medium text-sm">Erreur de synchronisation</p>
                        <p class="text-xs opacity-90">{{ offlineStore.syncErrors.length }} vente(s) non synchronisée(s)</p>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useOfflineStore } from '../stores/offline'
import { 
    WifiIcon, 
    CloudArrowUpIcon, 
    CheckCircleIcon,
    ExclamationCircleIcon 
} from '@heroicons/vue/24/outline'

const offlineStore = useOfflineStore()
const showSyncSuccess = ref(false)

// Watch for successful sync
watch(() => offlineStore.syncStatus, (newStatus, oldStatus) => {
    if (oldStatus === 'syncing' && newStatus === 'synced') {
        showSyncSuccess.value = true
        setTimeout(() => {
            showSyncSuccess.value = false
        }, 3000)
    }
})
</script>
