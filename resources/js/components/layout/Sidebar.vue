<template>
    <aside 
        class="sidebar-shell fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300"
        :class="sidebarClasses"
    >
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-white/10">
            <div class="flex items-center space-x-3">
                <div class="logo-badge w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <span v-if="!collapsed" class="text-xl font-bold tracking-wide text-white">GREENPOS</span>
            </div>
            <button
                v-if="isMobile"
                type="button"
                class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
                @click="$emit('close')"
            >
                ✕
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 min-h-0 px-3 py-4 space-y-1 overflow-y-auto sidebar-scroll">
            <!-- Accès rapides -->
            <div v-if="!collapsed" class="space-y-2 mb-4 pb-4 border-b border-white/10">
                <p class="px-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Accès rapide</p>

                <router-link
                    to="/dashboard"
                    class="nav-feature"
                    :class="isActive('/dashboard') ? 'nav-feature--dashboard-active' : 'nav-feature--idle'"
                >
                    <div class="nav-feature-icon nav-feature-icon--dashboard">
                        <ChartPieIcon class="w-5 h-5" />
                    </div>
                    <div v-if="!collapsed" class="min-w-0">
                        <p class="text-sm font-semibold text-white truncate">Dashboard</p>
                        <p class="text-[11px] text-slate-400 truncate">Vue d'ensemble & statistiques</p>
                    </div>
                </router-link>

                <router-link
                    to="/pos"
                    class="nav-feature"
                    :class="isActive('/pos') ? 'nav-feature--pos-active' : 'nav-feature--idle'"
                >
                    <div class="nav-feature-icon nav-feature-icon--pos">
                        <ComputerDesktopIcon class="w-5 h-5" />
                    </div>
                    <div v-if="!collapsed" class="min-w-0">
                        <p class="text-sm font-semibold text-white truncate">Point de Vente (PDV)</p>
                        <p class="text-[11px] text-slate-400 truncate">Caisse & encaissement</p>
                    </div>
                </router-link>
            </div>

            <!-- Collapsed quick icons -->
            <template v-else>
                <router-link
                    to="/dashboard"
                    class="nav-icon-only"
                    :class="isActive('/dashboard') ? 'nav-icon-only--dashboard' : ''"
                    title="Dashboard"
                >
                    <ChartPieIcon class="w-5 h-5" />
                </router-link>
                <router-link
                    to="/pos"
                    class="nav-icon-only"
                    :class="isActive('/pos') ? 'nav-icon-only--pos' : ''"
                    title="Point de Vente (PDV)"
                >
                    <ComputerDesktopIcon class="w-5 h-5" />
                </router-link>
            </template>

            <!-- Administration -->
            <div class="nav-section nav-section--featured" :class="{ 'nav-section--open': expandedSections.administration && !collapsed }">
                <button @click="toggleSection('administration')" class="nav-section-btn nav-section-btn--admin">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--admin"><ShieldCheckIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="nav-section-title">Administration</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="nav-chevron" :class="{ 'rotate-180': expandedSections.administration }" />
                </button>
                <div v-show="expandedSections.administration && !collapsed" class="nav-submenu nav-submenu--tiles">
                    <router-link to="/fiche-pdv" :class="tileClass('/fiche-pdv', 'green')"><span class="nav-tile-icon nav-tile-icon--green"><BuildingStorefrontIcon class="w-4 h-4" /></span><span class="nav-tile-label">Fiche PDV</span></router-link>
                    <router-link to="/etat-paiement-pdv" :class="tileClass('/etat-paiement-pdv', 'orange')"><span class="nav-tile-icon nav-tile-icon--orange"><BanknotesIcon class="w-4 h-4" /></span><span class="nav-tile-label">État paiement PDV</span></router-link>
                    <router-link to="/menu-pdv" :class="tileClass('/menu-pdv', 'blue')"><span class="nav-tile-icon nav-tile-icon--blue"><Squares2X2Icon class="w-4 h-4" /></span><span class="nav-tile-label">Menu PDV</span></router-link>
                    <router-link to="/balance-pdv" :class="tileClass('/balance-pdv', 'lime')"><span class="nav-tile-icon nav-tile-icon--lime"><ScaleIcon class="w-4 h-4" /></span><span class="nav-tile-label">Balance PDV</span></router-link>
                </div>
            </div>

            <!-- Gestion Achats -->
            <div class="nav-section nav-section--featured nav-section--amber" :class="{ 'nav-section--open': expandedSections.achats && !collapsed }">
                <button @click="toggleSection('achats')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--amber"><ShoppingCartIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="nav-section-title">Gestion Achats</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="nav-chevron" :class="{ 'rotate-180': expandedSections.achats }" />
                </button>
                <div v-show="expandedSections.achats && !collapsed" class="nav-submenu nav-submenu--tiles">
                    <router-link to="/fournisseurs" :class="tileClass('/fournisseurs', 'orange')"><span class="nav-tile-icon nav-tile-icon--orange"><BuildingOfficeIcon class="w-4 h-4" /></span><span class="nav-tile-label">Fournisseurs</span></router-link>
                    <router-link to="/bon-achat" :class="tileClass('/bon-achat', 'green')"><span class="nav-tile-icon nav-tile-icon--green"><ClipboardDocumentListIcon class="w-4 h-4" /></span><span class="nav-tile-label">Bon Achat</span></router-link>
                    <router-link to="/reglement-frns" :class="tileClass('/reglement-frns', 'blue')"><span class="nav-tile-icon nav-tile-icon--blue"><BanknotesIcon class="w-4 h-4" /></span><span class="nav-tile-label">Règlement Frns</span></router-link>
                    <router-link to="/stock" :class="tileClass('/stock', 'lime')"><span class="nav-tile-icon nav-tile-icon--lime"><ArchiveBoxIcon class="w-4 h-4" /></span><span class="nav-tile-label">Stock</span></router-link>
                    <router-link to="/etat-sortie" :class="tileClass('/etat-sortie', 'orange')"><span class="nav-tile-icon nav-tile-icon--orange"><InboxArrowDownIcon class="w-4 h-4" /></span><span class="nav-tile-label">État Sortie</span></router-link>
                    <router-link to="/facture-fournisseur" :class="tileClass('/facture-fournisseur', 'green')"><span class="nav-tile-icon nav-tile-icon--green"><DocumentIcon class="w-4 h-4" /></span><span class="nav-tile-label">Facture fournisseur</span></router-link>
                    <router-link to="/balance-achats" :class="tileClass('/balance-achats', 'blue')"><span class="nav-tile-icon nav-tile-icon--blue"><ScaleIcon class="w-4 h-4" /></span><span class="nav-tile-label">Balance</span></router-link>
                </div>
            </div>

            <!-- Gestion de Stock -->
            <div class="nav-section nav-section--featured nav-section--green" :class="{ 'nav-section--open': expandedSections.stock && !collapsed }">
                <button @click="toggleSection('stock')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--cyan"><ArchiveBoxIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="nav-section-title">Gestion de Stock</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="nav-chevron" :class="{ 'rotate-180': expandedSections.stock }" />
                </button>
                <div v-show="expandedSections.stock && !collapsed" class="nav-submenu nav-submenu--tiles">
                    <router-link to="/fiche-produit" :class="tileClass('/fiche-produit', 'green')"><span class="nav-tile-icon nav-tile-icon--green"><ClipboardDocumentListIcon class="w-4 h-4" /></span><span class="nav-tile-label">Fiche produit</span></router-link>
                    <router-link to="/famille-produit" :class="tileClass('/famille-produit', 'lime')"><span class="nav-tile-icon nav-tile-icon--lime"><FolderIcon class="w-4 h-4" /></span><span class="nav-tile-label">Famille Produit</span></router-link>
                    <router-link to="/unites-mesure" :class="tileClass('/unites-mesure', 'blue')"><span class="nav-tile-icon nav-tile-icon--blue"><ScaleIcon class="w-4 h-4" /></span><span class="nav-tile-label">Unités Mesure</span></router-link>
                    <router-link to="/options" :class="tileClass('/options', 'orange')"><span class="nav-tile-icon nav-tile-icon--orange"><AdjustmentsHorizontalIcon class="w-4 h-4" /></span><span class="nav-tile-label">Options & Variantes</span></router-link>
                </div>
            </div>

            <!-- Production -->
            <div class="nav-section nav-section--featured nav-section--blue" :class="{ 'nav-section--open': expandedSections.production && !collapsed }">
                <button @click="toggleSection('production')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--indigo"><WrenchScrewdriverIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="nav-section-title">Production</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="nav-chevron" :class="{ 'rotate-180': expandedSections.production }" />
                </button>
                <div v-show="expandedSections.production && !collapsed" class="nav-submenu nav-submenu--tiles">
                    <router-link to="/production" :class="tileClass('/production', 'blue', true)"><span class="nav-tile-icon nav-tile-icon--blue"><ClipboardDocumentListIcon class="w-4 h-4" /></span><span class="nav-tile-label">Entrée production</span></router-link>
                    <router-link to="/production/consumption" :class="tileClass('/production/consumption', 'green')"><span class="nav-tile-icon nav-tile-icon--green"><BeakerIcon class="w-4 h-4" /></span><span class="nav-tile-label">Consommation MP</span></router-link>
                    <router-link to="/production/history" :class="tileClass('/production/history', 'lime')"><span class="nav-tile-icon nav-tile-icon--lime"><ChartBarIcon class="w-4 h-4" /></span><span class="nav-tile-label">Historique</span></router-link>
                    <router-link to="/production/costs" :class="tileClass('/production/costs', 'orange')"><span class="nav-tile-icon nav-tile-icon--orange"><PresentationChartLineIcon class="w-4 h-4" /></span><span class="nav-tile-label">Coût de production</span></router-link>
                    <p class="nav-tile-group-label">Gestion de perte</p>
                    <router-link to="/losses" :class="tileClass('/losses', 'rose', true)"><span class="nav-tile-icon nav-tile-icon--rose"><ClipboardDocumentCheckIcon class="w-4 h-4" /></span><span class="nav-tile-label">Déclaration de perte</span></router-link>
                    <router-link to="/losses/history" :class="tileClass('/losses/history', 'orange')"><span class="nav-tile-icon nav-tile-icon--orange"><ClockIcon class="w-4 h-4" /></span><span class="nav-tile-label">Historique des pertes</span></router-link>
                </div>
            </div>

            <!-- Ventes & Clients -->
            <div class="nav-section nav-section--featured nav-section--green" :class="{ 'nav-section--open': expandedSections.ventesClients && !collapsed }">
                <button @click="toggleSection('ventesClients')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--emerald"><CurrencyDollarIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="nav-section-title">Ventes & Clients</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="nav-chevron" :class="{ 'rotate-180': expandedSections.ventesClients }" />
                </button>
                <div v-show="expandedSections.ventesClients && !collapsed" class="nav-submenu nav-submenu--tiles">
                    <router-link to="/commandes" :class="tileClass('/commandes', 'green')"><span class="nav-tile-icon nav-tile-icon--green"><ClipboardDocumentListIcon class="w-4 h-4" /></span><span class="nav-tile-label">Commandes clients</span></router-link>
                    <router-link to="/devis" :class="tileClass('/devis', 'lime')"><span class="nav-tile-icon nav-tile-icon--lime"><DocumentTextIcon class="w-4 h-4" /></span><span class="nav-tile-label">Devis</span></router-link>
                    <router-link to="/bon-livraison" :class="tileClass('/bon-livraison', 'blue')"><span class="nav-tile-icon nav-tile-icon--blue"><TruckIcon class="w-4 h-4" /></span><span class="nav-tile-label">Bon de livraison</span></router-link>
                    <router-link to="/livreurs" :class="tileClass('/livreurs', 'orange')"><span class="nav-tile-icon nav-tile-icon--orange"><UserIcon class="w-4 h-4" /></span><span class="nav-tile-label">Livreurs</span></router-link>
                    <router-link to="/facture" :class="tileClass('/facture', 'green')"><span class="nav-tile-icon nav-tile-icon--green"><DocumentIcon class="w-4 h-4" /></span><span class="nav-tile-label">Facture</span></router-link>
                    <router-link to="/customers" :class="tileClass('/customers', 'blue')"><span class="nav-tile-icon nav-tile-icon--blue"><UserGroupIcon class="w-4 h-4" /></span><span class="nav-tile-label">Clients</span></router-link>
                </div>
            </div>

            <!-- Gestion Financière -->
            <div class="nav-section nav-section--featured nav-section--blue" :class="{ 'nav-section--open': expandedSections.finance && !collapsed }">
                <button @click="toggleSection('finance')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--sky"><BanknotesIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="nav-section-title">Gestion Financière</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="nav-chevron" :class="{ 'rotate-180': expandedSections.finance }" />
                </button>
                <div v-show="expandedSections.finance && !collapsed" class="nav-submenu nav-submenu--tiles">
                    <router-link to="/journal-caisse" :class="tileClass('/journal-caisse', 'blue')"><span class="nav-tile-icon nav-tile-icon--blue"><BookOpenIcon class="w-4 h-4" /></span><span class="nav-tile-label">Journal de caisse</span></router-link>
                    <router-link to="/depenses" :class="tileClass('/depenses', 'orange')"><span class="nav-tile-icon nav-tile-icon--orange"><CreditCardIcon class="w-4 h-4" /></span><span class="nav-tile-label">Dépenses</span></router-link>
                    <router-link to="/bilan" :class="tileClass('/bilan', 'green')"><span class="nav-tile-icon nav-tile-icon--green"><PresentationChartLineIcon class="w-4 h-4" /></span><span class="nav-tile-label">Bilan</span></router-link>
                    <router-link to="/suivi-encaissement" :class="tileClass('/suivi-encaissement', 'lime')"><span class="nav-tile-icon nav-tile-icon--lime"><BanknotesIcon class="w-4 h-4" /></span><span class="nav-tile-label">Suivi Encaissement</span></router-link>
                    <router-link to="/historique-ticket" :class="tileClass('/historique-ticket', 'blue')"><span class="nav-tile-icon nav-tile-icon--blue"><ReceiptPercentIcon class="w-4 h-4" /></span><span class="nav-tile-label">Historique ticket</span></router-link>
                </div>
            </div>

            <!-- Agenda -->
            <div class="nav-section nav-section--featured nav-section--blue" :class="{ 'nav-section--open': expandedSections.agenda && !collapsed }">
                <button @click="toggleSection('agenda')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--blue"><CalendarDaysIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="nav-section-title">Agenda</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="nav-chevron" :class="{ 'rotate-180': expandedSections.agenda }" />
                </button>
                <div v-show="expandedSections.agenda && !collapsed" class="nav-submenu nav-submenu--tiles">
                    <router-link to="/agenda" :class="tileClass('/agenda', 'blue', true)"><span class="nav-tile-icon nav-tile-icon--blue"><CalendarDaysIcon class="w-4 h-4" /></span><span class="nav-tile-label">Calendrier</span></router-link>
                    <router-link to="/agenda/appointments" :class="tileClass('/agenda/appointments', 'green')"><span class="nav-tile-icon nav-tile-icon--green"><CalendarIcon class="w-4 h-4" /></span><span class="nav-tile-label">Rendez-vous</span></router-link>
                    <router-link to="/agenda/tasks" :class="tileClass('/agenda/tasks', 'lime')"><span class="nav-tile-icon nav-tile-icon--lime"><CheckCircleIcon class="w-4 h-4" /></span><span class="nav-tile-label">Tâches</span></router-link>
                    <router-link to="/agenda/incidents" :class="tileClass('/agenda/incidents', 'orange')"><span class="nav-tile-icon nav-tile-icon--orange"><TicketIcon class="w-4 h-4" /></span><span class="nav-tile-label">Tickets Incidents</span></router-link>
                </div>
            </div>

            <!-- Employés -->
            <div class="nav-section nav-section--featured nav-section--amber" :class="{ 'nav-section--open': expandedSections.employees && !collapsed }">
                <button @click="toggleSection('employees')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--violet"><UsersIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="nav-section-title">Employés</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="nav-chevron" :class="{ 'rotate-180': expandedSections.employees }" />
                </button>
                <div v-show="expandedSections.employees && !collapsed" class="nav-submenu nav-submenu--tiles">
                    <router-link to="/employees" :class="tileClass('/employees', 'orange', true)"><span class="nav-tile-icon nav-tile-icon--orange"><UsersIcon class="w-4 h-4" /></span><span class="nav-tile-label">Gestion des employés</span></router-link>
                    <router-link to="/employees/payroll" :class="tileClass('/employees/payroll', 'green')"><span class="nav-tile-icon nav-tile-icon--green"><CreditCardIcon class="w-4 h-4" /></span><span class="nav-tile-label">Paie</span></router-link>
                </div>
            </div>

            <!-- Paramètres -->
            <div class="nav-section nav-section--featured nav-section--slate" :class="{ 'nav-section--open': expandedSections.parametres && !collapsed }">
                <button @click="toggleSection('parametres')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--slate"><Cog6ToothIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="nav-section-title">Paramètres</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="nav-chevron" :class="{ 'rotate-180': expandedSections.parametres }" />
                </button>
                <div v-show="expandedSections.parametres && !collapsed" class="nav-submenu nav-submenu--tiles">
                    <router-link to="/settings" :class="tileClass('/settings', 'slate', true)"><span class="nav-tile-icon nav-tile-icon--slate"><Cog6ToothIcon class="w-4 h-4" /></span><span class="nav-tile-label">Général</span></router-link>
                    <router-link to="/settings/imprimantes" :class="tileClass('/settings/imprimantes', 'blue')"><span class="nav-tile-icon nav-tile-icon--blue"><PrinterIcon class="w-4 h-4" /></span><span class="nav-tile-label">Billetterie</span></router-link>
                </div>
            </div>

            <!-- Logout -->
            <button @click="handleLogout" class="nav-logout mt-3">
                <ArrowRightOnRectangleIcon class="w-5 h-5 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-3">
                    {{ authStore.offlineGuestMode ? 'Quitter Mode Hors ligne' : 'Déconnexion' }}
                </span>
            </button>
        </nav>
    </aside>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import {
    ChartPieIcon,
    ComputerDesktopIcon,
    ClipboardDocumentListIcon,
    ArchiveBoxIcon,
    ChartBarIcon,
    Cog6ToothIcon,
    ArrowRightOnRectangleIcon,
    UserGroupIcon,
    UsersIcon,
    UserIcon,
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
    KeyIcon,
    AdjustmentsHorizontalIcon,
    FolderIcon,
    WrenchScrewdriverIcon,
    ClockIcon,
    ClipboardDocumentCheckIcon,
    CalendarDaysIcon,
    CalendarIcon,
    CheckCircleIcon,
    TicketIcon,
    BeakerIcon,
    InboxArrowDownIcon,
    ReceiptPercentIcon,
    LifebuoyIcon,
    PrinterIcon,
    ShieldCheckIcon,
    Squares2X2Icon,
    ScaleIcon,
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
        : (props.collapsed ? 'w-20' : 'w-72')
    const translateClass = props.isMobile
        ? (props.mobileOpen ? 'translate-x-0' : '-translate-x-full')
        : 'translate-x-0'
    return `${widthClass} ${translateClass}`
})

