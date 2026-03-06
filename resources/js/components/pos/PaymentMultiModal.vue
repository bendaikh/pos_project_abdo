<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

            <!-- Modal -->
            <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-auto z-10 max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">💳 Paiement Multiple</h3>
                    <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6 space-y-6">
                    <!-- Payment Summary (Top) -->
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

                    <!-- Payment Methods Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-3">🔵 Méthode de Paiement</label>
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
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
                    </div>

                    <!-- Payment Form (Dynamic based on selected method) -->
                    <div v-if="selectedMethod" class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 mb-4">{{ selectedMethod.label }} - Détails</h4>
                        
                        <!-- Common: Amount -->
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

                        <!-- Espèce: Billets & Monnaie -->
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

                            <!-- Quick Amount Buttons -->
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

                        <!-- Carte / Mobile / Virement Instantané: Transaction Number -->
                        <template v-if="['card', 'mobile', 'instant_transfer'].includes(selectedMethod.id)">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Numéro de Transaction *</label>
                                <input 
                                    v-model="paymentForm.transaction_number"
                                    type="text"
                                    placeholder="Ex: 12345678, ABC123XYZ"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                            </div>
                        </template>

                        <!-- Virement Simple / Chèque / Crédit: All Fields -->
                        <template v-if="['simple_transfer', 'check', 'credit'].includes(selectedMethod.id)">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ transactionLabel }}{{ requiresTransaction ? ' *' : '' }}</label>
                                    <input 
                                        v-model="paymentForm.transaction_number"
                                        type="text"
                                        :placeholder="transactionPlaceholder"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ pieceLabel }}</label>
                                    <input 
                                        v-model="paymentForm.piece_number"
                                        type="text"
                                        :placeholder="piecePlaceholder"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date d'émission *</label>
                                    <input 
                                        v-model="paymentForm.issue_date"
                                        type="date"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Banque {{ requiresBankName ? '*' : '' }}</label>
                                    <input 
                                        v-model="paymentForm.bank_name"
                                        type="text"
                                        :placeholder="bankPlaceholder"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date d'échéance *</label>
                                <input 
                                    v-model="paymentForm.due_date"
                                    type="date"
                                    :min="paymentForm.issue_date || undefined"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                            </div>
                            <p class="text-xs text-orange-700 mt-2">
                                Ce mode de paiement est différé et sera suivi dans le module Encaissement.
                            </p>
                        </template>

                        <!-- Notes -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (optionnel)</label>
                            <textarea 
                                v-model="paymentForm.notes"
                                placeholder="Remarques ou informations supplémentaires..."
                                rows="2"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
                            ></textarea>
                        </div>

                        <!-- Add Payment Button -->
                        <button 
                            @click="addPayment"
                            :disabled="!canAddPayment"
                            class="w-full mt-6 py-3 bg-primary-500 text-gray-900 font-bold rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
                        >
                            <PlusIcon class="w-5 h-5 mr-2" />
                            Ajouter ce paiement
                        </button>
                    </div>

                    <!-- Added Payments List -->
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
                                        {{ getMethodLabel(payment.payment_type) }}
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

                <!-- Footer -->
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
                        Finaliser le Paiement
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useSettingsStore } from '../../stores/settings'
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
    }
})

const emit = defineEmits(['close', 'complete'])

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const paymentMethods = [
    {
        id: 'cash',
        paymentType: 'cash',
        label: 'Espèce',
        description: 'Paiement en liquide',
        icon: '💵'
    },
    {
        id: 'card',
        paymentType: 'card',
        label: 'Carte',
        description: 'Carte bancaire',
        icon: '💳'
    },
    {
        id: 'mobile',
        paymentType: 'mobile',
        label: 'Mobile',
        description: 'Paiement mobile',
        icon: '📱'
    },
    {
        id: 'instant_transfer',
        paymentType: 'virement',
        label: 'Virement Instantané',
        description: 'Transfert bancaire rapide',
        icon: '⚡'
    },
    {
        id: 'simple_transfer',
        paymentType: 'virement',
        label: 'Virement Simple',
        description: 'Transfert bancaire standard',
        icon: '🏦'
    },
    {
        id: 'check',
        paymentType: 'cheque',
        label: 'Chèque',
        description: 'Paiement par chèque',
        icon: '📄'
    },
    {
        id: 'credit',
        paymentType: 'credit',
        label: 'Crédit (LCN)',
        description: 'Lettre de change',
        icon: '📋'
    }
]

