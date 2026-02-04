<template>
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
        <!-- Left side -->
        <div class="flex items-center space-x-4">
            <button 
                @click="$emit('toggle-sidebar')"
                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg"
            >
                <Bars3Icon class="w-5 h-5" />
            </button>
            <h1 class="text-lg font-semibold text-gray-900">{{ pageTitle }}</h1>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-4">
            <!-- Dark mode toggle (placeholder) -->
            <button class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg">
                <MoonIcon class="w-5 h-5" />
            </button>

            <!-- Notifications -->
            <button class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg relative">
                <BellIcon class="w-5 h-5" />
                <span v-if="notificationCount > 0" class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- User dropdown -->
            <div class="relative" ref="dropdownRef">
                <button 
                    @click="showDropdown = !showDropdown"
                    class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-lg"
                >
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium text-gray-900">{{ authStore.userName }}</p>
                        <p class="text-xs text-gray-500">{{ roleLabel }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-green-600 font-semibold text-sm">{{ userInitials }}</span>
                    </div>
                </button>

                <!-- Dropdown menu -->
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
                        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                    >
                        <router-link 
                            to="/settings" 
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            @click="showDropdown = false"
                        >
                            Paramètres
                        </router-link>
                        <hr class="my-1">
                        <button 
                            @click="handleLogout"
                            class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { Bars3Icon, BellIcon, MoonIcon } from '@heroicons/vue/24/outline'

defineEmits(['toggle-sidebar'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const showDropdown = ref(false)
const dropdownRef = ref(null)
const notificationCount = ref(0)

const pageTitle = computed(() => {
    const titles = {
        '/dashboard': 'Aperçu du Dashboard',
        '/pos': 'Menu POS',
        '/articles': 'Gestion des articles',
        '/categories': 'Catégories',
        '/stock': 'Gestion de stock',
        '/customers': 'Gestion des clients',
        '/employees': 'Gestion des employés',
        '/reports': 'Rapports',
        '/settings': 'Paramètres',
    }
    return titles[route.path] || 'Dashboard'
})

const roleLabel = computed(() => {
    const roles = {
        superadmin: 'Super Admin',
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

function handleClickOutside(event) {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        showDropdown.value = false
    }
}

async function handleLogout() {
    showDropdown.value = false
    await authStore.logout()
    router.push('/login')
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>
