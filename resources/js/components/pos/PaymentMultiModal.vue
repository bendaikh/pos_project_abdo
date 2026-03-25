<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

            <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-auto z-10 max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">💳 Paiement Multiple</h3>
                    <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <div v-if="sale" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-4">
                        <div class="grid gap-3 md:grid-cols-2 text-sm">
                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-500">Commande N°</p>
                                <p class="font-semibold text-slate-900">{{ sale.order_number || sale.reference || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-500">Client</p>
                                <p class="font-semibold text-slate-900">{{ sale.customer?.name || 'Client anonyme' }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500">Articles</p>
                            <p class="text-sm text-slate-800">
                                {{ orderArticles.length ? orderArticles.map((item) => `${item.article_name} x${item.quantity}`).join(', ') : '-' }}
                            </p>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5 text-sm">
                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                <p class="text-xs uppercase text-slate-500">Montant total</p>
                                <p class="text-base font-semibold text-slate-900">{{ formatCurrency(sale.total || 0) }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                <p class="text-xs uppercase text-slate-500">Montant déjà payé</p>
                                <p class="text-base font-semibold text-emerald-700">{{ formatCurrency(paidConfirmedAmount) }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                <p class="text-xs uppercase text-slate-500">En attente d'encaissement</p>
                                <p class="text-base font-semibold text-amber-700">{{ formatCurrency(pendingCollectionAmount) }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                <p class="text-xs uppercase text-slate-500">Reste à payer</p>
                                <p class="text-base font-semibold text-slate-900">{{ formatCurrency(remainingToPayNow) }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                <p class="text-xs uppercase text-slate-500">Statut paiement</p>
                                <p class="text-base font-semibold text-slate-900">{{ sale.payment_status_label || 'À payer' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border border-blue-200">
                        <div class="text-center">
                            <p class="text-xs text-gray-600 font-medium">Total à Payer</p>
                            <p class="text-xl font-bold text-gray-900">{{ formatCurrency(total) }}</p>
                        </div>
                        <div class="text-center border-l border-r border-blue-200">
                            <p class="text-xs text-gray-600 font-medium">Total Payé</p>
                            <p class="text-xl font-bold text-green-600">{{ formatCurrency(totalPaid) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-600 font-medium">Restant</p>
                            <p class="text-xl font-bold" :class="remaining <= 0 ? 'text-green-600' : 'text-orange-600'">
                                {{ formatCurrency(remaining) }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-3">🔵 Méthode de Paiement</label>
                        <div v-if="paymentMethods.length" class="grid grid-cols-2 gap-3 md:grid-cols-3">
                            <button
                                v-for="method in paymentMethods"
                                :key="method.id"
                                @click="selectMethod(method)"
                                class="p-4 border-2 rounded-lg flex flex-col items-center justify-center transition-all hover:shadow-md"
                                :class="selectedMethod?.id === method.id
                                    ? 'border-primary-500 bg-primary-50 shadow-md'
                                    : 'border-gray-200 hover:border-gray-300'"
                            >
                                <span class="text-2xl mb-2">{{ method.icon }}</span>
                                <span class="text-sm font-medium text-gray-900 text-center">{{ method.label }}</span>
                                <span class="text-xs text-gray-500 mt-1">{{ method.description }}</span>
                            </button>
                        </div>
                        <div v-else class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-sm text-gray-500">
                            Aucun mode de paiement actif n’est configuré dans les paramètres.
                        </div>
                    </div>

                    <div v-if="selectedMethod" class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 mb-4">{{ selectedMethod.label }} - Détails</h4>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Montant *</label>
                            <div class="flex gap-2">
                                <input
                                    v-model.number="paymentForm.amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :max="remaining + 0.01"
                                    placeholder="0.00"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                                <span class="flex items-center px-3 text-gray-500 font-medium">{{ settingsStore.currencyCode }}</span>
                            </div>
                            <p v-if="paymentForm.amount > remaining" class="text-xs text-orange-600 mt-1">
                                ⚠️ Montant supérieur au restant ({{ formatCurrency(remaining) }})
                            </p>
                        </div>

                        <template v-if="selectedMethod.id === 'cash'">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Billets (montant reçu) *</label>
                                    <div class="flex gap-2">
                                        <input
                                            v-model.number="paymentForm.received_amount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="0.00"
                                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        >
                                        <span class="flex items-center px-3 text-gray-500 font-medium">{{ settingsStore.currencyCode }}</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Monnaie (calculée)</label>
                                    <div class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg">
                                        <span class="text-lg font-bold text-green-600">{{ formatCurrency(calculateChange) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="text-xs text-gray-600 mb-2">Montants rapides:</p>
                                <div class="grid grid-cols-4 gap-2">
                                    <button
                                        v-for="amount in quickCashAmounts"
                                        :key="amount"
                                        @click="paymentForm.received_amount = amount"
                                        class="py-2 px-3 text-sm border border-gray-300 rounded-lg hover:bg-primary-50 hover:border-primary-300 transition-colors font-medium"
                                    >
                                        {{ formatCurrency(amount) }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div v-if="selectedMethod.id !== 'cash' && hasVisibleExtraFields" class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
                            <div v-if="selectedMethod.show_transaction_number">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ transactionFieldLabel }} *</label>
                                <input
                                    v-model="paymentForm.transaction_number"
                                    type="text"
                                    :placeholder="transactionFieldPlaceholder"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                            </div>
                            <div v-if="selectedMethod.show_piece_number">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ pieceFieldLabel }} *</label>
                                <input
                                    v-model="paymentForm.piece_number"
                                    type="text"
                                    :placeholder="pieceFieldPlaceholder"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                            </div>
                            <div v-if="selectedMethod.show_issue_date">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date d'émission *</label>
                                <input
                                    v-model="paymentForm.issue_date"
                                    type="date"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                            </div>
                            <div v-if="selectedMethod.show_due_date">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date d'échéance *</label>
                                <input
                                    v-model="paymentForm.due_date"
                                    type="date"
                                    :min="paymentForm.issue_date || undefined"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                            </div>
                            <div v-if="selectedMethod.show_bank_name">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ bankFieldLabel }} *</label>
                                <input
                                    v-model="paymentForm.bank_name"
                                    type="text"
                                    :placeholder="bankFieldPlaceholder"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                            </div>
                        </div>

                        <p v-if="selectedMethod.paymentTiming === 'deferred'" class="text-xs text-orange-700 mt-2">
                            Ce mode de paiement est différé et sera suivi dans le module Encaissement.
                        </p>

                        <div v-if="selectedMethod.show_notes" class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (optionnel)</label>
                            <textarea
                                v-model="paymentForm.notes"
                                placeholder="Remarques ou informations supplémentaires..."
                                rows="2"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
                            ></textarea>
                        </div>

                        <button
                            @click="addPayment"
                            :disabled="!canAddPayment"
                            class="w-full mt-6 py-3 bg-primary-500 text-gray-900 font-bold rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
                        >
                            <PlusIcon class="w-5 h-5 mr-2" />
                            Ajouter ce paiement
                        </button>
                    </div>

                    <div v-if="payments.length > 0" class="bg-white rounded-xl border border-gray-200 p-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">📋 Paiements Ajoutés ({{ payments.length }})</h4>
                        <div class="space-y-2">
                            <div
                                v-for="(payment, index) in payments"
                                :key="index"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200"
                            >
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ payment.display_label || getMethodLabel(payment.payment_type, payment.transfer_mode) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ payment.transaction_number || payment.reference || 'Aucune référence' }}
                                    </p>
                                </div>
                                <div class="text-right mr-3">
                                    <p class="text-lg font-bold text-gray-900">{{ formatCurrency(payment.amount) }}</p>
                                </div>
                                <button
                                    @click="removePayment(index)"
                                    class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Supprimer ce paiement"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex space-x-3">
                    <button
                        @click="$emit('close')"
                        class="flex-1 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        Annuler
                    </button>
                    <button
                        @click="confirmPayments"
                        :disabled="!canConfirmPayments"
                        class="flex-1 py-3 bg-green-500 text-gray-900 font-bold rounded-lg hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
                    >
                        <CheckIcon class="w-5 h-5 mr-2" />
                        {{ confirmLabelText }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useSettingsStore } from '../../stores/settings'
import { useCustomListsStore } from '../../stores/customLists'
import {
    XMarkIcon,
    PlusIcon,
    TrashIcon,
    CheckIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    total: {
        type: Number,
        required: true
    },
    sale: {
        type: Object,
        default: null
    },
    allowPartialConfirmation: {
        type: Boolean,
        default: false
    },
    confirmLabel: {
        type: String,
        default: 'Finaliser le Paiement'
    }
})

const emit = defineEmits(['close', 'complete'])

const settingsStore = useSettingsStore()
const customListsStore = useCustomListsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const saleSummary = computed(() => props.sale?.payment_summary || null)
const paidConfirmedAmount = computed(() => Number(saleSummary.value?.paid_confirmed_amount || 0))
const pendingCollectionAmount = computed(() => Number(saleSummary.value?.pending_collection_amount || 0))
const remainingToPayNow = computed(() => Number(saleSummary.value?.remaining_amount || props.total || 0))
const orderArticles = computed(() => Array.isArray(props.sale?.items) ? props.sale.items : [])

const selectedMethod = ref(null)
const payments = ref([])
const paymentForm = ref(getEmptyForm())

const paymentMethods = computed(() => {
    return customListsStore.activePaymentModes.map((item, index) => ({
        id: createMethodId(item, index),
        paymentType: item.payment_type || 'other',
        transferMode: item.transfer_mode || null,
        label: item.label,
        description: describePaymentMethod(item),
        icon: iconForPaymentMethod(item),
        isDefault: item.is_default === true,
        paymentTiming: item.payment_timing === 'deferred' ? 'deferred' : 'immediate',
        show_transaction_number: item.show_transaction_number === true,
        show_piece_number: item.show_piece_number === true,
        show_issue_date: item.show_issue_date === true,
        show_due_date: item.show_due_date === true,
        show_bank_name: item.show_bank_name === true,
        show_notes: item.show_notes !== false,
    }))
})

function getEmptyForm() {
    return {
        amount: 0,
        payment_type: null,
        received_amount: null,
        transaction_number: '',
        piece_number: '',
        issue_date: '',
        bank_name: '',
        due_date: '',
        notes: ''
    }
}

const totalPaid = computed(() => {
    return payments.value.reduce((sum, payment) => sum + Number(payment.amount || 0), 0)
})

const remaining = computed(() => {
    return Math.max(0, props.total - totalPaid.value)
})

const calculateChange = computed(() => {
    if (!paymentForm.value.received_amount || !paymentForm.value.amount) return 0
    return Math.max(0, paymentForm.value.received_amount - paymentForm.value.amount)
})

const quickCashAmounts = computed(() => {
    const base = [100, 200, 500, 1000]
    if (!base.includes(Math.ceil(props.total))) {
        base.push(Math.ceil(props.total))
    }
    return base.sort((a, b) => a - b)
})

const canAddPayment = computed(() => {
    if (!selectedMethod.value || !paymentForm.value.amount || paymentForm.value.amount <= 0) return false

    if (selectedMethod.value.id === 'cash') {
        return paymentForm.value.received_amount && paymentForm.value.received_amount >= paymentForm.value.amount
    }

    const requiredFields = [
        !selectedMethod.value.show_transaction_number || !!paymentForm.value.transaction_number,
        !selectedMethod.value.show_piece_number || !!paymentForm.value.piece_number,
        !selectedMethod.value.show_issue_date || !!paymentForm.value.issue_date,
        !selectedMethod.value.show_due_date || !!paymentForm.value.due_date,
        !selectedMethod.value.show_bank_name || !!paymentForm.value.bank_name,
    ]

    return requiredFields.every(Boolean)
})

const canConfirmPayments = computed(() => {
    return payments.value.length > 0 && (props.allowPartialConfirmation || remaining.value <= 0)
})

const confirmLabelText = computed(() => props.confirmLabel || 'Finaliser le Paiement')

const hasVisibleExtraFields = computed(() => {
    if (!selectedMethod.value) return false

    return [
        selectedMethod.value.show_transaction_number,
        selectedMethod.value.show_piece_number,
        selectedMethod.value.show_issue_date,
        selectedMethod.value.show_due_date,
        selectedMethod.value.show_bank_name,
    ].some(Boolean)
})

const transactionFieldLabel = computed(() => {
    if (selectedMethod.value?.paymentType === 'virement') return 'N° de transaction'
    return 'N° transaction'
})

const transactionFieldPlaceholder = computed(() => {
    if (selectedMethod.value?.paymentType === 'virement') return 'N° opération bancaire'
    return 'Ex: 12345678, ABC123XYZ'
})

const pieceFieldLabel = computed(() => {
    if (selectedMethod.value?.paymentType === 'credit') return 'N° pièce'
    return 'N° pièce'
})

const pieceFieldPlaceholder = computed(() => {
    if (selectedMethod.value?.paymentType === 'credit') return 'Référence dossier / effet / LCN'
    return 'CIN / justificatif'
})

const bankFieldLabel = computed(() => 'Banque')
const bankFieldPlaceholder = computed(() => 'Nom de la banque')

function normalizeKey(value) {
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
}

function createMethodId(item, index) {
    if (item.payment_type === 'cash') return 'cash'
    if (item.payment_type === 'card') return 'card'
    if (item.payment_type === 'mobile') return 'mobile'
    if (item.payment_type === 'credit') return 'credit'
    if (item.payment_type === 'virement' && item.transfer_mode === 'instant') return 'instant_transfer'
    if (item.payment_type === 'virement') return 'simple_transfer'

    return `other:${normalizeKey(item.label || item.value || index)}`
}

function iconForPaymentMethod(item) {
    if (item.payment_type === 'cash') return '💵'
    if (item.payment_type === 'card') return '💳'
    if (item.payment_type === 'mobile') return '📱'
    if (item.payment_type === 'virement' && item.transfer_mode === 'instant') return '⚡'
    if (item.payment_type === 'virement') return '🏦'
    if (item.payment_type === 'credit') return '📋'
    return '🧾'
}

function describePaymentMethod(item) {
    if (item.payment_type === 'cash') return 'Paiement en liquide'
    if (item.payment_type === 'card') return 'Carte bancaire'
    if (item.payment_type === 'mobile') return 'Paiement mobile'
    if (item.payment_type === 'virement' && item.transfer_mode === 'instant') return 'Transfert bancaire rapide'
    if (item.payment_type === 'virement') return 'Transfert bancaire standard'
    if (item.payment_type === 'credit') return 'Paiement différé'
    return 'Mode personnalisé'
}

function encodePaymentModeLabel(label, paymentTiming, notes) {
    const cleanNotes = String(notes || '')
        .replace(/\[PAYMENT_TIMING:[^\]]+\]\s*/g, '')
        .replace(/\[PAYMENT_MODE_LABEL:[^\]]+\]\s*/g, '')
        .trim()
    const cleanLabel = String(label || '').trim()
    const timing = paymentTiming === 'deferred' ? 'deferred' : 'immediate'
    const timingMarker = `[PAYMENT_TIMING:${timing}]`

    if (!cleanLabel) {
        return cleanNotes ? `${timingMarker} ${cleanNotes}` : timingMarker
    }

    const marker = `[PAYMENT_MODE_LABEL:${cleanLabel}]`
    return cleanNotes ? `${marker} ${timingMarker} ${cleanNotes}` : `${marker} ${timingMarker}`
}

function selectDefaultMethod() {
    if (!paymentMethods.value.length) {
        selectedMethod.value = null
        paymentForm.value = getEmptyForm()
        return
    }

    const configuredDefault = paymentMethods.value.find((method) => method.isDefault)
    selectMethod(configuredDefault || paymentMethods.value[0])
}

function selectMethod(method) {
    selectedMethod.value = method
    const suggestedAmount = Number(remaining.value || 0)
    paymentForm.value = {
        ...getEmptyForm(),
        payment_type: method.paymentType,
        amount: suggestedAmount,
        received_amount: method.id === 'cash' ? suggestedAmount : null,
    }
}

function addPayment() {
    if (!canAddPayment.value) return

    const apiPaymentType = ['simple_transfer', 'instant_transfer'].includes(selectedMethod.value.id)
        ? selectedMethod.value.id
        : selectedMethod.value.paymentType
    const transferMode = selectedMethod.value.id === 'simple_transfer'
        ? 'simple'
        : (selectedMethod.value.id === 'instant_transfer' ? 'instant' : null)

    payments.value.push({
        payment_type: apiPaymentType,
        transfer_mode: transferMode,
        amount: paymentForm.value.amount,
        received_amount: paymentForm.value.received_amount,
        change_amount: selectedMethod.value.id === 'cash' ? calculateChange.value : 0,
        transaction_number: paymentForm.value.transaction_number,
        piece_number: paymentForm.value.piece_number,
        issue_date: paymentForm.value.issue_date,
        bank_name: paymentForm.value.bank_name,
        due_date: paymentForm.value.due_date,
        reference: selectedMethod.value.id === 'credit'
            ? paymentForm.value.piece_number
            : paymentForm.value.transaction_number || paymentForm.value.piece_number,
        notes: encodePaymentModeLabel(selectedMethod.value.label, selectedMethod.value.paymentTiming, paymentForm.value.notes),
        display_label: selectedMethod.value.label,
    })

    selectDefaultMethod()
}

function removePayment(index) {
    payments.value.splice(index, 1)
}

function getMethodLabel(type, transferMode = null) {
    const method = paymentMethods.value.find((item) => {
        if (item.paymentType !== type) {
            return false
        }

        if (item.paymentType !== 'virement') {
            return true
        }

        return (item.transferMode || 'simple') === (transferMode || 'simple')
    })

    return method?.label || type
}

function confirmPayments() {
    if (!canConfirmPayments.value) return
    emit('complete', payments.value)
}

watch(paymentMethods, () => {
    if (!paymentMethods.value.length) {
        selectedMethod.value = null
        paymentForm.value = getEmptyForm()
        return
    }

    const stillExists = paymentMethods.value.find((method) => method.id === selectedMethod.value?.id)
    if (!stillExists) {
        selectDefaultMethod()
    }
}, { immediate: true })

onMounted(async () => {
    await customListsStore.fetchList('mode_de_paiement', { force: true })
    selectDefaultMethod()
})
</script>