const expandedSections = reactive({
    administration: false,
    stock: false,
    production: false,
    ventesClients: false,
    achats: false,
    finance: false,
    agenda: false,
    employees: false,
    parametres: false,
})

const subLinkThemes = {
    admin:   { active: 'nav-sublink--admin-active',   idle: 'nav-sublink--idle' },
    cyan:    { active: 'nav-sublink--cyan-active',    idle: 'nav-sublink--idle' },
    indigo:  { active: 'nav-sublink--indigo-active',  idle: 'nav-sublink--idle' },
    emerald: { active: 'nav-sublink--emerald-active', idle: 'nav-sublink--idle' },
    amber:   { active: 'nav-sublink--amber-active',   idle: 'nav-sublink--idle' },
    sky:     { active: 'nav-sublink--sky-active',     idle: 'nav-sublink--idle' },
    blue:    { active: 'nav-sublink--blue-active',    idle: 'nav-sublink--idle' },
    violet:  { active: 'nav-sublink--violet-active',  idle: 'nav-sublink--idle' },
    slate:   { active: 'nav-sublink--slate-active',   idle: 'nav-sublink--idle' },
    rose:    { active: 'nav-sublink--rose-active',    idle: 'nav-sublink--idle' },
}

function toggleSection(section) {
    expandedSections[section] = !expandedSections[section]
}

