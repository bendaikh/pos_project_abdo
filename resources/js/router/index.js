import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useOfflineStore } from '../stores/offline'

// Layouts
import MainLayout from '../components/layout/MainLayout.vue'

// Views
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import PosView from '../views/PosView.vue'
import ArticlesList from '../views/articles/ArticlesList.vue'
import ArticleForm from '../views/articles/ArticleForm.vue'
import CategoriesList from '../views/categories/CategoriesList.vue'
import OptionsList from '../views/options/OptionsList.vue'
import OptionForm from '../views/options/OptionForm.vue'
import OptionVariants from '../views/optionsVariants/OptionVariants.vue'
import StockView from '../views/stock/StockView.vue'
import LossesView from '../views/losses/LossesView.vue'
import LossHistoryView from '../views/losses/LossHistoryView.vue'
import ProductionEntryView from '../views/production/ProductionEntryView.vue'
import MaterialConsumptionView from '../views/production/MaterialConsumptionView.vue'
import ConsumptionHistoryView from '../views/production/ConsumptionHistoryView.vue'
import ProductionCostView from '../views/production/ProductionCostView.vue'
import CustomersList from '../views/customers/CustomersList.vue'
import CustomerDetail from '../views/customers/CustomerDetail.vue'
import EmployeesList from '../views/employees/EmployeesList.vue'
import EmployeeDetail from '../views/employees/EmployeeDetail.vue'
import PayrollList from '../views/employees/PayrollList.vue'
import ReportsView from '../views/reports/ReportsView.vue'
import SettingsView from '../views/settings/SettingsView.vue'

// VENTE Views
import DevisList from '../views/vente/DevisList.vue'
import BonLivraisonList from '../views/vente/BonLivraisonList.vue'
import FactureList from '../views/vente/FactureList.vue'
import CommandesList from '../views/vente/CommandesList.vue'
import CommandeForm from '../views/vente/CommandeForm.vue'
import CommandeDetail from '../views/vente/CommandeDetail.vue'
import LivreursList from '../views/livreurs/LivreursList.vue'
import LivreurForm from '../views/livreurs/LivreurForm.vue'

// ACHAT Views
import BonCommandeList from '../views/achat/BonCommandeList.vue'
import BonCommandeDetail from '../views/achat/BonCommandeDetail.vue'
import ReceptionMarchandiseList from '../views/achat/ReceptionMarchandiseList.vue'
import FactureFournisseurList from '../views/achat/FactureFournisseurList.vue'
import HistoriqueAchatsList from '../views/achat/HistoriqueAchatsList.vue'

// FINANCE Views
import JournalCaisseList from '../views/finance/JournalCaisseList.vue'
import DepensesList from '../views/finance/DepensesList.vue'
import BilanView from '../views/finance/BilanView.vue'

// FOURNISSEURS Views
import FournisseursList from '../views/fournisseurs/FournisseursList.vue'
import FournisseurDetail from '../views/fournisseurs/FournisseurDetail.vue'
import FournisseurEdit from '../views/fournisseurs/FournisseurEdit.vue'

// UTILISATEURS Views
import UtilisateursList from '../views/utilisateurs/UtilisateursList.vue'

// ACTIVITES Views
import ActivitiesList from '../views/activites/ActivitiesList.vue'

// AGENDA Views
import CalendarView from '../views/agenda/CalendarView.vue'
import AppointmentsList from '../views/agenda/AppointmentsList.vue'
import TasksList from '../views/agenda/TasksList.vue'

// SUIVI ENCAISSEMENT Views
import SuiviEncaissement from '../views/SuiviEncaissement.vue'

// MAGASINS Views
import MagasinsList from '../views/magasins/MagasinsList.vue'

// ASSISTANCE Views
import AssistanceList from '../views/assistance/AssistanceList.vue'

