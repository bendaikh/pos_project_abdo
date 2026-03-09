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
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Statut commande</label>
                        <select v-model="statusForm.order_status" class="px-3 py-2 border border-gray-300 rounded-lg" @change="saveStatus">
                            <option v-for="status in orderStatuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                        </select>
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
                        <p class="font-semibold text-gray-900 mt-1">{{ formatOrigin(commande.origin) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-3 border border-green-200">
                        <p class="text-green-700 text-xs font-semibold uppercase">Date retrait</p>
                        <p class="font-semibold text-gray-900 mt-1">{{ formatDate(commande.pickup_date) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-3 border border-amber-200">
                        <p class="text-amber-700 text-xs font-semibold uppercase">Activité</p>
                        <p class="font-semibold text-gray-900 mt-1">{{ commande.customer_activity || '-' }}</p>
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
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                    <div class="bg-blue-50 rounded-xl p-3 border border-blue-100">
                        <p class="text-blue-700">Montant total</p>
                        <p class="text-lg font-bold text-blue-900">{{ formatCurrency(commande.total || 0) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3 border border-green-100">
                        <p class="text-green-700">Avance</p>
                        <p class="text-lg font-bold text-green-900">{{ formatCurrency(advanceAmount) }}</p>
                    </div>
                    <div class="bg-amber-50 rounded-xl p-3 border border-amber-100">
                        <p class="text-amber-700">Reste à payer</p>
                        <p class="text-lg font-bold text-amber-900">{{ formatCurrency(remainingAmount) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-200">
                        <p class="text-gray-700">Statut paiement</p>
                        <p class="text-lg font-bold text-gray-900">{{ formatPaymentStatus(commande.payment_status) }}</p>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                <div class="bg-white rounded-2xl border border-gray-200 p-4 md:p-5 space-y-4">
                    <h2 class="text-sm font-semibold uppercase text-gray-500 tracking-wide">Gestion paiement</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                        <div>
                            <label class="block text-gray-600 mb-1">Mode de paiement</label>
                            <select v-model="paymentForm.payment_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="cash">Cash</option>
                                <option value="card">Carte</option>
                                <option value="mobile">Mobile</option>
                                <option value="cheque">Chèque (différé)</option>
                                <option value="simple_transfer">Virement simple (différé)</option>
                                <option value="instant_transfer">Virement instantané</option>
                                <option value="credit">Crédit (différé)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Montant</label>
                            <input v-model.number="paymentForm.amount" type="number" min="0.01" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="button" class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-40" :disabled="savingPayment" @click="savePayment">
                                {{ savingPayment ? 'Validation...' : 'Valider paiement' }}
                            </button>
                            <button type="button" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-100" @click="resetPaymentForm">Annuler</button>
                        </div>
                    </div>

                    <div v-if="requiresTransactionNumber || requiresPieceNumber || requiresBankName || requiresDueDates || allowsNotes" class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div v-if="requiresTransactionNumber">
                            <label class="block text-gray-600 mb-1">{{ transactionLabel }}</label>
                            <input v-model.trim="paymentForm.transaction_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" :placeholder="transactionPlaceholder">
                        </div>
                        <div v-if="requiresPieceNumber">
                            <label class="block text-gray-600 mb-1">N° pièce / effet</label>
                            <input v-model.trim="paymentForm.piece_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Référence pièce">
                        </div>
                        <div v-if="requiresBankName">
                            <label class="block text-gray-600 mb-1">Banque</label>
                            <input v-model.trim="paymentForm.bank_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Nom de la banque">
                        </div>
                        <div v-if="requiresDueDates">
                            <label class="block text-gray-600 mb-1">Date émission</label>
                            <input v-model="paymentForm.issue_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div v-if="requiresDueDates">
                            <label class="block text-gray-600 mb-1">Date échéance</label>
                            <input v-model="paymentForm.due_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div v-if="allowsNotes" class="md:col-span-2">
                            <label class="block text-gray-600 mb-1">Note</label>
                            <input v-model.trim="paymentForm.notes" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Note interne paiement">
                        </div>
                    </div>

                    <p v-if="isDeferredType" class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        Ce paiement est différé. Il sera suivi dans le module Suivi Encaissement jusqu'à validation finale.
                    </p>

                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-3 py-2 text-left">Date</th>
                                    <th class="px-3 py-2 text-left">Mode</th>
                                    <th class="px-3 py-2 text-left">Statut</th>
                                    <th class="px-3 py-2 text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="payment in commande.payments" :key="payment.id">
                                    <td class="px-3 py-2">{{ formatDateTime(payment.created_at) }}</td>
                                    <td class="px-3 py-2">{{ formatPaymentType(payment) }}</td>
                                    <td class="px-3 py-2">{{ formatCollectionStatus(payment) }}</td>
                                    <td class="px-3 py-2 text-right font-semibold">{{ formatCurrency(payment.amount) }}</td>
                                </tr>
                                <tr v-if="!commande.payments?.length">
                                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">Aucun paiement.</td>
                                </tr>
                            </tbody>
                        </table>
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
                            <input v-model.number="returnForm.quantity" type="number" min="0.001" step="0.001" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
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
                        <button type="button" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-40" :disabled="savingReturn" @click="saveReturn">
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
                <h2 class="text-sm font-semibold uppercase text-gray-500 tracking-wide">Journal de commande</h2>
                <div class="space-y-2">
                    <div v-for="entry in journal" :key="entry.id" class="border border-gray-200 rounded-xl p-3 text-sm">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-1">
                            <p class="font-semibold text-gray-900">{{ formatAction(entry.action) }}</p>
                            <p class="text-gray-500">{{ formatDateTime(entry.created_at) }}</p>
                        </div>
                        <p class="text-gray-600">Utilisateur: {{ entry.user?.name || '-' }}</p>
                        <p class="text-gray-700" v-if="entry.comment">{{ entry.comment }}</p>
                    </div>
                    <p v-if="!journal.length" class="text-sm text-gray-500">Aucun événement.</p>
                </div>
            </section>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { commandesApi, salesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'

const route = useRoute()
const router = useRouter()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const loading = ref(false)
const savingPayment = ref(false)
const savingReturn = ref(false)

const commande = ref(null)
const journal = ref([])
const returns = ref([])

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
    return (commande.value.payments || []).reduce((sum, payment) => sum + Number(payment.amount || 0), 0)
})

const remainingAmount = computed(() => {
    if (!commande.value) return 0
    return Math.max(0, Number(commande.value.total || 0) - advanceAmount.value)
})

function formatDate(value) {
    if (!value) return '-'
    return new Date(value).toLocaleDateString('fr-FR')
}

function formatDateTime(value) {
    if (!value) return '-'
    return new Date(value).toLocaleString('fr-FR')
}

function formatOrigin(origin) {
    const map = { pos: 'POS', menu_commande: 'Menu commande', livraison: 'Livraison' }
    return map[origin] || 'POS'
}

function formatPaymentStatus(status) {
    const map = { unpaid: 'A payer', partial: 'Partiel', paid: 'Payee' }
    return map[status] || 'A payer'
}

function formatPaymentType(payment) {
    const transferSimple = payment.payment_type === 'virement' && String(payment.notes || '').includes('[VIREMENT_SIMPLE]')
    const transferInstant = payment.payment_type === 'virement' && String(payment.notes || '').includes('[VIREMENT_INSTANT]')
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
    }
    return map[payment.payment_type] || payment.payment_type
}

function formatCollectionStatus(payment) {
    if (!payment.is_deferred) return 'Immédiat'
    const map = {
        pending: 'En attente',
        collected: 'Payé',
        cancelled: 'Impayé',
    }
    return map[payment.collection_status] || 'En attente'
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
    } catch (error) {
        console.error('Erreur chargement fiche commande:', error)
        alert(error.response?.data?.message || 'Impossible de charger la commande.')
    } finally {
        loading.value = false
    }
}

async function saveStatus() {
    if (!commande.value) return

    try {
        await commandesApi.updateStatus(commande.value.id, {
            order_status: statusForm.order_status,
            comment: 'Statut modifié depuis la fiche commande',
        })
        await refresh()
    } catch (error) {
        console.error('Erreur mise a jour statut:', error)
        alert(error.response?.data?.message || 'Impossible de mettre à jour le statut.')
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

        const payload = {
            payment_type: paymentForm.payment_type,
            transfer_mode: paymentForm.payment_type === 'simple_transfer'
                ? 'simple'
                : (paymentForm.payment_type === 'instant_transfer' ? 'instant' : undefined),
            amount: Number(paymentForm.amount),
            notes: paymentForm.notes || 'Paiement saisi depuis Gestion paiement',
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
    if (!commande.value || !returnForm.sale_item_id || Number(returnForm.quantity || 0) <= 0) return

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

function goList() {
    router.push({ name: 'commandes' })
}

onMounted(async () => {
    await settingsStore.fetchSettings()
    await refresh()
})
</script>
