<template>
    <aside 
        class="fixed inset-y-0 left-0 z-50 bg-white border-r border-gray-200 transition-all duration-300"
        :class="collapsed ? 'w-20' : 'w-64'"
    >
        <!-- Logo -->
        <div class="flex items-center h-16 px-4 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <span v-if="!collapsed" class="text-xl font-bold text-green-600">GREENPOS</span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <router-link 
                v-for="item in mainNavItems" 
                :key="item.to"
                :to="item.to"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive(item.to) ? 'bg-green-50 text-green-600' : 'text-gray-600 hover:bg-gray-100'"
            >
                <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">{{ item.label }}</span>
            </router-link>

            <!-- Divider -->
            <div class="my-4 border-t border-gray-200"></div>
            <p v-if="!collapsed" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Compte</p>

            <router-link 
                v-for="item in accountNavItems" 
                :key="item.to"
                :to="item.to"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive(item.to) ? 'bg-green-50 text-green-600' : 'text-gray-600 hover:bg-gray-100'"
            >
                <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">{{ item.label }}</span>
            </router-link>

            <!-- Logout -->
            <button 
                @click="handleLogout"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors w-full text-red-600 hover:bg-red-50"
            >
                <ArrowRightOnRectangleIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Déconnexion</span>
            </button>
        </nav>
    </aside>
</template>

<script setup>
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import {
    Squares2X2Icon,
    CalculatorIcon,
    ClipboardDocumentListIcon,
    ArchiveBoxIcon,
    ChartBarIcon,
    Cog6ToothIcon,
    ArrowRightOnRectangleIcon,
    UserGroupIcon,
    UsersIcon
} from '@heroicons/vue/24/outline'

defineProps({
    collapsed: Boolean
})

defineEmits(['toggle'])

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const mainNavItems = [
    { to: '/dashboard', label: 'Dashboard', icon: Squares2X2Icon },
    { to: '/pos', label: 'Menu POS', icon: CalculatorIcon },
    { to: '/articles', label: 'Gestion des articles', icon: ClipboardDocumentListIcon },
    { to: '/stock', label: 'Gestion de stock', icon: ArchiveBoxIcon },
    { to: '/reports', label: 'Rapports', icon: ChartBarIcon },
]

const accountNavItems = [
    { to: '/customers', label: 'Clients', icon: UserGroupIcon },
    { to: '/employees', label: 'Employés', icon: UsersIcon },
    { to: '/settings', label: 'Paramètres', icon: Cog6ToothIcon },
]

function isActive(path) {
    return route.path === path || route.path.startsWith(path + '/')
}

async function handleLogout() {
    await authStore.logout()
    router.push('/login')
}
</script>

