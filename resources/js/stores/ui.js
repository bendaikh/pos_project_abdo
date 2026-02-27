import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
    const posSidebarOpen = ref(false)
    const appSidebarOpen = ref(true)

    function openPosSidebar() {
        posSidebarOpen.value = true
    }

    function closePosSidebar() {
        posSidebarOpen.value = false
    }

    function togglePosSidebar() {
        posSidebarOpen.value = !posSidebarOpen.value
    }

    function openAppSidebar() {
        appSidebarOpen.value = true
    }

    function closeAppSidebar() {
        appSidebarOpen.value = false
    }

    function toggleAppSidebar() {
        appSidebarOpen.value = !appSidebarOpen.value
    }

    return {
        posSidebarOpen,
        openPosSidebar,
        closePosSidebar,
        togglePosSidebar,
        appSidebarOpen,
        openAppSidebar,
        closeAppSidebar,
        toggleAppSidebar
    }
})
