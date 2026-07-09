<template>
    <div class="dashboard-shell">
        <div
            ref="statsPanelRef"
            class="dashboard-stats-panel"
            :style="statsPanelStyle"
        >
            <div class="dashboard-stats-grid">
                <div
                    v-for="card in dashboardCards"
                    :key="card.key"
                    class="dashboard-glow-card"
                    :class="card.theme"
                >
                    <div class="dashboard-glow-card__shine" aria-hidden="true"></div>
                    <div class="relative z-10 flex items-center justify-between gap-2 min-w-0">
                        <div class="dashboard-glow-card__icon">
                            <component :is="card.icon" class="w-4 h-4" />
                        </div>
                        <span v-if="card.badge" class="dashboard-glow-card__badge">{{ card.badge }}</span>
                    </div>
                    <div class="relative z-10 dashboard-glow-card__body">
                        <p class="dashboard-glow-card__label">{{ card.label }}</p>
                        <div class="dashboard-glow-card__value" :title="card.isCurrency ? formatCurrency(card.value) : String(card.value)">
                            <template v-if="card.isCurrency">
                                <span class="dashboard-glow-card__amount">{{ formatCardAmount(card.value) }}</span>
                                <span class="dashboard-glow-card__currency">{{ currencySymbol }}</span>
                            </template>
                            <span v-else class="dashboard-glow-card__count">{{ formatCardCount(card.value) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="dashboard-content space-y-5"
            :style="{ paddingTop: `${statsPanelHeight}px` }"
        >
        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Sales Chart -->
            <div class="lg:col-span-2 surface-card p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-primary-50">
                            <PresentationChartLineIcon class="w-5 h-5 text-primary-600" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Tendances des Ventes</h3>
                            <p class="text-sm text-slate-500">Semaine en cours vs semaine précédente</p>
                        </div>
                    </div>
                    <select 
                        v-model="chartDays"
                        @change="fetchChartData"
                        class="px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 bg-white"
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
            <div class="surface-card p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-50">
                        <TagIcon class="w-5 h-5 text-emerald-600" />
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Top Catégories</h3>
                </div>
                <div class="space-y-3">
                    <div 
                        v-for="category in topCategories" 
                        :key="category.id"
                        class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 transition-colors"
                    >
                        <div class="flex items-center space-x-3">
                            <div 
                                class="w-10 h-10 rounded-xl flex items-center justify-center"
                                :style="{ backgroundColor: category.color + '20' }"
                            >
                                <span class="text-lg">{{ getCategoryIcon(category.icon) }}</span>
                            </div>
                            <span class="text-sm font-medium text-slate-700">{{ category.name }}</span>
                        </div>
                        <span class="text-sm font-bold text-primary-600">{{ category.percentage }}%</span>
                    </div>
                </div>
                <button class="btn-primary w-full mt-5 py-2.5 text-sm">
                    Voir tous les détails
                </button>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="surface-card overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-sky-50">
                        <ReceiptPercentIcon class="w-5 h-5 text-sky-600" />
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Transactions Récentes</h3>
                </div>
                <router-link to="/reports" class="btn-primary px-4 py-2 text-sm">
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-secondary-600">
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
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, inject, watch, nextTick } from 'vue'
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
    RectangleStackIcon,
    BoltIcon,
    ClockIcon,
    XCircleIcon,
    BriefcaseIcon,
    UserIcon,
    EyeIcon,
    PresentationChartLineIcon,
    TagIcon,
    ReceiptPercentIcon,
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
const currencySymbol = computed(() => settingsStore.currencySymbol)

const contentInsetLeft = inject('contentInsetLeft', ref(0))
const statsPanelRef = ref(null)
const statsPanelHeight = ref(236)

const statsPanelStyle = computed(() => ({
    left: `${contentInsetLeft.value}px`,
}))

let statsPanelObserver = null

function updateStatsPanelHeight() {
    if (statsPanelRef.value) {
        statsPanelHeight.value = statsPanelRef.value.offsetHeight
    }
}

watch(contentInsetLeft, async () => {
    await nextTick()
    updateStatsPanelHeight()
})

function formatCardCount(value) {
    return Number(value || 0).toLocaleString('fr-FR')
}

function formatCardAmount(value) {
    return Number(value || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
}

const stats = ref({})
const chartDays = ref(7)
const chartData = ref(null)
const topCategories = ref([])
const recentSales = ref([])
const loading = ref(true)

const projectRevenueThemes = [
  'dashboard-glow-card--project-a',
  'dashboard-glow-card--project-b',
  'dashboard-glow-card--project-c',
  'dashboard-glow-card--project-d',
]

const dashboardCards = computed(() => {
    const projects = stats.value.projects || {}
    const revenues = stats.value.project_revenues || []

    const summaryCards = [
        {
            key: 'total',
            label: 'Nbrs Projets',
            value: projects.total || 0,
            icon: RectangleStackIcon,
            theme: 'dashboard-glow-card--cyan',
            badge: 'Total',
            isCurrency: false,
        },
        {
            key: 'active',
            label: 'Projets Actifs',
            value: projects.active || 0,
            icon: BoltIcon,
            theme: 'dashboard-glow-card--emerald',
            badge: 'En cours',
            isCurrency: false,
        },
        {
            key: 'pending',
            label: 'Projets En Attente',
            value: projects.pending || 0,
            icon: ClockIcon,
            theme: 'dashboard-glow-card--amber',
            badge: 'Attente',
            isCurrency: false,
        },
        {
            key: 'cancelled',
            label: 'Projets Annulées',
            value: projects.cancelled || 0,
            icon: XCircleIcon,
            theme: 'dashboard-glow-card--rose',
            badge: 'Annulés',
            isCurrency: false,
        },
    ]

    const revenueCards = (revenues.length ? revenues : [
        { name: 'Projet A', revenue: 0 },
        { name: 'Projet B', revenue: 0 },
        { name: 'Projet C', revenue: 0 },
        { name: 'Projet D', revenue: 0 },
    ]).map((project, index) => ({
        key: `project-${index}`,
        label: project.name,
        value: project.revenue || 0,
        icon: BriefcaseIcon,
        theme: projectRevenueThemes[index],
        badge: 'CA',
        isCurrency: true,
    }))

    return [...summaryCards, ...revenueCards]
})

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
        completed: 'bg-primary-100 text-gray-900',
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
                    borderColor: '#06b6d4',
                    backgroundColor: 'rgba(6, 182, 212, 0.1)',
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

    if (statsPanelRef.value) {
        statsPanelObserver = new ResizeObserver(() => {
            updateStatsPanelHeight()
        })
        statsPanelObserver.observe(statsPanelRef.value)
        updateStatsPanelHeight()
    }

    window.addEventListener('resize', updateStatsPanelHeight)

    await Promise.all([
        fetchStats(),
        fetchChartData(),
        fetchTopCategories(),
        fetchRecentSales(),
    ])
    loading.value = false

    updateStatsPanelHeight()
})

