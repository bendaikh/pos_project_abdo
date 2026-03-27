<template>
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4 md:p-5">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Livreurs</h1>
                    <p class="text-sm text-gray-500">Gestion des livreurs internes, plateformes et suivi financier des livraisons.</p>
                </div>
                <button
                    type="button"
                    class="px-4 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700"
                    @click="goToCreate"
                >
                    Ajouter livreur
                </button>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                    <p class="text-xs text-blue-600">Total livreurs</p>
                    <p class="text-xl font-bold text-blue-900">{{ agents.length }}</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3">
                    <p class="text-xs text-emerald-700">Actifs</p>
                    <p class="text-xl font-bold text-emerald-900">{{ activeAgentsCount }}</p>
                </div>
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-3">
                    <p class="text-xs text-amber-700">Commandes livrées</p>
                    <p class="text-xl font-bold text-amber-900">{{ reportTotals.orders_count || 0 }}</p>
                </div>
                <div class="bg-purple-50 border border-purple-100 rounded-xl p-3">
                    <p class="text-xs text-purple-700">Commission totale</p>
                    <p class="text-xl font-bold text-purple-900">{{ formatCurrency(reportTotals.total_commission_amount || 0) }}</p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-6 gap-3">
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Rechercher nom, téléphone ou plateforme"
                    class="md:col-span-2 px-3 py-2 border border-gray-300 rounded-lg"
                    @keyup.enter="applyFilters"
                >
                <select v-model="filters.type" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Tous types</option>
                    <option value="internal">Interne</option>
                    <option value="platform">Plateforme</option>
                </select>
                <select v-model="filters.status" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Tous statuts</option>
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                </select>
                <select v-model="filters.platform_name" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Toutes plateformes</option>
                    <option v-for="platform in platformOptions" :key="platform" :value="platform">{{ platform }}</option>
                </select>
                <div class="flex gap-2">
                    <button type="button" class="flex-1 px-3 py-2 rounded-lg bg-gray-900 text-white font-medium hover:bg-gray-800" @click="applyFilters">
                        Filtrer
                    </button>
                    <button type="button" class="px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-100" @click="resetFilters">
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-4 md:px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Liste des livreurs</h2>
                <span class="text-sm text-gray-500">{{ agents.length }} résultat(s)</span>
            </div>
            <div v-if="loadingAgents" class="p-6 text-gray-500">Chargement des livreurs...</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[980px]">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Nom</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Téléphone</th>
                            <th class="px-4 py-3 text-left">Commission</th>
                            <th class="px-4 py-3 text-left">Statut</th>
                            <th class="px-4 py-3 text-left">Plateforme</th>
                            <th class="px-4 py-3 text-left">Actif</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="agent in agents" :key="agent.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ agent.name }}</p>
                                    <div v-if="agent.type === 'platform'" class="mt-1">
                                        <PlatformBadge :platform="agent.platform_name" size="sm" />
                                    </div>
                                    <p v-else class="text-xs text-gray-500">Livreur interne</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="agent.type === 'platform' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'" class="px-2.5 py-1 rounded-full text-xs font-semibold">
                                    {{ agent.type === 'platform' ? 'Plateforme' : 'Interne' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ agent.phone || '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ formatCommission(agent) }}</td>
                            <td class="px-4 py-3">
                                <span :class="agent.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'" class="px-2.5 py-1 rounded-full text-xs font-semibold">
                                    {{ agent.status === 'active' ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <PlatformBadge v-if="agent.platform_name" :platform="agent.platform_name" />
                                <span v-else>-</span>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="agent.active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'" class="px-2.5 py-1 rounded-full text-xs font-semibold">
                                    {{ agent.active ? 'Oui' : 'Non' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button" class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg hover:bg-gray-100" @click="goToEdit(agent.id)">
                                        Modifier
                                    </button>
                                    <button
                                        type="button"
                                        class="px-3 py-1.5 text-xs rounded-lg"
                                        :class="agent.active ? 'bg-rose-100 text-rose-700 hover:bg-rose-200' : 'bg-gray-100 text-gray-500'"
                                        :disabled="!agent.active"
                                        @click="deactivateAgent(agent)"
                                    >
                                        Désactiver
                                    </button>
                                    <button
                                        type="button"
                                        class="px-3 py-1.5 text-xs rounded-lg bg-red-50 text-red-700 hover:bg-red-100"
                                        @click="deleteAgent(agent)"
                                    >
                                        Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="agents.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">Aucun livreur trouvé.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-4 md:px-5 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Suivi des livreurs</h2>
                    <p class="text-sm text-gray-500">Commandes livrées, commissions et suivi financier.</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <input v-model="filters.from_date" type="date" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <input v-model="filters.to_date" type="date" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <button type="button" class="px-3 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800" @click="applyFilters">
                        Actualiser
                    </button>
                </div>
            </div>
            <div v-if="loadingReport" class="p-6 text-gray-500">Chargement du suivi des livreurs...</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[1040px]">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Livreur</th>
                            <th class="px-4 py-3 text-right">Nb commandes</th>
                            <th class="px-4 py-3 text-right">Total livraison</th>
                            <th class="px-4 py-3 text-right">Commission totale</th>
                            <th class="px-4 py-3 text-right">Total encaissé</th>
                            <th class="px-4 py-3 text-right">Total restant</th>
                            <th class="px-4 py-3 text-left">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="row in reportRows" :key="row.delivery_agent_id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900">{{ row.display_name }}</p>
                                <div v-if="row.type === 'platform'" class="mt-1">
                                    <PlatformBadge :platform="row.platform_name || row.display_name" size="sm" />
                                </div>
                                <p v-else class="text-xs text-gray-500">Interne</p>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ row.orders_count }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ formatCurrency(row.total_delivery_amount) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-purple-700">{{ formatCurrency(row.total_commission_amount) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-emerald-700">{{ formatCurrency(row.total_collected_amount) }}</td>
                            <td class="px-4 py-3 text-right font-medium" :class="row.total_remaining_amount > 0 ? 'text-amber-700' : 'text-gray-700'">
                                {{ formatCurrency(row.total_remaining_amount) }}
                            </td>
                            <td class="px-4 py-3">
                                <span :class="row.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'" class="px-2.5 py-1 rounded-full text-xs font-semibold">
                                    {{ row.status === 'active' ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="reportRows.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucune donnée de suivi disponible.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { deliveryAgentsApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import PlatformBadge from '../../components/common/PlatformBadge.vue'

const router = useRouter()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount || 0)

const loadingAgents = ref(false)
const loadingReport = ref(false)
const agents = ref([])
const reportRows = ref([])
const reportTotals = ref({})

const filters = reactive({
    search: '',
    type: '',
    status: '',
    platform_name: '',
    from_date: '',
    to_date: '',
})

const activeAgentsCount = computed(() => agents.value.filter((agent) => agent.active).length)
const platformOptions = computed(() => {
    return Array.from(new Set(
        agents.value
            .map((agent) => agent.platform_name)
            .filter(Boolean)
    )).sort()
})

function buildCommonParams() {
    return {
        search: filters.search || undefined,
        type: filters.type || undefined,
        status: filters.status || undefined,
        platform_name: filters.platform_name || undefined,
    }
}

async function fetchAgents() {
    loadingAgents.value = true
    try {
        const { data } = await deliveryAgentsApi.list({
            ...buildCommonParams(),
            paginate: false,
        })
        agents.value = Array.isArray(data) ? data : (data?.data || [])
    } catch (error) {
        console.error('Erreur chargement livreurs:', error)
        agents.value = []
    } finally {
        loadingAgents.value = false
    }
}

async function fetchReport() {
    loadingReport.value = true
    try {
        const { data } = await deliveryAgentsApi.report({
            ...buildCommonParams(),
            from_date: filters.from_date || undefined,
            to_date: filters.to_date || undefined,
        })
        reportRows.value = Array.isArray(data?.rows) ? data.rows : []
        reportTotals.value = data?.totals || {}
    } catch (error) {
        console.error('Erreur chargement suivi livreurs:', error)
        reportRows.value = []
        reportTotals.value = {}
    } finally {
        loadingReport.value = false
    }
}

async function applyFilters() {
    await Promise.all([fetchAgents(), fetchReport()])
}

async function resetFilters() {
    filters.search = ''
    filters.type = ''
    filters.status = ''
    filters.platform_name = ''
    filters.from_date = ''
    filters.to_date = ''
    await applyFilters()
}

function formatCommission(agent) {
    const value = Number(agent?.commission_value || 0)
    return agent?.commission_type === 'fixed'
        ? formatCurrency(value)
        : `${value}%`
}

function goToCreate() {
    router.push({ name: 'livreurs.create' })
}

function goToEdit(id) {
    router.push({ name: 'livreurs.edit', params: { id } })
}

async function deactivateAgent(agent) {
    if (!agent?.id) {
        return
    }

    if (!window.confirm(`Désactiver ${agent.name} ?`)) {
        return
    }

    try {
        await deliveryAgentsApi.deactivate(agent.id)
        await applyFilters()
    } catch (error) {
        console.error('Erreur désactivation livreur:', error)
        alert(error.response?.data?.message || 'Impossible de désactiver ce livreur.')
    }
}

async function deleteAgent(agent) {
    if (!agent?.id) {
        return
    }

    if (!window.confirm(`Supprimer définitivement ${agent.name} ?`)) {
        return
    }

    try {
        await deliveryAgentsApi.delete(agent.id)
        await applyFilters()
    } catch (error) {
        console.error('Erreur suppression livreur:', error)
        alert(error.response?.data?.message || 'Impossible de supprimer ce livreur.')
    }
}

onMounted(async () => {
    await settingsStore.fetchSettings()
    await applyFilters()
})
</script>