function isActive(path, { exact = false } = {}) {
    if (exact) {
        return route.path === path
    }
    return route.path === path || route.path.startsWith(path + '/')
}

function tileClass(path, color = 'green', exact = false) {
    return isActive(path, { exact })
        ? `nav-tile nav-tile--active-${color}`
        : 'nav-tile'
}

function subLinkClass(path, theme, exact = false) {
    const t = subLinkThemes[theme] || subLinkThemes.cyan
    const base = 'nav-sublink'
    return isActive(path, { exact }) ? `${base} ${t.active}` : `${base} ${t.idle}`
}

async function handleLogout() {
    await authStore.logout()
    router.push('/login')
}
</script>

<style scoped>
.sidebar-shell {
    background:
        radial-gradient(ellipse 100% 55% at 0% 0%, rgba(124, 58, 237, 0.14), transparent 55%),
        radial-gradient(ellipse 90% 50% at 100% 100%, rgba(34, 211, 238, 0.1), transparent 50%),
        linear-gradient(180deg, #181b2a 0%, #141625 55%, #0c0d16 100%);
    border-right: 1px solid rgba(148, 163, 184, 0.12);
    box-shadow: 8px 0 32px rgba(0, 0, 0, 0.45);
}

.logo-badge {
    background: #1E2132;
    border: 1.5px solid #FB923C;
    color: #FB923C;
    box-shadow: 0 0 16px rgba(251, 146, 60, 0.35);
}

.sidebar-scroll::-webkit-scrollbar { width: 4px; }
.sidebar-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #22D3EE, #FB923C);
    border-radius: 4px;
}

