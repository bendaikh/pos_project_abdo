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
                <option value="to_pay">À payer</option>
                <option value="to_collect">À encaisser</option>
                <option value="paid">Payé</option>
                <option value="collected">Encaissé</option>
                <option value="unpaid">Impayé</option>
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
                <table class="w-full min-w-[1320px]">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Utilisateur</th>
                            <th class="px-4 py-3 text-left">N° cmd</th>
                            <th class="px-4 py-3 text-left">Client</th>
                            <th class="px-4 py-3 text-left">Origine</th>
                            <th class="px-4 py-3 text-left">Retrait prévu</th>
                            <th class="px-4 py-3 text-left">Livreur</th>
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
                        <tr v-for="commande in commandes" :key="commande.id" class="hover:bg-gray-50 cursor-pointer" @click="openPaymentPanel(commande.id)">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ commande.user?.name || '-' }}</td>
                            <td class="px-4 py-3 font-semibold">
                                <button type="button" class="text-blue-700 hover:underline" @click.stop="goToDetail(commande.id)">
                                    {{ commande.order_number || commande.reference }}
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-gray-900">{{ commande.customer?.name || 'Client anonyme' }}</p>
                                <p class="text-xs text-gray-500">{{ commande.customer?.phone || '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ formatOrigin(commande.origin) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(commande.pickup_date) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <p class="font-medium text-gray-900">{{ commande.delivery_agent?.name || commande.delivery_agent_name_snapshot || '-' }}</p>
                                <p class="text-xs text-gray-500">{{ commande.delivery_agent?.platform_name || commande.delivery_platform_name_snapshot || '' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="orderStatusClass(commande.order_status)">
                                    {{ formatOrderStatus(commande.order_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="paymentStatusClass(commande.payment_status_code)">
                                    {{ commande.payment_status_label || formatPaymentStatus(commande.payment_status_code) }}
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
                                    <button type="button" class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg hover:bg-gray-100" @click.stop="goToDetail(commande.id)">Voir commande</button>
                                    <button
                                        type="button"
                                        class="px-3 py-1.5 text-xs rounded-lg"
                                        :class="isFullyPaid(commande)
                                            ? 'bg-gray-100 text-gray-600 border border-gray-300 hover:bg-gray-200'
                                            : 'bg-blue-600 text-white hover:bg-blue-700'"
                                        @click.stop="openPaymentPanel(commande.id)"
                                    >
                                        {{ isFullyPaid(commande) ? 'Voir paiements' : 'Paiement' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="commandes.length === 0">
                            <td colspan="13" class="p-8 text-center text-gray-500">Aucune commande trouvée.</td>
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

        <PaymentMultiModal
            v-if="showPaymentModal && selectedCommande"
            :total="getReste(selectedCommande)"
            :sale="selectedCommande"
            :allow-partial-confirmation="true"
            confirm-label="Valider le paiement"
            @close="closePaymentModal"
            @complete="submitPayments"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { commandesApi, salesApi } from '../../api'
import PaymentMultiModal from '../../components/pos/PaymentMultiModal.vue'
import { useSettingsStore } from '../../stores/settings'

const router = useRouter()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const loading = ref(false)
const showFilters = ref(false)
const commandes = ref([])
const showPaymentModal = ref(false)
const selectedCommande = ref(null)
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
    const map = {
        to_pay: 'À payer',
        to_collect: 'À encaisser',
        paid: 'Payé',
        collected: 'Encaissé',
        unpaid: 'Impayé',
    }
    return map[status] || 'À payer'
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
        to_pay: 'bg-amber-100 text-amber-700',
        to_collect: 'bg-cyan-100 text-cyan-700',
        paid: 'bg-green-100 text-green-700',
        collected: 'bg-blue-100 text-blue-700',
        unpaid: 'bg-red-100 text-red-700',
    }
    return map[status] || 'bg-amber-100 text-amber-700'
}

function getAdvance(commande) {
    return Number(commande.paid_confirmed_amount ?? commande.payment_summary?.paid_confirmed_amount ?? 0)
}

function getReste(commande) {
    return Number(commande.remaining_amount ?? commande.payment_summary?.remaining_amount ?? 0)
}

function isFullyPaid(commande) {
    return getReste(commande) <= 0.001
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

async function openPaymentPanel(id) {
    try {
        const { data } = await salesApi.get(id)
        if (isFullyPaid(data)) {
            goToDetail(id)
            return
        }
        selectedCommande.value = data
        showPaymentModal.value = true
    } catch (error) {
        console.error('Erreur chargement commande:', error)
        alert("Impossible d'ouvrir le paiement pour cette commande.")
    }
}

function closePaymentModal() {
    showPaymentModal.value = false
    selectedCommande.value = null
}

async function submitPayments(payments) {
    if (!selectedCommande.value) return

    try {
        for (const payment of payments || []) {
            await salesApi.addPayment(selectedCommande.value.id, payment)
        }

        const { data } = await salesApi.get(selectedCommande.value.id)
        selectedCommande.value = data
        if (getReste(selectedCommande.value) <= 0) {
            closePaymentModal()
        }
        await fetchCommandes(pagination.current_page)
    } catch (error) {
        console.error('Erreur paiement commande:', error)
        alert(error.response?.data?.message || "Impossible d'enregistrer le paiement.")
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
