<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <button type="button" class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50" @click="goBack">
                <ArrowLeftIcon class="w-5 h-5 text-gray-600" />
            </button>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Rembourser le ticket {{ formatTicketNumber(ticket) }}</h1>
                <p v-if="ticket" class="text-sm text-gray-500 mt-0.5">
                    {{ formatDateTime(ticket.created_at) }}
                    <span v-if="formatLocation(ticket) !== '-'"> · {{ formatLocation(ticket) }}</span>
                    · {{ ticket.items?.length || 0 }} article(s)
                    · {{ formatPaymentMethod(ticket) }}
                    · Total: {{ formatCurrency(ticket.total || 0) }}
                </p>
            </div>
        </div>

        <div v-if="loading" class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-500">
            Chargement du ticket...
        </div>

        <template v-else-if="ticket">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <!-- Left: Ticket articles -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Articles du ticket</h2>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input v-model="selectAll" type="checkbox" class="rounded border-gray-300" @change="toggleSelectAll">
                            Sélectionner tout
                        </label>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-3 w-10"></th>
                                    <th class="px-4 py-3 text-left">Article</th>
                                    <th class="px-4 py-3 text-center">Qté vendue</th>
                                    <th class="px-4 py-3 text-right">Prix unitaire</th>
                                    <th class="px-4 py-3 text-right">Total</th>
                                    <th class="px-4 py-3 text-center">Sélection</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="line in articleLines"
                                    :key="line.sale_item_id"
                                    class="hover:bg-gray-50"
                                    :class="{ 'bg-blue-50/50': line.selected }"
                                >
                                    <td class="px-4 py-3">
                                        <input
                                            v-model="line.selected"
                                            type="checkbox"
                                            class="rounded border-gray-300"
                                            :disabled="line.maxQty <= 0"
                                            @change="onLineSelectChange(line)"
                                        >
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ line.article_name }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700">{{ line.soldQty }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ formatCurrency(line.unitPrice) }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">{{ formatCurrency(line.lineTotal) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1">
                                            <button
                                                type="button"
                                                class="w-7 h-7 rounded-lg border border-gray-300 hover:bg-gray-100 disabled:opacity-40"
                                                :disabled="!line.selected || line.refundQty <= 0"
                                                @click="adjustQty(line, -1)"
                                            >−</button>
                                            <span class="w-8 text-center font-medium">{{ line.refundQty }}</span>
                                            <button
                                                type="button"
                                                class="w-7 h-7 rounded-lg border border-gray-300 hover:bg-gray-100 disabled:opacity-40"
                                                :disabled="!line.selected || line.refundQty >= line.maxQty"
                                                @click="adjustQty(line, 1)"
                                            >+</button>
                                            <span class="text-xs text-gray-400 ml-1">Max: {{ line.maxQty }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!articleLines.length">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">Aucun article disponible pour remboursement.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right: Refunded items + summary -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h2 class="font-semibold text-gray-900">Articles remboursés</h2>
                            <button
                                type="button"
                                class="text-sm text-red-600 hover:text-red-700 disabled:opacity-40"
                                :disabled="!refundedItems.length"
                                @click="clearRefundedList"
                            >
                                Vider la liste
                            </button>
                        </div>
                        <div v-if="!refundedItems.length" class="px-5 py-10 text-center text-gray-400 text-sm">
                            Aucun article ajouté
                        </div>
                        <table v-else class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">Article</th>
                                    <th class="px-4 py-3 text-center">Qté remboursée</th>
                                    <th class="px-4 py-3 text-right">Prix unitaire</th>
                                    <th class="px-4 py-3 text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in refundedItems" :key="item.sale_item_id">
                                    <td class="px-4 py-3 text-gray-900">{{ item.article_name }}</td>
                                    <td class="px-4 py-3 text-center">{{ item.quantity }}</td>
                                    <td class="px-4 py-3 text-right">{{ formatCurrency(item.unitPrice) }}</td>
                                    <td class="px-4 py-3 text-right font-medium">{{ formatCurrency(item.amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-5">
                        <h2 class="font-semibold text-gray-900">Résumé du remboursement</h2>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Nombre d'articles</span>
                                <span class="font-medium">{{ refundSummary.itemCount }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Sous-total</span>
                                <span class="font-medium">{{ formatCurrency(refundSummary.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Taxes remboursées</span>
                                <span class="font-medium">{{ formatCurrency(refundSummary.tax) }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-gray-200">
                                <span class="font-semibold text-gray-900">Montant à rembourser</span>
                                <span class="text-xl font-bold text-green-600">{{ formatCurrency(refundSummary.total) }}</span>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">Mode de remboursement</p>
                            <div v-if="!paymentMethods.length" class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center text-sm text-gray-500">
                                Aucun mode de paiement configuré. Ajoutez-en dans Paramètres &gt; Listes personnalisées &gt; Mode de paiement.
                            </div>
                            <div v-else class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                <button
                                    v-for="method in paymentMethods"
                                    :key="method.id"
                                    type="button"
                                    class="w-full rounded-[16px] border px-3 py-3 text-left transition-all duration-150"
                                    :class="selectedMethodId === method.id
                                        ? 'border-sky-300 bg-sky-50 shadow-sm ring-2 ring-sky-100'
                                        : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'"
                                    @click="selectPaymentMethod(method)"
                                >
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg"
                                            :class="selectedMethodId === method.id ? 'bg-white text-sky-700' : 'bg-gray-100 text-gray-700'"
                                        >
                                            {{ method.icon }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-gray-900">{{ method.label }}</p>
                                            <p class="mt-1 text-[11px] font-medium text-gray-500">{{ paymentTimingLabel(method) }}</p>
                                            <p class="mt-1 hidden text-xs text-gray-400 sm:block">{{ method.description }}</p>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div v-if="selectedMethod" class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-lg shadow-sm">
                                    {{ selectedMethod.icon }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ selectedMethod.label }}</p>
                                    <p class="text-xs text-gray-500">{{ selectedMethod.description }}</p>
                                </div>
                            </div>

                            <div v-if="hasVisibleExtraFields(selectedMethod)" class="rounded-xl border border-gray-200 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Informations du remboursement</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div v-if="selectedMethod.show_transaction_number">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ fieldLabels.transactionLabel }} *</label>
                                        <input
                                            v-model="paymentForm.transaction_number"
                                            type="text"
                                            :placeholder="fieldLabels.transactionPlaceholder"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-sky-300 focus:ring-2 focus:ring-sky-100 outline-none"
                                        >
                                    </div>
                                    <div v-if="selectedMethod.show_piece_number">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ fieldLabels.pieceLabel }} *</label>
                                        <input
                                            v-model="paymentForm.piece_number"
                                            type="text"
                                            :placeholder="fieldLabels.piecePlaceholder"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-sky-300 focus:ring-2 focus:ring-sky-100 outline-none"
                                        >
                                    </div>
                                    <div v-if="selectedMethod.show_issue_date">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Date d'émission *</label>
                                        <input
                                            v-model="paymentForm.issue_date"
                                            type="date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-sky-300 focus:ring-2 focus:ring-sky-100 outline-none"
                                        >
                                    </div>
                                    <div v-if="selectedMethod.show_due_date">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Date d'échéance *</label>
                                        <input
                                            v-model="paymentForm.due_date"
                                            type="date"
                                            :min="paymentForm.issue_date || undefined"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-sky-300 focus:ring-2 focus:ring-sky-100 outline-none"
                                        >
                                    </div>
                                    <div v-if="selectedMethod.show_bank_name" class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ fieldLabels.bankLabel }} *</label>
                                        <input
                                            v-model="paymentForm.bank_name"
                                            type="text"
                                            :placeholder="fieldLabels.bankPlaceholder"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-sky-300 focus:ring-2 focus:ring-sky-100 outline-none"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div v-if="selectedMethod.show_notes" class="rounded-xl border border-gray-200 bg-white p-4">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Note</label>
                                <textarea
                                    v-model="paymentForm.notes"
                                    placeholder="Remarques..."
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:border-sky-300 focus:ring-2 focus:ring-sky-100 outline-none"
                                ></textarea>
                            </div>

                            <div v-if="selectedMethod.paymentTiming === 'deferred'" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                Ce mode de paiement est différé et sera suivi dans le module Encaissement.
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Motif</label>
                                <select v-model="refundForm.reason" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <option value="">Sélectionner un motif (optionnel)</option>
                                    <option v-for="r in refundReasons" :key="r.value" :value="r.value">{{ r.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire</label>
                                <input
                                    v-model="refundForm.note"
                                    type="text"
                                    placeholder="Commentaire optionnel sur le remboursement"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between bg-white rounded-xl border border-gray-200 px-5 py-4 shadow-sm">
                <button type="button" class="px-5 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium" @click="goBack">
                    Annuler
                </button>
                <button
                    type="button"
                    class="px-6 py-2.5 bg-primary-500 text-gray-900 rounded-lg hover:bg-primary-600 font-semibold flex items-center gap-2 disabled:opacity-50"
                    :disabled="!canValidate || submitting"
                    @click="validateRefund"
                >
                    <CheckIcon class="w-5 h-5" />
                    {{ submitting ? 'Traitement...' : 'Valider le remboursement' }}
                </button>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeftIcon, CheckIcon } from '@heroicons/vue/24/outline'
import { salesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { useCustomListsStore, PAYMENT_MODE_LIST_NAME } from '../../stores/customLists'
import {
    encodePaymentModeLabel,
    findMatchingPaymentMethod,
    findPaymentMethodLabel,
    getEmptyPaymentForm,
    getPaymentFieldLabels,
    hasVisibleExtraFields,
    mapPaymentModeItem,
    paymentTimingLabel,
    prefillPaymentFormFromPayment,
    resolveApiPaymentMethod,
    resolveApiTransferMode,
    validatePaymentMethodFields,
} from '../../utils/paymentMethods'

const route = useRoute()
const router = useRouter()
const settingsStore = useSettingsStore()
const customListsStore = useCustomListsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const loading = ref(true)
const submitting = ref(false)
const ticket = ref(null)
const existingReturns = ref([])
const selectAll = ref(false)
const selectedMethodId = ref(null)

const articleLines = ref([])

const refundForm = reactive({
    reason: '',
    note: '',
})

const paymentForm = reactive(getEmptyPaymentForm())
const originalPayment = ref(null)

const paymentMethods = computed(() => {
    return customListsStore.activePaymentModes.map((item, index) => mapPaymentModeItem(item, index))
})

const selectedMethod = computed(() => {
    return paymentMethods.value.find((method) => method.id === selectedMethodId.value) || null
})

const fieldLabels = computed(() => getPaymentFieldLabels(selectedMethod.value))

const refundReasons = [
    { value: 'erreur_commande', label: 'Erreur de commande' },
    { value: 'client_insatisfait', label: 'Client insatisfait' },
    { value: 'article_defectueux', label: 'Article défectueux' },
    { value: 'double_paiement', label: 'Double paiement' },
    { value: 'autre', label: 'Autre' },
]

const taxRate = computed(() => Number(ticket.value?.tax_rate || 0))

const refundedItems = computed(() => {
    return articleLines.value
        .filter((line) => line.selected && line.refundQty > 0)
        .map((line) => ({
            sale_item_id: line.sale_item_id,
            article_name: line.article_name,
            quantity: line.refundQty,
            unitPrice: line.unitPrice,
            amount: line.unitPrice * line.refundQty,
        }))
})

const refundSummary = computed(() => {
    const subtotal = refundedItems.value.reduce((sum, item) => sum + item.amount, 0)
    const tax = subtotal * (taxRate.value / 100)
    const itemCount = refundedItems.value.reduce((sum, item) => sum + item.quantity, 0)
    return {
        subtotal,
        tax,
        total: subtotal + tax,
        itemCount,
    }
})

const canValidate = computed(() => {
    return refundedItems.value.length > 0
        && refundSummary.value.total > 0
        && !!selectedMethod.value
        && validatePaymentMethodFields(selectedMethod.value, paymentForm)
})

function formatTicketNumber(t) {
    if (!t) return ''
    return `#${String(t.id).padStart(6, '0')}`
}

function formatDateTime(date) {
    if (!date) return '-'
    const d = new Date(date)
    return `${d.toLocaleDateString('fr-FR')} à ${d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}`
}

function formatLocation(t) {
    if (!t) return '-'
    const parts = []
    if (t.ticket_name) parts.push(t.ticket_name)
    if (t.ticket_group) parts.push(t.ticket_group)
    return parts.length ? parts.join(' - ') : '-'
}

function formatPaymentMethod(t) {
    const payments = t?.payments || []
    if (!payments.length) return 'Non payé'

    const payment = payments.find((p) => Number(p.amount) > 0) || payments[0]
    const configuredLabel = findPaymentMethodLabel(
        payment?.payment_type,
        payment?.transfer_mode,
        paymentMethods.value
    )
    if (configuredLabel) return configuredLabel

    const type = payment?.payment_type
    const map = {
        cash: 'Espèces',
        card: 'Carte',
        mobile: 'Mobile',
        credit: 'Avoir client',
        virement: 'Virement',
        cheque: 'Chèque',
        other: 'Autre',
    }
    return map[type] || type || 'Espèces'
}

function selectPaymentMethod(method) {
    selectedMethodId.value = method.id
    Object.assign(paymentForm, getEmptyPaymentForm())

    if (originalPayment.value && findMatchingPaymentMethod(originalPayment.value, [method])) {
        Object.assign(paymentForm, prefillPaymentFormFromPayment(originalPayment.value, method))
    }
}

function selectDefaultPaymentMethod(sale) {
    if (!paymentMethods.value.length) {
        selectedMethodId.value = null
        Object.assign(paymentForm, getEmptyPaymentForm())
        return
    }

    originalPayment.value = sale?.payments?.find((p) => Number(p.amount) > 0) || sale?.payments?.[0] || null
    const matchedMethod = findMatchingPaymentMethod(originalPayment.value, paymentMethods.value)
    if (matchedMethod) {
        selectPaymentMethod(matchedMethod)
        return
    }

    const configuredDefault = paymentMethods.value.find((method) => method.isDefault)
    const cashMethod = paymentMethods.value.find((method) => method.paymentType === 'cash')
    const fallback = configuredDefault || cashMethod || paymentMethods.value[0]
    if (fallback) {
        selectPaymentMethod(fallback)
    }
}

function alreadyReturnedQty(saleItemId) {
    return existingReturns.value
        .filter((r) => Number(r.sale_item_id) === Number(saleItemId))
        .reduce((sum, r) => sum + Number(r.quantity || 0), 0)
}

function buildArticleLines() {
    const items = ticket.value?.items || []
    articleLines.value = items.map((item) => {
        const soldQty = Number(item.quantity || 0)
        const returned = alreadyReturnedQty(item.id)
        const maxQty = Math.max(0, soldQty - returned)
        const unitPrice = soldQty > 0 ? Number(item.total || 0) / soldQty : Number(item.unit_price || 0)
        return {
            sale_item_id: item.id,
            article_name: item.article_name || item.article?.name || 'Article',
            soldQty,
            maxQty,
            unitPrice,
            lineTotal: Number(item.total || 0),
            selected: false,
            refundQty: maxQty > 0 ? 1 : 0,
        }
    })
}

function onLineSelectChange(line) {
    if (line.selected && line.refundQty === 0 && line.maxQty > 0) {
        line.refundQty = 1
    }
    if (!line.selected) {
        line.refundQty = 0
    }
    updateSelectAllState()
}

function adjustQty(line, delta) {
    if (!line.selected) {
        line.selected = true
    }
    line.refundQty = Math.max(0, Math.min(line.maxQty, line.refundQty + delta))
    if (line.refundQty === 0) {
        line.selected = false
    }
    updateSelectAllState()
}

function toggleSelectAll() {
    const eligible = articleLines.value.filter((l) => l.maxQty > 0)
    eligible.forEach((line) => {
        line.selected = selectAll.value
        line.refundQty = selectAll.value ? line.maxQty : 0
    })
}

function updateSelectAllState() {
    const eligible = articleLines.value.filter((l) => l.maxQty > 0)
    selectAll.value = eligible.length > 0 && eligible.every((l) => l.selected)
}

function clearRefundedList() {
    articleLines.value.forEach((line) => {
        line.selected = false
        line.refundQty = 0
    })
    selectAll.value = false
}

function goBack() {
    router.push({ name: 'historique-ticket' })
}

async function loadTicket() {
    loading.value = true
    try {
        const id = route.params.id
        const [{ data: sale }, { data: returns }] = await Promise.all([
            salesApi.get(id),
            salesApi.returns(id),
            customListsStore.fetchList(PAYMENT_MODE_LIST_NAME, { force: true }),
        ])
        if (sale.status !== 'completed') {
            alert('Ce ticket ne peut pas être remboursé.')
            goBack()
            return
        }
        ticket.value = sale
        existingReturns.value = Array.isArray(returns) ? returns : []
        buildArticleLines()
        selectDefaultPaymentMethod(sale)
    } catch (error) {
        console.error(error)
        alert('Impossible de charger le ticket.')
        goBack()
    } finally {
        loading.value = false
    }
}

async function validateRefund() {
    if (!canValidate.value || !ticket.value || !selectedMethod.value) return
    submitting.value = true
    try {
        const reasonLabel = refundReasons.find((r) => r.value === refundForm.reason)?.label
        const transferMode = resolveApiTransferMode(selectedMethod.value)
        const encodedPaymentNotes = encodePaymentModeLabel(
            selectedMethod.value.label,
            selectedMethod.value.paymentTiming,
            paymentForm.notes
        )

        await salesApi.refund(ticket.value.id, {
            items: refundedItems.value.map((item) => ({
                sale_item_id: item.sale_item_id,
                quantity: item.quantity,
            })),
            payment_method: resolveApiPaymentMethod(selectedMethod.value),
            transfer_mode: transferMode || undefined,
            transaction_number: paymentForm.transaction_number.trim() || undefined,
            piece_number: paymentForm.piece_number.trim() || undefined,
            issue_date: paymentForm.issue_date || undefined,
            due_date: paymentForm.due_date || undefined,
            bank_name: paymentForm.bank_name.trim() || undefined,
            payment_notes: encodedPaymentNotes || undefined,
            reason: reasonLabel || refundForm.reason || undefined,
            note: refundForm.note || undefined,
            reintegrate_stock: true,
        })
        router.push({ name: 'historique-ticket' })
    } catch (error) {
        alert(error.response?.data?.message || 'Impossible de valider le remboursement.')
    } finally {
        submitting.value = false
    }
}

onMounted(loadTicket)
</script>
