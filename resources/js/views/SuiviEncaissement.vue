<template>
  <div class="p-4 md:p-6 space-y-5 bg-slate-50 min-h-full">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-slate-900">Suivi Encaissements</h1>
        <p class="text-slate-600 mt-1">Suivi simple des paiements différés: virement, chèque, crédit.</p>
      </div>
      <button
        class="bg-amber-400 text-slate-900 font-semibold px-4 py-2.5 rounded-xl border border-amber-500/40 shadow-sm"
        disabled
      >
        Encaissements à valider : {{ pendingCount }}
      </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4 md:p-5 space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Statut</label>
          <select v-model="filters.status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Tous</option>
            <option value="pending">En attente</option>
            <option value="collected">Validé / Payé</option>
            <option value="cancelled">Impayé</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Mode de paiement</label>
          <select v-model="filters.paymentType" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Tous</option>
            <option value="cheque">Chèque</option>
            <option value="virement">Virement</option>
            <option value="credit">Crédit</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Date depuis</label>
          <input
            v-model="filters.dateFrom"
            type="date"
            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"
          />
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Date jusqu'à</label>
          <input
            v-model="filters.dateTo"
            type="date"
            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"
          />
        </div>
      </div>
      <div class="flex flex-wrap gap-2">
        <button
          @click="applyFilters"
          class="px-4 py-2 bg-slate-900 text-white rounded-lg font-medium text-sm hover:bg-slate-800 transition"
        >
          Filtrer
        </button>
        <button
          @click="toggleOverdue"
          :class="showOnlyOverdue ? 'bg-rose-600 text-white' : 'bg-slate-200 text-slate-700'"
          class="px-4 py-2 rounded-lg font-medium text-sm"
        >
          Arriérés seulement
        </button>
        <button
          @click="toggleDueToday"
          :class="showOnlyDueToday ? 'bg-amber-600 text-white' : 'bg-slate-200 text-slate-700'"
          class="px-4 py-2 rounded-lg font-medium text-sm"
        >
          À encaisser aujourd'hui
        </button>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
      <div class="px-4 md:px-6 py-4 border-b border-slate-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-slate-900">Paiements différés</h2>
        <span class="text-sm text-slate-500">{{ filteredPayments.length }} paiement(s)</span>
      </div>
      <div v-if="loading" class="p-8 text-center text-slate-500">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2">Chargement...</p>
      </div>
      <div v-else-if="filteredPayments.length === 0" class="p-8 text-center text-slate-500">
        Aucun paiement trouvé
      </div>
      <table v-else class="min-w-full text-sm text-left">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
          <tr>
            <th class="px-4 py-3">Date</th>
            <th class="px-4 py-3">Client</th>
            <th class="px-4 py-3">Facture n°</th>
            <th class="px-4 py-3">Mode de paiement</th>
            <th class="px-4 py-3">N° pièce</th>
            <th class="px-4 py-3">Montant</th>
            <th class="px-4 py-3">Date d'échéance</th>
            <th class="px-4 py-3">Statut</th>
            <th class="px-4 py-3">Motif</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="payment in filteredPayments"
            :key="payment.id"
            class="border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
            @click="openPaymentAction(payment)"
          >
            <td class="px-4 py-3 text-gray-600">{{ formatDate(payment.created_at) }}</td>
            <td class="px-4 py-3 text-gray-900 font-medium">{{ payment.sale?.customer?.name || '-' }}</td>
            <td class="px-4 py-3 text-gray-600">{{ payment.sale?.reference || '-' }}</td>
            <td class="px-4 py-3">
              <span :class="getPaymentTypeClass(payment.payment_type)" class="px-2 py-1 rounded text-xs font-semibold">
                {{ getPaymentTypeName(payment) }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ payment.piece_number || payment.transaction_number || '-' }}</td>
            <td class="px-4 py-3 font-bold text-gray-900">{{ formatCurrency(payment.amount) }}</td>
            <td class="px-4 py-3 text-gray-600">{{ formatDate(payment.due_date) }}</td>
            <td class="px-4 py-3">
              <span :class="getStatusClass(payment.collection_status)" class="px-3 py-1 rounded-full text-xs font-semibold">
                {{ getStatusLabel(payment.collection_status) }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ getPaymentMotif(payment) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Validation Modal -->
    <div
      v-if="showValidationModal && selectedPayment"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
      <div class="bg-white w-full max-w-md rounded-lg shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">
            Valider le Paiement de {{ selectedPayment.sale?.customer?.name || 'Client' }}
          </h3>
          <button class="text-gray-400 hover:text-gray-600" @click="closeValidationModal">✕</button>
        </div>
        <div class="p-6 space-y-5">
          <div class="space-y-2 border-b border-gray-200 pb-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">En haut</div>
            <div class="flex justify-between text-gray-700">
              <span>Client :</span>
              <span class="font-semibold">{{ selectedPayment.sale?.customer?.name || 'Client inconnu' }}</span>
            </div>
            <div class="flex justify-between text-gray-700">
              <span>Chèque N° :</span>
              <span class="font-semibold">{{ selectedPayment.reference || selectedPayment.piece_number || '-' }}</span>
            </div>
            <div class="flex justify-between text-gray-700">
              <span>Montant :</span>
              <span class="font-semibold">{{ formatCurrency(selectedPayment.amount) }}</span>
            </div>
          </div>

          <div class="space-y-3">
            <div class="text-xs uppercase text-gray-500 font-semibold">En bas</div>
            <div class="flex items-center gap-3">
              <label class="flex items-center gap-2 text-sm">
                <input type="radio" value="collected" v-model="validationForm.status" class="form-radio" />
                <span>Payé</span>
              </label>
              <label class="flex items-center gap-2 text-sm">
                <input type="radio" value="cancelled" v-model="validationForm.status" class="form-radio" />
                <span>Impayé</span>
              </label>
            </div>
            <div v-if="validationForm.status !== 'collected'" class="space-y-2">
              <label class="text-sm text-gray-600">Motif d'impayé</label>
              <textarea
                v-model="validationForm.notes"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                rows="3"
                placeholder="Motif d'impayé..."
              ></textarea>
            </div>
            <div class="space-y-2">
              <label class="text-sm text-gray-600">Historique des statuts</label>
              <div class="max-h-40 overflow-y-auto space-y-2">
                <div
                  v-for="record in historyRecords"
                  :key="`history-${record.id}`"
                  class="border border-gray-100 rounded px-3 py-2 text-xs text-gray-700"
                >
                  <div class="flex justify-between">
                    <span>{{ formatDate(record.created_at) }}</span>
                    <span class="font-semibold capitalize">{{ record.action }}</span>
                  </div>
                  <p class="text-gray-500 text-[11px]">{{ record.notes || 'Aucun motif' }}</p>
                </div>
                <div v-if="!historyRecords.length" class="text-gray-400 text-xs">Aucun historique</div>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3">
            <button
              @click="closeValidationModal"
              class="px-4 py-2 border border-gray-300 rounded text-sm font-semibold text-gray-700 hover:bg-gray-50"
            >
              Annuler
            </button>
            <button
              @click="submitValidation"
              class="px-4 py-2 rounded text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500"
            >
              Valider
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Status Modal -->
    <div
      v-if="showStatusModal && selectedPayment"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
      <div class="bg-white w-full max-w-md rounded-lg shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">Changer le Statut du Paiement</h3>
          <button class="text-gray-400 hover:text-gray-600" @click="closeStatusModal">✕</button>
        </div>
        <div class="p-6 space-y-5">
          <div class="space-y-2 border-b border-gray-200 pb-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">En haut</div>
            <div class="flex justify-between text-gray-700">
              <span>Client :</span>
              <span class="font-semibold">{{ selectedPayment.sale?.customer?.name || 'Client inconnu' }}</span>
            </div>
            <div class="flex justify-between text-gray-700">
              <span>Chèque N° :</span>
              <span class="font-semibold">{{ selectedPayment.reference || selectedPayment.piece_number || '-' }}</span>
            </div>
            <div class="flex justify-between text-gray-700">
              <span>Montant :</span>
              <span class="font-semibold">{{ formatCurrency(selectedPayment.amount) }}</span>
            </div>
          </div>

          <div class="space-y-3">
            <div class="text-xs uppercase text-gray-500 font-semibold">En bas</div>
            <p class="text-sm text-gray-700">
              Statut actuel :
              <span class="font-semibold" :class="getStatusTextClass(selectedPayment.collection_status)">
                {{ getStatusLabel(selectedPayment.collection_status) }}
              </span>
            </p>
            <div class="space-y-2 text-sm text-gray-700">
              <label class="flex items-center gap-2">
                <input type="radio" value="collected" v-model="statusForm.status" class="form-radio" />
                <span>Payé</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="radio" value="pending" v-model="statusForm.status" class="form-radio" />
                <span>En cours</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="radio" value="cancelled" v-model="statusForm.status" class="form-radio" />
                <span>Impayé</span>
              </label>
            </div>
            <div v-if="statusForm.status !== 'collected'" class="space-y-2">
              <label class="text-sm text-gray-600">Motif</label>
              <textarea
                v-model="statusForm.notes"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                rows="3"
                placeholder="Motif..."
              ></textarea>
            </div>
            <div class="space-y-2">
              <label class="text-sm text-gray-600">Historique des statuts</label>
              <div class="max-h-40 overflow-y-auto space-y-2">
                <div
                  v-for="record in historyRecords"
                  :key="`status-history-${record.id}`"
                  class="border border-gray-100 rounded px-3 py-2 text-xs text-gray-700"
                >
                  <div class="flex justify-between">
                    <span>{{ formatDate(record.created_at) }}</span>
                    <span class="font-semibold capitalize">{{ record.action }}</span>
                  </div>
                  <p class="text-gray-500 text-[11px]">{{ record.notes || 'Aucun motif' }}</p>
                </div>
                <div v-if="!historyRecords.length" class="text-gray-400 text-xs">Aucun historique</div>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3">
            <button
              @click="closeStatusModal"
              class="px-4 py-2 border border-gray-300 rounded text-sm font-semibold text-gray-700 hover:bg-gray-50"
            >
              Annuler
            </button>
            <button
              @click="submitStatusChange"
              class="px-4 py-2 rounded text-sm font-semibold text-white bg-red-600 hover:bg-red-500"
            >
              Enregistrer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../api'

const loading = ref(false)
const payments = ref([])

const filters = ref({
  status: '',
  paymentType: '',
  dateFrom: '',
  dateTo: '',
})
const showOnlyOverdue = ref(false)
const showOnlyDueToday = ref(false)

const showValidationModal = ref(false)
const showStatusModal = ref(false)
const selectedPayment = ref(null)
const historyRecords = ref([])

const validationForm = ref({
  status: 'collected',
  notes: '',
})
const statusForm = ref({
  status: 'pending',
  notes: '',
})

const today = computed(() => new Date().toISOString().split('T')[0])
const pendingCount = computed(() => payments.value.filter((p) => p.collection_status === 'pending').length)

const filteredPayments = computed(() => {
  let result = payments.value

  if (filters.value.status) {
    result = result.filter((p) => p.collection_status === filters.value.status)
  }
  if (filters.value.paymentType) {
    result = result.filter((p) => p.payment_type === filters.value.paymentType)
  }
  if (filters.value.dateFrom) {
    result = result.filter((p) => (p.due_date || '') >= filters.value.dateFrom)
  }
  if (filters.value.dateTo) {
    result = result.filter((p) => (p.due_date || '') <= filters.value.dateTo)
  }
  if (showOnlyOverdue.value) {
    result = result.filter((p) => isOverdue(p.due_date))
  }
  if (showOnlyDueToday.value) {
    result = result.filter((p) => (p.due_date || '') === today.value)
  }
  return result
})

const loadDeferredPayments = async () => {
  try {
    loading.value = true
    const response = await api.get('/payment-collections/deferred', { params: buildFilterParams() })
    payments.value = Array.isArray(response.data) ? response.data : response.data?.payments || []
  } catch (error) {
    console.error('Erreur chargement paiements :', error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  loadDeferredPayments()
}

const toggleOverdue = () => {
  showOnlyOverdue.value = !showOnlyOverdue.value
  loadDeferredPayments()
}

const toggleDueToday = () => {
  showOnlyDueToday.value = !showOnlyDueToday.value
  loadDeferredPayments()
}

const formatCurrency = (value) => {
  const amount = Number(value) || 0
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD' }).format(amount)
}

const formatDate = (value) => {
  if (!value) return '-'
  return new Intl.DateTimeFormat('fr-FR').format(new Date(value))
}

const getPaymentTypeName = (payment) => {
  const type = payment?.payment_type
  const notes = String(payment?.notes || '')
  if (type === 'virement' && notes.includes('[VIREMENT_SIMPLE]')) return 'Virement simple'
  if (type === 'virement' && notes.includes('[VIREMENT_INSTANT]')) return 'Virement instantané'
  const names = {
    cheque: 'Chèque',
    check: 'Chèque',
    virement: 'Virement',
    credit: 'Crédit',
    cash: 'Espèces',
    card: 'Carte',
  }
  return names[type] || type
}

const getPaymentTypeClass = (type) => {
  const classes = {
    cheque: 'bg-blue-100 text-blue-800',
    check: 'bg-blue-100 text-blue-800',
    virement: 'bg-purple-100 text-purple-800',
    credit: 'bg-indigo-100 text-indigo-800',
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const buildFilterParams = () => ({
  collection_status: filters.value.status || undefined,
  payment_type: filters.value.paymentType || undefined,
  from_date: filters.value.dateFrom || undefined,
  to_date: filters.value.dateTo || undefined,
  overdue: showOnlyOverdue.value || undefined,
  due_today: showOnlyDueToday.value || undefined,
})

const getStatusLabel = (status) => {
  const map = {
    pending: 'En cours',
    collected: 'Validé / Payé',
    cancelled: 'Impayé',
  }
  return map[status] || status
}

const getStatusClass = (status) => {
  const map = {
    pending: 'bg-orange-100 text-orange-800',
    collected: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

const getStatusTextClass = (status) => {
  const map = {
    pending: 'text-orange-700',
    collected: 'text-green-700',
    cancelled: 'text-red-700',
  }
  return map[status] || 'text-gray-700'
}

const getPaymentMotif = (payment) => {
  if (payment.collection_notes) return payment.collection_notes
  const latest = payment.collections?.slice(-1)[0]
  return latest?.notes || payment.notes || '-'
}

const isOverdue = (date) => {
  if (!date) return false
  return new Date(date) < new Date(today.value + 'T00:00:00')
}

const openPaymentAction = async (payment) => {
  selectedPayment.value = payment

  try {
    const response = await api.get(`/payment-collections/${payment.id}/history`)
    historyRecords.value = response.data?.collections || []
  } catch (error) {
    console.error('Erreur historique :', error)
    historyRecords.value = payment.collections || []
  }

  validationForm.value = { status: 'collected', notes: '' }
  statusForm.value = {
    status:
      payment.collection_status === 'pending'
        ? 'pending'
        : payment.collection_status === 'cancelled'
          ? 'cancelled'
          : 'collected',
    notes: '',
  }

  if (payment.collection_status === 'cancelled') {
    showStatusModal.value = true
  } else {
    showValidationModal.value = true
  }
}

const closeValidationModal = () => {
  showValidationModal.value = false
  validationForm.value.notes = ''
}

const closeStatusModal = () => {
  showStatusModal.value = false
  statusForm.value.notes = ''
}

const submitValidation = async () => {
  if (!selectedPayment.value) return
  if (validationForm.value.status !== 'collected' && !validationForm.value.notes.trim()) {
    alert('Le motif est obligatoire pour les statuts autres que Payé.')
    return
  }
  try {
    await api.post(`/payment-collections/${selectedPayment.value.id}/status`, {
      status: validationForm.value.status,
      notes: validationForm.value.notes,
    })
    await loadDeferredPayments()
    closeValidationModal()
  } catch (error) {
    console.error('Erreur validation :', error)
    alert('Impossible de valider le paiement.')
  }
}

const submitStatusChange = async () => {
  if (!selectedPayment.value) return
  if (statusForm.value.status !== 'collected' && !statusForm.value.notes.trim()) {
    alert('Le motif est obligatoire pour les statuts autres que Payé.')
    return
  }
  try {
    await api.post(`/payment-collections/${selectedPayment.value.id}/status`, {
      status: statusForm.value.status,
      notes: statusForm.value.notes,
    })
    await loadDeferredPayments()
    closeStatusModal()
  } catch (error) {
    console.error('Erreur changement statut :', error)
    alert('Impossible de changer le statut.')
  }
}

onMounted(() => {
  loadDeferredPayments()
})
</script>
