<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <p class="text-sm text-gray-500">Fiche commande</p>
                <h1 class="text-2xl font-semibold text-gray-900">{{ commande?.order_number || commande?.reference || 'Commande' }}</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100" @click="goList">Retour liste</button>
                <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" @click="refresh">Actualiser</button>
            </div>
        </div>

        <div v-if="loading" class="bg-white rounded-2xl border border-gray-200 p-6 text-gray-500">Chargement de la commande...</div>

        <template v-else-if="commande">
            <section class="bg-white rounded-2xl border border-gray-200 p-4 md:p-5 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold uppercase text-gray-500 tracking-wide">Informations</h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 space-y-2">
                        <div class="flex items-center gap-2 text-amber-800 text-sm font-semibold">
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-500 inline-block"></span>
                            Statut actuel
                        </div>
                        <div class="inline-flex items-center gap-2 px-3 py-2 bg-white rounded-lg border border-amber-200 text-sm font-semibold text-amber-800">
                            {{ formatOrderStatus(commande.order_status) }}
                        </div>
                        <div class="text-xs text-amber-700">
                            Montant total: {{ formatCurrency(commande.total || 0) }}
                            • En attente: {{ formatCurrency(pendingCollectionAmount) }}
                            • Reste: {{ formatCurrency(remainingAmount) }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-3 space-y-2">
                        <div class="flex items-center justify-between gap-2 text-blue-800 text-sm font-semibold">
                            <span>Passer au statut suivant</span>
                            <span v-if="nextOrderStatus" class="text-xs px-2 py-0.5 rounded-full bg-white border border-blue-200">{{ formatOrderStatus(nextOrderStatus) }}</span>
                        </div>
                        <p class="text-sm text-blue-900">Valider le passage au statut {{ formatOrderStatus(nextOrderStatus) || '-' }}.</p>
                        <textarea
                            v-model="statusComment"
                            rows="2"
                            class="w-full border border-blue-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ajouter un commentaire..."
                        ></textarea>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-40"
                                :disabled="!nextOrderStatus || statusSaving"
                                @click="saveStatus(nextOrderStatus)"
                            >
                                {{ statusSaving ? 'Validation...' : 'Valider le statut' }}
                            </button>
                            <button
                                type="button"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100"
                                @click="statusComment = ''"
                            >
                                Annuler
                            </button>
                            <button
                                v-if="commande.order_status === 'livree' && canOpenPayment"
                                type="button"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                                @click="openPaymentModal"
                            >
                                Passer au paiement
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-3 border border-blue-200">
                        <p class="text-blue-700 text-xs font-semibold uppercase">Client</p>
                        <p class="font-semibold text-gray-900 mt-1">{{ commande.customer?.name || 'Client anonyme' }}</p>
                        <p class="text-gray-600 text-xs mt-0.5">{{ commande.customer?.phone || '-' }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-3 border border-purple-200">
                        <p class="text-purple-700 text-xs font-semibold uppercase">Origine</p>
                        <p class="font-semibold text-gray-900 mt-1">{{ formatOrigin(commande.origin, commande.ticket_type) }}</p>
                        <p v-if="commande.ticket_name" class="text-xs text-gray-500 mt-0.5">
                            {{ commande.ticket_group ? `${commande.ticket_group} · ` : '' }}{{ commande.ticket_name }}
                        </p>
                    </div>
                    <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-xl p-3 border border-cyan-200">
                        <p class="text-cyan-700 text-xs font-semibold uppercase">Mode de service</p>
                        <p class="font-semibold text-gray-900 mt-1">{{ formatServiceMode(commande.service_mode || commande.delivery_mode) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-3 border border-green-200">
                        <p class="text-green-700 text-xs font-semibold uppercase">Date retrait</p>
                        <p class="font-semibold text-gray-900 mt-1">{{ commande.appointment_at ? formatDateTime(commande.appointment_at) : formatDate(commande.pickup_date) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-3 border border-amber-200">
                        <p class="text-amber-700 text-xs font-semibold uppercase">Activité</p>
                        <p class="font-semibold text-gray-900 mt-1">{{ commande.customer_activity || '-' }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-3 border border-indigo-200">
                        <p class="text-indigo-700 text-xs font-semibold uppercase">Livreur</p>
                        <p class="font-semibold text-gray-900 mt-1">{{ commande.delivery_agent?.name || commande.delivery_agent_name_snapshot || '-' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ commande.delivery_agent?.platform_name || commande.delivery_platform_name_snapshot || '' }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-rose-50 to-rose-100 rounded-xl p-3 border border-rose-200">
                        <p class="text-rose-700 text-xs font-semibold uppercase">Commission livraison</p>
                        <p class="font-semibold text-gray-900 mt-1">{{ formatCurrency(commande.delivery_commission_amount || 0) }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ commande.delivery_commission_type === 'fixed'
                                ? formatCurrency(commande.delivery_commission_value_snapshot || 0)
                                : `${Number(commande.delivery_commission_value_snapshot || 0)}%` }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 lg:col-span-2 border border-gray-200">
                        <p class="text-gray-600 text-xs font-semibold uppercase">Adresse</p>
                        <p class="font-semibold text-gray-900 mt-1">{{ commande.delivery_address || '-' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 lg:col-span-2 border border-gray-200">
                        <p class="text-gray-600 text-xs font-semibold uppercase">Note</p>
                        <p class="font-semibold text-gray-900 whitespace-pre-line text-sm mt-1">{{ commande.notes || '-' }}</p>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-2xl overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 text-sm font-semibold text-gray-700">Articles commandés</div>
                    <table class="w-full">
                        <thead class="text-xs uppercase text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left">Article</th>
                                <th class="px-4 py-2 text-right">Qté</th>
                                <th class="px-4 py-2 text-right">PU</th>
                                <th class="px-4 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in commande.items" :key="item.id">
                                <td class="px-4 py-2 text-sm text-gray-900">{{ item.article_name }}</td>
                                <td class="px-4 py-2 text-sm text-right">{{ item.quantity }}</td>
                                <td class="px-4 py-2 text-sm text-right">{{ formatCurrency(item.unit_price) }}</td>
                                <td class="px-4 py-2 text-sm text-right font-semibold">{{ formatCurrency(item.total) }}</td>
                            </tr>
                            <tr class="bg-gray-50 font-semibold">
                                <td colspan="3" class="px-4 py-2 text-sm text-right text-gray-700">Sous-total</td>
                                <td class="px-4 py-2 text-sm text-right text-gray-900">{{ formatCurrency(commande.subtotal || 0) }}</td>
                            </tr>
                            <tr v-if="commande.tax_amount && commande.tax_amount > 0" class="bg-amber-50">
                                <td colspan="3" class="px-4 py-2 text-sm text-right text-amber-700">TVA {{ commande.tax_rate || 0 }}%</td>
                                <td class="px-4 py-2 text-sm text-right font-semibold text-amber-900">{{ formatCurrency(commande.tax_amount || 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                    <div class="bg-blue-50 rounded-xl p-3 border border-blue-100">
                        <p class="text-blue-700">Montant total</p>
                        <p class="text-lg font-bold text-blue-900">{{ formatCurrency(commande.total || 0) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3 border border-green-100">
                        <p class="text-green-700">Montant déjà payé</p>
                        <p class="text-lg font-bold text-green-900">{{ formatCurrency(advanceAmount) }}</p>
                    </div>
                    <div class="bg-cyan-50 rounded-xl p-3 border border-cyan-100">
                        <p class="text-cyan-700">Montant en attente d'encaissement</p>
                        <p class="text-lg font-bold text-cyan-900">{{ formatCurrency(pendingCollectionAmount) }}</p>
                    </div>
                    <div class="bg-amber-50 rounded-xl p-3 border border-amber-100">
                        <p class="text-amber-700">Reste à payer</p>
                        <p class="text-lg font-bold text-amber-900">{{ formatCurrency(remainingAmount) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-200">
                        <p class="text-gray-700">Statut paiement</p>
                        <p class="text-lg font-bold text-gray-900">{{ commande.payment_status_label || formatPaymentStatus(commande.payment_status_code || commande.payment_status) }}</p>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                <div v-if="remainingAmount > 0.001" class="bg-white rounded-2xl border border-gray-200 p-4 md:p-5 space-y-3">
                    <div class="flex items-center justify-between gap-3 flex-wrap rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm">
                        <div class="text-gray-700 space-y-1">
                            <p>Il reste {{ formatCurrency(remainingAmount) }} à encaisser pour cette commande.</p>
                            <p class="text-xs text-gray-500">Cliquez sur le bouton pour finaliser ou compléter le paiement (cash, chèque, crédit, virement).</p>
                        </div>
                        <button
                            type="button"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            @click="openPaymentModal"
                        >
                            Continuer le paiement
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-4 md:p-5 space-y-4">
                    <h2 class="text-sm font-semibold uppercase text-gray-500 tracking-wide">Gestion retour</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div>
                            <label class="block text-gray-600 mb-1">Article</label>
                            <select v-model="returnForm.sale_item_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">Sélectionner</option>
                                <option v-for="item in commande.items" :key="item.id" :value="item.id">
                                    {{ item.article_name }} ({{ item.quantity }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Quantité retournée</label>
                            <input
                                v-model.number="returnForm.quantity"
                                type="number"
                                :min="maxReturnableQty > 0 ? 0.001 : 0"
                                :max="maxReturnableQty || undefined"
                                step="0.001"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                            >
                            <p class="text-xs text-gray-500 mt-1" v-if="selectedReturnItem">
                                Restant retournable: {{ maxReturnableQty }} / {{ selectedReturnItem.quantity }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Etat</label>
                            <select v-model="returnForm.condition" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="bon_etat">Bon état</option>
                                <option value="endommage">Endommagé</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input v-model="returnForm.reintegrate_stock" type="checkbox" class="rounded border-gray-300">
                                Réintégrer stock
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-600 mb-1 text-sm">Commentaire</label>
                        <textarea v-model="returnForm.note" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Commentaire retour"></textarea>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-100" @click="resetReturnForm">Annuler</button>
                        <button
                            type="button"
                            class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-40"
                            :disabled="savingReturn || !selectedReturnItem || maxReturnableQty <= 0"
                            @click="saveReturn"
                        >
                            {{ savingReturn ? 'Validation...' : 'Valider retour' }}
                        </button>
                    </div>

                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-3 py-2 text-left">Date</th>
                                    <th class="px-3 py-2 text-left">Article</th>
                                    <th class="px-3 py-2 text-right">Qté</th>
                                    <th class="px-3 py-2 text-left">Etat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="retour in returns" :key="retour.id">
                                    <td class="px-3 py-2">{{ formatDateTime(retour.created_at) }}</td>
                                    <td class="px-3 py-2">{{ retour.article?.name || retour.sale_item?.article_name || '-' }}</td>
                                    <td class="px-3 py-2 text-right">{{ retour.quantity }}</td>
                                    <td class="px-3 py-2">{{ retour.condition === 'bon_etat' ? 'Bon état' : 'Endommagé' }}</td>
                                </tr>
                                <tr v-if="!returns.length">
                                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">Aucun retour.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-2xl border border-gray-200 p-4 md:p-5 space-y-3">
                <h2 class="text-sm font-semibold uppercase text-gray-500 tracking-wide">Paiements enregistrés</h2>
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left">Date</th>
                                <th class="px-3 py-2 text-left">Mode</th>
                                <th class="px-3 py-2 text-left">Référence</th>
                                <th class="px-3 py-2 text-right">Montant</th>
                                <th class="px-3 py-2 text-left">Échéance</th>
                                <th class="px-3 py-2 text-left">Statut</th>
                                <th class="px-3 py-2 text-left">Commentaire</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="payment in commande.payments || []" :key="payment.id">
                                <td class="px-3 py-2">{{ formatDateTime(payment.paid_at || payment.created_at) }}</td>
                                <td class="px-3 py-2">{{ payment.payment_method_label || formatPaymentType(payment) }}</td>
                                <td class="px-3 py-2">{{ payment.reference_number || '-' }}</td>
                                <td class="px-3 py-2 text-right font-semibold">{{ formatCurrency(payment.amount || 0) }}</td>
                                <td class="px-3 py-2">{{ formatDate(payment.due_date) }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        {{ payment.workflow_status_label || formatCollectionStatus(payment) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-gray-600">{{ payment.collection_notes || payment.notes || '-' }}</td>
                            </tr>
                            <tr v-if="!(commande.payments || []).length">
                                <td colspan="7" class="px-3 py-4 text-center text-gray-500">Aucun paiement enregistré.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bg-white rounded-2xl border border-gray-200 p-4 md:p-5 space-y-3">
                <h2 class="text-sm font-semibold uppercase text-gray-500 tracking-wide">Journal de commande</h2>
                <div class="space-y-2">
                    <div v-for="entry in journal" :key="entry.id" class="border border-gray-200 rounded-xl p-3 text-sm">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-1">
                            <p class="font-semibold text-gray-900">{{ formatAction(entry.action) }}</p>
                            <p class="text-gray-500">{{ formatDateTime(entry.created_at) }}</p>
                        </div>
                        <p class="text-gray-600">Utilisateur: {{ entry.user?.name || '-' }}</p>
                        <p v-if="entry.status" class="text-gray-700">Statut: {{ formatOrderStatus(entry.status) }}</p>
                        <p class="text-gray-700" v-if="entry.comment">{{ entry.comment }}</p>
                    </div>
                    <p v-if="!journal.length" class="text-sm text-gray-500">Aucun événement.</p>
                </div>
            </section>
        </template>

        <PaymentMultiModal
            v-if="showPaymentModal && commande"
            :total="remainingAmount"
            :sale="commande"
            :allow-partial-confirmation="true"
            confirm-label="Valider le paiement"
            @close="showPaymentModal = false"
            @complete="submitPaymentsFromModal"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { commandesApi, salesApi } from '../../api'
import PaymentMultiModal from '../../components/pos/PaymentMultiModal.vue'
import { useCustomListsStore } from '../../stores/customLists'
import { useSettingsStore } from '../../stores/settings'

const route = useRoute()
const router = useRouter()
const customListsStore = useCustomListsStore()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const loading = ref(false)
const statusSaving = ref(false)
const savingPayment = ref(false)
const savingReturn = ref(false)
const showPaymentModal = ref(false)
const statusComment = ref('')

const commande = ref(null)
const journal = ref([])
const returns = ref([])

const selectedReturnItem = computed(() => {
    if (!commande.value || !returnForm.sale_item_id) return null
    return (commande.value.items || []).find((item) => Number(item.id) === Number(returnForm.sale_item_id)) || null
})

const alreadyReturnedQty = computed(() => {
    if (!returnForm.sale_item_id) return 0
    return (returns.value || [])
        .filter((retour) => Number(retour.sale_item_id) === Number(returnForm.sale_item_id))
        .reduce((sum, retour) => sum + Number(retour.quantity || 0), 0)
})

const maxReturnableQty = computed(() => {
    if (!selectedReturnItem.value) return 0
    return Math.max(0, Number(selectedReturnItem.value.quantity || 0) - alreadyReturnedQty.value)
})

const orderStatuses = [
    { value: 'confirmee', label: 'Confirmee' },
    { value: 'en_preparation', label: 'En preparation' },
    { value: 'envoyee', label: 'Envoyee' },
    { value: 'livree', label: 'Livree' },
    { value: 'retournee', label: 'Retournee' },
    { value: 'annulee', label: 'Annulee' },
]

const statusForm = reactive({
    order_status: 'confirmee',
})

const paymentForm = reactive({
    payment_type: 'cash',
    amount: 0,
    transaction_number: '',
    piece_number: '',
    issue_date: '',
    due_date: '',
    bank_name: '',
    notes: '',
})

const returnForm = reactive({
    sale_item_id: '',
    quantity: 1,
    condition: 'bon_etat',
    reintegrate_stock: true,
    note: '',
})

const advanceAmount = computed(() => {
    if (!commande.value) return 0
    return Number(commande.value.paid_confirmed_amount ?? commande.value.payment_summary?.paid_confirmed_amount ?? 0)
})

const pendingCollectionAmount = computed(() => {
    if (!commande.value) return 0
    return Number(commande.value.pending_collection_amount ?? commande.value.payment_summary?.pending_collection_amount ?? 0)
})

const nextOrderStatus = computed(() => {
    if (!commande.value) return null
    const sequence = ['confirmee', 'en_preparation', 'envoyee', 'livree']
    const current = commande.value.order_status || 'confirmee'
    const idx = sequence.indexOf(current)
    if (idx === -1 || idx >= sequence.length - 1) return null
    return sequence[idx + 1]
})

const remainingAmount = computed(() => {
    if (!commande.value) return 0
    return Number(commande.value.remaining_amount ?? commande.value.payment_summary?.remaining_amount ?? 0)
})

const canOpenPayment = computed(() => remainingAmount.value > 0.001)

function formatDate(value) {
    if (!value) return '-'
    return new Date(value).toLocaleDateString('fr-FR')
}

function formatDateTime(value) {
    if (!value) return '-'
    return new Date(value).toLocaleString('fr-FR')
}

function formatOrigin(origin, ticketType = null) {
    if (ticketType === 'liste') return 'Ticket liste'
    if (ticketType === 'personnalise') return 'Ticket personnalise'
    if (ticketType === 'commande') return 'Commande'

    const map = { pos: 'POS', menu_commande: 'Menu commande', livraison: 'Livraison' }
    return map[origin] || 'POS'
}

function formatServiceMode(mode) {
    return customListsStore.getServiceModeLabel(mode)
}

function formatOrderStatus(status) {
    return orderStatuses.find((s) => s.value === status)?.label || 'Confirmee'
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

function formatPaymentType(payment) {
    const transferSimple = payment.payment_type === 'virement'
        && (payment.transfer_mode === 'simple' || String(payment.notes || '').includes('[VIREMENT_SIMPLE]'))
    const transferInstant = payment.payment_type === 'virement'
        && (payment.transfer_mode === 'instant' || String(payment.notes || '').includes('[VIREMENT_INSTANT]'))
    if (transferSimple) return 'Virement simple'
    if (transferInstant) return 'Virement instantané'
    const map = {
        cash: 'Cash',
        card: 'Carte',
        mobile: 'Mobile',
        cheque: 'Chèque',
        check: 'Chèque',
        virement: 'Virement',
        credit: 'Crédit',
        simple_transfer: 'Virement simple',
        instant_transfer: 'Virement instantané',
    }
    return map[payment.payment_type] || payment.payment_type
}

function formatCollectionStatus(payment) {
    if (!payment.is_deferred) return 'Immédiat'
    const map = {
        pending: 'À encaisser',
        to_collect: 'À encaisser',
        collected: 'Encaissé',
        paid: 'Payé',
        cancelled: 'Impayé',
        unpaid: 'Impayé',
    }
    return map[payment.collection_status] || 'À encaisser'
}

function formatAction(action) {
    const map = {
        commande_confirmee: 'Commande confirmée',
        commande_modifiee: 'Commande modifiée',
        statut_commande_modifie: 'Statut commande mis à jour',
        commande_annulee: 'Commande annulée',
        paiement: 'Paiement',
        retour: 'Retour marchandise',
        livraison: 'Livraison',
    }
    return map[action] || action
}

const isDeferredType = computed(() => ['cheque', 'credit', 'simple_transfer', 'virement'].includes(paymentForm.payment_type))
const requiresTransactionNumber = computed(() => ['card', 'mobile', 'cheque', 'simple_transfer', 'instant_transfer', 'virement'].includes(paymentForm.payment_type))
const requiresPieceNumber = computed(() => paymentForm.payment_type === 'credit')
const requiresBankName = computed(() => false)
const requiresDueDates = computed(() => ['cheque', 'credit', 'simple_transfer', 'virement'].includes(paymentForm.payment_type))
const allowsNotes = computed(() => isDeferredType.value || paymentForm.payment_type === 'instant_transfer')
const transactionLabel = computed(() => {
    if (paymentForm.payment_type === 'cheque') return 'N° chèque'
    if (paymentForm.payment_type === 'simple_transfer' || paymentForm.payment_type === 'virement') return 'N° transaction bancaire'
    if (paymentForm.payment_type === 'instant_transfer') return 'Référence transaction'
    return 'Référence'
})
const transactionPlaceholder = computed(() => {
    if (paymentForm.payment_type === 'cheque') return 'Ex: CHQ-000123'
    if (paymentForm.payment_type === 'simple_transfer' || paymentForm.payment_type === 'virement') return 'Ex: VIR-2026-001'
    if (paymentForm.payment_type === 'instant_transfer') return 'Ex: INST-2026-001'
    return 'Référence transaction'
})

async function refresh() {
    loading.value = true
    try {
        const id = route.params.id
        const [commandeRes, journalRes, returnsRes] = await Promise.all([
            commandesApi.get(id),
            commandesApi.journal(id),
            commandesApi.returns(id),
        ])
        commande.value = commandeRes.data
        statusForm.order_status = commande.value.order_status || 'confirmee'
        journal.value = journalRes.data || []
        returns.value = returnsRes.data || []
        paymentForm.amount = remainingAmount.value
        statusComment.value = ''
    } catch (error) {
        console.error('Erreur chargement fiche commande:', error)
        alert(error.response?.data?.message || 'Impossible de charger la commande.')
    } finally {
        loading.value = false
    }
}

async function saveStatus(targetStatus) {
    if (!commande.value || !targetStatus) return

    statusSaving.value = true
    try {
        statusForm.order_status = targetStatus
        await commandesApi.updateStatus(commande.value.id, {
            order_status: targetStatus,
            comment: statusComment.value || `Statut changé de ${commande.value.order_status} vers ${targetStatus}`,
        })
        await refresh()
        statusComment.value = ''
    } catch (error) {
        console.error('Erreur mise a jour statut:', error)
        alert(error.response?.data?.message || 'Impossible de mettre à jour le statut.')
    } finally {
        statusSaving.value = false
    }
}

async function savePayment() {
    if (!commande.value || Number(paymentForm.amount || 0) <= 0) return

    savingPayment.value = true
    try {
        if (requiresTransactionNumber.value && !String(paymentForm.transaction_number || '').trim()) {
            alert('Veuillez renseigner la référence de transaction.')
            return
        }

        if (requiresPieceNumber.value && !String(paymentForm.piece_number || '').trim()) {
            alert('Veuillez renseigner le numéro de pièce/effet.')
            return
        }

        if (requiresBankName.value && !String(paymentForm.bank_name || '').trim()) {
            alert('Veuillez renseigner la banque.')
            return
        }

        if (requiresDueDates.value) {
            const today = new Date().toISOString().slice(0, 10)
            if (!paymentForm.issue_date) paymentForm.issue_date = today
            if (!paymentForm.due_date) paymentForm.due_date = paymentForm.issue_date
            if (paymentForm.due_date < paymentForm.issue_date) paymentForm.due_date = paymentForm.issue_date
        }

        const normalizedType = ['simple_transfer', 'instant_transfer'].includes(paymentForm.payment_type)
            ? 'virement'
            : paymentForm.payment_type

        const noteMarker = paymentForm.payment_type === 'simple_transfer'
            ? '[VIREMENT_SIMPLE]'
            : (paymentForm.payment_type === 'instant_transfer' ? '[VIREMENT_INSTANT]' : '')
        const baseNote = paymentForm.notes?.trim() || 'Paiement saisi depuis Gestion paiement'
        const normalizedNotes = noteMarker ? `${noteMarker} ${baseNote}`.trim() : baseNote

        const payload = {
            payment_type: normalizedType,
            transfer_mode: paymentForm.payment_type === 'simple_transfer'
                ? 'simple'
                : (paymentForm.payment_type === 'instant_transfer' ? 'instant' : undefined),
            amount: Number(paymentForm.amount),
            notes: normalizedNotes,
        }

        if (payload.payment_type === 'cash') {
            payload.received_amount = payload.amount
        }

        if (requiresTransactionNumber.value) payload.transaction_number = paymentForm.transaction_number.trim()
        if (requiresPieceNumber.value) payload.piece_number = paymentForm.piece_number.trim()
        if (requiresBankName.value) payload.bank_name = paymentForm.bank_name.trim()
        if (requiresDueDates.value) {
            payload.issue_date = paymentForm.issue_date
            payload.due_date = paymentForm.due_date
        }

        await salesApi.addPayment(commande.value.id, payload)
        resetPaymentForm()
        await refresh()
    } catch (error) {
        console.error('Erreur paiement:', error)
        alert(error.response?.data?.message || 'Impossible d\'enregistrer le paiement.')
    } finally {
        savingPayment.value = false
    }
}

async function saveReturn() {
    if (!commande.value || !selectedReturnItem.value) return

    const qty = Number(returnForm.quantity || 0)

    if (qty <= 0) {
        alert('Veuillez saisir une quantité à retourner.')
        return
    }

    if (maxReturnableQty.value <= 0) {
        alert('Plus aucune quantité n\'est retournable pour cet article.')
        return
    }

    if (qty > maxReturnableQty.value) {
        alert(`Quantité maximale retournable: ${maxReturnableQty.value}`)
        return
    }

    savingReturn.value = true
    try {
        await commandesApi.addReturn(commande.value.id, {
            returns: [
                {
                    sale_item_id: Number(returnForm.sale_item_id),
                    quantity: Number(returnForm.quantity),
                    condition: returnForm.condition,
                    reintegrate_stock: Boolean(returnForm.reintegrate_stock),
                    note: returnForm.note || null,
                },
            ],
        })
        resetReturnForm()
        await refresh()
    } catch (error) {
        console.error('Erreur retour:', error)
        alert(error.response?.data?.message || 'Impossible de valider le retour.')
    } finally {
        savingReturn.value = false
    }
}

function resetPaymentForm() {
    paymentForm.payment_type = 'cash'
    paymentForm.amount = remainingAmount.value
    paymentForm.transaction_number = ''
    paymentForm.piece_number = ''
    paymentForm.issue_date = ''
    paymentForm.due_date = ''
    paymentForm.bank_name = ''
    paymentForm.notes = ''
}

function resetReturnForm() {
    returnForm.sale_item_id = ''
    returnForm.quantity = 1
    returnForm.condition = 'bon_etat'
    returnForm.reintegrate_stock = true
    returnForm.note = ''
}

watch(
    () => returnForm.sale_item_id,
    () => {
        if (maxReturnableQty.value > 0) {
            returnForm.quantity = Math.min(Number(returnForm.quantity || maxReturnableQty.value), maxReturnableQty.value)
        } else {
            returnForm.quantity = 0
        }
    }
)

function goList() {
    router.push({ name: 'commandes' })
}

function openPaymentModal() {
    if (!commande.value || remainingAmount.value <= 0) return
    showPaymentModal.value = true
}

async function submitPaymentsFromModal(payments) {
    if (!commande.value) return

    savingPayment.value = true
    try {
        const normalizePaymentType = (type) => {
            if (type === 'check') return 'cheque'
            return type
        }

        for (const payment of payments || []) {
            await salesApi.addPayment(commande.value.id, {
                ...payment,
                payment_type: normalizePaymentType(payment.payment_type || payment.type),
            })
        }

        showPaymentModal.value = false
        await refresh()
    } catch (error) {
        console.error('Erreur paiement:', error)
        alert(error.response?.data?.message || 'Impossible d\'enregistrer le paiement.')
    } finally {
        savingPayment.value = false
    }
}

onMounted(async () => {
    await settingsStore.fetchSettings()
    await customListsStore.fetchList('mode_de_service')
    await refresh()
})
</script>
