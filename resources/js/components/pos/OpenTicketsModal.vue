<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center px-4 py-6">
            <div class="fixed inset-0 bg-slate-900/45 backdrop-blur-[2px]" @click="$emit('close')"></div>

            <div class="relative z-10 w-full max-w-4xl overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-2xl">
                <div class="border-b border-slate-100 px-6 py-4">
                    <div class="relative flex items-center justify-center">
                        <h2 class="text-2xl font-semibold text-slate-900">Ouvrir un ticket</h2>
                        <button
                            type="button"
                            class="absolute right-0 inline-flex h-10 w-10 items-center justify-center rounded-full text-3xl leading-none text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                            @click="$emit('close')"
                        >
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>

                <div class="px-6 pb-6 pt-4">
                    <div class="flex items-center justify-between gap-3 rounded-[20px] bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <p>Parcourez les tickets déjà enregistrés et chargez-en un dans le POS.</p>
                        <button
                            type="button"
                            class="shrink-0 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 disabled:opacity-50"
                            :disabled="savedTicketsLoading"
                            @click="$emit('refresh-tickets')"
                        >
                            {{ savedTicketsLoading ? '...' : 'Actualiser' }}
                        </button>
                    </div>

                    <section class="mt-5 min-h-[420px] space-y-5">
                        <div v-if="currentTicket" class="space-y-3">
                            <h3 class="text-xl font-semibold text-slate-700">Ticket actuel</h3>
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <button
                                    type="button"
                                    class="rounded-[18px] border p-4 text-left shadow-sm transition"
                                    :class="getTicketCardClass(currentTicket)"
                                    :disabled="isTicketBusy(currentTicket)"
                                    @click="$emit('load-ticket', currentTicket.id)"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-lg font-semibold text-slate-900">{{ getTicketTitle(currentTicket) }}</p>
                                            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                                {{ getTicketModeLabel(currentTicket) }}
                                            </p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-blue-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-blue-700">
                                            Actuel
                                        </span>
                                    </div>
                                    <div class="mt-3 space-y-1 text-xs text-slate-500">
                                        <p class="truncate">{{ currentTicket.customer?.name || 'Client anonyme' }}</p>
                                        <p>{{ currentTicket.ticket_group || 'Sans groupe' }}</p>
                                        <div class="flex items-center justify-between gap-3">
                                            <span>{{ formatTicketDate(currentTicket) }}</span>
                                            <span class="font-semibold text-emerald-600">{{ formatCurrency(currentTicket.total || 0) }}</span>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div v-if="otherTickets.length" class="space-y-3">
                            <h3 class="text-xl font-semibold text-slate-700">Autres tickets</h3>
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <button
                                    v-for="ticket in otherTickets"
                                    :key="ticket.id"
                                    type="button"
                                    class="rounded-[18px] border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:border-slate-300 hover:shadow-md disabled:opacity-50"
                                    :disabled="isTicketBusy(ticket)"
                                    @click="$emit('load-ticket', ticket.id)"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-lg font-semibold text-slate-900">{{ getTicketTitle(ticket) }}</p>
                                            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                                {{ getTicketModeLabel(ticket) }}
                                            </p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-700">
                                            Ouvrir
                                        </span>
                                    </div>
                                    <div class="mt-3 space-y-1 text-xs text-slate-500">
                                        <p class="truncate">{{ ticket.customer?.name || 'Client anonyme' }}</p>
                                        <p>{{ ticket.ticket_group || 'Sans groupe' }}</p>
                                        <div class="flex items-center justify-between gap-3">
                                            <span>{{ formatTicketDate(ticket) }}</span>
                                            <span class="font-semibold text-emerald-600">{{ formatCurrency(ticket.total || 0) }}</span>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div v-if="!currentTicket && !otherTickets.length" class="rounded-[22px] border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center text-sm text-slate-500">
                            Aucun ticket enregistré pour le moment.
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCustomListsStore } from '../../stores/customLists'
import { useSettingsStore } from '../../stores/settings'

const props = defineProps({
    savedTickets: {
        type: Array,
        default: () => [],
    },
    savedTicketsLoading: {
        type: Boolean,
        default: false,
    },
    loadingSavedTicketId: {
        type: [Number, String, null],
        default: null,
    },
    currentSaleId: {
        type: [Number, String, null],
        default: null,
    },
    currentServiceMode: {
        type: String,
        default: '',
    },
})

defineEmits(['close', 'refresh-tickets', 'load-ticket'])

const customListsStore = useCustomListsStore()
const settingsStore = useSettingsStore()

const currentTicket = computed(() => {
    return (props.savedTickets || [])
        .find((ticket) => Number(ticket?.id || 0) === Number(props.currentSaleId || 0)) || null
})

const otherTickets = computed(() => {
    return (props.savedTickets || [])
        .filter((ticket) => Number(ticket?.id || 0) !== Number(props.currentSaleId || 0))
        .sort(sortTickets)
})

function getTicketTitle(ticket) {
    return ticket?.ticket_name || ticket?.reference || `Ticket #${ticket?.id || '-'}`
}

function getTicketModeLabel(ticket) {
    return customListsStore.getServiceModeLabel(ticket?.service_mode || ticket?.delivery_mode)
}

function formatCurrency(amount) {
    return settingsStore.formatCurrency(amount)
}

function formatTicketDate(ticket) {
    const source = ticket?.updated_at || ticket?.created_at
    if (!source) return '-'

    return new Date(source).toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })
}

function isTicketBusy(ticket) {
    return Number(props.loadingSavedTicketId || 0) === Number(ticket?.id || 0)
}

function getTicketCardClass(ticket) {
    if (Number(props.currentSaleId || 0) === Number(ticket?.id || 0)) {
        return 'border-blue-500 bg-blue-50 ring-2 ring-blue-100'
    }

    return 'border-slate-200 bg-white hover:border-slate-300 hover:shadow-md'
}

function sortTickets(left, right) {
    return new Date(right?.updated_at || right?.created_at || 0) - new Date(left?.updated_at || left?.created_at || 0)
}
</script>
