<template>
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4 md:p-5">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Liste des Commandes</h1>
                    <p class="text-sm text-gray-500">Affiche uniquement les commandes client (hors POS instantané).</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="px-4 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700" @click="goToCreate">
                        Nouvelle commande
                    </button>
                    <button type="button" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100" @click="toggleFilters">
                        Filtrer
                    </button>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                    <p class="text-xs text-blue-600">Total commandes</p>
                    <p class="text-xl font-bold text-blue-900">{{ commandes.length }}</p>
                </div>
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-3">
                    <p class="text-xs text-amber-700">En préparation</p>
                    <p class="text-xl font-bold text-amber-900">{{ prepCount }}</p>
                </div>
                <div class="bg-green-50 border border-green-100 rounded-xl p-3">
                    <p class="text-xs text-green-700">Montant cumulé</p>
                    <p class="text-xl font-bold text-green-900">{{ formatCurrency(totalAmount) }}</p>
                </div>
                <div class="bg-purple-50 border border-purple-100 rounded-xl p-3">
                    <p class="text-xs text-purple-700">Reste cumulé</p>
                    <p class="text-xl font-bold text-purple-900">{{ formatCurrency(totalRemaining) }}</p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Rechercher N° cmd / client / téléphone"
                    class="md:col-span-2 px-3 py-2 border border-gray-300 rounded-lg"
                    @keyup.enter="fetchCommandes"
                >
                <input v-model="filters.pickup_date_from" type="date" class="px-3 py-2 border border-gray-300 rounded-lg" @change="fetchCommandes">
                <input v-model="filters.pickup_date_to" type="date" class="px-3 py-2 border border-gray-300 rounded-lg" @change="fetchCommandes">
            </div>
        </div>

        <div v-if="showFilters" class="bg-white rounded-2xl border border-gray-200 p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <select v-model="filters.order_status" class="px-3 py-2 border border-gray-300 rounded-lg" @change="fetchCommandes">
                <option value="">Tous statuts commande</option>
                <option v-for="status in orderStatuses" :key="status.value" :value="status.value">{{ status.label }}</option>
            </select>
            <select v-model="filters.payment_status" class="px-3 py-2 border border-gray-300 rounded-lg" @change="fetchCommandes">
                <option value="">Tous statuts paiement</option>
                <option value="unpaid">A payer</option>
                <option value="partial">Partiel</option>
                <option value="paid">Payée</option>
            </select>
            <select v-model="filters.origin" class="px-3 py-2 border border-gray-300 rounded-lg" @change="fetchCommandes">
                <option value="">Toutes origines (hors POS)</option>
                <option value="menu_commande">Menu commande</option>
                <option value="livraison">Livraison</option>
            </select>
            <button type="button" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100" @click="resetFilters">Réinitialiser</button>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div v-if="loading" class="p-6 text-gray-500">Chargement des commandes...</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[1200px]">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Utilisateur</th>
                            <th class="px-4 py-3 text-left">N° cmd</th>
                            <th class="px-4 py-3 text-left">Client</th>
                            <th class="px-4 py-3 text-left">Origine</th>
                            <th class="px-4 py-3 text-left">Retrait prévu</th>
                            <th class="px-4 py-3 text-left">Statut commande</th>
                            <th class="px-4 py-3 text-left">Statut paiement</th>
                            <th class="px-4 py-3 text-right">Montant</th>
                            <th class="px-4 py-3 text-right">Avance</th>
                            <th class="px-4 py-3 text-right">Reste</th>
                            <th class="px-4 py-3 text-left">Note</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="commande in commandes" :key="commande.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ commande.user?.name || '-' }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ commande.order_number || commande.reference }}</td>
                            <td class="px-4 py-3">
                                <p class="text-gray-900">{{ commande.customer?.name || 'Client anonyme' }}</p>
                                <p class="text-xs text-gray-500">{{ commande.customer?.phone || '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ formatOrigin(commande.origin) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(commande.pickup_date) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="orderStatusClass(commande.order_status)">
                                    {{ formatOrderStatus(commande.order_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="paymentStatusClass(commande.payment_status)">
                                    {{ formatPaymentStatus(commande.payment_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ formatCurrency(commande.total || 0) }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ formatCurrency(getAdvance(commande)) }}</td>
                            <td class="px-4 py-3 text-right font-semibold" :class="getReste(commande) > 0 ? 'text-amber-700' : 'text-green-700'">
                                {{ formatCurrency(getReste(commande)) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 max-w-[180px] truncate">{{ commande.notes || '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button" class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg hover:bg-gray-100" @click="goToDetail(commande.id)">Voir commande</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="commandes.length === 0">
                            <td colspan="12" class="p-8 text-center text-gray-500">Aucune commande trouvée.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 p-4 flex items-center justify-between text-sm">
                <p class="text-gray-500">Page {{ pagination.current_page }} / {{ pagination.last_page }}</p>
                <div class="flex gap-2">
                    <button type="button" class="px-3 py-1.5 border border-gray-300 rounded-lg disabled:opacity-40" :disabled="pagination.current_page <= 1" @click="changePage(pagination.current_page - 1)">
                        Précédent
                    </button>
                    <button type="button" class="px-3 py-1.5 border border-gray-300 rounded-lg disabled:opacity-40" :disabled="pagination.current_page >= pagination.last_page" @click="changePage(pagination.current_page + 1)">
                        Suivant
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { commandesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'

const router = useRouter()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const loading = ref(false)
const showFilters = ref(false)
const commandes = ref([])
const pagination = reactive({
    current_page: 1,
    last_page: 1,
})

const filters = reactive({
    search: '',
    order_status: '',
    payment_status: '',
    origin: '',
    pickup_date_from: '',
    pickup_date_to: '',
})

const orderStatuses = computed(() => [
    { value: 'confirmee', label: 'Confirmée' },
    { value: 'en_preparation', label: 'En préparation' },
    { value: 'envoyee', label: 'Envoyée' },
    { value: 'livree', label: 'Livrée' },
    { value: 'retournee', label: 'Retournée' },
    { value: 'annulee', label: 'Annulée' },
])

const prepCount = computed(() => commandes.value.filter((c) => c.order_status === 'en_preparation').length)
const totalAmount = computed(() => commandes.value.reduce((sum, c) => sum + Number(c.total || 0), 0))
const totalRemaining = computed(() => commandes.value.reduce((sum, c) => sum + getReste(c), 0))

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function formatOrigin(origin) {
    const map = { pos: 'POS', menu_commande: 'Menu commande', livraison: 'Livraison' }
    return map[origin] || 'Menu commande'
}

function formatOrderStatus(status) {
    return orderStatuses.value.find((s) => s.value === status)?.label || 'Confirmée'
}

function formatPaymentStatus(status) {
    const map = { unpaid: 'A payer', partial: 'Partiel', paid: 'Payée' }
    return map[status] || 'A payer'
}

function orderStatusClass(status) {
    const map = {
        confirmee: 'bg-blue-100 text-blue-700',
        en_preparation: 'bg-yellow-100 text-yellow-700',
        envoyee: 'bg-indigo-100 text-indigo-700',
        livree: 'bg-green-100 text-green-700',
        retournee: 'bg-red-100 text-red-700',
        annulee: 'bg-gray-200 text-gray-700',
    }
    return map[status] || 'bg-blue-100 text-blue-700'
}

function paymentStatusClass(status) {
    const map = {
        unpaid: 'bg-amber-100 text-amber-700',
        partial: 'bg-cyan-100 text-cyan-700',
        paid: 'bg-green-100 text-green-700',
    }
    return map[status] || 'bg-amber-100 text-amber-700'
}

function getAdvance(commande) {
    return (commande.payments || []).reduce((sum, payment) => sum + Number(payment.amount || 0), 0)
}

function getReste(commande) {
    return Math.max(0, Number(commande.total || 0) - getAdvance(commande))
}

function buildParams(page = 1) {
    return {
        page,
        per_page: 15,
        search: filters.search || undefined,
        order_status: filters.order_status || undefined,
        payment_status: filters.payment_status || undefined,
        origin: filters.origin || undefined,
        exclude_origin: filters.origin ? undefined : 'pos',
        pickup_date_from: filters.pickup_date_from || undefined,
        pickup_date_to: filters.pickup_date_to || undefined,
    }
}

async function fetchCommandes(page = 1) {
    loading.value = true
    try {
        const { data } = await commandesApi.list(buildParams(page))
        commandes.value = data.data || []
        pagination.current_page = data.current_page || 1
        pagination.last_page = data.last_page || 1
    } catch (error) {
        console.error('Erreur chargement commandes:', error)
        commandes.value = []
    } finally {
        loading.value = false
    }
}

function changePage(page) {
    fetchCommandes(page)
}

function goToCreate() {
    router.push({ name: 'commandes.create' })
}

function goToDetail(id) {
    router.push({ name: 'commandes.detail', params: { id } })
}

function toggleFilters() {
    showFilters.value = !showFilters.value
}

function resetFilters() {
    filters.search = ''
    filters.order_status = ''
    filters.payment_status = ''
    filters.origin = ''
    filters.pickup_date_from = ''
    filters.pickup_date_to = ''
    fetchCommandes(1)
}

onMounted(async () => {
    await settingsStore.fetchSettings()
    await fetchCommandes()
})
</script>
