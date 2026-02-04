<template>
    <div class="space-y-6">
        <!-- Page Header -->
        <div>
            <p class="text-gray-500">Bienvenue, voici le résumé de votre activité aujourd'hui.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Today's Sales -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <BanknotesIcon class="w-6 h-6 text-green-600" />
                    </div>
                    <span 
                        v-if="stats.today_sales?.change_percent !== 0"
                        class="text-sm font-medium px-2 py-1 rounded-full"
                        :class="stats.today_sales?.change_percent > 0 ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'"
                    >
                        {{ stats.today_sales?.change_percent > 0 ? '+' : '' }}{{ stats.today_sales?.change_percent }}%
                    </span>
                </div>
                <p class="mt-4 text-sm text-gray-500">Ventes aujourd'hui</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.today_sales?.amount || 0) }}</p>
            </div>

            <!-- Low Stock Alerts -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                        <ExclamationTriangleIcon class="w-6 h-6 text-orange-600" />
                    </div>
                    <span 
                        class="text-sm font-medium px-2 py-1 rounded-full"
                        :class="{
                            'bg-red-100 text-red-600': stats.low_stock?.status === 'critical',
                            'bg-orange-100 text-orange-600': stats.low_stock?.status === 'warning',
                            'bg-green-100 text-green-600': stats.low_stock?.status === 'ok'
                        }"
                    >
                        {{ stats.low_stock?.count || 0 }} articles
                    </span>
                </div>
                <p class="mt-4 text-sm text-gray-500">Alertes Stock Bas</p>
                <p class="text-2xl font-bold text-gray-900">
                    {{ stats.low_stock?.status === 'critical' ? 'Alerte Critique' : (stats.low_stock?.status === 'warning' ? 'Attention' : 'OK') }}
                </p>
            </div>

            <!-- Transactions -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <ShoppingCartIcon class="w-6 h-6 text-blue-600" />
                    </div>
                    <span class="text-sm font-medium px-2 py-1 rounded-full bg-blue-100 text-blue-600">
                        {{ stats.transactions?.pending || 0 }} commandes
                    </span>
                </div>
                <p class="mt-4 text-sm text-gray-500">Nombre de Transactions</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.transactions?.total || 0 }} Total</p>
            </div>

            <!-- New Customers -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <UsersIcon class="w-6 h-6 text-purple-600" />
                    </div>
                    <span class="text-sm font-medium px-2 py-1 rounded-full bg-green-100 text-green-600">
                        Nouveaux
                    </span>
                </div>
                <p class="mt-4 text-sm text-gray-500">Nouveaux Clients</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.new_customers?.count || 0 }} Aujourd'hui</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sales Chart -->
            <div class="lg:col-span-2 bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Tendances des Ventes</h3>
                        <p class="text-sm text-gray-500">Semaine en cours vs semaine précédente</p>
                    </div>
                    <select 
                        v-model="chartDays"
                        @change="fetchChartData"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        <option :value="7">7 derniers jours</option>
                        <option :value="14">14 derniers jours</option>
                        <option :value="30">30 derniers jours</option>
                    </select>
                </div>
                <div class="h-64">
                    <Line v-if="chartData" :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <!-- Top Categories -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Catégories</h3>
                <div class="space-y-4">
                    <div 
                        v-for="category in topCategories" 
                        :key="category.id"
                        class="flex items-center justify-between"
                    >
                        <div class="flex items-center space-x-3">
                            <div 
                                class="w-10 h-10 rounded-lg flex items-center justify-center"
                                :style="{ backgroundColor: category.color + '20' }"
                            >
                                <span class="text-lg">{{ getCategoryIcon(category.icon) }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ category.name }}</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ category.percentage }}%</span>
                    </div>
                </div>
                <button class="w-full mt-4 py-2 text-sm text-gray-600 hover:text-gray-900 border border-gray-200 rounded-lg hover:bg-gray-50">
                    Voir tous les détails
                </button>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Transactions Récentes</h3>
                <router-link to="/reports" class="text-sm text-green-600 hover:text-green-700 font-medium">
                    Voir tout
                </router-link>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Transaction</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Heure</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="sale in recentSales" :key="sale.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #{{ sale.reference }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                        <UserIcon class="w-4 h-4 text-gray-500" />
                                    </div>
                                    <span class="text-sm text-gray-700">{{ sale.customer }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ sale.date_formatted }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                {{ formatCurrency(sale.total) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    class="px-2 py-1 text-xs font-medium rounded-full"
                                    :class="getStatusClass(sale.status)"
                                >
                                    {{ getStatusLabel(sale.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="text-gray-400 hover:text-gray-600">
                                    <EyeIcon class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="recentSales.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Aucune transaction récente
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Line } from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js'
import { dashboardApi } from '../api'
import { useSettingsStore } from '../stores/settings'
import {
    BanknotesIcon,
    ExclamationTriangleIcon,
    ShoppingCartIcon,
    UsersIcon,
    UserIcon,
    EyeIcon
} from '@heroicons/vue/24/outline'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
)

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const stats = ref({})
const chartDays = ref(7)
const chartData = ref(null)
const topCategories = ref([])
const recentSales = ref([])
const loading = ref(true)

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: '#f3f4f6',
            },
        },
        x: {
            grid: {
                display: false,
            },
        },
    },
}

const categoryIcons = {
    apple: '🍎',
    cup: '🥤',
    bread: '🥖',
    home: '🏠',
    cookie: '🍪',
    meat: '🥩',
}

function getCategoryIcon(icon) {
    return categoryIcons[icon] || '📦'
}

function getStatusClass(status) {
    const classes = {
        completed: 'bg-green-100 text-green-700',
        pending: 'bg-yellow-100 text-yellow-700',
        cancelled: 'bg-red-100 text-red-700',
    }
    return classes[status] || 'bg-gray-100 text-gray-700'
}

function getStatusLabel(status) {
    const labels = {
        completed: 'Complété',
        pending: 'En Attente',
        cancelled: 'Annulé',
    }
    return labels[status] || status
}

async function fetchStats() {
    try {
        const response = await dashboardApi.stats()
        stats.value = response.data
    } catch (error) {
        console.error('Failed to fetch stats:', error)
    }
}

async function fetchChartData() {
    try {
        const response = await dashboardApi.salesChart(chartDays.value)
        const data = response.data
        
        chartData.value = {
            labels: data.labels,
            datasets: [
                {
                    label: data.datasets[0].label,
                    data: data.datasets[0].data,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: data.datasets[1].label,
                    data: data.datasets[1].data,
                    borderColor: '#9ca3af',
                    backgroundColor: 'transparent',
                    borderDash: [5, 5],
                    tension: 0.4,
                },
            ],
        }
    } catch (error) {
        console.error('Failed to fetch chart data:', error)
    }
}

async function fetchTopCategories() {
    try {
        const response = await dashboardApi.topCategories()
        topCategories.value = response.data
    } catch (error) {
        console.error('Failed to fetch top categories:', error)
    }
}

async function fetchRecentSales() {
    try {
        const response = await dashboardApi.recentSales()
        recentSales.value = response.data
    } catch (error) {
        console.error('Failed to fetch recent sales:', error)
    }
}

onMounted(async () => {
    loading.value = true
    await Promise.all([
        fetchStats(),
        fetchChartData(),
        fetchTopCategories(),
        fetchRecentSales(),
    ])
    loading.value = false
})
</script>
