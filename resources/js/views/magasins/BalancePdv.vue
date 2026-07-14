<template>
    <div class="space-y-6">
        <div class="pdv-page-hero pdv-page-hero--lime">
            <div>
                <p class="pdv-kicker">Administration · Finance</p>
                <h1 class="pdv-title">Balance PDV</h1>
                <p class="pdv-subtitle">Solde consolidé et détail par point de vente</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="pdv-stat">
                <p class="pdv-stat-label">Nbr PDV</p>
                <p class="pdv-stat-value text-white">{{ stores.length }}</p>
            </div>
            <div class="pdv-stat pdv-stat--green">
                <p class="pdv-stat-label">Total montants</p>
                <p class="pdv-stat-value text-primary-400">{{ formatCurrency(totalAmount) }}</p>
            </div>
            <div class="pdv-stat pdv-stat--orange">
                <p class="pdv-stat-label">Mensuel</p>
                <p class="pdv-stat-value text-accent-400">{{ formatCurrency(totalMensuel) }}</p>
            </div>
            <div class="pdv-stat pdv-stat--blue">
                <p class="pdv-stat-label">Annuel</p>
                <p class="pdv-stat-value text-info-400">{{ formatCurrency(totalAnnuel) }}</p>
            </div>
        </div>

        <div class="surface-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="pdv-thead">
                            <th class="px-4 py-3 text-left">Réf</th>
                            <th class="px-4 py-3 text-left">PDV</th>
                            <th class="px-4 py-3 text-left">Ville</th>
                            <th class="px-4 py-3 text-left">Échéance</th>
                            <th class="px-4 py-3 text-left">Mode</th>
                            <th class="px-4 py-3 text-right">Montant</th>
                            <th class="px-4 py-3 text-right">Part %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="7" class="px-4 py-10 text-center text-text-secondary">Chargement...</td>
                        </tr>
                        <tr v-else-if="stores.length === 0">
                            <td colspan="7" class="px-4 py-10 text-center text-text-secondary">Aucune balance</td>
                        </tr>
                        <tr v-for="store in stores" :key="store.id" class="pdv-row">
                            <td class="px-4 py-3 font-mono text-sm text-primary-400">{{ store.code || '—' }}</td>
                            <td class="px-4 py-3 font-medium text-white">{{ store.name }}</td>
                            <td class="px-4 py-3 text-sm text-text-secondary">{{ store.city || '—' }}</td>
                            <td class="px-4 py-3 text-sm text-text-secondary">{{ store.echeance || '—' }}</td>
                            <td class="px-4 py-3 text-sm text-text-secondary">{{ store.payment_method || '—' }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-white">{{ formatCurrency(store.payment_amount) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-info-300">{{ partPercent(store) }}%</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="stores.length" class="pdv-tfoot">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-sm font-semibold text-right text-text-secondary">Total balance</td>
                            <td class="px-4 py-3 text-sm font-bold text-right text-primary-400">{{ formatCurrency(totalAmount) }}</td>
                            <td class="px-4 py-3 text-sm font-bold text-right text-white">100%</td>
                        </tr>
                    </tfoot>
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

const totalAmount = computed(() => stores.value.reduce((sum, s) => sum + (Number(s.payment_amount) || 0), 0))
const totalMensuel = computed(() =>
    stores.value
        .filter(s => s.echeance === 'Mensuel')
        .reduce((sum, s) => sum + (Number(s.payment_amount) || 0), 0)
)
const totalAnnuel = computed(() =>
    stores.value
        .filter(s => s.echeance === 'Annuel')
        .reduce((sum, s) => sum + (Number(s.payment_amount) || 0), 0)
)

function formatCurrency(amount) {
    if (amount === null || amount === undefined || amount === '') return '—'
    return Number(amount).toFixed(2)
}

function partPercent(store) {
    if (!totalAmount.value) return '0.00'
    return ((Number(store.payment_amount || 0) / totalAmount.value) * 100).toFixed(2)
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
}
.pdv-page-hero--lime {
    background: linear-gradient(135deg, #141625 0%, #142838 50%, #1a1a38 120%);
    border-color: rgba(34, 211, 238, 0.3);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
}
.pdv-kicker {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #22D3EE;
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
.pdv-stat--orange { border-color: rgba(251, 146, 60, 0.3); }
.pdv-stat--green  { border-color: rgba(34, 211, 238, 0.3); }
.pdv-stat--blue   { border-color: rgba(34, 211, 238, 0.3); }
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
.pdv-tfoot {
    border-top: 1px solid rgba(34, 211, 238, 0.25);
    background: rgba(34, 211, 238, 0.06);
}
</style>