onUnmounted(() => {
    statsPanelObserver?.disconnect()
    window.removeEventListener('resize', updateStatsPanelHeight)
})
</script>

<style scoped>
.dashboard-shell {
    position: relative;
    height: 100%;
    min-height: 0;
    overflow: hidden;
}

.dashboard-stats-panel {
    position: fixed;
    top: 4rem;
    right: 0;
    z-index: 30;
    padding: 1rem 1.5rem 0.75rem;
    background: var(--color-bg-main, #f8fafc);
    border-bottom: 1px solid rgba(148, 163, 184, 0.25);
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    transition: left 0.3s ease;
}

.dashboard-content {
    height: 100%;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    padding-bottom: 1.5rem;
    box-sizing: border-box;
}

@media (max-width: 639px) {
    .dashboard-stats-panel {
        padding: 1rem 1rem 0.75rem;
    }

    .dashboard-content {
        padding-left: 1rem;
        padding-right: 1rem;
        padding-bottom: 1rem;
    }
}

.dashboard-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    grid-template-rows: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
    overflow: hidden;
}

@media (max-width: 1023px) {
    .dashboard-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        grid-template-rows: repeat(4, minmax(0, 1fr));
    }
}

@media (max-width: 639px) {
    .dashboard-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

.dashboard-glow-card {
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 0;
    height: 6.25rem;
    border-radius: 0.875rem;
    padding: 0.7rem 0.8rem;
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.22);
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.14);
}

.dashboard-glow-card__body {
    min-width: 0;
    margin-top: 0.35rem;
}

.dashboard-glow-card__label {
    font-size: 0.68rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.82);
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dashboard-glow-card__value {
    display: flex;
    align-items: baseline;
    gap: 0.3rem;
    min-width: 0;
    margin-top: 0.15rem;
    line-height: 1;
}

.dashboard-glow-card__amount,
.dashboard-glow-card__count {
    font-size: clamp(0.9rem, 1.35vw, 1.05rem);
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.02em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dashboard-glow-card__currency {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: rgba(255, 255, 255, 0.88);
    flex-shrink: 0;
}

.dashboard-glow-card__shine {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 85% 15%, rgba(255, 255, 255, 0.28) 0%, transparent 55%);
    pointer-events: none;
}

.dashboard-glow-card__icon {
    width: 2rem;
    height: 2rem;
    border-radius: 0.625rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.28);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.3);
}

.dashboard-glow-card__badge {
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 0.2rem 0.45rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.25);
    flex-shrink: 0;
}

.dashboard-glow-card--cyan {
    background: linear-gradient(135deg, #0891b2 0%, #06b6d4 45%, #22d3ee 100%);
    box-shadow: 0 8px 20px rgba(6, 182, 212, 0.28);
}

.dashboard-glow-card--emerald {
    background: linear-gradient(135deg, #047857 0%, #10b981 50%, #34d399 100%);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.28);
}

.dashboard-glow-card--amber {
    background: linear-gradient(135deg, #b45309 0%, #f59e0b 50%, #fbbf24 100%);
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.28);
}

.dashboard-glow-card--rose {
    background: linear-gradient(135deg, #be123c 0%, #f43f5e 50%, #fb7185 100%);
    box-shadow: 0 8px 20px rgba(244, 63, 94, 0.28);
}

.dashboard-glow-card--project-a {
    background: linear-gradient(135deg, #4338ca 0%, #6366f1 50%, #818cf8 100%);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.28);
}

.dashboard-glow-card--project-b {
    background: linear-gradient(135deg, #6d28d9 0%, #8b5cf6 50%, #a78bfa 100%);
    box-shadow: 0 8px 20px rgba(139, 92, 246, 0.28);
}

.dashboard-glow-card--project-c {
    background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 50%, #38bdf8 100%);
    box-shadow: 0 8px 20px rgba(14, 165, 233, 0.28);
}

.dashboard-glow-card--project-d {
    background: linear-gradient(135deg, #0f766e 0%, #14b8a6 50%, #2dd4bf 100%);
    box-shadow: 0 8px 20px rgba(20, 184, 166, 0.28);
}
</style>
