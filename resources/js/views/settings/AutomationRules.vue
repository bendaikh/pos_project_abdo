<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tâches automatiques</h1>
                <p class="text-gray-500">Créez automatiquement des tâches selon des conditions</p>
            </div>
            <button @click="openModal()" class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Ajouter règle
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 space-y-4">
            <div class="flex flex-wrap gap-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Rechercher..."
                    class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                />
                <select v-model="filterActive" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Tous les statuts</option>
                    <option :value="true">Actives</option>
                    <option :value="false">Inactives</option>
                </select>
                <select v-model="filterCondition" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Tous les types</option>
                    <option value="stock_level">Stock faible</option>
                    <option value="sales_threshold">Seuil de vente</option>
                    <option value="production_event">Événement production</option>
                    <option value="time_based">Basé sur l'heure</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Condition</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Tâche créée</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Assigné à</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Exécutions</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="rule in filteredRules" :key="rule.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div>
                                <p class="font-medium">{{ getConditionLabel(rule.condition_type) }}</p>
                                <p class="text-xs text-gray-500">{{ formatCondition(rule) }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ rule.task_subject }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ rule.assigned_to_employee?.name || rule.assigned_to_role || '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ rule.execution_count }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span v-if="rule.is_active" class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                Actif
                            </span>
                            <span v-else class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                Inactif
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <button @click="triggerRule(rule)" class="text-blue-600 hover:text-blue-800 font-medium text-xs">
                                Déclencher
                            </button>
                            <button @click="openModal(rule)" class="text-green-600 hover:text-green-800 font-medium text-xs">
                                Modifier
                            </button>
                            <button @click="deleteRule(rule.id)" class="text-red-600 hover:text-red-800 font-medium text-xs">
                                Supprimer
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="filteredRules.length === 0" class="px-6 py-12 text-center text-gray-500">
                Aucune règle trouvée
            </div>
        </div>

        <!-- Modal -->
        <AutomationRuleModal
            v-if="showModal"
            :rule="selectedRule"
            @close="closeModal"
            @saved="handleSaved"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { PlusIcon } from '@heroicons/vue/24/outline'
import AutomationRuleModal from './components/AutomationRuleModal.vue'
import api from '../../api'

const rules = ref([])
const search = ref('')
const filterActive = ref('')
const filterCondition = ref('')
const showModal = ref(false)
const selectedRule = ref(null)
const loading = ref(false)

const filteredRules = computed(() => {
    return rules.value.filter(rule => {
        const matchesSearch = !search.value ||
            rule.name.toLowerCase().includes(search.value.toLowerCase()) ||
            rule.task_subject.toLowerCase().includes(search.value.toLowerCase())

        const matchesActive = filterActive.value === '' || rule.is_active === (filterActive.value === 'true' || filterActive.value === true)

        const matchesCondition = !filterCondition.value || rule.condition_type === filterCondition.value

        return matchesSearch && matchesActive && matchesCondition
    })
})

function getConditionLabel(type) {
    const labels = {
        'stock_level': 'Stock article < minimum',
        'sales_threshold': 'Ventes article > X',
        'production_event': 'Production terminée',
        'time_based': 'Basé sur l\'heure',
        'custom': 'Personnalisé'
    }
    return labels[type] || type
}

function formatCondition(rule) {
    const data = rule.condition_data || {}
    
    switch (rule.condition_type) {
        case 'stock_level':
            return `${data.minimum_stock} kg minimum`
        case 'sales_threshold':
            return `Seuil: ${data.sales_threshold}`
        case 'time_based':
            return `${data.trigger === 'end_of_day' ? 'Fin de journée' : 'Début de journée'}`
        default:
            return ''
    }
}

function openModal(rule = null) {
    selectedRule.value = rule
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    selectedRule.value = null
}

function handleSaved() {
    closeModal()
    loadRules()
}

async function triggerRule(rule) {
    try {
        await api.post(`/automation-rules/${rule.id}/trigger`)
        alert('Tâche créée avec succès')
        loadRules()
    } catch (error) {
        console.error('Error triggering rule:', error)
        alert('Erreur lors du déclenchement')
    }
}

async function deleteRule(id) {
    if (!confirm('Êtes-vous sûr?')) return

    try {
        await api.delete(`/automation-rules/${id}`)
        loadRules()
    } catch (error) {
        console.error('Error deleting rule:', error)
        alert('Erreur lors de la suppression')
    }
}

async function loadRules() {
    try {
        loading.value = true
        const response = await api.get('/automation-rules')
        rules.value = response.data.data || response.data
    } catch (error) {
        console.error('Error loading rules:', error)
        rules.value = []
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    loadRules()
})
</script>