.nav-feature {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 12px;
    border-radius: 12px;
    border: 1px solid rgba(148, 163, 184, 0.12);
    transition: all 0.25s ease;
    text-decoration: none;
}
.nav-feature--idle { background: rgba(30, 33, 50, 0.7); }
.nav-feature--idle:hover {
    background: rgba(34, 211, 238, 0.1);
    border-color: rgba(34, 211, 238, 0.35);
    transform: translateX(2px);
}
.nav-feature--dashboard-active {
    background: linear-gradient(135deg, rgba(34, 211, 238, 0.18) 0%, rgba(30, 33, 50, 0.9) 100%);
    border-color: rgba(34, 211, 238, 0.45);
    box-shadow: 0 6px 20px rgba(34, 211, 238, 0.15);
}
.nav-feature--pos-active {
    background: linear-gradient(135deg, rgba(251, 146, 60, 0.18) 0%, rgba(30, 33, 50, 0.9) 100%);
    border-color: rgba(251, 146, 60, 0.45);
    box-shadow: 0 6px 20px rgba(251, 146, 60, 0.15);
}

.nav-feature-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.nav-feature-icon--dashboard {
    background: rgba(34, 211, 238, 0.12);
    color: #22D3EE;
}
.nav-feature-icon--pos {
    background: rgba(251, 146, 60, 0.14);
    color: #FB923C;
}

