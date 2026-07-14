<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Fiche PDV</h1>
                <p class="text-gray-500">Gestion des fiches points de vente</p>
            </div>
            <button
                type="button"
                class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center"
                @click="openForm()"
            >
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouveau PDV
            </button>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <input
                v-model="search"
                type="text"
                placeholder="Rechercher par nom PDV, propriétaire, ville..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
        </div>

        <div class="fiche-pdv-card">
            <div class="overflow-x-auto">
                <table class="fiche-pdv-table w-full min-w-[1100px]">
                    <thead>
                        <tr>
                            <th>Réf PDV</th>
                            <th>Date</th>
                            <th>Nom PDV</th>
                            <th>Nom Propriétaire</th>
                            <th>Ville</th>
                            <th>Montant</th>
                            <th>Type Règlement</th>
                            <th>Échéance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="loading">
                            <tr>
                                <td colspan="9" class="empty-cell">Chargement...</td>
                            </tr>
                        </template>
                        <template v-else-if="filteredStores.length === 0">
                            <tr>
                                <td colspan="9" class="empty-cell">Aucune fiche PDV</td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr
                                v-for="store in filteredStores"
                                :key="store.id"
                                :class="{ 'row-inactive': store.is_active === false }"
                            >
                                <td class="ref-cell">{{ store.code || '—' }}</td>
                                <td>{{ formatDate(store.opening_date || store.created_at) }}</td>
                                <td>
                                    <p class="name-cell">{{ store.name }}</p>
                                    <p v-if="store.activity" class="activity-cell">{{ store.activity }}</p>
                                </td>
                                <td>{{ store.display_owner_name || store.owner_name || store.owner?.name || '—' }}</td>
                                <td>{{ store.city || '—' }}</td>
                                <td class="amount-cell">{{ formatCurrency(store.payment_amount) }}</td>
                                <td>{{ store.payment_method || '—' }}</td>
                                <td>{{ store.echeance || '—' }}</td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg" title="Voir" @click="openView(store)">
                                            <EyeIcon class="w-4 h-4" />
                                        </button>
                                        <button type="button" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg" title="Modifier" @click="openForm(store)">
                                            <PencilIcon class="w-4 h-4" />
                                        </button>
                                        <button type="button" class="p-2 text-red-500 hover:bg-red-50 rounded-lg" title="Supprimer" @click="confirmDelete(store)">
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                        <button type="button" class="p-2 text-purple-500 hover:bg-purple-50 rounded-lg" title="Imprimer" @click="printStore(store)">
                                            <PrinterIcon class="w-4 h-4" />
                                        </button>
                                        <button type="button" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg" title="PDF" @click="exportPdf(store)">
                                            <DocumentArrowDownIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Formulaire Nouveau / Modifier -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
            <div class="fixed inset-0 bg-gray-900/60" @click="closeForm"></div>
            <div class="relative z-10 w-full max-w-6xl bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-white border-b border-gray-100 px-5 py-3 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ viewing ? 'Détail fiche PDV' : (editing ? 'Modifier fiche PDV' : 'Nouveau PDV') }}
                    </h2>
                    <button type="button" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" @click="closeForm">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <form class="p-5" @submit.prevent="saveStore">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-3 gap-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Création *</label>
                            <input v-model="form.opening_date" type="date" required :disabled="viewing" class="field">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Réf PDV *</label>
                            <input
                                v-model="form.code"
                                type="text"
                                required
                                readonly
                                class="field field--ref font-mono font-semibold"
                                placeholder="pdv-0001"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom PDV *</label>
                            <input v-model="form.name" type="text" required :disabled="viewing" class="field" placeholder="Nom du point de vente">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Activité</label>
                            <input v-model="form.activity" type="text" :disabled="viewing" class="field" placeholder="Ex: Restauration...">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Propriétaire *</label>
                            <input v-model="form.owner_name" type="text" required :disabled="viewing" class="field" placeholder="Nom du propriétaire">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">N° Téléphone *</label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                inputmode="numeric"
                                maxlength="10"
                                required
                                :disabled="viewing"
                                class="field"
                                placeholder="0612345678"
                                @input="onPhoneInput"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville *</label>
                            <input v-model="form.city" type="text" required :disabled="viewing" class="field">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                            <input v-model="form.address" type="text" :disabled="viewing" class="field" placeholder="Rue, n°, quartier...">
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Montant Paiement *</label>
                            <input
                                v-model="form.payment_amount"
                                type="text"
                                inputmode="decimal"
                                required
                                :disabled="viewing"
                                class="field"
                                placeholder="0.00"
                                @blur="normalizeAmount"
                            >
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mode Paiement *</label>
                            <select v-model="form.payment_method" required :disabled="viewing" class="field">
                                <option value="">Sélectionner...</option>
                                <option v-for="mode in paymentModes" :key="mode" :value="mode">{{ mode }}</option>
                            </select>
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Échéance *</label>
                            <select v-model="form.echeance" required :disabled="viewing" class="field">
                                <option value="">Sélectionner...</option>
                                <option v-for="opt in echeanceOptions" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                        </div>

                        <div class="lg:col-span-6 flex items-end gap-3">
                            <button
                                v-if="!viewing"
                                type="submit"
                                :disabled="saving"
                                class="form-btn form-btn--validate flex-1"
                            >
                                {{ saving ? '...' : 'Valider' }}
                            </button>
                            <button
                                type="button"
                                class="form-btn form-btn--print flex-1"
                                @click="printForm"
                            >
                                <PrinterIcon class="w-4 h-4" />
                                Imprimer
                            </button>
                            <button
                                type="button"
                                class="form-btn form-btn--cancel flex-1"
                                @click="closeForm"
                            >
                                {{ viewing ? 'Fermer' : 'Annuler' }}
                            </button>
                        </div>
                    </div>

                    <p v-if="formError" class="text-sm text-red-600 mt-3">{{ formError }}</p>
                </form>
            </div>
        </div>

        <!-- Confirm delete -->
        <div v-if="showDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60" @click="showDelete = false"></div>
            <div class="relative z-10 bg-white rounded-xl p-6 max-w-sm w-full shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer la fiche PDV</h3>
                <p class="text-gray-500 mb-4">Désactiver « {{ storeToDelete?.name }} » ?</p>
                <div class="flex gap-3">
                    <button type="button" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg" @click="showDelete = false">Annuler</button>
                    <button type="button" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700" @click="deleteStore">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import {
    PlusIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    PrinterIcon,
    DocumentArrowDownIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline'
import { storesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'

const settingsStore = useSettingsStore()

const stores = ref([])
const loading = ref(false)
const saving = ref(false)
const search = ref('')
const showForm = ref(false)
const showDelete = ref(false)
const editing = ref(null)
const viewing = ref(false)
const storeToDelete = ref(null)
const formError = ref('')

const form = reactive({
    code: '',
    opening_date: '',
    name: '',
    activity: '',
    owner_name: '',
    phone: '',
    city: '',
    address: '',
    payment_amount: '',
    payment_method: '',
    echeance: '',
})

const paymentModes = ['Esp', 'Chq', 'Eff', 'Vir', 'Vers']
const echeanceOptions = ['Mensuel', 'Annuel']

const filteredStores = computed(() => {
    // Une seule ligne par id + uniquement les fiches actives
    const seen = new Set()
    let list = stores.value.filter((s) => {
        if (s.is_active === false) return false
        if (seen.has(s.id)) return false
        seen.add(s.id)
        return true
    })

    if (search.value) {
        const q = search.value.toLowerCase()
        list = list.filter((s) => {
            const owner = s.display_owner_name || s.owner_name || s.owner?.name || ''
            return (
                s.name?.toLowerCase().includes(q) ||
                owner.toLowerCase().includes(q) ||
                s.city?.toLowerCase().includes(q) ||
                s.activity?.toLowerCase().includes(q)
            )
        })
    }

    return list
})

function formatDate(value) {
    if (!value) return '—'
    const d = new Date(value)
    if (Number.isNaN(d.getTime())) return String(value).slice(0, 10)
    return d.toLocaleDateString('fr-FR')
}

function formatCurrency(amount) {
    if (amount === null || amount === undefined || amount === '') return '—'
    const n = Number(amount)
    if (Number.isNaN(n)) return '—'
    return `${n.toFixed(2)}`
}

function todayIso() {
    return new Date().toISOString().slice(0, 10)
}

function onPhoneInput(event) {
    form.phone = String(event.target.value || '').replace(/\D/g, '').slice(0, 10)
}

function normalizeAmount() {
    if (form.payment_amount === '' || form.payment_amount === null || form.payment_amount === undefined) {
        return
    }
    const raw = String(form.payment_amount).replace(',', '.').trim()
    const n = Number(raw)
    if (Number.isNaN(n)) {
        form.payment_amount = ''
        return
    }
    form.payment_amount = n.toFixed(2)
}

function isAmountEndingWith00(value) {
    const raw = String(value ?? '').replace(',', '.').trim()
    if (!/^\d+(\.00)?$/.test(raw) && !/^\d+\.00$/.test(raw)) {
        // accept 1500 or 1500.00 then normalize to .00
        const n = Number(raw)
        if (Number.isNaN(n)) return false
        return Number.isInteger(Math.round(n * 100) / 100) && Math.abs(n * 100 - Math.round(n * 100)) < 1e-6 && (n * 100) % 100 === 0
    }
    const n = Number(raw)
    if (Number.isNaN(n)) return false
    return Math.round(n * 100) % 100 === 0
}

function resetForm() {
    form.code = ''
    form.opening_date = todayIso()
    form.name = ''
    form.activity = ''
    form.owner_name = ''
    form.phone = ''
    form.city = ''
    form.address = ''
    form.payment_amount = ''
    form.payment_method = ''
    form.echeance = ''
    formError.value = ''
}

function fillForm(store) {
    form.code = store.code || ''
    form.opening_date = (store.opening_date || store.created_at || '').toString().slice(0, 10) || todayIso()
    form.name = store.name || ''
    form.activity = store.activity || ''
    form.owner_name = store.owner_name || store.display_owner_name || store.owner?.name || ''
    form.phone = store.phone || ''
    form.city = store.city || ''
    form.address = store.address || ''
    form.payment_amount = store.payment_amount != null && store.payment_amount !== ''
        ? Number(store.payment_amount).toFixed(2)
        : ''
    form.payment_method = store.payment_method || ''
    form.echeance = store.echeance || ''
}

async function prepareNextCode() {
    try {
        const { data } = await storesApi.nextCode()
        form.code = data.code || 'pdv-0001'
    } catch (error) {
        form.code = 'pdv-0001'
    }
}

async function openForm(store = null) {
    viewing.value = false
    editing.value = store
    formError.value = ''
    if (store) {
        fillForm(store)
    } else {
        resetForm()
        await prepareNextCode()
    }
    showForm.value = true
}

function openView(store) {
    viewing.value = true
    editing.value = store
    fillForm(store)
    showForm.value = true
}

function closeForm() {
    showForm.value = false
    viewing.value = false
    editing.value = null
}

function confirmDelete(store) {
    storeToDelete.value = store
    showDelete.value = true
}

async function loadStores() {
    loading.value = true
    try {
        const { data } = await storesApi.list()
        stores.value = Array.isArray(data) ? data : []
    } catch (error) {
        console.error(error)
        stores.value = []
    } finally {
        loading.value = false
    }
}

function validateForm() {
    if (!/^\d{10}$/.test(String(form.phone || ''))) {
        return 'Le N° de téléphone doit contenir exactement 10 chiffres.'
    }
    if (!String(form.city || '').trim()) {
        return 'La ville est obligatoire.'
    }
    normalizeAmount()
    if (!form.payment_amount || !isAmountEndingWith00(form.payment_amount)) {
        return 'Le montant paiement doit se terminer par .00 (ex: 1500.00).'
    }
    if (!paymentModes.includes(form.payment_method)) {
        return 'Mode de paiement invalide (Esp, Chq, Eff, Vir, Vers).'
    }
    if (!echeanceOptions.includes(form.echeance)) {
        return 'Échéance invalide (Mensuel ou Annuel).'
    }
    return null
}

async function saveStore() {
    if (saving.value) return

    saving.value = true
    formError.value = ''

    const validationError = validateForm()
    if (validationError) {
        formError.value = validationError
        saving.value = false
        return
    }

    try {
        const payload = {
            code: form.code || undefined,
            opening_date: form.opening_date,
            name: form.name,
            activity: form.activity || null,
            owner_name: form.owner_name,
            phone: form.phone,
            city: form.city.trim(),
            address: form.address || null,
            payment_amount: Number(form.payment_amount),
            payment_method: form.payment_method,
            echeance: form.echeance,
        }

        if (editing.value?.id) {
            await storesApi.update(editing.value.id, payload)
        } else {
            await storesApi.create(payload)
        }

        closeForm()
        await loadStores()
    } catch (error) {
        formError.value = error.response?.data?.message
            || Object.values(error.response?.data?.errors || {})?.[0]?.[0]
            || 'Erreur lors de l\'enregistrement'
    } finally {
        saving.value = false
    }
}

async function deleteStore() {
    try {
        await storesApi.delete(storeToDelete.value.id)
        showDelete.value = false
        await loadStores()
    } catch (error) {
        alert(error.response?.data?.message || 'Suppression impossible')
    }
}

function buildPrintHtml(store) {
    return `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Fiche PDV - ${store.name || ''}</title>
<style>
body{font-family:Arial,sans-serif;padding:32px;color:#111}
h1{margin:0 0 8px;font-size:22px}
.meta{color:#666;margin-bottom:24px}
table{width:100%;border-collapse:collapse}
td{padding:10px 8px;border-bottom:1px solid #e5e7eb;vertical-align:top}
td:first-child{width:38%;color:#555;font-weight:600}
@media print{button{display:none}}
</style></head><body>
<h1>Fiche Point de Vente</h1>
<p class="meta">Document généré le ${formatDate(new Date().toISOString())}</p>
<table>
<tr><td>Réf PDV</td><td>${store.code || '—'}</td></tr>
<tr><td>Date création</td><td>${formatDate(store.opening_date || store.created_at)}</td></tr>
<tr><td>Nom PDV</td><td>${store.name || '—'}</td></tr>
<tr><td>Activité</td><td>${store.activity || '—'}</td></tr>
<tr><td>Propriétaire</td><td>${store.display_owner_name || store.owner_name || store.owner?.name || '—'}</td></tr>
<tr><td>Téléphone</td><td>${store.phone || '—'}</td></tr>
<tr><td>Ville</td><td>${store.city || '—'}</td></tr>
<tr><td>Adresse</td><td>${store.address || '—'}</td></tr>
<tr><td>Montant paiement</td><td>${formatCurrency(store.payment_amount)}</td></tr>
<tr><td>Mode paiement</td><td>${store.payment_method || '—'}</td></tr>
<tr><td>Échéance</td><td>${store.echeance || '—'}</td></tr>
</table>
<script>window.onload=()=>window.print()<\/script>
</body></html>`
}

function printStore(store) {
    const win = window.open('', '_blank', 'width=900,height=700')
    if (!win) {
        alert('Autorisez les pop-ups pour imprimer')
        return
    }
    win.document.write(buildPrintHtml(store))
    win.document.close()
}

function printForm() {
    printStore({
        ...editing.value,
        code: form.code,
        opening_date: form.opening_date,
        name: form.name,
        activity: form.activity,
        owner_name: form.owner_name,
        phone: form.phone,
        city: form.city,
        address: form.address,
        payment_amount: form.payment_amount,
        payment_method: form.payment_method,
        echeance: form.echeance,
    })
}

function exportPdf(store) {
    printStore(store)
}

onMounted(() => {
    loadStores()
})
</script>

<style scoped>
.field {
    width: 100%;
    height: 2.5rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid rgba(148, 163, 184, 0.22);
    border-radius: 0.65rem;
    background: #141625;
    color: #FFFFFF;
    box-sizing: border-box;
}
.field:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(34, 211, 238, 0.35);
    border-color: #22D3EE;
}
.field:disabled {
    background: rgba(255, 255, 255, 0.04);
    color: #94A3B8;
}
.field--ref {
    background: rgba(34, 211, 238, 0.1) !important;
    border-color: rgba(34, 211, 238, 0.45);
    color: #22D3EE !important;
    cursor: default;
}
.form-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    height: 2.5rem;
    padding: 0 1rem;
    border-radius: 0.65rem;
    font-size: 0.9rem;
    font-weight: 700;
    transition: all 0.15s ease;
}
.form-btn--validate {
    background: #22D3EE;
    color: #141625;
    border: none;
}
.form-btn--validate:hover { filter: brightness(1.08); }
.form-btn--validate:disabled { opacity: 0.6; cursor: not-allowed; }
.form-btn--print {
    background: #FB923C;
    color: #141625;
    border: none;
}
.form-btn--print:hover { filter: brightness(1.08); }
.form-btn--cancel {
    background: transparent;
    color: #94A3B8;
    border: 1px solid rgba(148, 163, 184, 0.25);
}
.form-btn--cancel:hover {
    background: rgba(255, 255, 255, 0.04);
    color: #FFFFFF;
}
.hint {
    margin-top: 0.25rem;
    font-size: 0.7rem;
    line-height: 1.2;
    color: #94A3B8;
}
.fiche-pdv-card {
    background: #1E2132;
    border-radius: 0.85rem;
    border: 1px solid rgba(148, 163, 184, 0.12);
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.35);
    overflow: hidden;
}
.fiche-pdv-table { border-collapse: collapse; border-spacing: 0; }
.fiche-pdv-table thead th {
    padding: 0.85rem 1rem;
    text-align: center;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #FFFFFF;
    text-decoration: underline;
    text-underline-offset: 4px;
    text-decoration-thickness: 2px;
    text-decoration-color: #22D3EE;
    background: transparent;
    border: none;
}
.fiche-pdv-table tbody td {
    padding: 0.85rem 1rem;
    font-size: 0.875rem;
    color: #94A3B8;
    text-align: center;
    vertical-align: middle;
    border: none;
    background: transparent;
}
.fiche-pdv-table tbody tr:hover td { background: rgba(34, 211, 238, 0.06); }
.fiche-pdv-table .ref-cell {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-weight: 700;
    color: #22D3EE;
}
.fiche-pdv-table .name-cell { font-weight: 600; color: #FFFFFF; }
.fiche-pdv-table .activity-cell { font-size: 0.7rem; color: #64748b; margin-top: 0.15rem; }
.fiche-pdv-table .amount-cell { font-weight: 600; color: #FFFFFF; text-align: center; }
.fiche-pdv-table .empty-cell { padding: 2.5rem 1rem; text-align: center; color: #94A3B8; }
.fiche-pdv-table .row-inactive { opacity: 0.5; }
.fiche-pdv-table tbody td .flex { justify-content: center; }
</style>