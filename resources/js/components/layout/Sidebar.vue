<template>
    <aside 
        class="sidebar-shell fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300"
        :class="sidebarClasses"
    >
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-white/10">
            <div class="flex items-center space-x-3">
                <div class="logo-badge w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

            <!-- Gestion Achats -->
            <div class="nav-section">
                <button @click="toggleSection('achats')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--amber"><ShoppingCartIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="text-xs font-semibold uppercase tracking-wider text-slate-300 whitespace-nowrap">Gestion Achats</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.achats }" />
                </button>
                <div v-show="expandedSections.achats && !collapsed" class="nav-submenu">
                    <router-link to="/fournisseurs" :class="subLinkClass('/fournisseurs', 'amber')"><BuildingOfficeIcon class="w-4 h-4" /><span>Fournisseurs</span></router-link>
                    <router-link to="/bon-commande" :class="subLinkClass('/bon-commande', 'amber')"><ClipboardDocumentListIcon class="w-4 h-4" /><span>Bon de commande</span></router-link>
                    <router-link to="/reception-marchandise" :class="subLinkClass('/reception-marchandise', 'amber')"><InboxArrowDownIcon class="w-4 h-4" /><span>Réception de Marchandise</span></router-link>
                    <router-link to="/facture-fournisseur" :class="subLinkClass('/facture-fournisseur', 'amber')"><DocumentIcon class="w-4 h-4" /><span>Facture fournisseur</span></router-link>
                    <router-link to="/historique-achats" :class="subLinkClass('/historique-achats', 'amber')"><ClockIcon class="w-4 h-4" /><span>Historique d'achats</span></router-link>
                </div>
            </div>

            <!-- Gestion de Stock -->
            <div class="nav-section">
                <button @click="toggleSection('stock')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--cyan"><ArchiveBoxIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="text-xs font-semibold uppercase tracking-wider text-slate-300 whitespace-nowrap">Gestion de Stock</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.stock }" />
                </button>
                <div v-show="expandedSections.stock && !collapsed" class="nav-submenu">
                    <router-link to="/stock" :class="subLinkClass('/stock', 'cyan')"><ArchiveBoxIcon class="w-4 h-4" /><span>Stock</span></router-link>
                    <router-link to="/articles" :class="subLinkClass('/articles', 'cyan')"><ClipboardDocumentListIcon class="w-4 h-4" /><span>Articles</span></router-link>
                    <router-link to="/categories" :class="subLinkClass('/categories', 'cyan')"><FolderIcon class="w-4 h-4" /><span>Catégories</span></router-link>
                    <router-link to="/options" :class="subLinkClass('/options', 'cyan')"><AdjustmentsHorizontalIcon class="w-4 h-4" /><span class="whitespace-nowrap">Options & Variantes</span></router-link>
                </div>
            </div>

            <!-- Production -->
            <div class="nav-section">
                <button @click="toggleSection('production')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--indigo"><WrenchScrewdriverIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="text-xs font-semibold uppercase tracking-wider text-slate-300 whitespace-nowrap">Production</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.production }" />
                </button>
                <div v-show="expandedSections.production && !collapsed" class="nav-submenu">
                    <router-link to="/production" :class="subLinkClass('/production', 'indigo', true)"><ClipboardDocumentListIcon class="w-4 h-4" /><span>Entrée production</span></router-link>
                    <router-link to="/production/consumption" :class="subLinkClass('/production/consumption', 'indigo')"><BeakerIcon class="w-4 h-4" /><span>Consommation MP</span></router-link>
                    <router-link to="/production/history" :class="subLinkClass('/production/history', 'indigo')"><ChartBarIcon class="w-4 h-4" /><span>Historique</span></router-link>
                    <router-link to="/production/costs" :class="subLinkClass('/production/costs', 'indigo')"><PresentationChartLineIcon class="w-4 h-4" /><span>Coût de production</span></router-link>
                    <div class="nav-subgroup">
                        <p class="nav-subgroup-label">Gestion de perte</p>
                        <router-link to="/losses" :class="subLinkClass('/losses', 'rose', true)"><ClipboardDocumentCheckIcon class="w-4 h-4" /><span>Déclaration de perte</span></router-link>
                        <router-link to="/losses/history" :class="subLinkClass('/losses/history', 'rose')"><ClockIcon class="w-4 h-4" /><span>Historique des pertes</span></router-link>
                    </div>
                </div>
            </div>

            <!-- Ventes & Clients -->
            <div class="nav-section">
                <button @click="toggleSection('ventesClients')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--emerald"><CurrencyDollarIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="text-xs font-semibold uppercase tracking-wider text-slate-300 whitespace-nowrap">Ventes & Clients</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.ventesClients }" />
                </button>
                <div v-show="expandedSections.ventesClients && !collapsed" class="nav-submenu">
                    <router-link to="/commandes" :class="subLinkClass('/commandes', 'emerald')"><ClipboardDocumentListIcon class="w-4 h-4" /><span>Commandes clients</span></router-link>
                    <router-link to="/devis" :class="subLinkClass('/devis', 'emerald')"><DocumentTextIcon class="w-4 h-4" /><span>Devis</span></router-link>
                    <router-link to="/bon-livraison" :class="subLinkClass('/bon-livraison', 'emerald')"><TruckIcon class="w-4 h-4" /><span>Bon de livraison</span></router-link>
                    <router-link to="/livreurs" :class="subLinkClass('/livreurs', 'emerald')"><UserIcon class="w-4 h-4" /><span>Livreurs</span></router-link>
                    <router-link to="/facture" :class="subLinkClass('/facture', 'emerald')"><DocumentIcon class="w-4 h-4" /><span>Facture</span></router-link>
                    <router-link to="/customers" :class="subLinkClass('/customers', 'emerald')"><UserGroupIcon class="w-4 h-4" /><span>Clients</span></router-link>
                </div>
            </div>

            <!-- Gestion Financière -->
            <div class="nav-section">
                <button @click="toggleSection('finance')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--sky"><BanknotesIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="text-xs font-semibold uppercase tracking-wider text-slate-300 whitespace-nowrap">Gestion Financière</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.finance }" />
                </button>
                <div v-show="expandedSections.finance && !collapsed" class="nav-submenu">
                    <router-link to="/journal-caisse" :class="subLinkClass('/journal-caisse', 'sky')"><BookOpenIcon class="w-4 h-4" /><span>Journal de caisse</span></router-link>
                    <router-link to="/depenses" :class="subLinkClass('/depenses', 'sky')"><CreditCardIcon class="w-4 h-4" /><span>Dépenses</span></router-link>
                    <router-link to="/bilan" :class="subLinkClass('/bilan', 'sky')"><PresentationChartLineIcon class="w-4 h-4" /><span>Bilan</span></router-link>
                    <router-link to="/suivi-encaissement" :class="subLinkClass('/suivi-encaissement', 'sky')"><BanknotesIcon class="w-4 h-4" /><span>Suivi Encaissement</span></router-link>
                    <router-link to="/historique-ticket" :class="subLinkClass('/historique-ticket', 'sky')"><ReceiptPercentIcon class="w-4 h-4" /><span>Historique ticket</span></router-link>
                </div>
            </div>

            <!-- Agenda -->
            <div class="nav-section">
                <button @click="toggleSection('agenda')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--blue"><CalendarDaysIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="text-xs font-semibold uppercase tracking-wider text-slate-300 whitespace-nowrap">Agenda</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.agenda }" />
                </button>
                <div v-show="expandedSections.agenda && !collapsed" class="nav-submenu">
                    <router-link to="/agenda" :class="subLinkClass('/agenda', 'blue')"><CalendarDaysIcon class="w-4 h-4" /><span>Calendrier</span></router-link>
                    <router-link to="/agenda/appointments" :class="subLinkClass('/agenda/appointments', 'blue')"><CalendarIcon class="w-4 h-4" /><span>Rendez-vous</span></router-link>
                    <router-link to="/agenda/tasks" :class="subLinkClass('/agenda/tasks', 'blue')"><CheckCircleIcon class="w-4 h-4" /><span>Tâches</span></router-link>
                    <router-link to="/agenda/incidents" :class="subLinkClass('/agenda/incidents', 'blue')"><TicketIcon class="w-4 h-4" /><span>Tickets Incidents</span></router-link>
                </div>
            </div>

            <!-- Employés -->
            <div class="nav-section">
                <button @click="toggleSection('employees')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--violet"><UsersIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="text-xs font-semibold uppercase tracking-wider text-slate-300 whitespace-nowrap">Employés</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.employees }" />
                </button>
                <div v-show="expandedSections.employees && !collapsed" class="nav-submenu">
                    <router-link to="/employees" :class="subLinkClass('/employees', 'violet', true)"><UsersIcon class="w-4 h-4" /><span>Gestion des employés</span></router-link>
                    <router-link to="/employees/payroll" :class="subLinkClass('/employees/payroll', 'violet')"><CreditCardIcon class="w-4 h-4" /><span>Paie</span></router-link>
                </div>
            </div>

            <!-- Paramètres -->
            <div class="nav-section">
                <button @click="toggleSection('parametres')" class="nav-section-btn">
                    <div class="flex items-center min-w-0 gap-2.5">
                        <span class="nav-section-icon nav-section-icon--slate"><Cog6ToothIcon class="w-4 h-4" /></span>
                        <span v-if="!collapsed" class="text-xs font-semibold uppercase tracking-wider text-slate-300 whitespace-nowrap">Paramètres</span>
                    </div>
                    <ChevronDownIcon v-if="!collapsed" class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': expandedSections.parametres }" />
                </button>
                <div v-show="expandedSections.parametres && !collapsed" class="nav-submenu">
                    <router-link to="/settings" :class="subLinkClass('/settings', 'slate', true)"><Cog6ToothIcon class="w-4 h-4" /><span>Général</span></router-link>
                    <router-link to="/users" :class="subLinkClass('/users', 'slate')"><KeyIcon class="w-4 h-4" /><span>Utilisateurs</span></router-link>
                    <router-link to="/magasins" :class="subLinkClass('/magasins', 'slate')"><BuildingStorefrontIcon class="w-4 h-4" /><span>Magasins</span></router-link>
                    <router-link to="/assistance" :class="subLinkClass('/assistance', 'slate')"><LifebuoyIcon class="w-4 h-4" /><span>Assistance</span></router-link>
                    <router-link to="/settings/imprimantes" :class="subLinkClass('/settings/imprimantes', 'slate')"><PrinterIcon class="w-4 h-4" /><span>Billetterie</span></router-link>
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

