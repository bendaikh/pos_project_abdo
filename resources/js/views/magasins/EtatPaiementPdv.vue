<template>
    <div class="space-y-6">
        <div class="pdv-page-hero pdv-page-hero--orange">
            <div>
                <p class="pdv-kicker">Administration · Paiements</p>
                <h1 class="pdv-title">État paiement PDV</h1>
                <p class="pdv-subtitle">Suivi des règlements et échéances des points de vente</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="pdv-stat pdv-stat--orange">
                <p class="pdv-stat-label">Total dû</p>
                <p class="pdv-stat-value text-accent-400">{{ formatCurrency(totalDu) }}</p>
            </div>
            <div class="pdv-stat pdv-stat--green">
                <p class="pdv-stat-label">Payés</p>
                <p class="pdv-stat-value text-primary-400">{{ payesCount }}</p>
            </div>
            <div class="pdv-stat pdv-stat--blue">
                <p class="pdv-stat-label">En attente</p>
                <p class="pdv-stat-value text-info-400">{{ enAttenteCount }}</p>
            </div>
        </div>

        <div class="surface-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="pdv-thead">
                            <th class="px-4 py-3 text-left">Réf</th>
                            <th class="px-4 py-3 text-left">PDV</th>
                            <th class="px-4 py-3 text-left">Propriétaire</th>
                            <th class="px-4 py-3 text-right">Montant</th>
                            <th class="px-4 py-3 text-left">Mode</th>
                            <th class="px-4 py-3 text-left">Échéance</th>
                            <th class="px-4 py-3 text-left">État</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="7" class="px-4 py-10 text-center text-text-secondary">Chargement...</td>
                        </tr>
                        <tr v-else-if="stores.length === 0">
                            <td colspan="7" class="px-4 py-10 text-center text-text-secondary">Aucun PDV</td>
                        </tr>
                        <tr
                            v-for="store in stores"
                            :key="store.id"
                            class="pdv-row"
                        >
                            <td class="px-4 py-3 font-mono text-sm text-primary-400">{{ store.code || '—' }}</td>
                            <td class="px-4 py-3 font-medium text-white">{{ store.name }}</td>
                            <td class="px-4 py-3 text-sm text-text-secondary">{{ store.display_owner_name || store.owner_name || '—' }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-white">{{ formatCurrency(store.payment_amount) }}</td>
                            <td class="px-4 py-3 text-sm text-text-secondary">{{ store.payment_method || '—' }}</td>
                            <td class="px-4 py-3 text-sm text-text-secondary">{{ store.echeance || '—' }}</td>
                            <td class="px-4 py-3">
                                <span :class="statusClass(store)" class="px-2.5 py-1 text-xs font-semibold rounded-lg">
                                    {{ statusLabel(store) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { storesApi } from '../../api'

const stores = ref([])
const loading = ref(false)

const totalDu = computed(() => stores.value.reduce((sum, s) => sum + (Number(s.payment_amount) || 0), 0))
const payesCount = computed(() => stores.value.filter(s => statusLabel(s) === 'Payé').length)
const enAttenteCount = computed(() => stores.value.filter(s => statusLabel(s) !== 'Payé').length)

function formatCurrency(amount) {
    if (amount === null || amount === undefined || amount === '') return '—'
    return Number(amount).toFixed(2)
}

function statusLabel(store) {
    const amount = Number(store.payment_amount || 0)
    if (amount <= 0) return 'Gratuit'
    if (store.payment_method === 'Esp') return 'Payé'
    if (['Chq', 'Eff', 'Vir', 'Vers'].includes(store.payment_method)) return 'En cours'
    return 'En attente'
}

function statusClass(store) {
    const label = statusLabel(store)
    if (label === 'Payé' || label === 'Gratuit') return 'bg-primary-500/20 text-primary-300'
    if (label === 'En cours') return 'bg-info-500/20 text-info-300'
    return 'bg-accent-500/20 text-accent-300'
}

onMounted(async () => {
    loading.value = true
    try {
        const { data } = await storesApi.list()
        stores.value = Array.isArray(data) ? data : []
    } catch (e) {
        stores.value = []
    } finally {
        loading.value = false
    }
})
</script>

<style scoped>
.pdv-page-hero {
    border-radius: 1.25rem;
    padding: 1.35rem 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: linear-gradient(135deg, #141625 0%, #1E2132 55%, #142838 100%);
}
.pdv-page-hero--orange {
    background: linear-gradient(135deg, #141625 0%, #2a1a28 50%, #142838 120%);
    border-color: rgba(251, 146, 60, 0.28);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
}
.pdv-kicker {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #FB923C;
    margin-bottom: 0.35rem;
}
.pdv-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.02em;
}
.pdv-subtitle { margin-top: 0.25rem; color: #94A3B8; font-size: 0.9rem; }

.pdv-stat {
    border-radius: 1rem;
    padding: 1.1rem 1.2rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(18, 22, 30, 0.92);
}
.pdv-stat--orange { border-color: rgba(251, 146, 60, 0.3); box-shadow: 0 8px 24px rgba(251, 146, 60, 0.08); }
.pdv-stat--green  { border-color: rgba(34, 211, 238, 0.3); box-shadow: 0 8px 24px rgba(34, 211, 238, 0.08); }
.pdv-stat--blue   { border-color: rgba(34, 211, 238, 0.3); box-shadow: 0 8px 24px rgba(34, 211, 238, 0.08); }
.pdv-stat-label { font-size: 0.8rem; color: #6b7280; }
.pdv-stat-value { font-size: 1.65rem; font-weight: 800; margin-top: 0.2rem; }

.pdv-thead {
    background: rgba(255, 255, 255, 0.03);
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}
.pdv-thead th {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #6b7280;
}
.pdv-row {
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    transition: background 0.15s;
}
.pdv-row:hover { background: rgba(34, 211, 238, 0.06); }
</style>
