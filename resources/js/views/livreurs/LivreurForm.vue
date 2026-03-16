<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <p class="text-sm text-gray-500">Module livreurs</p>
                <h1 class="text-2xl font-semibold text-gray-900">{{ isEditing ? 'Modifier le livreur' : 'Nouveau livreur' }}</h1>
            </div>
            <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100" @click="goBack">
                Retour liste
            </button>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
            <form class="xl:col-span-2 bg-white rounded-2xl border border-gray-200 p-5 space-y-5" @submit.prevent="saveAgent">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du livreur *</label>
                        <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select v-model="form.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="internal">Interne</option>
                            <option value="platform">Plateforme</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input v-model="form.phone" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type de commission *</label>
                        <select v-model="form.commission_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="percentage">Pourcentage</option>
                            <option value="fixed">Montant fixe</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valeur de commission *</label>
                        <div class="relative">
                            <input v-model.number="form.commission_value" type="number" min="0" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg pr-12">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">
                                {{ form.commission_type === 'percentage' ? '%' : 'DH' }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                        <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input v-model="form.active" type="checkbox" class="rounded border-gray-300">
                            Actif
                        </label>
                    </div>
                    <div v-if="form.type === 'platform'" class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Plateforme *</label>
                        <input v-model="form.platform_name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Glovo, Uber Eats...">
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100" @click="goBack">
                        Annuler
                    </button>
                    <button type="submit" :disabled="saving" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">
                        {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
                    </button>
                </div>
            </form>

            <div class="space-y-5">
                <div class="bg-white rounded-2xl border border-gray-200 p-5 space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900">Résumé</h2>
                    <div class="grid grid-cols-1 gap-3">
                        <div class="rounded-xl border border-blue-100 bg-blue-50 p-3">
                            <p class="text-xs text-blue-600">Mode de commission</p>
                            <p class="text-lg font-bold text-blue-900">{{ form.commission_type === 'percentage' ? 'Pourcentage' : 'Montant fixe' }}</p>
                        </div>
                        <div class="rounded-xl border border-purple-100 bg-purple-50 p-3">
                            <p class="text-xs text-purple-600">Valeur</p>
                            <p class="text-lg font-bold text-purple-900">{{ displayCommissionValue }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                            <p class="text-xs text-gray-500">Type</p>
                            <p class="text-lg font-bold text-gray-900">{{ form.type === 'platform' ? 'Plateforme' : 'Interne' }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="isEditing" class="bg-white rounded-2xl border border-gray-200 p-5 space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900">Historique récent</h2>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl border border-amber-100 bg-amber-50 p-3">
                            <p class="text-amber-700">Nb commandes</p>
                            <p class="text-xl font-bold text-amber-900">{{ summary.orders_count || 0 }}</p>
                        </div>
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-3">
                            <p class="text-emerald-700">Commission totale</p>
                            <p class="text-xl font-bold text-emerald-900">{{ formatCurrency(summary.total_commission_amount || 0) }}</p>
                        </div>
                    </div>
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-3 py-2 text-left">Commande</th>
                                    <th class="px-3 py-2 text-right">Montant</th>
                                    <th class="px-3 py-2 text-right">Commission</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="sale in recentSales" :key="sale.id">
                                    <td class="px-3 py-2">
                                        <p class="font-medium text-gray-900">{{ sale.order_number || sale.reference }}</p>
                                        <p class="text-xs text-gray-500">{{ sale.customer?.name || 'Client anonyme' }}</p>
                                    </td>
                                    <td class="px-3 py-2 text-right">{{ formatCurrency(sale.total || 0) }}</td>
                                    <td class="px-3 py-2 text-right font-semibold text-purple-700">{{ formatCurrency(sale.delivery_commission_amount || 0) }}</td>
                                </tr>
                                <tr v-if="recentSales.length === 0">
                                    <td colspan="3" class="px-3 py-4 text-center text-gray-500">Aucune livraison récente.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { deliveryAgentsApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'

const route = useRoute()
const router = useRouter()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount || 0)

const saving = ref(false)
const summary = ref({})
const recentSales = ref([])

const form = reactive({
    name: '',
    type: 'internal',
    phone: '',
    commission_type: 'percentage',
    commission_value: 0,
    status: 'active',
    active: true,
    platform_name: '',
})

const isEditing = computed(() => Boolean(route.params.id))
const displayCommissionValue = computed(() => {
    const value = Number(form.commission_value || 0)
    return form.commission_type === 'fixed' ? formatCurrency(value) : `${value}%`
})

watch(() => form.type, (type) => {
    if (type === 'internal') {
        form.platform_name = ''
    }
})

watch(() => form.status, (status) => {
    form.active = status === 'active'
})

watch(() => form.active, (active) => {
    form.status = active ? 'active' : 'inactive'
})

function hydrateForm(agent) {
    form.name = agent?.name || ''
    form.type = agent?.type || 'internal'
    form.phone = agent?.phone || ''
    form.commission_type = agent?.commission_type || 'percentage'
    form.commission_value = Number(agent?.commission_value || 0)
    form.status = agent?.status || 'active'
    form.active = Boolean(agent?.active ?? true)
    form.platform_name = agent?.platform_name || ''
}

async function loadAgent() {
    if (!isEditing.value) {
        return
    }

    try {
        const { data } = await deliveryAgentsApi.get(route.params.id)
        hydrateForm(data?.delivery_agent)
        summary.value = data?.summary || {}
        recentSales.value = Array.isArray(data?.recent_sales) ? data.recent_sales : []
    } catch (error) {
        console.error('Erreur chargement livreur:', error)
        alert(error.response?.data?.message || 'Impossible de charger ce livreur.')
        router.push({ name: 'livreurs' })
    }
}

async function saveAgent() {
    saving.value = true
    try {
        const payload = {
            name: form.name.trim(),
            type: form.type,
            phone: form.phone || null,
            commission_type: form.commission_type,
            commission_value: Number(form.commission_value || 0),
            status: form.status,
            active: Boolean(form.active),
            platform_name: form.type === 'platform' ? (form.platform_name || null) : null,
        }

        if (isEditing.value) {
            await deliveryAgentsApi.update(route.params.id, payload)
        } else {
            await deliveryAgentsApi.create(payload)
        }

        router.push({ name: 'livreurs' })
    } catch (error) {
        console.error('Erreur enregistrement livreur:', error)
        alert(error.response?.data?.message || 'Impossible d’enregistrer le livreur.')
    } finally {
        saving.value = false
    }
}

function goBack() {
    router.push({ name: 'livreurs' })
}

onMounted(async () => {
    await settingsStore.fetchSettings()
    await loadAgent()
})
</script>