const expandedSections = reactive({
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
    background: linear-gradient(180deg, #0f172a 0%, #0c1524 55%, #0a101c 100%);
    border-right: 1px solid rgba(34, 211, 238, 0.12);
    box-shadow: 4px 0 24px rgba(0, 0, 0, 0.25);
}

.logo-badge {
    background: linear-gradient(135deg, #22d3ee 0%, #06b6d4 50%, #0891b2 100%);
    box-shadow: 0 4px 14px rgba(6, 182, 212, 0.35);
}

.sidebar-scroll::-webkit-scrollbar { width: 4px; }
.sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.3); border-radius: 4px; }

/* Featured nav cards */
.nav-feature {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.06);
    transition: all 0.25s ease;
    text-decoration: none;
}

.nav-feature--idle {
    background: rgba(255, 255, 255, 0.03);
}
.nav-feature--idle:hover {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.1);
}

.nav-feature--dashboard-active {
    background: linear-gradient(135deg, rgba(34, 211, 238, 0.18) 0%, rgba(6, 182, 212, 0.08) 100%);
    border-color: rgba(34, 211, 238, 0.35);
    box-shadow: 0 4px 20px rgba(6, 182, 212, 0.15);
}

.nav-feature--pos-active {
    background: linear-gradient(135deg, rgba(34, 211, 238, 0.18) 0%, rgba(6, 182, 212, 0.08) 100%);
    border-color: rgba(34, 211, 238, 0.35);
    box-shadow: 0 4px 20px rgba(6, 182, 212, 0.15);
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
    background: linear-gradient(135deg, #22d3ee 0%, #0891b2 100%);
    color: #0f172a;
    box-shadow: 0 4px 12px rgba(34, 211, 238, 0.4);
}

.nav-feature-icon--pos {
    background: linear-gradient(135deg, #22d3ee 0%, #0891b2 100%);
    color: #0f172a;
    box-shadow: 0 4px 12px rgba(34, 211, 238, 0.4);
}

.nav-icon-only {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 10px;
    border-radius: 10px;
    color: #94a3b8;
    transition: all 0.2s;
}
.nav-icon-only:hover { background: rgba(255,255,255,0.06); color: #e2e8f0; }
.nav-icon-only--dashboard { background: rgba(34,211,238,0.15); color: #22d3ee; }
.nav-icon-only--pos { background: rgba(34,211,238,0.15); color: #22d3ee; }

/* Section headers */
.nav-section { margin-top: 6px; }

.nav-section-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 8px 10px;
    border-radius: 10px;
    transition: background 0.2s;
}
.nav-section-btn:hover { background: rgba(255, 255, 255, 0.05); }

.nav-section-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.nav-section-icon--cyan    { background: rgba(34, 211, 238, 0.12); color: #22d3ee; }
.nav-section-icon--indigo  { background: rgba(129, 140, 248, 0.12); color: #818cf8; }
.nav-section-icon--emerald { background: rgba(52, 211, 153, 0.12); color: #34d399; }
.nav-section-icon--amber   { background: rgba(251, 191, 36, 0.12); color: #fbbf24; }
.nav-section-icon--sky     { background: rgba(56, 189, 248, 0.12); color: #38bdf8; }
.nav-section-icon--blue    { background: rgba(96, 165, 250, 0.12); color: #60a5fa; }
.nav-section-icon--violet  { background: rgba(167, 139, 250, 0.12); color: #a78bfa; }
.nav-section-icon--slate   { background: rgba(148, 163, 184, 0.12); color: #94a3b8; }

/* Submenu links */
.nav-submenu {
    margin-top: 4px;
    margin-left: 8px;
    padding-left: 12px;
    border-left: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.nav-sublink {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 7px 10px;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 500;
    transition: all 0.2s;
    text-decoration: none;
}

.nav-sublink--idle {
    color: #94a3b8;
}
.nav-sublink--idle:hover {
    color: #e2e8f0;
    background: rgba(255, 255, 255, 0.05);
}

.nav-sublink--cyan-active    { color: #67e8f9; background: rgba(34, 211, 238, 0.12); box-shadow: inset 3px 0 0 #22d3ee; }
.nav-sublink--indigo-active  { color: #a5b4fc; background: rgba(129, 140, 248, 0.12); box-shadow: inset 3px 0 0 #818cf8; }
.nav-sublink--emerald-active { color: #6ee7b7; background: rgba(52, 211, 153, 0.12); box-shadow: inset 3px 0 0 #34d399; }
.nav-sublink--amber-active   { color: #fcd34d; background: rgba(251, 191, 36, 0.12); box-shadow: inset 3px 0 0 #fbbf24; }
.nav-sublink--sky-active     { color: #7dd3fc; background: rgba(56, 189, 248, 0.12); box-shadow: inset 3px 0 0 #38bdf8; }
.nav-sublink--blue-active    { color: #93c5fd; background: rgba(96, 165, 250, 0.12); box-shadow: inset 3px 0 0 #60a5fa; }
.nav-sublink--violet-active  { color: #c4b5fd; background: rgba(167, 139, 250, 0.12); box-shadow: inset 3px 0 0 #a78bfa; }
.nav-sublink--slate-active   { color: #cbd5e1; background: rgba(148, 163, 184, 0.12); box-shadow: inset 3px 0 0 #94a3b8; }
.nav-sublink--rose-active    { color: #fda4af; background: rgba(251, 113, 133, 0.12); box-shadow: inset 3px 0 0 #fb7185; }

.nav-subgroup {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.nav-subgroup-label {
    padding: 0 10px 4px;
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: #64748b;
}

.nav-logout {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #f87171;
    transition: all 0.2s;
}
.nav-logout:hover {
    background: rgba(248, 113, 113, 0.1);
    color: #fca5a5;
}
</style>
