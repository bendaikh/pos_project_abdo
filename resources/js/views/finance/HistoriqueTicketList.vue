<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <div class="flex items-center gap-2">
                <ClockIcon class="w-7 h-7 text-primary-600" />
                <h1 class="text-2xl font-bold text-gray-900">Historique des tickets</h1>
            </div>
            <p class="text-gray-500 mt-1">Consultez l'historique complet de tous les tickets enregistrés</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-12 gap-3">
                <div class="sm:col-span-2 xl:col-span-5">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Période</label>
                    <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-2">
                        <input
                            v-model="filters.from_date"
                            type="date"
                            class="w-full min-w-0 px-2 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"
                            @change="applyFilters"
                        >
                        <span class="text-gray-400 text-sm shrink-0">→</span>
                        <input
                            v-model="filters.to_date"
                            type="date"
                            class="w-full min-w-0 px-2 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"
                            @change="applyFilters"
                        >
                    </div>
                </div>
                <div class="xl:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
                    <select
                        v-model="filters.ticket_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"
                        @change="applyFilters"
                    >
                        <option value="">Tous les statuts</option>
                        <option value="enregistre">Enregistré</option>
                        <option value="annule">Annulé</option>
                        <option value="rembourse">Remboursé</option>
                        <option value="transfere">Transféré</option>
                    </select>
                </div>
                <div class="xl:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Emplacement</label>
                    <select
                        v-model="filters.ticket_group"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"
                        @change="applyFilters"
                    >
                        <option value="">Tous</option>
                        <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
                    </select>
                </div>
                <div class="sm:col-span-2 xl:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Rechercher</label>
                    <div class="relative">
                        <MagnifyingGlassIcon class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Rechercher..."
                            class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"
                            @keyup.enter="applyFilters"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <DocumentTextIcon class="w-5 h-5 text-blue-500" />
                    <span class="text-xs text-gray-500">Total tickets</span>
                </div>
                <p class="text-2xl font-bold text-blue-600">{{ stats.total }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <ClockIcon class="w-5 h-5 text-green-500" />
                    <span class="text-xs text-gray-500">Enregistrés</span>
                </div>
                <p class="text-2xl font-bold text-green-600">{{ stats.registered }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <TrashIcon class="w-5 h-5 text-red-500" />
                    <span class="text-xs text-gray-500">Annulés</span>
                </div>
                <p class="text-2xl font-bold text-red-600">{{ stats.cancelled }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <BanknotesIcon class="w-5 h-5 text-orange-500" />
                    <span class="text-xs text-gray-500">Remboursés</span>
                </div>
                <p class="text-2xl font-bold text-orange-600">{{ stats.refunded }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <ArrowPathIcon class="w-5 h-5 text-purple-500" />
                    <span class="text-xs text-gray-500">Transférés</span>
                </div>
                <p class="text-2xl font-bold text-purple-600">{{ stats.transferred }}</p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div v-if="loading" class="p-8 text-center text-gray-500">Chargement des tickets...</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr class="text-xs font-medium text-gray-500 uppercase">
                            <th class="px-4 py-3 text-left">Ticket</th>
                            <th class="px-4 py-3 text-left">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort">
                                    Date & Heure
                                    <ChevronDownIcon class="w-3.5 h-3.5" :class="{ 'rotate-180': sortDesc }" />
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left">Client</th>
                            <th class="px-4 py-3 text-left">Emplacement</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-left">Statut</th>
                            <th class="px-4 py-3 text-left">Utilisateur</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="ticket in tickets"
                            :key="ticket.id"
                            class="hover:bg-gray-50 cursor-pointer"
                            @click="openDetail(ticket.id)"
                        >
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900">{{ formatTicketNumber(ticket) }}</p>
                                <p class="text-xs text-gray-500">{{ formatTicketType(ticket) }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <p>{{ formatDate(ticket.created_at) }}</p>
                                <p class="text-xs text-gray-500">{{ formatTime(ticket.created_at) }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <UserIcon class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                    <span class="text-sm text-gray-900">{{ ticket.customer?.name || 'Client Anonyme' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-700">{{ formatLocation(ticket) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ formatCurrency(ticket.total || 0) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="ticketStatusClass(ticket)">
                                    {{ ticketStatusLabel(ticket) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold flex items-center justify-center flex-shrink-0">
                                        {{ getUserInitials(ticket.user) }}
                                    </span>
                                    <span class="text-sm text-gray-700">{{ ticket.user?.name || '-' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <ChevronRightIcon class="w-5 h-5 text-gray-400 inline-block" />
                            </td>
                        </tr>
                        <tr v-if="!tickets.length">
                            <td colspan="8" class="px-4 py-10 text-center text-gray-500">Aucun ticket trouvé pour cette période.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="border-t border-gray-200 px-4 py-3 flex items-center justify-between">
                <button
                    type="button"
                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40"
                    :disabled="pagination.current_page <= 1"
                    @click="changePage(pagination.current_page - 1)"
                >
                    Précédent
                </button>
                <div class="flex items-center gap-1">
                    <template v-for="page in visiblePages" :key="page">
                        <span v-if="page === '...'" class="px-2 text-gray-400">...</span>
                        <button
                            v-else
                            type="button"
                            class="w-8 h-8 text-sm rounded-lg"
                            :class="page === pagination.current_page ? 'bg-primary-500 text-gray-900 font-semibold' : 'text-gray-600 hover:bg-gray-100'"
                            @click="changePage(page)"
                        >
                            {{ page }}
                        </button>
                    </template>
                </div>
                <button
                    type="button"
                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40"
                    :disabled="pagination.current_page >= pagination.last_page"
                    @click="changePage(pagination.current_page + 1)"
                >
                    Suivant
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between text-sm">
            <div class="flex items-center gap-2 text-gray-500">
                <InformationCircleIcon class="w-4 h-4" />
                <span>Les tickets sont conservés pendant 12 mois.</span>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium text-gray-700"
                @click="exportTickets"
            >
                <ArrowDownTrayIcon class="w-4 h-4" />
                Exporter
            </button>
        </div>

        <!-- Detail Modal -->
        <Teleport to="body">
            <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40" @click="closeDetail" />
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-start justify-between z-10">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <DocumentTextIcon class="w-5 h-5 text-primary-600" />
                                <h2 class="text-lg font-bold text-gray-900">Détail du ticket</h2>
                            </div>
                            <p class="text-sm font-semibold text-gray-700">{{ formatTicketNumber(selectedTicket) }}</p>
                            <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="ticketStatusClass(selectedTicket)">
                                {{ ticketStatusLabel(selectedTicket) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="relative" ref="actionMenuRef">
                                <button
                                    type="button"
                                    class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                                    @click.stop="showActionMenu = !showActionMenu"
                                >
                                    <EllipsisVerticalIcon class="w-5 h-5 text-gray-600" />
                                </button>
                                <div
                                    v-if="showActionMenu"
                                    class="absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-1 z-20"
                                >
                                    <button type="button" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2" @click="handleReprint">
                                        <PrinterIcon class="w-4 h-4 text-green-600" /> Réimprimer
                                    </button>
                                    <button
                                        v-if="canRefund(selectedTicket)"
                                        type="button"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2"
                                        @click="handleRefund"
                                    >
                                        <BanknotesIcon class="w-4 h-4 text-orange-600" /> Rembourser
                                    </button>
                                    <button
                                        v-if="canCancel(selectedTicket)"
                                        type="button"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 text-red-600"
                                        @click="openCancelModal"
                                    >
                                        <TrashIcon class="w-4 h-4" /> Annuler
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="p-2 hover:bg-gray-100 rounded-lg" @click="closeDetail">
                                <XMarkIcon class="w-5 h-5 text-gray-500" />
                            </button>
                        </div>
                    </div>

                    <div v-if="detailLoading" class="p-8 text-center text-gray-500">Chargement...</div>
                    <div v-else-if="selectedTicket" class="p-6 space-y-6">
                        <div class="flex items-start justify-between">
                            <div class="space-y-1 text-sm">
                                <p><span class="text-gray-500">Client:</span> <span class="font-medium">{{ selectedTicket.customer?.name || 'Client Anonyme' }}</span></p>
                                <p><span class="text-gray-500">Date:</span> {{ formatDateTime(selectedTicket.created_at) }}</p>
                                <p><span class="text-gray-500">Utilisateur:</span> {{ selectedTicket.user?.name || '-' }} ({{ getUserInitials(selectedTicket.user) }})</p>
                            </div>
                            <p class="text-xl font-bold text-primary-600">{{ formatCurrency(selectedTicket.total || 0) }}</p>
                        </div>

                        <section>
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Informations</h3>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs text-gray-500">Emplacement</p>
                                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ formatLocation(selectedTicket) }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs text-gray-500">Nombre d'articles</p>
                                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ selectedTicket.items?.length || 0 }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs text-gray-500">Mode de paiement</p>
                                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ formatPaymentMethod(selectedTicket) }}</p>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Articles</h3>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-xs text-gray-500 border-b border-gray-200">
                                        <th class="py-2 text-left font-medium">Article</th>
                                        <th class="py-2 text-center font-medium">Qté</th>
                                        <th class="py-2 text-right font-medium">Prix unitaire</th>
                                        <th class="py-2 text-right font-medium">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="item in selectedTicket.items || []" :key="item.id">
                                        <td class="py-2 text-gray-900">{{ item.article_name || item.article?.name }}</td>
                                        <td class="py-2 text-center text-gray-700">{{ item.quantity }}</td>
                                        <td class="py-2 text-right text-gray-700">{{ formatCurrency(item.unit_price) }}</td>
                                        <td class="py-2 text-right font-medium text-gray-900">{{ formatCurrency(item.total) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="border-t border-gray-200">
                                        <td colspan="3" class="py-2 text-right font-semibold text-gray-700">Total</td>
                                        <td class="py-2 text-right font-bold text-primary-600">{{ formatCurrency(selectedTicket.total || 0) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </section>

                        <section>
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Notes</h3>
                            <p class="text-sm text-gray-600 bg-gray-50 rounded-xl p-3">{{ selectedTicket.notes || 'Aucune note pour ce ticket' }}</p>
                        </section>

                        <section>
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Historique</h3>
                            <div class="space-y-3">
                                <div v-for="log in ticketLogs" :key="log.id" class="flex gap-3">
                                    <div class="w-12 text-xs text-gray-500 flex-shrink-0 pt-0.5">{{ formatTime(log.created_at) }}</div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-900">{{ formatLogMessage(log) }}</p>
                                        <p class="text-xs text-gray-500">{{ log.user?.name || '-' }} ({{ getUserInitials(log.user) }})</p>
                                    </div>
                                </div>
                                <p v-if="!ticketLogs.length" class="text-sm text-gray-500">Aucun historique disponible.</p>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Cancel Modal -->
        <Teleport to="body">
            <div v-if="showCancelModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40" @click="closeCancelModal" />
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <TrashIcon class="w-5 h-5 text-red-600" />
                            <h2 class="text-lg font-bold text-gray-900">Annuler le ticket</h2>
                        </div>
                        <button type="button" @click="closeCancelModal">
                            <XMarkIcon class="w-5 h-5 text-gray-500" />
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="font-semibold text-gray-900">{{ formatTicketNumber(selectedTicket) }}</p>
                            <p class="text-sm text-gray-600 mt-1">Client: {{ selectedTicket?.customer?.name || 'Client Anonyme' }}</p>
                            <p class="text-sm text-gray-600">{{ formatTime(selectedTicket?.created_at) }}</p>
                            <p class="text-sm font-semibold text-primary-600 mt-2">Total: {{ formatCurrency(selectedTicket?.total || 0) }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">Motif d'annulation</p>
                            <div class="space-y-2">
                                <label
                                    v-for="reason in cancelReasons"
                                    :key="reason.value"
                                    class="flex items-start gap-3 p-3 border rounded-xl cursor-pointer transition"
                                    :class="cancelForm.reason === reason.value ? 'border-red-400 bg-red-50' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <input v-model="cancelForm.reason" type="radio" :value="reason.value" class="mt-1">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ reason.label }}</p>
                                        <p class="text-xs text-gray-500">{{ reason.description }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire (optionnel)</label>
                            <textarea
                                v-model="cancelForm.comment"
                                rows="3"
                                maxlength="200"
                                placeholder="Ajoutez un commentaire..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"
                            />
                            <p class="text-xs text-gray-400 text-right mt-1">{{ cancelForm.comment.length }} / 200</p>
                        </div>

                        <div class="flex items-start gap-2 bg-blue-50 border border-blue-100 rounded-xl p-3">
                            <InformationCircleIcon class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" />
                            <p class="text-xs text-blue-700">Le ticket sera annulé et ne sera plus accessible. Cette action sera enregistrée dans l'historique.</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 flex gap-3">
                        <button type="button" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50" @click="closeCancelModal">
                            Annuler
                        </button>
                        <button
                            type="button"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center justify-center gap-2 disabled:opacity-50"
                            :disabled="!cancelForm.reason || cancelling"
                            @click="confirmCancel"
                        >
                            <TrashIcon class="w-4 h-4" />
                            Confirmer l'annulation
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
    ArrowDownTrayIcon,
    ArrowPathIcon,
    BanknotesIcon,
    ChevronDownIcon,
    ChevronRightIcon,
    ClockIcon,
    DocumentTextIcon,
    EllipsisVerticalIcon,
    InformationCircleIcon,
    MagnifyingGlassIcon,
    PrinterIcon,
    TrashIcon,
    UserIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline'
import { salesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'

const settingsStore = useSettingsStore()
const router = useRouter()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const loading = ref(false)
const detailLoading = ref(false)
const cancelling = ref(false)
const tickets = ref([])
const locations = ref([])
const ticketLogs = ref([])
const selectedTicket = ref(null)
const showDetailModal = ref(false)
const showCancelModal = ref(false)
const showActionMenu = ref(false)
const actionMenuRef = ref(null)
const sortDesc = ref(true)

const stats = reactive({
    total: 0,
    registered: 0,
    cancelled: 0,
    refunded: 0,
    transferred: 0,
})

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const now = new Date()
const filters = reactive({
    from_date: formatInputDate(new Date(now.getFullYear(), now.getMonth(), 1)),
    to_date: formatInputDate(new Date(now.getFullYear(), now.getMonth() + 1, 0)),
    ticket_status: '',
    ticket_group: '',
    search: '',
})

const cancelForm = reactive({
    reason: 'client',
    comment: '',
})

const cancelReasons = [
    { value: 'client', label: 'Annulation par le client', description: 'Le client a demandé l\'annulation du ticket' },
    { value: 'saisie', label: 'Erreur de saisie', description: 'Erreur dans les articles ou les quantités' },
    { value: 'attente', label: 'Temps d\'attente trop long', description: 'Le client a annulé à cause du temps d\'attente' },
    { value: 'autre', label: 'Autre motif', description: 'Autre raison' },
]

function formatInputDate(date) {
    const y = date.getFullYear()
    const m = String(date.getMonth() + 1).padStart(2, '0')
    const d = String(date.getDate()).padStart(2, '0')
    return `${y}-${m}-${d}`
}

function buildParams(page = 1) {
    return {
        page,
        per_page: 15,
        from_date: filters.from_date || undefined,
        to_date: filters.to_date || undefined,
        ticket_status: filters.ticket_status || undefined,
        ticket_group: filters.ticket_group || undefined,
        search: filters.search || undefined,
    }
}

async function fetchTickets(page = 1) {
    loading.value = true
    try {
        const [{ data: listData }, { data: statsData }] = await Promise.all([
            salesApi.list(buildParams(page)),
            salesApi.ticketStats({
                from_date: filters.from_date || undefined,
                to_date: filters.to_date || undefined,
                ticket_group: filters.ticket_group || undefined,
                search: filters.search || undefined,
            }),
        ])

        tickets.value = listData.data || []
        pagination.current_page = listData.current_page || 1
        pagination.last_page = listData.last_page || 1
        pagination.total = listData.total || 0

        stats.total = statsData.total || 0
        stats.registered = statsData.registered || 0
        stats.cancelled = statsData.cancelled || 0
        stats.refunded = statsData.refunded || 0
        stats.transferred = statsData.transferred || 0
        locations.value = statsData.locations || []
    } catch (error) {
        console.error('Erreur chargement tickets:', error)
        tickets.value = []
    } finally {
        loading.value = false
    }
}

function applyFilters() {
    fetchTickets(1)
}

function changePage(page) {
    if (page < 1 || page > pagination.last_page) return
    fetchTickets(page)
}

function toggleSort() {
    sortDesc.value = !sortDesc.value
    tickets.value = [...tickets.value].reverse()
}

const visiblePages = computed(() => {
    const current = pagination.current_page
    const last = pagination.last_page
    if (last <= 7) {
        return Array.from({ length: last }, (_, i) => i + 1)
    }
    const pages = [1]
    if (current > 3) pages.push('...')
    for (let i = Math.max(2, current - 1); i <= Math.min(last - 1, current + 1); i++) {
        pages.push(i)
    }
    if (current < last - 2) pages.push('...')
    pages.push(last)
    return pages
})

function formatTicketNumber(ticket) {
    if (!ticket) return '-'
    const num = String(ticket.id).padStart(6, '0')
    return `Ticket #${num}`
}

function formatTicketType(ticket) {
    const type = String(ticket?.ticket_type || '').toLowerCase()
    if (type === 'liste') return 'Liste'
    if (type === 'personnalise') return 'Personnalisé'
    if (type === 'commande') return 'Commande'
    if (ticket?.origin === 'menu_commande') return 'Commande'
    if (ticket?.origin === 'livraison') return 'Livraison'
    return 'Commande'
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function formatTime(date) {
    if (!date) return '-'
    return new Date(date).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
}

function formatDateTime(date) {
    if (!date) return '-'
    return `${formatDate(date)} à ${formatTime(date)}`
}

function formatLocation(ticket) {
    if (!ticket) return '-'
    const parts = []
    if (ticket.ticket_name) parts.push(ticket.ticket_name)
    if (ticket.ticket_group) parts.push(ticket.ticket_group)
    if (parts.length) return parts.join(' / ')
    if (ticket.service_mode) return ticket.service_mode
    return '-'
}

function getUserInitials(user) {
    if (!user?.name) return '?'
    return user.name.split(' ').map((n) => n[0]).join('').slice(0, 2).toUpperCase()
}

function ticketStatusLabel(ticket) {
    if (!ticket) return '-'
    if (ticket.status === 'cancelled') return 'Annulé'
    if (ticket.status === 'refunded') return 'Remboursé'
    if (ticket.status === 'completed') return 'Enregistré'
    if (ticket.status === 'pending') return 'En cours'
    return ticket.status || '-'
}

function ticketStatusClass(ticket) {
    if (!ticket) return 'bg-gray-100 text-gray-700'
    const map = {
        completed: 'bg-green-100 text-green-700',
        cancelled: 'bg-red-100 text-red-700',
        refunded: 'bg-orange-100 text-orange-700',
        pending: 'bg-blue-100 text-blue-700',
    }
    return map[ticket.status] || 'bg-purple-100 text-purple-700'
}

function formatPaymentMethod(ticket) {
    const payments = ticket?.payments || []
    if (!payments.length) return 'Non payé'
    const type = payments[0]?.payment_type
    const map = {
        cash: 'Espèces',
        card: 'Carte',
        mobile: 'Mobile',
        virement: 'Virement',
        cheque: 'Chèque',
        credit: 'Crédit',
    }
    return map[type] || type || 'Espèces'
}

function formatLogMessage(log) {
    const actionMap = {
        commande_confirmee: 'Ticket enregistré',
        commande_modifiee: 'Ticket modifié',
        commande_annulee: 'Ticket annulé',
        statut_commande_modifie: 'Statut modifié',
        livraison: 'Commande livrée',
        retour: 'Retour enregistré',
        remboursement: 'Remboursement enregistré',
        paiement: 'Paiement enregistré',
    }
    if (actionMap[log.action]) return actionMap[log.action]
    if (log.comment) return log.comment
    return log.action || 'Action'
}

function canCancel(ticket) {
    return ticket && !['cancelled', 'refunded'].includes(ticket.status)
}

function canRefund(ticket) {
    return ticket && ticket.status === 'completed'
}

async function openDetail(id) {
    showDetailModal.value = true
    detailLoading.value = true
    showActionMenu.value = false
    try {
        const [{ data: sale }, { data: logs }] = await Promise.all([
            salesApi.get(id),
            salesApi.journal(id),
        ])
        selectedTicket.value = sale
        ticketLogs.value = Array.isArray(logs) ? logs : (logs?.data || [])
    } catch (error) {
        console.error('Erreur chargement détail:', error)
        closeDetail()
    } finally {
        detailLoading.value = false
    }
}

function closeDetail() {
    showDetailModal.value = false
    showActionMenu.value = false
    selectedTicket.value = null
    ticketLogs.value = []
}

function openCancelModal() {
    showActionMenu.value = false
    cancelForm.reason = 'client'
    cancelForm.comment = ''
    showCancelModal.value = true
}

function closeCancelModal() {
    showCancelModal.value = false
}

async function confirmCancel() {
    if (!selectedTicket.value || !cancelForm.reason) return
    cancelling.value = true
    try {
        const reasonLabel = cancelReasons.find((r) => r.value === cancelForm.reason)?.label || cancelForm.reason
        await salesApi.cancel(selectedTicket.value.id, {
            reason: reasonLabel,
            comment: cancelForm.comment || undefined,
        })
        closeCancelModal()
        closeDetail()
        await fetchTickets(pagination.current_page)
    } catch (error) {
        alert(error.response?.data?.message || "Impossible d'annuler ce ticket.")
    } finally {
        cancelling.value = false
    }
}

function handleReprint() {
    showActionMenu.value = false
    alert('Réimpression du ticket en cours de développement.')
}

function handleRefund() {
    showActionMenu.value = false
    if (!selectedTicket.value) return
    router.push({ name: 'historique-ticket.refund', params: { id: selectedTicket.value.id } })
}

function exportTickets() {
    const headers = ['Ticket', 'Date', 'Client', 'Emplacement', 'Total', 'Statut', 'Utilisateur']
    const rows = tickets.value.map((t) => [
        formatTicketNumber(t),
        formatDateTime(t.created_at),
        t.customer?.name || 'Client Anonyme',
        formatLocation(t),
        t.total || 0,
        ticketStatusLabel(t),
        t.user?.name || '-',
    ])
    const csv = [headers, ...rows].map((row) => row.map((cell) => `"${String(cell).replace(/"/g, '""')}"`).join(',')).join('\n')
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.download = `historique-tickets-${filters.from_date}-${filters.to_date}.csv`
    link.click()
}

function handleClickOutside(event) {
    if (actionMenuRef.value && !actionMenuRef.value.contains(event.target)) {
        showActionMenu.value = false
    }
}

onMounted(() => {
    fetchTickets()
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>
