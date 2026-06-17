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
                            <div class="grid grid-cols-4 gap-2">
                                <button
                                    v-for="method in paymentMethods"
                                    :key="method.value"
                                    type="button"
                                    class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 transition"
                                    :class="refundForm.payment_method === method.value
                                        ? 'border-primary-500 bg-primary-50 text-primary-700'
                                        : 'border-gray-200 hover:border-gray-300 text-gray-600'"
                                    @click="refundForm.payment_method = method.value"
                                >
                                    <component :is="method.icon" class="w-6 h-6" />
                                    <span class="text-xs font-medium">{{ method.label }}</span>
                                </button>
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                                <input
                                    v-model="refundForm.note"
                                    type="text"
                                    placeholder="Commentaire optionnel"
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
import {
    ArrowLeftIcon,
    BanknotesIcon,
    CheckIcon,
    CreditCardIcon,
    DevicePhoneMobileIcon,
    TicketIcon,
} from '@heroicons/vue/24/outline'
import { salesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'

const route = useRoute()
const router = useRouter()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const loading = ref(true)
const submitting = ref(false)
const ticket = ref(null)
const existingReturns = ref([])
const selectAll = ref(false)

const articleLines = ref([])

const refundForm = reactive({
    payment_method: 'cash',
    reason: '',
    note: '',
})

const paymentMethods = [
    { value: 'cash', label: 'Espèces', icon: BanknotesIcon },
    { value: 'card', label: 'Carte', icon: CreditCardIcon },
    { value: 'mobile', label: 'Mobile', icon: DevicePhoneMobileIcon },
    { value: 'credit', label: 'Avoir client', icon: TicketIcon },
]

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

const canValidate = computed(() => refundedItems.value.length > 0 && refundSummary.value.total > 0)

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
    const type = payments.find((p) => Number(p.amount) > 0)?.payment_type || payments[0]?.payment_type
    const map = { cash: 'Espèces', card: 'Carte', mobile: 'Mobile', credit: 'Avoir client', virement: 'Virement', cheque: 'Chèque' }
    return map[type] || type || 'Espèces'
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
        ])
        if (sale.status !== 'completed') {
            alert('Ce ticket ne peut pas être remboursé.')
            goBack()
            return
        }
        ticket.value = sale
        existingReturns.value = Array.isArray(returns) ? returns : []
        buildArticleLines()
        const defaultPayment = sale.payments?.find((p) => Number(p.amount) > 0)?.payment_type
        if (defaultPayment && ['cash', 'card', 'mobile', 'credit'].includes(defaultPayment)) {
            refundForm.payment_method = defaultPayment
        }
    } catch (error) {
        console.error(error)
        alert('Impossible de charger le ticket.')
        goBack()
    } finally {
        loading.value = false
    }
}

async function validateRefund() {
    if (!canValidate.value || !ticket.value) return
    submitting.value = true
    try {
        const reasonLabel = refundReasons.find((r) => r.value === refundForm.reason)?.label
        await salesApi.refund(ticket.value.id, {
            items: refundedItems.value.map((item) => ({
                sale_item_id: item.sale_item_id,
                quantity: item.quantity,
            })),
            payment_method: refundForm.payment_method,
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
