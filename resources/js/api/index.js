import axios from 'axios'

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
})

// Request interceptor to add auth token
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('auth_token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    (error) => {
        return Promise.reject(error)
    }
)

// Response interceptor to handle errors
api.interceptors.response.use(
    (response) => response,
    (error) => {
        const isOfflineGuestMode = localStorage.getItem('offline_guest_mode') === 'true'
        
        if (error.response?.status === 401) {
            // Don't redirect if in offline guest mode and it's a 401 from a background call
            if (isOfflineGuestMode) {
                console.warn('401 error ignored in offline guest mode')
                return Promise.reject(error)
            }
            
            localStorage.removeItem('auth_token')
            localStorage.removeItem('auth_user')
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
)

export default api

// Auth API
export const authApi = {
    login: (credentials) => api.post('/login', credentials),
    logout: () => api.post('/logout'),
    user: () => api.get('/user'),
}

// Dashboard API
export const dashboardApi = {
    stats: () => api.get('/dashboard/stats'),
    salesChart: (days = 7) => api.get('/dashboard/sales-chart', { params: { days } }),
    topCategories: () => api.get('/dashboard/top-categories'),
    recentSales: () => api.get('/dashboard/recent-sales'),
    lowStock: () => api.get('/dashboard/low-stock'),
}

// Categories API
export const categoriesApi = {
    list: (params = {}) => api.get('/categories', { params }),
    get: (id) => api.get(`/categories/${id}`),
    create: (data) => api.post('/categories', data),
    update: (id, data) => api.put(`/categories/${id}`, data),
    delete: (id) => api.delete(`/categories/${id}`),
}

// Subcategories API
export const subcategoriesApi = {
    list: (params = {}) => api.get('/subcategories', { params }),
    get: (id) => api.get(`/subcategories/${id}`),
    create: (data) => api.post('/subcategories', data),
    update: (id, data) => api.put(`/subcategories/${id}`, data),
    delete: (id) => api.delete(`/subcategories/${id}`),
}

// Articles API
export const articlesApi = {
    list: (params = {}) => api.get('/articles', { params }),
    favorites: () => api.get('/articles/favorites'),
    lowStock: () => api.get('/articles/low-stock'),
    get: (id) => api.get(`/articles/${id}`),
    create: (data) => api.post('/articles', data),
    update: (id, data) => api.put(`/articles/${id}`, data),
    delete: (id) => api.delete(`/articles/${id}`),
    // Variants management
    listVariants: (articleId) => api.get(`/articles/${articleId}/variants`),
    getVariant: (articleId, variantId) => api.get(`/articles/${articleId}/variants/${variantId}`),
    createVariant: (articleId, data) => api.post(`/articles/${articleId}/variants`, data),
    updateVariant: (articleId, variantId, data) => api.put(`/articles/${articleId}/variants/${variantId}`, data),
    deleteVariant: (articleId, variantId) => api.delete(`/articles/${articleId}/variants/${variantId}`),
}

// Options API
export const optionsApi = {
    list: (params = {}) => api.get('/options', { params }),
    get: (id) => api.get(`/options/${id}`),
    create: (data) => api.post('/options', data),
    update: (id, data) => api.put(`/options/${id}`, data),
    delete: (id) => api.delete(`/options/${id}`),
    variants: (optionId, params = {}) => api.get(`/options/${optionId}/variants`, { params }),
    getVariant: (optionId, variantId) => api.get(`/options/${optionId}/variants/${variantId}`),
    createVariant: (optionId, data) => api.post(`/options/${optionId}/variants`, data),
    updateVariant: (optionId, variantId, data) => api.put(`/options/${optionId}/variants/${variantId}`, data),
    deleteVariant: (optionId, variantId) => api.delete(`/options/${optionId}/variants/${variantId}`),
}

// Sales API
export const salesApi = {
    list: (params = {}) => api.get('/sales', { params }),
    pending: () => api.get('/sales/pending'),
    get: (id) => api.get(`/sales/${id}`),
    create: (data) => api.post('/sales', data),
    update: (id, data) => api.put(`/sales/${id}`, data),
    updateStatus: (id, data) => api.post(`/sales/${id}/status`, data),
    journal: (id) => api.get(`/sales/${id}/journal`),
    returns: (id) => api.get(`/sales/${id}/returns`),
    addReturn: (id, data) => api.post(`/sales/${id}/returns`, data),
    complete: (id) => api.post(`/sales/${id}/complete`),
    cancel: (id) => api.post(`/sales/${id}/cancel`),
    delete: (id) => api.delete(`/sales/${id}`),
    addPayment: (saleId, data) => api.post(`/sales/${saleId}/payments`, data),
}

export const commandesApi = salesApi

// Customers API
export const customersApi = {
    list: (params = {}) => api.get('/customers', { params }),
    get: (id) => api.get(`/customers/${id}`),
    history: (id) => api.get(`/customers/${id}/history`),
    create: (data) => api.post('/customers', data),
    update: (id, data) => api.put(`/customers/${id}`, data),
    delete: (id) => api.delete(`/customers/${id}`),
}

// Employees API
export const employeesApi = {
    list: (params = {}) => api.get('/employees', { params }),
    get: (id) => api.get(`/employees/${id}`),
    create: (data) => api.post('/employees', data),
    update: (id, data) => api.put(`/employees/${id}`, data),
    delete: (id) => api.delete(`/employees/${id}`),
    payrollHistory: (id, params = {}) => api.get(`/employees/${id}/payroll-history`, { params }),
    attendanceSummary: (id, params = {}) => api.get(`/employees/${id}/attendance-summary`, { params }),
}

// Payroll API
export const payrollApi = {
    list: (params = {}) => api.get('/payrolls', { params }),
    get: (id) => api.get(`/payrolls/${id}`),
    store: (data) => api.post('/payrolls', data),
    update: (id, data) => api.put(`/payrolls/${id}`, data),
    delete: (id) => api.delete(`/payrolls/${id}`),
    processPayment: (id, data) => api.post(`/payrolls/${id}/process-payment`, data),
    statistics: (params = {}) => api.get('/payroll-statistics', { params }),
    preview: (data) => api.post('/payroll-preview', data),
    bulkCalculate: (data) => api.post('/payroll-bulk-calculate', data),
}

// Attendance API
export const attendanceApi = {
    list: (params = {}) => api.get('/attendances', { params }),
    get: (id) => api.get(`/attendances/${id}`),
    store: (data) => api.post('/attendances', data),
    update: (id, data) => api.put(`/attendances/${id}`, data),
    delete: (id) => api.delete(`/attendances/${id}`),
    bulk: (data) => api.post('/attendances/bulk', data),
    monthlySummary: (params = {}) => api.get('/attendances/summary/monthly', { params }),
}

// Stock API
export const stockApi = {
    list: (params = {}) => api.get('/stock', { params }),
    movements: (params = {}) => api.get('/stock/movements', { params }),
    recordMovement: (data) => api.post('/stock/movement', data),
    alerts: () => api.get('/stock/alerts'),
}

// Losses API
export const lossesApi = {
    reference: () => api.get('/losses/reference'),
    list: (params = {}) => api.get('/losses', { params }),
    create: (data) => api.post('/losses', data),
}

// Production API
export const productionApi = {
    list: (params = {}) => api.get('/production', { params }),
    get: (id) => api.get(`/production/${id}`),
    create: (data) => api.post('/production', data),
    update: (id, data) => api.put(`/production/${id}`, data),
    validate: (id) => api.post(`/production/${id}/validate`),
    delete: (id) => api.delete(`/production/${id}`),
}

// Material Consumption API
export const consumptionsApi = {
    list: (params = {}) => api.get('/consumptions', { params }),
    create: (data) => api.post('/consumptions', data),
}

// Settings API
export const settingsApi = {
    all: () => api.get('/settings'),
    byGroup: (group) => api.get(`/settings/${group}`),
    update: (settings) => api.put('/settings', { settings }),
}

// Reports API
export const reportsApi = {
    sales: (params = {}) => api.get('/reports/sales', { params }),
    articles: (params = {}) => api.get('/reports/articles', { params }),
    categories: (params = {}) => api.get('/reports/categories', { params }),
    payments: (params = {}) => api.get('/reports/payments', { params }),
    daily: (date) => api.get('/reports/daily', { params: { date } }),
}

// Fournisseurs API
export const fournisseursApi = {
    list: (params = {}) => api.get('/fournisseurs', { params }),
    get: (id) => api.get(`/fournisseurs/${id}`),
    create: (data) => api.post('/fournisseurs', data),
    update: (id, data) => api.put(`/fournisseurs/${id}`, data),
    delete: (id) => api.delete(`/fournisseurs/${id}`),
}
