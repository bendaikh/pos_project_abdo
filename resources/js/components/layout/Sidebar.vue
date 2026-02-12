<template>
    <aside 
        class="fixed inset-y-0 left-0 z-50 bg-gray-900 border-r border-gray-800 transition-all duration-300"
        :class="collapsed ? 'w-20' : 'w-64'"
    >
        <!-- Logo -->
        <div class="flex items-center h-16 px-4 border-b border-gray-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <span v-if="!collapsed" class="text-xl font-bold text-white">GREENPOS</span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-2 overflow-y-auto">
            <!-- Dashboard -->
            <router-link 
                to="/dashboard"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive('/dashboard') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
            >
                <Squares2X2Icon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Dashboard</span>
            </router-link>

            <!-- POS -->
            <router-link 
                to="/pos"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive('/pos') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
            >
                <CalculatorIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Menu POS</span>
            </router-link>

            <!-- VENTE Section (Collapsible) -->
            <div class="mt-4">
                <!-- Section Header - Clickable to expand/collapse -->
                <button 
                    @click="toggleSection('vente')"
                    class="flex items-center justify-between w-full px-3 py-2 text-sm font-bold text-yellow-400 uppercase tracking-wider rounded-lg hover:bg-gray-800 transition-colors"
                >
                    <div class="flex items-center">
                        <CurrencyDollarIcon class="w-5 h-5 flex-shrink-0" />
                        <span v-if="!collapsed" class="ml-3">VENTE</span>
                    </div>
                    <ChevronDownIcon 
                        v-if="!collapsed"
                        class="w-4 h-4 transition-transform duration-200"
                        :class="{ 'rotate-180': expandedSections.vente }"
                    />
                </button>
                
                <!-- Sub-sections (Collapsible content) -->
                <div 
                    v-show="expandedSections.vente && !collapsed"
                    class="mt-1 space-y-1 overflow-hidden"
                >
                    <!-- Devis -->
                    <router-link 
                        to="/devis"
                        class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors"
                        :class="isActive('/devis') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'"
                    >
                        <DocumentTextIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Devis</span>
                    </router-link>

                    <!-- Bon de livraison -->
                    <router-link 
                        to="/bon-livraison"
                        class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors"
                        :class="isActive('/bon-livraison') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'"
                    >
                        <TruckIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Bon de livraison</span>
                    </router-link>

                    <!-- Facture -->
                    <router-link 
                        to="/facture"
                        class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors"
                        :class="isActive('/facture') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'"
                    >
                        <DocumentIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Facture</span>
                    </router-link>
                </div>
            </div>

            <!-- ACHAT Section (Collapsible) -->
            <div class="mt-2">
                <!-- Section Header - Clickable to expand/collapse -->
                <button 
                    @click="toggleSection('achat')"
                    class="flex items-center justify-between w-full px-3 py-2 text-sm font-bold text-yellow-400 uppercase tracking-wider rounded-lg hover:bg-gray-800 transition-colors"
                >
                    <div class="flex items-center">
                        <ShoppingCartIcon class="w-5 h-5 flex-shrink-0" />
                        <span v-if="!collapsed" class="ml-3">ACHAT</span>
                    </div>
                    <ChevronDownIcon 
                        v-if="!collapsed"
                        class="w-4 h-4 transition-transform duration-200"
                        :class="{ 'rotate-180': expandedSections.achat }"
                    />
                </button>
                
                <!-- Sub-sections (Collapsible content) -->
                <div 
                    v-show="expandedSections.achat && !collapsed"
                    class="mt-1 space-y-1 overflow-hidden"
                >
                    <!-- Fournisseur -->
                    <router-link 
                        to="/fournisseurs"
                        class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors"
                        :class="isActive('/fournisseurs') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'"
                    >
                        <BuildingOfficeIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Fournisseur</span>
                    </router-link>

                    <!-- Bon de commande -->
                    <router-link 
                        to="/bon-commande"
                        class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors"
                        :class="isActive('/bon-commande') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'"
                    >
                        <ClipboardDocumentListIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Bon de commande</span>
                    </router-link>

                    <!-- Réception de Marchandise -->
                    <router-link 
                        to="/reception-marchandise"
                        class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors"
                        :class="isActive('/reception-marchandise') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'"
                    >
                        <TruckIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Réception de Marchandise</span>
                    </router-link>

                    <!-- Facture fournisseur -->
                    <router-link 
                        to="/facture-fournisseur"
                        class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors"
                        :class="isActive('/facture-fournisseur') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'"
                    >
                        <DocumentIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Facture fournisseur</span>
                    </router-link>

                    <!-- Historique d'achats -->
                    <router-link 
                        to="/historique-achats"
                        class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors"
                        :class="isActive('/historique-achats') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'"
                    >
                        <ClipboardDocumentListIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Historique d'achats</span>
                    </router-link>
                </div>
            </div>

            <!-- GESTION FINANCIÈRE Section (Collapsible) -->
            <div class="mt-2">
                <!-- Section Header - Clickable to expand/collapse -->
                <button 
                    @click="toggleSection('finance')"
                    class="flex items-center justify-between w-full px-3 py-2 text-xs font-bold text-yellow-400 uppercase tracking-wider rounded-lg hover:bg-gray-800 transition-colors"
                >
                    <div class="flex items-center min-w-0">
                        <BanknotesIcon class="w-5 h-5 flex-shrink-0" />
                        <span v-if="!collapsed" class="ml-2 whitespace-nowrap">GESTION FINANCIÈRE</span>
                    </div>
                    <ChevronDownIcon 
                        v-if="!collapsed"
                        class="w-4 h-4 flex-shrink-0 transition-transform duration-200"
                        :class="{ 'rotate-180': expandedSections.finance }"
                    />
                </button>
                
                <!-- Sub-sections (Collapsible content) -->
                <div 
                    v-show="expandedSections.finance && !collapsed"
                    class="mt-1 space-y-1 overflow-hidden"
                >
                    <!-- Journal de caisse -->
                    <router-link 
                        to="/journal-caisse"
                        class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors"
                        :class="isActive('/journal-caisse') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'"
                    >
                        <BookOpenIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Journal de caisse</span>
                    </router-link>

                    <!-- Dépenses -->
                    <router-link 
                        to="/depenses"
                        class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors"
                        :class="isActive('/depenses') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'"
                    >
                        <CreditCardIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Dépenses</span>
                    </router-link>

                    <!-- Bilan -->
                    <router-link 
                        to="/bilan"
                        class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors"
                        :class="isActive('/bilan') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'"
                    >
                        <PresentationChartLineIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Bilan</span>
                    </router-link>
                </div>
            </div>

            <!-- Other menu items -->
            <div class="mt-4 space-y-1">
                <router-link 
                    to="/articles"
                    class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                    :class="isActive('/articles') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
                >
                    <ClipboardDocumentListIcon class="w-5 h-5 flex-shrink-0" />
                    <span v-if="!collapsed" class="ml-3">Gestion des articles</span>
                </router-link>

                <router-link 
                    to="/stock"
                    class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                    :class="isActive('/stock') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
                >
                    <ArchiveBoxIcon class="w-5 h-5 flex-shrink-0" />
                    <span v-if="!collapsed" class="ml-3">Gestion de stock</span>
                </router-link>

                <router-link 
                    to="/reports"
                    class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                    :class="isActive('/reports') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
                >
                    <ChartBarIcon class="w-5 h-5 flex-shrink-0" />
                    <span v-if="!collapsed" class="ml-3">Rapports</span>
                </router-link>
            </div>

            <!-- Divider -->
            <div class="my-4 border-t border-gray-700"></div>

            <!-- Clients -->
            <router-link 
                to="/customers"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive('/customers') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
            >
                <UserGroupIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Clients</span>
            </router-link>

            <!-- Employés -->
            <router-link 
                to="/employees"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive('/employees') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
            >
                <UsersIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Employés</span>
            </router-link>

            <!-- Utilisateurs -->
            <router-link 
                to="/utilisateurs"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive('/utilisateurs') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
            >
                <KeyIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Utilisateurs</span>
            </router-link>

            <!-- Divider -->
            <div class="my-4 border-t border-gray-700"></div>

            <!-- Paramètres -->
            <router-link 
                to="/settings"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive('/settings') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
            >
                <Cog6ToothIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Paramètres</span>
            </router-link>

            <!-- Logout / Exit Offline Mode -->
            <button 
                @click="handleLogout"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors w-full text-red-400 hover:bg-gray-800"
            >
                <ArrowRightOnRectangleIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">
                    {{ authStore.offlineGuestMode ? 'Quitter Mode Hors ligne' : 'Déconnexion' }}
                </span>
            </button>
        </nav>
    </aside>
</template>

<script setup>
import { ref, reactive } from 'vue'
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
    UsersIcon,
    DocumentTextIcon,
    TruckIcon,
    DocumentIcon,
    ShoppingCartIcon,
    ChevronDownIcon,
    CurrencyDollarIcon,
    BanknotesIcon,
    BookOpenIcon,
    CreditCardIcon,
    PresentationChartLineIcon,
    BuildingOfficeIcon,
    BuildingStorefrontIcon,
    KeyIcon
} from '@heroicons/vue/24/outline'

defineProps({
    collapsed: Boolean
})

defineEmits(['toggle'])

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

// Collapsible sections state
const expandedSections = reactive({
    vente: false,
    achat: false,
    finance: false,
    clients: false,
    fournisseurs: false
})

// Toggle section expand/collapse
function toggleSection(section) {
    expandedSections[section] = !expandedSections[section]
}

function isActive(path) {
    return route.path === path || route.path.startsWith(path + '/')
}

async function handleLogout() {
    await authStore.logout()
    router.push('/login')
}
</script>

