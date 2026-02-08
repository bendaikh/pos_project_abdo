<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Devis</h1>
                <p class="text-gray-500">Gérez vos devis clients</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouveau Devis
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Devis</p>
                <p class="text-2xl font-bold text-gray-900">{{ devisList.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">En attente</p>
                <p class="text-2xl font-bold text-yellow-600">{{ pendingCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Acceptés</p>
                <p class="text-2xl font-bold text-green-600">{{ acceptedCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Montant Total</p>
                <p class="text-2xl font-bold text-primary-600">{{ formatCurrency(totalAmount) }}</p>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
            <input 
                v-model="search"
                type="text"
                placeholder="Rechercher par numéro ou client..."
                class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <select v-model="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="brouillon">Brouillon</option>
                <option value="envoyé">Envoyé</option>
                <option value="accepté">Accepté</option>
                <option value="refusé">Refusé</option>
                <option value="expiré">Expiré</option>
            </select>
        </div>

        <!-- Devis Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Devis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Validité</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="devis in filteredDevis" :key="devis.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ devis.numero }}</td>
                        <td class="px-6 py-4">
                            <p class="text-gray-900">{{ devis.client_name }}</p>
                            <p class="text-sm text-gray-500">{{ devis.client_email }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(devis.date) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(devis.date_validite) }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ formatCurrency(devis.montant_total) }}</td>
                        <td class="px-6 py-4">
                            <span :class="getStatusClass(devis.statut)" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ devis.statut }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="viewDevis(devis)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Voir">
                                    <EyeIcon class="w-5 h-5" />
                                </button>
                                <button @click="openForm(devis)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                    <PencilIcon class="w-5 h-5" />
                                </button>
                                <button @click="printDevis(devis)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Imprimer">
                                    <PrinterIcon class="w-5 h-5" />
                                </button>
                                <button @click="confirmDelete(devis)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredDevis.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <DocumentTextIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            Aucun devis trouvé
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Devis Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-4xl w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingDevis ? 'Modifier le devis' : 'Nouveau devis' }}
                </h3>
                
                <form @submit.prevent="saveDevis" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                            <select v-model="form.customer_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Sélectionner un client</option>
                                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                    {{ customer.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date de validité *</label>
                            <input v-model="form.date_validite" type="date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <!-- Articles -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-900">Articles</h4>
                            <button type="button" @click="addLine" class="text-sm text-primary-600 hover:text-primary-700">+ Ajouter une ligne</button>
                        </div>
                        <div class="space-y-2">
                            <div v-for="(line, index) in form.lines" :key="index" class="flex items-center gap-2">
                                <input v-model="line.description" type="text" placeholder="Description" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input v-model.number="line.quantity" type="number" min="1" placeholder="Qté" class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <input v-model.number="line.price" type="number" step="0.01" placeholder="Prix" class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <span class="w-28 text-right font-medium">{{ formatCurrency(line.quantity * line.price) }}</span>
                                <button type="button" @click="removeLine(index)" class="p-2 text-red-400 hover:text-red-600">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200 text-right">
                            <span class="text-lg font-bold">Total: {{ formatCurrency(formTotal) }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea v-model="form.notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Conditions particulières, remarques..."></textarea>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="button" @click="showForm = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" :disabled="saving" class="flex-1 px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer le devis</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer le devis "{{ devisToDelete?.numero }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteDevis" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useSettingsStore } from '../../stores/settings'
import { customersApi } from '../../api'
import { 
    PlusIcon, 
    PencilIcon, 
    TrashIcon, 
    EyeIcon, 
    PrinterIcon,
    DocumentTextIcon 
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const devisList = ref([])
const customers = ref([])
const search = ref('')
const statusFilter = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const editingDevis = ref(null)
const devisToDelete = ref(null)
const saving = ref(false)

const form = reactive({
    customer_id: '',
    date_validite: '',
    notes: '',
    lines: [{ description: '', quantity: 1, price: 0 }]
})

const filteredDevis = computed(() => {
    let result = devisList.value
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(d => 
            d.numero.toLowerCase().includes(query) ||
            d.client_name?.toLowerCase().includes(query)
        )
    }
    if (statusFilter.value) {
        result = result.filter(d => d.statut === statusFilter.value)
    }
    return result
})

const pendingCount = computed(() => devisList.value.filter(d => d.statut === 'envoyé').length)
const acceptedCount = computed(() => devisList.value.filter(d => d.statut === 'accepté').length)
const totalAmount = computed(() => devisList.value.reduce((sum, d) => sum + (d.montant_total || 0), 0))
const formTotal = computed(() => form.lines.reduce((sum, l) => sum + (l.quantity * l.price), 0))

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function getStatusClass(status) {
    const classes = {
        'brouillon': 'bg-gray-100 text-gray-800',
        'envoyé': 'bg-yellow-100 text-yellow-800',
        'accepté': 'bg-green-100 text-green-800',
        'refusé': 'bg-red-100 text-red-800',
        'expiré': 'bg-gray-100 text-gray-500'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function addLine() {
    form.lines.push({ description: '', quantity: 1, price: 0 })
}

function removeLine(index) {
    if (form.lines.length > 1) {
        form.lines.splice(index, 1)
    }
}

function openForm(devis = null) {
    editingDevis.value = devis
    if (devis) {
        form.customer_id = devis.customer_id
        form.date_validite = devis.date_validite
        form.notes = devis.notes
        form.lines = devis.lines?.length ? [...devis.lines] : [{ description: '', quantity: 1, price: 0 }]
    } else {
        form.customer_id = ''
        form.date_validite = ''
        form.notes = ''
        form.lines = [{ description: '', quantity: 1, price: 0 }]
    }
    showForm.value = true
}

function viewDevis(devis) {
    // TODO: Implement view modal or navigate to detail page
    alert('Affichage du devis: ' + devis.numero)
}

function printDevis(devis) {
    // TODO: Implement PDF generation
    alert('Impression du devis: ' + devis.numero)
}

function confirmDelete(devis) {
    devisToDelete.value = devis
    showDeleteModal.value = true
}

async function saveDevis() {
    saving.value = true
    try {
        const customer = customers.value.find(c => c.id === form.customer_id)
        const newDevis = {
            id: editingDevis.value?.id || Date.now(),
            numero: editingDevis.value?.numero || `DEV-${Date.now()}`,
            customer_id: form.customer_id,
            client_name: customer?.name || 'Client inconnu',
            client_email: customer?.email || '',
            date: editingDevis.value?.date || new Date().toISOString(),
            date_validite: form.date_validite,
            montant_total: formTotal.value,
            statut: editingDevis.value?.statut || 'brouillon',
            notes: form.notes,
            lines: [...form.lines]
        }

        if (editingDevis.value) {
            const index = devisList.value.findIndex(d => d.id === editingDevis.value.id)
            if (index > -1) devisList.value[index] = newDevis
        } else {
            devisList.value.unshift(newDevis)
        }
        showForm.value = false
    } catch (error) {
        alert('Erreur: ' + error.message)
    } finally {
        saving.value = false
    }
}

function deleteDevis() {
    devisList.value = devisList.value.filter(d => d.id !== devisToDelete.value.id)
    showDeleteModal.value = false
}

onMounted(async () => {
    // Load customers
    try {
        const response = await customersApi.list()
        customers.value = Array.isArray(response.data) ? response.data : response.data.data || []
    } catch (e) {
        console.error('Error loading customers:', e)
    }

    // Demo data
    devisList.value = [
        { id: 1, numero: 'DEV-2026-001', customer_id: 1, client_name: 'Ahmed Benali', client_email: 'ahmed@example.com', date: '2026-02-01', date_validite: '2026-03-01', montant_total: 15000, statut: 'envoyé', lines: [] },
        { id: 2, numero: 'DEV-2026-002', customer_id: 2, client_name: 'Sara Mansouri', client_email: 'sara@example.com', date: '2026-02-05', date_validite: '2026-03-05', montant_total: 8500, statut: 'accepté', lines: [] },
        { id: 3, numero: 'DEV-2026-003', customer_id: 3, client_name: 'Mohamed Tazi', client_email: 'mohamed@example.com', date: '2026-02-08', date_validite: '2026-03-08', montant_total: 22000, statut: 'brouillon', lines: [] },
    ]
})
</script>
