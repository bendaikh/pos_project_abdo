<template>
    <aside 
        class="fixed inset-y-0 left-0 z-50 flex flex-col bg-gray-900 border-r border-gray-800 transition-all duration-300"
        :class="sidebarClasses"
    >
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <span v-if="!collapsed" class="text-xl font-bold text-white">GREENPOS</span>
            </div>
            <button
                v-if="isMobile"
                type="button"
                class="p-2 text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg"
                @click="$emit('close')"
            >
                ✕
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 min-h-0 px-3 py-4 space-y-2 overflow-y-auto">
            <!-- Point de Vente -->
            <router-link 
                to="/pos"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive('/pos') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
            >
                <CalculatorIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Point de Vente (PDV)</span>
            </router-link>

            <!-- Dashboard -->
            <router-link 
                to="/dashboard"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive('/dashboard') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
            >
                <Squares2X2Icon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Dashboard</span>
            </router-link>

            <!-- Gestion d'Articles -->
            <div class="mt-3">
                <button 
                    @click="toggleSection('articles')"
                    class="flex items-center justify-between w-full px-3 py-2 text-xs font-bold text-yellow-400 uppercase tracking-wider rounded-lg hover:bg-gray-800 transition-colors"
                >
                    <div class="flex items-center min-w-0">
                        <ClipboardDocumentListIcon class="w-5 h-5 flex-shrink-0" />
                        <span v-if="!collapsed" class="ml-2 whitespace-nowrap">Gestion d'Articles</span>
                    </div>
                    <ChevronDownIcon 
                        v-if="!collapsed"
                        class="w-4 h-4 transition-transform duration-200 flex-shrink-0 ml-2"
                        :class="{ 'rotate-180': expandedSections.articles }"
                    />
                </button>
                <div v-show="expandedSections.articles && !collapsed" class="mt-1 space-y-1 overflow-hidden">
                    <router-link to="/articles" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/articles') ? 'bg-cyan-500 text-gray-900' : 'text-cyan-300 hover:bg-gray-800'">
                        <ClipboardDocumentListIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Articles</span>
                    </router-link>
                    <router-link to="/categories" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/categories') ? 'bg-cyan-500 text-gray-900' : 'text-cyan-300 hover:bg-gray-800'">
                        <FolderIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Catégories</span>
                    </router-link>
                    <router-link to="/options" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/options') ? 'bg-cyan-500 text-gray-900' : 'text-cyan-300 hover:bg-gray-800'">
                        <AdjustmentsHorizontalIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3 whitespace-nowrap">Options & Variantes</span>
                    </router-link>
                </div>
            </div>

            <!-- Gestion de Stock -->
            <router-link 
                to="/stock"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive('/stock') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
            >
                <ArchiveBoxIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Gestion de Stock</span>
            </router-link>

            <!-- Production -->
            <div class="mt-3">
                <button 
                    @click="toggleSection('production')"
                    class="flex items-center justify-between w-full px-3 py-2 text-xs font-bold text-yellow-400 uppercase tracking-wider rounded-lg hover:bg-gray-800 transition-colors"
                >
                    <div class="flex items-center min-w-0">
                        <WrenchScrewdriverIcon class="w-5 h-5 flex-shrink-0" />
                        <span v-if="!collapsed" class="ml-2 whitespace-nowrap">Production</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.production }" />
                </button>
                <div v-show="expandedSections.production && !collapsed" class="mt-1 space-y-1 overflow-hidden">
                    <router-link to="/production" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/production', { exact: true }) ? 'bg-indigo-500 text-gray-900' : 'text-indigo-300 hover:bg-gray-800'">
                        <ClipboardDocumentListIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Entrée production</span>
                    </router-link>
                    <router-link to="/production/consumption" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/production/consumption') ? 'bg-indigo-500 text-gray-900' : 'text-indigo-300 hover:bg-gray-800'">
                        <AdjustmentsHorizontalIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Consommation MP</span>
                    </router-link>
                    <router-link to="/production/history" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/production/history') ? 'bg-indigo-500 text-gray-900' : 'text-indigo-300 hover:bg-gray-800'">
                        <ChartBarIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Historique</span>
                    </router-link>
                    <router-link to="/production/costs" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/production/costs') ? 'bg-indigo-500 text-gray-900' : 'text-indigo-300 hover:bg-gray-800'">
                        <PresentationChartLineIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Coût de production</span>
                    </router-link>
                    <div class="mt-3 border-t border-indigo-500/30 pt-3 space-y-1">
                        <p class="text-[10px] font-semibold tracking-[0.2em] text-indigo-200 uppercase">Gestion de perte</p>
                        <router-link to="/losses" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/losses', { exact: true }) ? 'bg-rose-500 text-gray-900' : 'text-rose-200 hover:bg-gray-800'">
                            <ClipboardDocumentCheckIcon class="w-4 h-4 flex-shrink-0" />
                            <span class="ml-3">Déclaration de perte</span>
                        </router-link>
                        <router-link to="/losses/history" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/losses/history') ? 'bg-rose-500 text-gray-900' : 'text-rose-200 hover:bg-gray-800'">
                            <ClockIcon class="w-4 h-4 flex-shrink-0" />
                            <span class="ml-3">Historique des pertes</span>
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Ventes & Clients -->
            <div class="mt-3">
                <button @click="toggleSection('ventesClients')" class="flex items-center justify-between w-full px-3 py-2 text-xs font-bold text-yellow-400 uppercase tracking-wider rounded-lg hover:bg-gray-800 transition-colors">
                    <div class="flex items-center min-w-0">
                        <CurrencyDollarIcon class="w-5 h-5 flex-shrink-0" />
                        <span v-if="!collapsed" class="ml-2 whitespace-nowrap">Ventes & Clients</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.ventesClients }" />
                </button>
                <div v-show="expandedSections.ventesClients && !collapsed" class="mt-1 space-y-1 overflow-hidden">
                    <router-link to="/commandes" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/commandes') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <ClipboardDocumentListIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Commandes clients</span>
                    </router-link>
                    <router-link to="/devis" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/devis') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <DocumentTextIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Devis</span>
                    </router-link>
                    <router-link to="/bon-livraison" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/bon-livraison') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <TruckIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Bon de livraison</span>
                    </router-link>
                    <router-link to="/livreurs" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/livreurs') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <TruckIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Livreurs</span>
                    </router-link>
                    <router-link to="/facture" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/facture') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <DocumentIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Facture</span>
                    </router-link>
                    <router-link to="/customers" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/customers') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <UserGroupIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Clients</span>
                    </router-link>
                </div>
            </div>

            <!-- Achats & Fournisseurs -->
            <div class="mt-3">
                <button @click="toggleSection('achats')" class="flex items-center justify-between w-full px-3 py-2 text-xs font-bold text-yellow-400 uppercase tracking-wider rounded-lg hover:bg-gray-800 transition-colors">
                    <div class="flex items-center min-w-0">
                        <ShoppingCartIcon class="w-5 h-5 flex-shrink-0" />
                        <span v-if="!collapsed" class="ml-2 whitespace-nowrap">Achats & Fournisseurs</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.achats }" />
                </button>
                <div v-show="expandedSections.achats && !collapsed" class="mt-1 space-y-1 overflow-hidden">
                    <router-link to="/fournisseurs" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/fournisseurs') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <BuildingOfficeIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Fournisseurs</span>
                    </router-link>
                    <router-link to="/bon-commande" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/bon-commande') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <ClipboardDocumentListIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Bon de commande</span>
                    </router-link>
                    <router-link to="/reception-marchandise" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/reception-marchandise') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <TruckIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Réception de Marchandise</span>
                    </router-link>
                    <router-link to="/facture-fournisseur" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/facture-fournisseur') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <DocumentIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Facture fournisseur</span>
                    </router-link>
                    <router-link to="/historique-achats" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/historique-achats') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <ClipboardDocumentListIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Historique d'achats</span>
                    </router-link>
                </div>
            </div>

            <!-- Gestion Financière -->
            <div class="mt-3">
                <button @click="toggleSection('finance')" class="flex items-center justify-between w-full px-3 py-2 text-xs font-bold text-yellow-400 uppercase tracking-wider rounded-lg hover:bg-gray-800 transition-colors">
                    <div class="flex items-center min-w-0">
                        <BanknotesIcon class="w-5 h-5 flex-shrink-0" />
                        <span v-if="!collapsed" class="ml-2 whitespace-nowrap">Gestion Financière</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.finance }" />
                </button>
                <div v-show="expandedSections.finance && !collapsed" class="mt-1 space-y-1 overflow-hidden">
                    <router-link to="/journal-caisse" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/journal-caisse') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <BookOpenIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Journal de caisse</span>
                    </router-link>
                    <router-link to="/depenses" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/depenses') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <CreditCardIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Dépenses</span>
                    </router-link>
                    <router-link to="/bilan" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/bilan') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <PresentationChartLineIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Bilan</span>
                    </router-link>
                    <router-link to="/suivi-encaissement" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/suivi-encaissement') ? 'bg-teal-500 text-gray-900' : 'text-teal-300 hover:bg-gray-800'">
                        <BanknotesIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Suivi Encaissement</span>
                    </router-link>
                </div>
            </div>

            <!-- Agenda -->
            <div class="mt-3">
                <button @click="toggleSection('agenda')" class="flex items-center justify-between w-full px-3 py-2 text-xs font-bold text-yellow-400 uppercase tracking-wider rounded-lg hover:bg-gray-800 transition-colors">
                    <div class="flex items-center min-w-0">
                        <CalendarDaysIcon class="w-5 h-5 flex-shrink-0" />
                        <span v-if="!collapsed" class="ml-2 whitespace-nowrap">Agenda</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.agenda }" />
                </button>
                <div v-show="expandedSections.agenda && !collapsed" class="mt-1 space-y-1 overflow-hidden">
                    <router-link to="/agenda" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/agenda') ? 'bg-blue-500 text-gray-900' : 'text-blue-300 hover:bg-gray-800'">
                        <CalendarDaysIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Calendrier</span>
                    </router-link>
                    <router-link to="/agenda/appointments" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/agenda/appointments') ? 'bg-blue-500 text-gray-900' : 'text-blue-300 hover:bg-gray-800'">
                        <CalendarDaysIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Rendez-vous</span>
                    </router-link>
                    <router-link to="/agenda/tasks" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/agenda/tasks') ? 'bg-blue-500 text-gray-900' : 'text-blue-300 hover:bg-gray-800'">
                        <CalendarDaysIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Tâches</span>
                    </router-link>
                </div>
            </div>

            <!-- Employés -->
            <div class="mt-3">
                <button @click="toggleSection('employees')" class="flex items-center justify-between w-full px-3 py-2 text-xs font-bold text-yellow-400 uppercase tracking-wider rounded-lg hover:bg-gray-800 transition-colors">
                    <div class="flex items-center min-w-0">
                        <UsersIcon class="w-5 h-5 flex-shrink-0" />
                        <span v-if="!collapsed" class="ml-2 whitespace-nowrap">Employés</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.employees }" />
                </button>
                <div v-show="expandedSections.employees && !collapsed" class="mt-1 space-y-1 overflow-hidden">
                    <router-link to="/employees" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/employees', { exact: true }) ? 'bg-purple-500 text-gray-900' : 'text-purple-300 hover:bg-gray-800'">
                        <UsersIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Gestion des employés</span>
                    </router-link>
                    <router-link to="/employees/payroll" class="flex items-center px-3 py-2 ml-4 text-sm font-medium rounded-lg transition-colors" :class="isActive('/employees/payroll') ? 'bg-purple-500 text-gray-900' : 'text-purple-300 hover:bg-gray-800'">
                        <CreditCardIcon class="w-4 h-4 flex-shrink-0" />
                        <span class="ml-3">Paie</span>
                    </router-link>
                </div>
            </div>

            <!-- Utilisateurs -->
            <router-link 
                to="/users"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive('/users') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
            >
                <KeyIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Utilisateurs</span>
            </router-link>

            <!-- Magasins -->
            <router-link 
                to="/magasins"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive('/magasins') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
            >
                <BuildingOfficeIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Magasins</span>
            </router-link>

            <!-- Paramètres -->
            <router-link 
                to="/settings"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive('/settings') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
            >
                <Cog6ToothIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Paramètres</span>
            </router-link>

            <!-- Assistance -->
            <router-link 
                to="/assistance"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors"
                :class="isActive('/assistance') ? 'bg-primary-500 text-gray-900' : 'text-gray-300 hover:bg-gray-800'"
            >
                <ChartBarIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">Assistance</span>
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
import { ref, reactive, computed } from 'vue'
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
    KeyIcon,
    AdjustmentsHorizontalIcon,
    FolderIcon,
    WrenchScrewdriverIcon,
    ClockIcon,
    ClipboardDocumentCheckIcon,
    CalendarDaysIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    collapsed: Boolean,
    isMobile: Boolean,
    mobileOpen: Boolean
})

defineEmits(['toggle', 'close'])

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const sidebarClasses = computed(() => {
    const widthClass = props.isMobile
        ? 'w-72 max-w-[85vw]'
        : (props.collapsed ? 'w-20' : 'w-64')
    const translateClass = props.isMobile
        ? (props.mobileOpen ? 'translate-x-0' : '-translate-x-full')
        : 'translate-x-0'
    return `${widthClass} ${translateClass}`
})

// Collapsible sections state
const expandedSections = reactive({
    articles: false,
    production: false,
    ventesClients: false,
    achats: false,
    finance: false,
    agenda: false,
    employees: false,
})

// Toggle section expand/collapse
function toggleSection(section) {
    expandedSections[section] = !expandedSections[section]
}

function isActive(path, { exact = false } = {}) {
    if (exact) {
        return route.path === path
    }
    return route.path === path || route.path.startsWith(path + '/')
}

async function handleLogout() {
    await authStore.logout()
    router.push('/login')
}
</script>
