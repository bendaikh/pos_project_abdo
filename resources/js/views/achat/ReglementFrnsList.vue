<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Règlement Frns</h1>
                <p class="text-gray-500">Suivi des règlements fournisseurs</p>
            </div>
            <button
                type="button"
                class="px-4 py-2 bg-primary-500 text-white font-medium rounded-lg hover:bg-primary-600"
                @click="openForm"
            >
                Nouveau règlement
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total règlements</p>
                <p class="text-2xl font-bold text-gray-900">{{ rows.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Payés</p>
                <p class="text-2xl font-bold text-primary-500">{{ payesCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">En attente</p>
                <p class="text-2xl font-bold text-accent-500">{{ enAttenteCount }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Réf</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Fournisseur</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">Montant</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Mode</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">État</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="rows.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-gray-500">Aucun règlement fournisseur</td>
                        </tr>
                        <tr v-for="row in rows" :key="row.id" class="border-t border-gray-100 hover:bg-primary-50/40">
                            <td class="px-4 py-3 font-mono text-sm text-primary-600">{{ row.ref }}</td>
                            <td class="px-4 py-3 text-sm">{{ row.date }}</td>
                            <td class="px-4 py-3 text-sm font-medium">{{ row.fournisseur }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold">{{ formatCurrency(row.montant) }}</td>
                            <td class="px-4 py-3 text-sm">{{ row.mode }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg" :class="row.etat === 'Payé' ? 'bg-primary-100 text-primary-700' : 'bg-accent-100 text-accent-700'">
                                    {{ row.etat }}
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
import { computed, ref } from 'vue'

const rows = ref([])

const payesCount = computed(() => rows.value.filter(r => r.etat === 'Payé').length)
const enAttenteCount = computed(() => rows.value.filter(r => r.etat !== 'Payé').length)

function formatCurrency(amount) {
    return Number(amount || 0).toFixed(2)
}

function openForm() {
    alert('Formulaire Règlement Frns à compléter')
}
</script>
