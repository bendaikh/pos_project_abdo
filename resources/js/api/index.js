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
        if (error.response?.status === 401) {
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
}

// Options API
export const optionsApi = {
    list: (params = {}) => api.get('/options', { params }),
    get: (id) => api.get(`/options/${id}`),
    create: (data) => api.post('/options', data),
    update: (id, data) => api.put(`/options/${id}`, data),
    delete: (id) => api.delete(`/options/${id}`),
}

// Sales API
export const salesApi = {
    list: (params = {}) => api.get('/sales', { params }),
    pending: () => api.get('/sales/pending'),
    get: (id) => api.get(`/sales/${id}`),
    create: (data) => api.post('/sales', data),
    update: (id, data) => api.put(`/sales/${id}`, data),
    complete: (id) => api.post(`/sales/${id}/complete`),
    cancel: (id) => api.post(`/sales/${id}/cancel`),
    delete: (id) => api.delete(`/sales/${id}`),
    addPayment: (saleId, data) => api.post(`/sales/${saleId}/payments`, data),
}

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
}

// Stock API
export const stockApi = {
    list: (params = {}) => api.get('/stock', { params }),
    movements: (params = {}) => api.get('/stock/movements', { params }),
    recordMovement: (data) => api.post('/stock/movement', data),
    alerts: () => api.get('/stock/alerts'),
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
