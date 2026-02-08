<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Bilan Financier</h1>
                <p class="text-gray-500">Vue d'ensemble de votre situation financière</p>
            </div>
            <div class="flex items-center space-x-3">
                <select v-model="selectedPeriod" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="month">Ce mois</option>
                    <option value="quarter">Ce trimestre</option>
                    <option value="year">Cette année</option>
                    <option value="all">Tout</option>
                </select>
                <button @click="exportBilan" class="px-4 py-2 bg-gray-900 text-white font-medium rounded-lg hover:bg-gray-800 flex items-center">
                    <ArrowDownTrayIcon class="w-5 h-5 mr-2" />
                    Exporter
                </button>
            </div>
        </div>

        <!-- Main Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Revenus -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">Total Revenus</p>
                        <p class="text-3xl font-bold mt-1">{{ formatCurrency(totalRevenus) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <ArrowTrendingUpIcon class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-green-400/30">
                    <p class="text-sm text-green-100">
                        <span class="text-white font-medium">+{{ growthPercentage }}%</span> vs période précédente
                    </p>
                </div>
            </div>

            <!-- Dépenses -->
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm">Total Dépenses</p>
                        <p class="text-3xl font-bold mt-1">{{ formatCurrency(totalDepenses) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <ArrowTrendingDownIcon class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-red-400/30">
                    <p class="text-sm text-red-100">
                        <span class="text-white font-medium">{{ depensesCount }}</span> transactions
                    </p>
                </div>
            </div>

            <!-- Bénéfice Net -->
            <div :class="beneficeNet >= 0 ? 'bg-gradient-to-br from-primary-500 to-primary-600' : 'bg-gradient-to-br from-orange-500 to-orange-600'" class="rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm">Bénéfice Net</p>
                        <p class="text-3xl font-bold mt-1">{{ formatCurrency(beneficeNet) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <ScaleIcon class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-white/20">
                    <p class="text-sm text-white/80">
                        Marge: <span class="text-white font-medium">{{ margePercentage }}%</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenus vs Dépenses -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenus vs Dépenses</h3>
                <div class="space-y-4">
                    <div v-for="month in monthlyData" :key="month.label" class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ month.label }}</span>
                            <span class="font-medium" :class="month.revenus - month.depenses >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatCurrency(month.revenus - month.depenses) }}
                            </span>
                        </div>
                        <div class="flex space-x-1 h-4">
                            <div 
                                class="bg-green-500 rounded-l"
                                :style="{ width: getBarWidth(month.revenus, maxValue) + '%' }"
                            ></div>
                            <div 
                                class="bg-red-500 rounded-r"
                                :style="{ width: getBarWidth(month.depenses, maxValue) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-center space-x-6 mt-4 pt-4 border-t border-gray-100">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded"></div>
                        <span class="text-sm text-gray-600">Revenus</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded"></div>
                        <span class="text-sm text-gray-600">Dépenses</span>
                    </div>
                </div>
            </div>

            <!-- Répartition des dépenses -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Répartition des Dépenses</h3>
                <div class="space-y-3">
                    <div v-for="cat in depensesByCategory" :key="cat.name" class="flex items-center space-x-3">
                        <div class="w-24 text-sm text-gray-600 truncate">{{ cat.name }}</div>
                        <div class="flex-1 h-6 bg-gray-100 rounded-full overflow-hidden">
                            <div 
                                class="h-full rounded-full"
                                :class="cat.color"
                                :style="{ width: cat.percentage + '%' }"
                            ></div>
                        </div>
                        <div class="w-20 text-right text-sm font-medium text-gray-900">{{ formatCurrency(cat.amount) }}</div>
                        <div class="w-12 text-right text-sm text-gray-500">{{ cat.percentage }}%</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détails Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Détail du Bilan</h3>
            </div>
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Revenus</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dépenses</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Solde</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">Ventes</td>
                        <td class="px-6 py-4 text-right text-green-600 font-medium">+{{ formatCurrency(bilanDetails.ventes) }}</td>
                        <td class="px-6 py-4 text-right text-gray-400">-</td>
                        <td class="px-6 py-4 text-right font-medium text-green-600">{{ formatCurrency(bilanDetails.ventes) }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">Achats</td>
                        <td class="px-6 py-4 text-right text-gray-400">-</td>
                        <td class="px-6 py-4 text-right text-red-600 font-medium">-{{ formatCurrency(bilanDetails.achats) }}</td>
                        <td class="px-6 py-4 text-right font-medium text-red-600">-{{ formatCurrency(bilanDetails.achats) }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">Charges fixes</td>
                        <td class="px-6 py-4 text-right text-gray-400">-</td>
                        <td class="px-6 py-4 text-right text-red-600 font-medium">-{{ formatCurrency(bilanDetails.chargesFixes) }}</td>
                        <td class="px-6 py-4 text-right font-medium text-red-600">-{{ formatCurrency(bilanDetails.chargesFixes) }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">Charges variables</td>
                        <td class="px-6 py-4 text-right text-gray-400">-</td>
                        <td class="px-6 py-4 text-right text-red-600 font-medium">-{{ formatCurrency(bilanDetails.chargesVariables) }}</td>
                        <td class="px-6 py-4 text-right font-medium text-red-600">-{{ formatCurrency(bilanDetails.chargesVariables) }}</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">Autres revenus</td>
                        <td class="px-6 py-4 text-right text-green-600 font-medium">+{{ formatCurrency(bilanDetails.autresRevenus) }}</td>
                        <td class="px-6 py-4 text-right text-gray-400">-</td>
                        <td class="px-6 py-4 text-right font-medium text-green-600">{{ formatCurrency(bilanDetails.autresRevenus) }}</td>
                    </tr>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-6 py-4 font-bold text-gray-900">TOTAL</td>
                        <td class="px-6 py-4 text-right font-bold text-green-600">+{{ formatCurrency(totalRevenus) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-red-600">-{{ formatCurrency(totalDepenses) }}</td>
                        <td class="px-6 py-4 text-right font-bold" :class="beneficeNet >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ beneficeNet >= 0 ? '+' : '' }}{{ formatCurrency(beneficeNet) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useSettingsStore } from '../../stores/settings'
import { 
    ArrowDownTrayIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ScaleIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const selectedPeriod = ref('month')

const bilanDetails = ref({
    ventes: 185000,
    achats: 45000,
    chargesFixes: 25000,
    chargesVariables: 12000,
    autresRevenus: 5000
})

const monthlyData = ref([
    { label: 'Février', revenus: 190000, depenses: 82000 },
    { label: 'Janvier', revenus: 175000, depenses: 78000 },
    { label: 'Décembre', revenus: 220000, depenses: 95000 },
    { label: 'Novembre', revenus: 165000, depenses: 72000 },
    { label: 'Octobre', revenus: 180000, depenses: 80000 },
])

const depensesByCategory = ref([
    { name: 'Achats', amount: 45000, percentage: 55, color: 'bg-red-500' },
    { name: 'Loyer', amount: 15000, percentage: 18, color: 'bg-orange-500' },
    { name: 'Salaires', amount: 12000, percentage: 15, color: 'bg-yellow-500' },
    { name: 'Utilities', amount: 5000, percentage: 6, color: 'bg-blue-500' },
    { name: 'Autres', amount: 5000, percentage: 6, color: 'bg-gray-500' },
])

const totalRevenus = computed(() => bilanDetails.value.ventes + bilanDetails.value.autresRevenus)
const totalDepenses = computed(() => bilanDetails.value.achats + bilanDetails.value.chargesFixes + bilanDetails.value.chargesVariables)
const beneficeNet = computed(() => totalRevenus.value - totalDepenses.value)
const margePercentage = computed(() => totalRevenus.value > 0 ? Math.round((beneficeNet.value / totalRevenus.value) * 100) : 0)
const growthPercentage = ref(12)
const depensesCount = ref(45)

const maxValue = computed(() => {
    let max = 0
    monthlyData.value.forEach(m => {
        if (m.revenus > max) max = m.revenus
        if (m.depenses > max) max = m.depenses
    })
    return max
})

function getBarWidth(value, max) {
    return max > 0 ? (value / max) * 45 : 0
}

function exportBilan() {
    alert('Export du bilan en cours...')
}

onMounted(() => {
    // Load data based on selected period
})
</script>
