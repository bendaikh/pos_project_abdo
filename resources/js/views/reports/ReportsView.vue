<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Rapports</h1>
                <p class="text-gray-500">Analysez les performances de votre point de vente</p>
            </div>
            <div class="flex items-center space-x-3">
                <input v-model="fromDate" type="date" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <span class="text-gray-400">à</span>
                <input v-model="toDate" type="date" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <button @click="fetchReports" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600">
                    Appliquer
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Ventes totales</p>
                <p class="text-2xl font-bold text-gray-900">{{ salesReport?.summary?.total_sales || 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Chiffre d'affaires</p>
                <p class="text-2xl font-bold text-secondary-600">{{ formatCurrency(salesReport?.summary?.total_revenue || 0) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Taxes collectées</p>
                <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(salesReport?.summary?.total_tax || 0) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Vente moyenne</p>
                <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(salesReport?.summary?.average_sale || 0) }}</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Articles -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Articles</h3>
                <div class="space-y-3">
                    <div v-for="(article, index) in articlesReport?.articles?.slice(0, 5)" :key="article.id" class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">{{ index + 1 }}</span>
                            <span class="text-sm text-gray-900">{{ article.name }}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ formatCurrency(article.revenue) }}</p>
                            <p class="text-xs text-gray-500">{{ article.quantity_sold }} vendus</p>
                        </div>
                    </div>
                    <div v-if="!articlesReport?.articles?.length" class="text-center py-8 text-gray-500">
                        Aucune donnée pour cette période
                    </div>
                </div>
            </div>

            <!-- Payment Types -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Types de Paiement</h3>
                <div class="space-y-3">
                    <div v-for="payment in paymentsReport?.payments" :key="payment.type" class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full" :class="getPaymentColor(payment.type)"></div>
                            <span class="text-sm text-gray-900">{{ payment.label }}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ formatCurrency(payment.total) }}</p>
                            <p class="text-xs text-gray-500">{{ payment.percentage }}%</p>
                        </div>
                    </div>
                    <div v-if="!paymentsReport?.payments?.length" class="text-center py-8 text-gray-500">
                        Aucune donnée pour cette période
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Report -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance par Catégorie</h3>
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité vendue</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nb de ventes</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">CA</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="category in categoriesReport?.categories" :key="category.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: category.color }"></div>
                                <span class="font-medium text-gray-900">{{ category.name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ category.quantity_sold }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ category.sale_count }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-secondary-600">{{ formatCurrency(category.revenue) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-primary-500 rounded-full" :style="{ width: category.percentage + '%' }"></div>
                                </div>
                                <span class="text-sm text-gray-500">{{ category.percentage }}%</span>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!categoriesReport?.categories?.length">
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            Aucune donnée pour cette période
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { reportsApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const fromDate = ref(new Date(new Date().setDate(1)).toISOString().split('T')[0])
const toDate = ref(new Date().toISOString().split('T')[0])

const salesReport = ref(null)
const articlesReport = ref(null)
const categoriesReport = ref(null)
const paymentsReport = ref(null)

function getPaymentColor(type) {
    const colors = {
        cash: 'bg-primary-500',
        card: 'bg-blue-500',
        mobile: 'bg-purple-500',
        check: 'bg-orange-500',
        other: 'bg-gray-500'
    }
    return colors[type] || 'bg-gray-500'
}

async function fetchReports() {
    const params = { from_date: fromDate.value, to_date: toDate.value }
    
    try {
        const [sales, articles, categories, payments] = await Promise.all([
            reportsApi.sales(params),
            reportsApi.articles(params),
            reportsApi.categories(params),
            reportsApi.payments(params)
        ])
        
        salesReport.value = sales.data
        articlesReport.value = articles.data
        categoriesReport.value = categories.data
        paymentsReport.value = payments.data
    } catch (error) {
        console.error('Failed to fetch reports:', error)
    }
}

onMounted(fetchReports)
</script>