const routes = [
    {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: { guest: true }
    },
    {
        path: '/',
        component: MainLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                redirect: '/dashboard'
            },
            {
                path: 'dashboard',
                name: 'dashboard',
                component: DashboardView
            },
            {
                path: 'pos',
                name: 'pos',
                component: PosView,
                meta: { allowOfflineAccess: true }
            },
            {
                path: 'articles',
                name: 'articles',
                component: ArticlesList
            },
            {
                path: 'articles/create',
                name: 'articles.create',
                component: ArticleForm
            },
            {
                path: 'articles/:id/edit',
                name: 'articles.edit',
                component: ArticleForm
            },
            {
                path: 'categories',
                name: 'categories',
                component: CategoriesList
            },
            {
                path: 'options',
                name: 'options',
                component: OptionsList
            },
            {
                path: 'options/create',
                name: 'options.create',
                component: OptionForm
            },
            {
                path: 'options/:id/edit',
                name: 'options.edit',
                component: OptionForm
            },
            {
                path: 'options-variants',
                name: 'options-variants',
                component: OptionVariants
            },
            {
                path: 'stock',
                name: 'stock',
                component: StockView
            },
            {
                path: 'losses',
                name: 'losses',
                component: LossesView
            },
            {
                path: 'losses/history',
                name: 'losses.history',
                component: LossHistoryView
            },
            {
                path: 'production',
                name: 'production',
                component: ProductionEntryView
            },
            {
                path: 'production/consumption',
                name: 'production.consumption',
                component: MaterialConsumptionView
            },
            {
                path: 'production/history',
                name: 'production.history',
                component: ConsumptionHistoryView
            },
            {
                path: 'production/costs',
                name: 'production.costs',
                component: ProductionCostView
            },
            {
                path: 'customers',
                name: 'customers',
                component: CustomersList
            },
            {
                path: 'customers/:id',
                name: 'customers.detail',
                component: CustomerDetail
            },
            {
                path: 'employees/payroll',
                name: 'employees.payroll',
                component: PayrollList
            },
            {
                path: 'employees',
                name: 'employees',
                component: EmployeesList
            },
            {
                path: 'employees/:id',
                name: 'employees.detail',
                component: EmployeeDetail
            },
            {
                path: 'reports',
                name: 'reports',
                component: ReportsView
            },
            {
                path: 'settings',
                name: 'settings',
                component: SettingsView
            },
            // VENTE Routes
            {
                path: 'devis',
                name: 'devis',
                component: DevisList
            },
            {
                path: 'bon-livraison',
                name: 'bon-livraison',
                component: BonLivraisonList
            },
            {
                path: 'facture',
                name: 'facture',
                component: FactureList
            },
            {
                path: 'commandes',
                name: 'commandes',
                component: CommandesList
            },
            {
                path: 'commandes/create',
                name: 'commandes.create',
                component: CommandeForm
            },
            {
                path: 'commandes/:id',
                name: 'commandes.detail',
                component: CommandeDetail
            },
            {
                path: 'livreurs',
                name: 'livreurs',
                component: LivreursList
            },
            {
                path: 'livreurs/create',
                name: 'livreurs.create',
                component: LivreurForm
            },
            {
                path: 'livreurs/:id/edit',
                name: 'livreurs.edit',
                component: LivreurForm
            },
            // ACHAT Routes
            {
                path: 'bon-commande',
                name: 'bon-commande',
                component: BonCommandeList
            },
            {
                path: 'bon-commande/:id',
                name: 'bon-commande.detail',
                component: BonCommandeDetail
            },
            {
                path: 'reception-marchandise',
                name: 'reception-marchandise',
                component: ReceptionMarchandiseList
            },
            {
                path: 'facture-fournisseur',
                name: 'facture-fournisseur',
                component: FactureFournisseurList
            },
            {
                path: 'historique-achats',
                name: 'historique-achats',
                component: HistoriqueAchatsList
            },
            // FINANCE Routes
            {
                path: 'journal-caisse',
                name: 'journal-caisse',
                component: JournalCaisseList
            },
            {
                path: 'depenses',
                name: 'depenses',
                component: DepensesList
            },
            {
                path: 'bilan',
                name: 'bilan',
                component: BilanView
            },
            // FOURNISSEURS Routes
            {
                path: 'fournisseurs',
                name: 'fournisseurs',
                component: FournisseursList
            },
            {
                path: 'fournisseurs/:id',
                name: 'fournisseurs.detail',
                component: FournisseurDetail
            },
            {
                path: 'fournisseurs/:id/edit',
                name: 'fournisseurs.edit',
                component: FournisseurEdit
            },
            // UTILISATEURS Routes
            {
                path: 'users',
                name: 'users',
                component: UtilisateursList,
                alias: '/utilisateurs'
            },
            // ACTIVITES Routes
            {
                path: 'activites',
                name: 'activites',
                component: ActivitiesList
            },
            // AGENDA Routes
            {
                path: 'agenda',
                name: 'agenda-calendar',
                component: CalendarView
            },
            {
                path: 'agenda/appointments',
                name: 'agenda-appointments',
                component: AppointmentsList
            },
            {
                path: 'agenda/tasks',
                name: 'agenda-tasks',
                component: TasksList
            },
            // SUIVI ENCAISSEMENT Routes
            {
                path: 'suivi-encaissement',
                name: 'suivi-encaissement',
                component: SuiviEncaissement
            },
            // MAGASINS Routes
            {
                path: 'magasins',
                name: 'magasins',
                component: MagasinsList
            },
            // ASSISTANCE Routes
            {
                path: 'assistance',
                name: 'assistance',
                component: AssistanceList
            }
        ]
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore()
    await authStore.initAuth()
    
    // Check if user is in offline guest mode (from localStorage directly for reliability)
    const isOfflineGuestMode = localStorage.getItem('offline_guest_mode') === 'true'
    
    // Allow POS access for offline guest mode
    if (to.meta.allowOfflineAccess && isOfflineGuestMode) {
        console.log('Allowing offline guest access to POS')
        // Ensure offline guest mode is set in store
        if (!authStore.offlineGuestMode) {
            authStore.setOfflineGuestMode()
        }
        next()
        return
    }
    
    // Check authentication
    const isAuth = authStore.isAuthenticated || isOfflineGuestMode
    
    if (to.meta.requiresAuth && !isAuth) {
        next('/login')
    } else if (to.meta.guest && isAuth) {
        next('/dashboard')
    } else {
        next()
    }
})

export default router
