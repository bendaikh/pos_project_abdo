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
import StockView from '../views/stock/StockView.vue'
import CustomersList from '../views/customers/CustomersList.vue'
import EmployeesList from '../views/employees/EmployeesList.vue'
import ReportsView from '../views/reports/ReportsView.vue'
import SettingsView from '../views/settings/SettingsView.vue'

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
                path: 'stock',
                name: 'stock',
                component: StockView
            },
            {
                path: 'customers',
                name: 'customers',
                component: CustomersList
            },
            {
                path: 'employees',
                name: 'employees',
                component: EmployeesList
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
