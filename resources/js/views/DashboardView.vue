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
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-primary-500/15">
                            <PresentationChartLineIcon class="w-5 h-5 text-primary-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">Tendances des Ventes</h3>
                            <p class="text-sm text-text-secondary">Semaine en cours vs semaine précédente</p>
                        </div>
                    </div>
                    <select 
                        v-model="chartDays"
                        @change="fetchChartData"
                        class="px-3 py-2 border border-white/10 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 bg-secondary-700 text-white"
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
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-accent-500/15">
                        <TagIcon class="w-5 h-5 text-accent-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-white">Top Catégories</h3>
                </div>
                <div class="space-y-3">
                    <div 
                        v-for="category in topCategories" 
                        :key="category.id"
                        class="flex items-center justify-between p-2 rounded-xl hover:bg-white/5 transition-colors"
                    >
                        <div class="flex items-center space-x-3">
                            <div 
                                class="w-10 h-10 rounded-xl flex items-center justify-center"
                                :style="{ backgroundColor: category.color + '20' }"
                            >
                                <span class="text-lg">{{ getCategoryIcon(category.icon) }}</span>
                            </div>
                            <span class="text-sm font-medium text-text-secondary">{{ category.name }}</span>
                        </div>
                        <span class="text-sm font-bold text-primary-400">{{ category.percentage }}%</span>
                    </div>
                </div>
                <button class="btn-primary w-full mt-5 py-2.5 text-sm">
                    Voir tous les détails
                </button>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="surface-card overflow-hidden">
            <div class="p-6 border-b border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-primary-500/15">
                        <ReceiptPercentIcon class="w-5 h-5 text-primary-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-white">Transactions Récentes</h3>
                </div>
                <router-link to="/reports" class="btn-primary px-4 py-2 text-sm">
                    Voir tout
                </router-link>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-700/60">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">ID Transaction</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Date & Heure</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="sale in recentSales" :key="sale.id" class="hover:bg-white/5">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                                #{{ sale.reference }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-secondary-700 rounded-full flex items-center justify-center">
                                        <UserIcon class="w-4 h-4 text-text-secondary" />
                                    </div>
                                    <span class="text-sm text-text-secondary">{{ sale.customer }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                                {{ sale.date_formatted }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-primary-400">
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
                                <button class="text-text-muted hover:text-white">
                                    <EyeIcon class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="recentSales.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-text-secondary">
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
            labels: {
                color: '#aeb0b4',
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(255, 255, 255, 0.06)',
            },
            ticks: {
                color: '#aeb0b4',
            },
        },
        x: {
            grid: {
                display: false,
            },
            ticks: {
                color: '#aeb0b4',
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
        completed: 'bg-primary-500/20 text-primary-300',
        pending: 'bg-accent-500/20 text-accent-300',
        cancelled: 'bg-danger/20 text-danger',
    }
    return classes[status] || 'bg-white/10 text-text-secondary'
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
                    borderColor: '#00d7d7',
                    backgroundColor: 'rgba(0, 215, 215, 0.12)',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: data.datasets[1].label,
                    data: data.datasets[1].data,
                    borderColor: '#00d7d7',
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
    top: 7.25rem;
    right: 0;
    z-index: 30;
    padding: 1rem 1.5rem 0.75rem;
    background: #0f0f12;
    border-bottom: 1px solid rgba(0, 215, 215, 0.2);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
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
    background: linear-gradient(135deg, #008a8a 0%, #00d7d7 55%, #1aefe8 100%);
    box-shadow: 0 8px 20px rgba(0, 215, 215, 0.32);
}

.dashboard-glow-card--emerald {
    background: linear-gradient(135deg, #12122b 0%, #1e1e3f 55%, #2563eb 160%);
    border-color: rgba(0, 215, 215, 0.45);
    box-shadow: 0 8px 20px rgba(0, 215, 215, 0.22);
}

.dashboard-glow-card--amber {
    background: linear-gradient(135deg, #9a4a12 0%, #ff6633 50%, #fdba74 100%);
    box-shadow: 0 8px 20px rgba(255, 102, 51, 0.32);
}

.dashboard-glow-card--rose {
    background: linear-gradient(135deg, #12122b 0%, #1e1e3f 50%, #00d7d7 150%);
    border-color: rgba(0, 215, 215, 0.35);
    box-shadow: 0 8px 20px rgba(0, 215, 215, 0.2);
}

.dashboard-glow-card--project-a {
    background: linear-gradient(135deg, #008a8a 0%, #00d7d7 50%, #1aefe8 100%);
    box-shadow: 0 8px 20px rgba(0, 215, 215, 0.3);
}

.dashboard-glow-card--project-b {
    background: linear-gradient(135deg, #9a4a12 0%, #ff6633 50%, #fdba74 100%);
    box-shadow: 0 8px 20px rgba(255, 102, 51, 0.28);
}

.dashboard-glow-card--project-c {
    background: linear-gradient(135deg, #12122b 0%, #12122b 45%, #00d7d7 160%);
    border-color: rgba(0, 215, 215, 0.4);
    box-shadow: 0 8px 20px rgba(0, 215, 215, 0.22);
}

.dashboard-glow-card--project-d {
    background: linear-gradient(135deg, #1e1e3f 0%, #2a313c 40%, #00d7d7 160%);
    border-color: rgba(0, 215, 215, 0.35);
    box-shadow: 0 8px 20px rgba(0, 215, 215, 0.2);
}
</style>
