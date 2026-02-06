<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

            <!-- Modal -->
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full mx-auto z-10">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Paiement</h3>
                    <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <!-- Total -->
                    <div class="text-center mb-6">
                        <p class="text-sm text-gray-500">Total à payer</p>
                        <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(total) }}</p>
                    </div>

                    <!-- Payment Type -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type de paiement</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button 
                                v-for="type in paymentTypes"
                                :key="type.value"
                                @click="selectedType = type.value"
                                class="p-3 border-2 rounded-lg flex flex-col items-center justify-center transition-colors"
                                :class="selectedType === type.value ? 'border-primary-500 bg-primary-100 text-gray-900 font-medium' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <component :is="type.icon" class="w-6 h-6 mb-1" />
                                <span class="text-xs">{{ type.label }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Cash Amount (for cash payments) -->
                    <div v-if="selectedType === 'cash'" class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Montant reçu</label>
                        <input 
                            v-model.number="receivedAmount"
                            type="number"
                            step="0.01"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-primary-500"
                            placeholder="0.00"
                        >
                        
                        <!-- Quick amounts -->
                        <div class="grid grid-cols-4 gap-2 mt-2">
                            <button 
                                v-for="amount in quickAmounts"
                                :key="amount"
                                @click="receivedAmount = amount"
                                class="py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50"
                            >
                                {{ amount }}
                            </button>
                        </div>

                        <!-- Change -->
                        <div v-if="change > 0" class="mt-4 p-3 bg-primary-100 rounded-lg text-center">
                            <p class="text-sm text-gray-600 font-medium">Monnaie à rendre</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(change) }}</p>
                        </div>
                    </div>

                    <!-- Card Reference (for card payments) -->
                    <div v-if="selectedType === 'card'" class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Référence (optionnel)</label>
                        <input 
                            v-model="reference"
                            type="text"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                            placeholder="N° de transaction"
                        >
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex space-x-3">
                    <button 
                        @click="$emit('close')"
                        class="flex-1 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button 
                        @click="confirmPayment"
                        :disabled="!canConfirm"
                        class="flex-1 py-3 bg-primary-500 text-gray-900 font-bold rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Confirmer
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
    BanknotesIcon,
    CreditCardIcon,
    DevicePhoneMobileIcon,
    DocumentTextIcon,
    EllipsisHorizontalIcon
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

const selectedType = ref('cash')
const receivedAmount = ref(null)
const reference = ref('')

const paymentTypes = [
    { value: 'cash', label: 'Espèces', icon: BanknotesIcon },
    { value: 'card', label: 'Carte', icon: CreditCardIcon },
    { value: 'mobile', label: 'Mobile', icon: DevicePhoneMobileIcon },
    { value: 'check', label: 'Chèque', icon: DocumentTextIcon },
    { value: 'other', label: 'Autre', icon: EllipsisHorizontalIcon },
]

const quickAmounts = computed(() => {
    const amounts = [10, 20, 50, 100, 200, 500]
    return amounts.filter(a => a >= props.total)
})

const change = computed(() => {
    if (selectedType.value !== 'cash' || !receivedAmount.value) return 0
    return Math.max(0, receivedAmount.value - props.total)
})

const canConfirm = computed(() => {
    if (selectedType.value === 'cash') {
        return receivedAmount.value >= props.total
    }
    return true
})

function confirmPayment() {
    const paymentData = {
        payment_type: selectedType.value,
        amount: props.total,
        received_amount: selectedType.value === 'cash' ? receivedAmount.value : null,
        reference: reference.value || null,
    }
    
    emit('complete', paymentData)
}
</script>