.nav-icon-only {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 10px;
    border-radius: 12px;
    color: #94A3B8;
    transition: all 0.2s;
}
.nav-icon-only:hover { background: rgba(34,211,238,0.12); color: #FFFFFF; }
.nav-icon-only--dashboard { background: rgba(34,211,238,0.16); color: #22D3EE; }
.nav-icon-only--pos { background: rgba(251,146,60,0.16); color: #FB923C; }

.nav-section { margin-top: 8px; }

.nav-section--featured {
    padding: 6px;
    border-radius: 14px;
    border: 1px solid transparent;
    transition: all 0.3s ease;
}
.nav-section--featured.nav-section--open {
    background: rgba(30, 33, 50, 0.65);
    border-color: rgba(148, 163, 184, 0.14);
}
.nav-section--amber.nav-section--open { border-color: rgba(251, 146, 60, 0.3); }
.nav-section--green.nav-section--open { border-color: rgba(34, 211, 238, 0.28); }
.nav-section--blue.nav-section--open { border-color: rgba(34, 211, 238, 0.28); }
.nav-section--slate.nav-section--open { border-color: rgba(148, 163, 184, 0.2); }

.nav-section-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 9px 10px;
    border-radius: 12px;
    transition: all 0.2s;
}
.nav-section-btn:hover { background: rgba(255, 255, 255, 0.04); }
.nav-section-btn--admin:hover { background: rgba(34, 211, 238, 0.1); }

.nav-section-title {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #FFFFFF;
    white-space: nowrap;
}

.nav-chevron {
    width: 1rem;
    height: 1rem;
    color: #64748b;
    transition: transform 0.25s ease, color 0.2s;
}
.nav-section-btn:hover .nav-chevron { color: #22D3EE; }

.nav-section-icon {
    width: 30px;
    height: 30px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.nav-section-icon--cyan    { background: rgba(34, 211, 238, 0.14); color: #22D3EE; }
.nav-section-icon--indigo  { background: rgba(34, 211, 238, 0.14); color: #22D3EE; }
.nav-section-icon--emerald { background: rgba(34, 211, 238, 0.14); color: #22D3EE; }
.nav-section-icon--amber   { background: rgba(251, 146, 60, 0.16); color: #FB923C; }
.nav-section-icon--sky     { background: rgba(34, 211, 238, 0.14); color: #22D3EE; }
.nav-section-icon--blue    { background: rgba(34, 211, 238, 0.14); color: #22D3EE; }
.nav-section-icon--violet  { background: rgba(251, 146, 60, 0.14); color: #FB923C; }
.nav-section-icon--slate   { background: rgba(148, 163, 184, 0.12); color: #94A3B8; }
.nav-section-icon--admin   {
    background: #1E2132;
    border: 1px solid #FB923C;
    color: #FB923C;
    box-shadow: 0 0 12px rgba(251, 146, 60, 0.25);
}

.nav-submenu--tiles {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 8px;
    padding: 2px;
    border-left: none;
    margin-left: 0;
    padding-left: 0;
}

.nav-tile {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
    padding: 10px 10px 12px;
    border-radius: 12px;
    text-decoration: none;
    background: #1E2132;
    border: 1px solid rgba(148, 163, 184, 0.12);
    transition: all 0.22s ease;
}
.nav-tile:hover {
    transform: translateY(-2px);
    border-color: rgba(34, 211, 238, 0.35);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.3);
}
.nav-tile-label {
    font-size: 0.7rem;
    font-weight: 650;
    line-height: 1.25;
    color: #FFFFFF;
}
.nav-tile-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.nav-tile-icon--green  { background: rgba(34, 211, 238, 0.12); color: #22D3EE; }
.nav-tile-icon--orange { background: rgba(251, 146, 60, 0.14); color: #FB923C; }
.nav-tile-icon--blue   { background: rgba(34, 211, 238, 0.12); color: #22D3EE; }
.nav-tile-icon--lime   { background: rgba(34, 211, 238, 0.12); color: #22D3EE; }
.nav-tile-icon--rose   { background: rgba(244, 63, 94, 0.16); color: #fb7185; }
.nav-tile-icon--slate  { background: rgba(148, 163, 184, 0.12); color: #94A3B8; }

.nav-tile--active-green,
.nav-tile--active-blue,
.nav-tile--active-lime {
    border-color: rgba(34, 211, 238, 0.55);
    background: linear-gradient(160deg, rgba(34, 211, 238, 0.16), rgba(30, 33, 50, 0.95));
    box-shadow: 0 0 0 1px rgba(34, 211, 238, 0.15);
}
.nav-tile--active-orange {
    border-color: rgba(251, 146, 60, 0.55);
    background: linear-gradient(160deg, rgba(251, 146, 60, 0.16), rgba(30, 33, 50, 0.95));
}
.nav-tile--active-rose {
    border-color: rgba(244, 63, 94, 0.5);
    background: rgba(244, 63, 94, 0.12);
}
.nav-tile--active-slate {
    border-color: rgba(148, 163, 184, 0.4);
    background: rgba(148, 163, 184, 0.1);
}

.nav-tile-group-label {
    grid-column: 1 / -1;
    margin: 4px 2px 0;
    padding: 6px 4px 2px;
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #94A3B8;
    border-top: 1px solid rgba(148, 163, 184, 0.12);
}

.nav-logout {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 10px 12px;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #fb7185;
    border: 1px solid transparent;
    transition: all 0.2s;
}
.nav-logout:hover {
    background: rgba(244, 63, 94, 0.12);
    border-color: rgba(244, 63, 94, 0.3);
    color: #fda4af;
}
</style>