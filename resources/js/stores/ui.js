import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
    const posSidebarOpen = ref(false)

    function openPosSidebar() {
        posSidebarOpen.value = true
    }

    function closePosSidebar() {
        posSidebarOpen.value = false
    }

    function togglePosSidebar() {
        posSidebarOpen.value = !posSidebarOpen.value
    }

    return {
        posSidebarOpen,
        openPosSidebar,
        closePosSidebar,
        togglePosSidebar
    }
})
