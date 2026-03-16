<template>
  <div class="p-4 md:p-6 space-y-5 bg-slate-50 min-h-full">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-slate-900">Suivi Encaissements</h1>
        <p class="text-slate-600 mt-1">Suivi des paiements différés POS et des commandes client en attente.</p>
      </div>
      <button
        class="bg-amber-400 text-slate-900 font-semibold px-4 py-2.5 rounded-xl border border-amber-500/40 shadow-sm"
        disabled
      >
        Dossiers à suivre : {{ followUpCount }}
      </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4 md:p-5 space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Statut</label>
          <select v-model="filters.status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Tous</option>
            <option value="to_pay">À payer</option>
            <option value="to_collect">En cours</option>
            <option value="unpaid">Impayé</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Mode de paiement</label>
          <select v-model="filters.paymentType" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Tous</option>
            <option value="balance_due">Reste à payer</option>
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
          À suivre aujourd'hui
        </button>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
      <div class="px-4 md:px-6 py-4 border-b border-slate-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-slate-900">Paiements à suivre</h2>
        <span class="text-sm text-slate-500">{{ filteredPayments.length }} dossier(s)</span>
      </div>
      <div v-if="loading" class="p-8 text-center text-slate-500">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2">Chargement...</p>
      </div>
      <div v-else-if="filteredPayments.length === 0" class="p-8 text-center text-slate-500">
        Aucun paiement à suivre
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full text-sm text-left">
          <thead class="bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
              <th class="px-4 py-3">Date</th>
              <th class="px-4 py-3">Client</th>
              <th class="px-4 py-3">Référence</th>
              <th class="px-4 py-3">Mode de paiement</th>
              <th class="px-4 py-3">N° pièce</th>
              <th class="px-4 py-3">Montant</th>
              <th class="px-4 py-3">Date d'échéance</th>
              <th class="px-4 py-3">Statut</th>
              <th class="px-4 py-3">Motif</th>
              <th class="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="payment in filteredPayments"
              :key="payment.id"
              class="border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
              @click="openEntry(payment)"
            >
              <td class="px-4 py-3 text-gray-600">{{ formatDate(payment.created_at) }}</td>
              <td class="px-4 py-3">
                <p class="text-gray-900 font-medium">{{ payment.sale?.customer?.name || '-' }}</p>
                <p class="text-xs text-slate-500">{{ payment.sale?.customer?.phone || formatOrigin(payment.sale_origin || payment.sale?.origin) }}</p>
              </td>
              <td class="px-4 py-3 text-gray-600">
                <p class="font-medium text-slate-900">{{ getSaleReference(payment) }}</p>
                <p class="text-xs text-slate-500">{{ formatOrigin(payment.sale_origin || payment.sale?.origin) }}</p>
              </td>
              <td class="px-4 py-3">
                <span :class="getPaymentTypeClass(payment.payment_type)" class="px-2 py-1 rounded text-xs font-semibold">
                  {{ payment.payment_method_label || getPaymentTypeName(payment) }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ payment.reference_number || '-' }}</td>
              <td class="px-4 py-3 font-bold text-gray-900">{{ formatCurrency(payment.amount) }}</td>
              <td class="px-4 py-3 text-gray-600">{{ formatDate(payment.due_date) }}</td>
              <td class="px-4 py-3">
                <span :class="getStatusClass(payment.workflow_status_code)" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ getStatusLabel(payment.workflow_status_code) }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ getPaymentMotif(payment) }}</td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap gap-2">
                  <button
                    v-if="canValidateEntry(payment)"
                    @click.stop="openEntry(payment, 'validate')"
                    class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded text-xs font-semibold hover:bg-emerald-200 transition"
                  >
                    Valider
                  </button>
                  <button
                    v-if="canManageEntry(payment)"
                    @click.stop="openEntry(payment, 'status')"
                    class="px-3 py-1 bg-amber-100 text-amber-800 rounded text-xs font-semibold hover:bg-amber-200 transition"
                  >
                    Statut
                  </button>
                  <button
                    @click.stop="openEntry(payment, 'view')"
                    class="px-3 py-1 bg-slate-100 text-slate-700 rounded text-xs font-semibold hover:bg-slate-200 transition"
                  >
                    Détails
                  </button>
                  <button
                    v-if="canOpenOrder(payment)"
                    @click.stop="openClientOrder(payment)"
                    class="px-3 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold hover:bg-blue-200 transition"
                  >
                    Commande
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showEntryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
      <div class="w-full max-w-3xl max-h-[90vh] overflow-hidden rounded-2xl bg-white shadow-2xl border border-slate-200">
        <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-200">
          <div>
            <h3 class="text-xl font-semibold text-slate-900">{{ modalTitle }}</h3>
            <p class="text-sm text-slate-500 mt-1">{{ getSaleReference(selectedEntry) }} · {{ formatOrigin(selectedEntry?.sale_origin || selectedEntry?.sale?.origin) }}</p>
          </div>
          <button @click="closeEntryModal" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
        </div>

        <div class="overflow-y-auto max-h-[calc(90vh-152px)] px-6 py-5 space-y-5">
          <div v-if="detailLoading" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-500">
            Chargement des détails du paiement...
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
              <div>
                <p class="text-xs font-semibold uppercase text-slate-500">Client</p>
                <p class="text-lg font-semibold text-slate-900">{{ selectedEntry?.sale?.customer?.name || 'Client anonyme' }}</p>
              </div>
              <div>
                <p class="text-xs font-semibold uppercase text-slate-500">Mode de paiement</p>
                <p class="text-base font-medium text-slate-900">{{ selectedEntry?.payment_method_label || getPaymentTypeName(selectedEntry) }}</p>
              </div>
              <div>
                <p class="text-xs font-semibold uppercase text-slate-500">N° pièce / référence</p>
                <p class="text-base font-medium text-slate-900">{{ selectedEntry?.reference_number || '-' }}</p>
              </div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
              <div>
                <p class="text-xs font-semibold uppercase text-slate-500">Montant</p>
                <p class="text-2xl font-bold text-slate-900">{{ formatCurrency(selectedEntry?.amount) }}</p>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase text-slate-500">Créé le</p>
                  <p class="text-sm font-medium text-slate-900">{{ formatDate(selectedEntry?.created_at) }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase text-slate-500">Échéance</p>
                  <p class="text-sm font-medium text-slate-900">{{ formatDate(selectedEntry?.due_date) }}</p>
                </div>
              </div>
              <div>
                <p class="text-xs font-semibold uppercase text-slate-500">Statut actuel</p>
                <span :class="getStatusClass(selectedEntry?.workflow_status_code)" class="inline-flex mt-1 px-3 py-1 rounded-full text-xs font-semibold">
                  {{ getStatusLabel(selectedEntry?.workflow_status_code) }}
                </span>
              </div>
            </div>
          </div>

          <div class="rounded-xl border border-slate-200 p-4 space-y-2">
            <p class="text-sm font-semibold text-slate-900">Informations</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-700">
              <div>
                <span class="text-slate-500">Origine :</span>
                <span class="font-medium ml-1">{{ formatOrigin(selectedEntry?.sale_origin || selectedEntry?.sale?.origin) }}</span>
              </div>
              <div>
                <span class="text-slate-500">Référence :</span>
                <span class="font-medium ml-1">{{ getSaleReference(selectedEntry) }}</span>
              </div>
              <div>
                <span class="text-slate-500">Créé par :</span>
                <span class="font-medium ml-1">{{ selectedEntry?.creator?.name || '-' }}</span>
              </div>
              <div>
                <span class="text-slate-500">Validé par :</span>
                <span class="font-medium ml-1">{{ selectedEntry?.validator?.name || '-' }}</span>
              </div>
            </div>
            <div v-if="selectedEntry?.collection_notes || selectedEntry?.notes" class="pt-2 border-t border-slate-200">
              <p class="text-xs font-semibold uppercase text-slate-500">Motif / note</p>
              <p class="text-sm text-slate-700 mt-1">{{ getPaymentMotif(selectedEntry) }}</p>
            </div>
          </div>

          <div v-if="canManageEntry(selectedEntry) && modalMode !== 'view'" class="rounded-xl border border-slate-200 p-4 space-y-4">
            <div class="flex flex-wrap gap-2">
              <button
                v-if="canValidateEntry(selectedEntry)"
                @click="setModalMode('validate')"
                :class="modalMode === 'validate' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700'"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition"
              >
                Valider le paiement
              </button>
              <button
                @click="setModalMode('status')"
                :class="modalMode === 'status' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700'"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition"
              >
                Changer le statut
              </button>
            </div>

            <div v-if="collectionHistory.length" class="space-y-3">
              <p class="text-sm font-semibold text-slate-900">Historique statut</p>
              <div class="space-y-2">
                <div
                  v-for="history in collectionHistory"
                  :key="history.id"
                  class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm"
                >
                  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                    <div>
                      <p class="font-medium text-slate-900">{{ formatHistoryAction(history.action) }}</p>
                      <p class="text-slate-500">{{ formatDateTime(history.created_at) }}</p>
                    </div>
                    <p class="font-semibold text-slate-900">{{ formatCurrency(history.amount) }}</p>
                  </div>
                  <p v-if="history.notes" class="text-slate-600 mt-2">{{ history.notes }}</p>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <p class="text-sm font-semibold text-slate-900">Information paiement</p>
              <div class="space-y-3">
                <label
                  v-for="option in statusOptions"
                  :key="option.value"
                  class="flex items-center gap-3 rounded-xl border px-4 py-3 cursor-pointer transition"
                  :class="statusForm.status === option.value ? option.activeClass : 'border-slate-200 bg-white text-slate-700'"
                >
                  <input
                    v-model="statusForm.status"
                    type="radio"
                    :value="option.value"
                    class="h-4 w-4"
                  >
                  <span class="font-medium">{{ option.label }}</span>
                </label>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">{{ noteLabel }}</label>
                <textarea
                  v-model="statusForm.notes"
                  rows="3"
                  :placeholder="notePlaceholder"
                  class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                ></textarea>
              </div>
            </div>
          </div>

          <div v-else-if="!canManageEntry(selectedEntry)" class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
            Ce dossier ne possède pas de paiement différé modifiable directement depuis ce suivi.
          </div>
        </div>

        <div class="flex flex-col-reverse md:flex-row md:items-center md:justify-between gap-3 px-6 py-4 border-t border-slate-200 bg-slate-50">
          <button
            v-if="canOpenOrder(selectedEntry)"
            @click="openClientOrder(selectedEntry)"
            class="px-4 py-2 rounded-lg bg-blue-100 text-blue-700 font-semibold hover:bg-blue-200 transition"
          >
            Ouvrir la commande
          </button>
          <div class="flex flex-col-reverse md:flex-row gap-3 md:ml-auto">
            <button
              @click="closeEntryModal"
              class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold hover:bg-slate-100 transition"
            >
              Annuler
            </button>
            <button
              v-if="canManageEntry(selectedEntry) && modalMode !== 'view'"
              @click="submitStatusChange"
              :disabled="actionLoading"
              class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 disabled:opacity-60 transition"
            >
              {{ actionLoading ? 'Validation...' : 'Valider' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()
const loading = ref(false)
const detailLoading = ref(false)
const actionLoading = ref(false)
const payments = ref([])
const showEntryModal = ref(false)
const selectedEntry = ref(null)
const modalMode = ref('view')
const statusForm = ref({
  status: 'collected',
  notes: '',
})

const filters = ref({
  status: '',
  paymentType: '',
  dateFrom: '',
  dateTo: '',
})

const showOnlyOverdue = ref(false)
const showOnlyDueToday = ref(false)

const STATUS_META = {
  to_pay: { label: 'À payer', chip: 'bg-slate-100 text-slate-800' },
  to_collect: { label: 'En cours', chip: 'bg-amber-100 text-amber-800' },
  unpaid: { label: 'Impayé', chip: 'bg-rose-100 text-rose-800' },
  collected: { label: 'Payé', chip: 'bg-emerald-100 text-emerald-800' },
  paid: { label: 'Payé', chip: 'bg-emerald-100 text-emerald-800' },
}

const STATUS_ALIASES = {
  to_pay: 'to_pay',
  a_payer: 'to_pay',
  pending: 'to_collect',
  a_encaisser: 'to_collect',
  to_collect: 'to_collect',
  cancelled: 'unpaid',
  impaye: 'unpaid',
  unpaid: 'unpaid',
  collected: 'collected',
  paid: 'paid',
}

const normalizeStatus = (status) => STATUS_ALIASES[status] || status || 'to_collect'
const workflowStatus = (payment) => normalizeStatus(payment?.workflow_status_code || payment?.collection_status)
const today = computed(() => new Date().toISOString().split('T')[0])
const followUpCount = computed(() => payments.value.length)

const filteredPayments = computed(() => {
  let result = payments.value

  if (filters.value.status) {
    result = result.filter((payment) => workflowStatus(payment) === filters.value.status)
  }

  if (filters.value.paymentType) {
    result = result.filter((payment) => payment.payment_type === filters.value.paymentType)
  }

  if (filters.value.dateFrom) {
    result = result.filter((payment) => (payment.due_date || '') >= filters.value.dateFrom)
  }

  if (filters.value.dateTo) {
    result = result.filter((payment) => (payment.due_date || '') <= filters.value.dateTo)
  }

  if (showOnlyOverdue.value) {
    result = result.filter((payment) => isOverdue(payment.due_date))
  }

  if (showOnlyDueToday.value) {
    result = result.filter((payment) => (payment.due_date || '') === today.value)
  }

  return result
})

const collectionHistory = computed(() => {
  const history = selectedEntry.value?.collections
  return Array.isArray(history) ? history : []
})

const statusOptions = computed(() => {
  if (!canManageEntry(selectedEntry.value)) {
    return []
  }

  if (modalMode.value === 'validate' && canValidateEntry(selectedEntry.value)) {
    return [
      {
        value: 'collected',
        label: 'Payé',
        activeClass: 'border-emerald-300 bg-emerald-50 text-emerald-800',
      },
      {
        value: 'cancelled',
        label: 'Impayé',
        activeClass: 'border-rose-300 bg-rose-50 text-rose-800',
      },
    ]
  }

  return [
    {
      value: 'collected',
      label: 'Payé',
      activeClass: 'border-emerald-300 bg-emerald-50 text-emerald-800',
    },
    {
      value: 'pending',
      label: 'En cours',
      activeClass: 'border-amber-300 bg-amber-50 text-amber-800',
    },
    {
      value: 'cancelled',
      label: 'Impayé',
      activeClass: 'border-rose-300 bg-rose-50 text-rose-800',
    },
  ]
})

const modalTitle = computed(() => {
  const customerName = selectedEntry.value?.sale?.customer?.name || 'Client'

  if (!canManageEntry(selectedEntry.value)) {
    return `Détails du dossier de ${customerName}`
  }

  if (modalMode.value === 'view') {
    return `Détails du paiement de ${customerName}`
  }

  if (modalMode.value === 'status') {
    return `Changer le statut du paiement de ${customerName}`
  }

  return `Valider le paiement de ${customerName}`
})

const noteLabel = computed(() => (
  statusForm.value.status === 'cancelled' ? "Motif d'impayé" : 'Note'
))

const notePlaceholder = computed(() => (
  statusForm.value.status === 'cancelled'
    ? "Saisir le motif d'impayé..."
    : 'Ajouter une note (optionnel)...'
))

const loadDeferredPayments = async () => {
  try {
    loading.value = true
    const { data } = await api.get('/payment-collections/deferred', { params: buildFilterParams() })
    payments.value = Array.isArray(data) ? data : data?.data || []
  } catch (error) {
    console.error('Erreur chargement paiements :', error)
  } finally {
    loading.value = false
  }
}

const buildFilterParams = () => ({
  status: filters.value.status || undefined,
  payment_type: filters.value.paymentType || undefined,
  from_date: filters.value.dateFrom || undefined,
  to_date: filters.value.dateTo || undefined,
  overdue: showOnlyOverdue.value || undefined,
  due_today: showOnlyDueToday.value || undefined,
})

const applyFilters = () => loadDeferredPayments()

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

const formatDateTime = (value) => {
  if (!value) return '-'
  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'short',
    timeStyle: 'short',
  }).format(new Date(value))
}

const formatOrigin = (origin) => {
  const map = {
    pos: 'PDV',
    menu_commande: 'Commande client',
    livraison: 'Livraison',
  }

  return map[origin] || 'Commande client'
}

const getStatusLabel = (status) => STATUS_META[normalizeStatus(status)]?.label || status || '-'
const getStatusClass = (status) => STATUS_META[normalizeStatus(status)]?.chip || 'bg-gray-100 text-gray-800'

const getPaymentTypeName = (payment) => {
  const type = payment?.payment_type
  const notes = String(payment?.notes || '')

  if (type === 'virement' && (payment?.transfer_mode === 'simple' || notes.includes('[VIREMENT_SIMPLE]'))) {
    return 'Virement simple'
  }

  if (type === 'virement' && (payment?.transfer_mode === 'instant' || notes.includes('[VIREMENT_INSTANT]'))) {
    return 'Virement instantané'
  }

  const names = {
    cheque: 'Chèque',
    check: 'Chèque',
    virement: 'Virement',
    credit: 'Crédit',
    balance_due: 'Reste à payer',
  }

  return names[type] || type || '-'
}

const getPaymentTypeClass = (type) => {
  const classes = {
    cheque: 'bg-blue-100 text-blue-800',
    check: 'bg-blue-100 text-blue-800',
    virement: 'bg-purple-100 text-purple-800',
    credit: 'bg-indigo-100 text-indigo-800',
    balance_due: 'bg-slate-100 text-slate-800',
  }

  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getPaymentMotif = (payment) => payment?.collection_notes || payment?.notes || '-'

const getSaleReference = (payment) => (
  payment?.sale_reference_display
  || (payment?.sale_origin === 'pos' || payment?.sale?.origin === 'pos'
    ? payment?.sale?.reference || payment?.sale?.order_number || '-'
    : payment?.sale?.order_number || payment?.sale?.reference || '-')
)

const isPosEntry = (payment) => (payment?.sale_origin || payment?.sale?.origin) === 'pos'
const canOpenOrder = (payment) => Boolean(payment?.sale_id) && !isPosEntry(payment)
const canManageEntry = (payment) => Boolean(payment?.payment_id)
const canValidateEntry = (payment) => canManageEntry(payment) && workflowStatus(payment) === 'to_collect'

const isOverdue = (date) => {
  if (!date) return false
  return new Date(date) < new Date(`${today.value}T00:00:00`)
}

const buildDefaultStatus = (entry, mode = modalMode.value) => {
  if (mode === 'validate' && canValidateEntry(entry)) {
    return 'collected'
  }

  if (entry?.collection_status === 'cancelled' || workflowStatus(entry) === 'unpaid') {
    return 'cancelled'
  }

  if (entry?.collection_status === 'collected' || workflowStatus(entry) === 'collected') {
    return 'collected'
  }

  return 'pending'
}

const syncStatusForm = (entry, mode = modalMode.value) => {
  statusForm.value = {
    status: buildDefaultStatus(entry, mode),
    notes: entry?.collection_notes || '',
  }
}

const normalizeEntryWithHistory = (entry, payload) => {
  const payment = payload?.payment || {}
  const sale = payment.sale || entry?.sale || null
  const origin = sale?.origin || entry?.sale_origin || null

  return {
    ...entry,
    ...payment,
    sale,
    sale_id: payment.sale_id || entry?.sale_id,
    payment_id: payment.id || entry?.payment_id,
    sale_origin: origin,
    sale_reference_display: origin === 'pos'
      ? sale?.reference || sale?.order_number || entry?.sale_reference_display || '-'
      : sale?.order_number || sale?.reference || entry?.sale_reference_display || '-',
    collections: Array.isArray(payload?.collections) ? payload.collections : [],
  }
}

const setModalMode = (mode) => {
  modalMode.value = mode
  const allowedStatuses = statusOptions.value.map((option) => option.value)
  const nextStatus = buildDefaultStatus(selectedEntry.value, mode)

  if (!allowedStatuses.includes(statusForm.value.status)) {
    statusForm.value.status = nextStatus
  }

  if (mode === 'validate' && canValidateEntry(selectedEntry.value)) {
    statusForm.value.status = 'collected'
  }
}

const openEntry = async (entry, mode = null) => {
  const resolvedMode = mode || (canValidateEntry(entry) ? 'validate' : canManageEntry(entry) ? 'status' : 'view')

  selectedEntry.value = {
    ...entry,
    collections: Array.isArray(entry?.collections) ? entry.collections : [],
  }
  showEntryModal.value = true
  setModalMode(resolvedMode)
  syncStatusForm(selectedEntry.value, resolvedMode)

  if (!entry?.payment_id) {
    return
  }

  detailLoading.value = true
  try {
    const { data } = await api.get(`/payment-collections/${entry.payment_id}/history`)
    selectedEntry.value = normalizeEntryWithHistory(selectedEntry.value, data)
    syncStatusForm(selectedEntry.value, modalMode.value)
  } catch (error) {
    console.error('Erreur chargement détails paiement :', error)
  } finally {
    detailLoading.value = false
  }
}

const closeEntryModal = () => {
  showEntryModal.value = false
  selectedEntry.value = null
  modalMode.value = 'view'
  statusForm.value = {
    status: 'collected',
    notes: '',
  }
}

const openClientOrder = (payment) => {
  const saleId = payment?.sale_id || payment?.sale?.id
  if (!saleId || isPosEntry(payment)) {
    return
  }

  router.push({ name: 'commandes.detail', params: { id: saleId } })
}

const submitStatusChange = async () => {
  if (!canManageEntry(selectedEntry.value)) {
    return
  }

  if (statusForm.value.status === 'cancelled' && !statusForm.value.notes.trim()) {
    alert("Veuillez saisir le motif d'impayé.")
    return
  }

  actionLoading.value = true

  try {
    await api.post(`/payment-collections/${selectedEntry.value.payment_id}/status`, {
      status: statusForm.value.status,
      notes: statusForm.value.notes.trim() || null,
    })

    await loadDeferredPayments()
    closeEntryModal()
  } catch (error) {
    console.error('Erreur mise à jour statut paiement :', error)
    alert(error.response?.data?.message || 'Impossible de mettre à jour le statut du paiement.')
  } finally {
    actionLoading.value = false
  }
}

const formatHistoryAction = (action) => {
  const map = {
    created: 'Paiement enregistré',
    collected: 'Paiement validé',
    failed: 'Paiement impayé',
    scheduled: 'Paiement planifié',
    rescheduled: 'Paiement remis en cours',
  }

  return map[action] || action || '-'
}

onMounted(() => {
  loadDeferredPayments()
})
</script>