const selectedMethod = ref(null)
const payments = ref([])
const paymentForm = ref(getEmptyForm())

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
    return payments.value.reduce((sum, p) => sum + p.amount, 0)
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
    // Add amount closest to total
    if (!base.includes(Math.ceil(props.total))) {
        base.push(Math.ceil(props.total))
    }
    return base.sort((a, b) => a - b)
})

const canAddPayment = computed(() => {
    if (!selectedMethod.value || !paymentForm.value.amount || paymentForm.value.amount <= 0) return false
    
    // Check for method-specific required fields
    if (selectedMethod.value.id === 'cash') {
        return paymentForm.value.received_amount && paymentForm.value.received_amount >= paymentForm.value.amount
    }
    
    if (['card', 'mobile', 'instant_transfer'].includes(selectedMethod.value.id)) {
        return !!paymentForm.value.transaction_number
    }
    
    if (selectedMethod.value.id === 'simple_transfer') {
        return !!(
            paymentForm.value.transaction_number &&
            paymentForm.value.bank_name &&
            paymentForm.value.issue_date &&
            paymentForm.value.due_date
        )
    }

    if (selectedMethod.value.id === 'check') {
        return !!(
            paymentForm.value.transaction_number &&
            paymentForm.value.bank_name &&
            paymentForm.value.issue_date &&
            paymentForm.value.due_date
        )
    }

    if (selectedMethod.value.id === 'credit') {
        return !!(
            paymentForm.value.piece_number &&
            paymentForm.value.issue_date &&
            paymentForm.value.due_date
        )
    }
    
    return false
})

const canConfirmPayments = computed(() => {
    return payments.value.length > 0 && remaining.value <= 0
})

const transactionLabel = computed(() => {
    if (selectedMethod.value?.id === 'check') return 'N° de chèque'
    if (selectedMethod.value?.id === 'simple_transfer') return 'N° de transaction'
    return 'Référence dossier'
})
const requiresTransaction = computed(() => ['simple_transfer', 'check'].includes(selectedMethod.value?.id))

const transactionPlaceholder = computed(() => {
    if (selectedMethod.value?.id === 'check') return 'N° chèque'
    if (selectedMethod.value?.id === 'simple_transfer') return 'N° opération bancaire'
    return 'Référence crédit (optionnel)'
})

const pieceLabel = computed(() => {
    if (selectedMethod.value?.id === 'credit') return 'N° effet / LCN *'
    return 'N° pièce (optionnel)'
})

const piecePlaceholder = computed(() => {
    if (selectedMethod.value?.id === 'credit') return 'N° effet / lettre de change'
    return 'CIN / justificatif'
})

const requiresBankName = computed(() => ['simple_transfer', 'check'].includes(selectedMethod.value?.id))
const bankPlaceholder = computed(() => selectedMethod.value?.id === 'credit' ? 'Banque (optionnel)' : 'Nom de la banque')

function selectMethod(method) {
    selectedMethod.value = method
    paymentForm.value.payment_type = method.paymentType
    paymentForm.value = {
        ...paymentForm.value,
        payment_type: method.paymentType,
        amount: 0,
        received_amount: null,
        transaction_number: '',
        piece_number: '',
        issue_date: '',
        bank_name: '',
        due_date: '',
        notes: ''
    }
}

function addPayment() {
    if (!canAddPayment.value) return

    const payment = {
        payment_type: selectedMethod.value.paymentType,
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
        notes: paymentForm.value.notes
    }

    payments.value.push(payment)
    
    // Reset form
    selectedMethod.value = null
    paymentForm.value = getEmptyForm()
}

function removePayment(index) {
    payments.value.splice(index, 1)
}

function getMethodLabel(type) {
    const method = paymentMethods.find(m => m.id === type || m.paymentType === type)
    return method ? method.label : type
}

function confirmPayments() {
    if (!canConfirmPayments.value) return
    
    emit('complete', payments.value)
}
</script>
