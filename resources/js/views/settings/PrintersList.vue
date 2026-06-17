<template>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-sm text-gray-500">Paramètres &gt; Matériel &gt; Imprimantes</p>
                <h1 class="text-2xl font-bold text-gray-900">Imprimantes</h1>
            </div>
            <div class="flex gap-2">
                <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50" @click="goSettings">
                    Retour paramètres
                </button>
                <router-link
                    :to="{ name: 'settings.printers.create' }"
                    class="px-4 py-2 bg-primary-500 text-gray-900 rounded-lg hover:bg-primary-600 font-medium"
                >
                    Ajouter une imprimante
                </router-link>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="relative max-w-md">
                <MagnifyingGlassIcon class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Rechercher une imprimante..."
                    class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm"
                    @keyup.enter="fetchPrinters"
                >
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div v-if="loading" class="p-10 text-center text-gray-500">Chargement...</div>
            <table v-else class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nom</th>
                        <th class="px-4 py-3 text-left">Marque / Modèle</th>
                        <th class="px-4 py-3 text-left">Connexion</th>
                        <th class="px-4 py-3 text-left">Utilisation</th>
                        <th class="px-4 py-3 text-left">Statut</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="printer in printers" :key="printer.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ printer.name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ printer.brand || '-' }} {{ printer.model || '' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ connectionLabel(printer.connection_type) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ usageLabel(printer.usage) }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="printer.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                            >
                                {{ printer.is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <router-link
                                :to="{ name: 'settings.printers.edit', params: { id: printer.id } }"
                                class="text-primary-600 hover:text-primary-700 font-medium mr-3"
                            >
                                Modifier
                            </router-link>
                            <button type="button" class="text-red-600 hover:text-red-700" @click="deletePrinter(printer)">
                                Supprimer
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!printers.length">
                        <td colspan="6" class="px-4 py-10 text-center text-gray-500">Aucune imprimante configurée.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import { printersApi } from '../../api'

const router = useRouter()
const loading = ref(false)
const printers = ref([])
const search = ref('')

function connectionLabel(type) {
    const map = { usb: 'USB', network: 'Réseau', ethernet: 'Ethernet', bluetooth: 'Bluetooth' }
    return map[type] || type || '-'
}

function usageLabel(usage) {
    const map = { ticket_client: 'Ticket client', cuisine: 'Cuisine', both: 'Ticket + Cuisine' }
    return map[usage] || usage || '-'
}

function goSettings() {
    router.push({ name: 'settings', query: { tab: 'material' } })
}

async function fetchPrinters() {
    loading.value = true
    try {
        const { data } = await printersApi.list({ paginate: false, search: search.value || undefined })
        printers.value = Array.isArray(data) ? data : (data.data || [])
    } catch (error) {
        console.error(error)
        printers.value = []
    } finally {
        loading.value = false
    }
}

async function deletePrinter(printer) {
    if (!confirm(`Supprimer l'imprimante "${printer.name}" ?`)) return
    try {
        await printersApi.delete(printer.id)
        await fetchPrinters()
    } catch (error) {
        alert(error.response?.data?.message || 'Impossible de supprimer cette imprimante.')
    }
}

watch(search, () => {
    fetchPrinters()
})

onMounted(fetchPrinters)
</script>
