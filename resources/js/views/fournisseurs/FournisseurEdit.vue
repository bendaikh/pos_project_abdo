<template>
    <div class="min-h-screen bg-slate-50 px-4 py-6">
        <form @submit.prevent="saveFournisseur" class="w-full max-w-5xl mx-auto bg-white rounded-3xl shadow-[0_25px_50px_rgba(15,23,42,0.25)] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="px-6 py-5 border-b flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.5em] text-gray-400">Fournisseur</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ isEditing ? 'Modifier le fournisseur' : 'Nouveau fournisseur' }}</h3>
                </div>
                <button type="button" @click="goBack" class="text-gray-400 hover:text-gray-600">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Tabs -->
            <div class="px-6 py-4 border-b flex flex-wrap gap-2">
                <button type="button" @click="activeTab = 'informations'" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeTab === 'informations' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                    <ClipboardDocumentListIcon class="w-4 h-4 inline-block mr-1" />
                    Informations
                </button>
                <button type="button" @click="activeTab = 'historique'" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeTab === 'historique' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                    <ClockIcon class="w-4 h-4 inline-block mr-1" />
                    Historique
                </button>
                <button type="button" @click="activeTab = 'documents'" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeTab === 'documents' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                    <CalendarDaysIcon class="w-4 h-4 inline-block mr-1" />
                    Documents
                </button>
            </div>

            <!-- Content -->
            <div class="px-6 py-6 overflow-y-auto space-y-6 flex-1">
                <!-- Informations Tab -->
                <div v-show="activeTab === 'informations'" class="space-y-6">
                    <div class="grid gap-6 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                        <!-- Left Panel: Photo & Basic Info -->
                        <section class="bg-gray-50 border border-gray-200 rounded-2xl p-5 shadow-sm space-y-5">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-primary-100">
                                    <img v-if="supplierPhotoPreview" :src="supplierPhotoPreview" alt="Photo fournisseur" class="w-full h-full object-cover" />
                                    <PhotoIcon v-else class="w-full h-full p-3 text-primary-500" />
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.3em] text-gray-400">ID Fournisseur</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ fournisseur?.fournisseur_id || (fournisseur ? `FRN-${String(fournisseur.id).padStart(4, '0')}` : 'Auto-généré') }}</p>
                                    <p class="text-xs text-gray-500">Type : {{ form.type === 'entreprise' ? 'Entreprise' : 'Particulier' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 border border-gray-200 rounded-xl cursor-pointer text-sm font-medium text-primary-600 hover:bg-primary-50">
                                    <PhotoIcon class="w-4 h-4" />
                                    Changer la photo
                                    <input type="file" accept="image/*" class="sr-only" @change="handlePhotoUpload">
                                </label>
                                <button type="button" @click="supplierPhotoPreview = ''" class="px-3 py-2 border border-gray-200 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50">Supprimer</button>
                            </div>

                            <!-- Basic Info Fields -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Nom *</label>
                                    <input v-model="form.nom" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom de famille">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Prénom</label>
                                    <input v-model="form.prenom" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Prénom">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Type</label>
                                    <select v-model="form.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="particulier">Particulier</option>
                                        <option value="entreprise">Entreprise</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Raison sociale</label>
                                    <input v-model="form.raison_sociale" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom de l'entreprise">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Statut</label>
                                    <select v-model="form.statut" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="Actif">Actif</option>
                                        <option value="Inactif">Inactif</option>
                                        <option value="Suspendu">Suspendu</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input v-model="form.prefere" type="checkbox" id="prefere" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <label for="prefere" class="text-sm font-medium text-gray-700">Fournisseur préféré</label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Activité</label>
                                <input v-model="form.activite" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Distribution, Import, ...">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Banque</label>
                                    <input v-model="form.banque" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Banque partenaire">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">IBAN</label>
                                    <input v-model="form.iban" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="IBAN">
                                </div>
                            </div>
                        </section>

                        <!-- Right Panel: Contact Info & Legal -->
                        <section class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm space-y-5">
                            <h4 class="text-md font-semibold text-gray-800">Coordonnées</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Téléphone</label>
                                    <input v-model="form.phone" type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="+212 600 000 000">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Email</label>
                                    <input v-model="form.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="email@fournisseur.com">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Ville</label>
                                    <input v-model="form.city" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ville">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Pays</label>
                                    <input v-model="form.country" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Pays">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Contact principal</label>
                                    <input v-model="form.contact_principal" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom du contact">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Site web</label>
                                    <input v-model="form.site_web" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="www.example.com">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Adresse</label>
                                <textarea v-model="form.address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Adresse complète"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">ICE</label>
                                    <input v-model="form.ice" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="000000000000000">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">IF</label>
                                    <input v-model="form.if" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="000000000000000">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Reg. Commercial</label>
                                    <input v-model="form.reg_commercial" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="RC-123456">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">TVA</label>
                                    <input v-model="form.tva" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="TVA-123456">
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <input v-model="form.is_active" type="checkbox" id="is_active" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                <label for="is_active" class="text-sm font-medium text-gray-700">Fournisseur actif</label>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Historique Tab -->
                <div v-show="activeTab === 'historique'" class="space-y-4">
                    <p class="text-gray-600">Historique des commandes (lecture seule)</p>
                </div>

                <!-- Documents Tab -->
                <div v-show="activeTab === 'documents'" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-700">Contrat</p>
                                <input type="file" class="text-xs text-primary-500" @change="handleContractUpload">
                            </div>
                            <p class="text-xs text-gray-500">Upload du contrat signé (PDF, DOC)</p>
                            <div v-if="form.contract_file" class="mt-2 flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700">
                                <span>{{ form.contract_file.name }}</span>
                                <button type="button" @click="form.contract_file = null" class="text-red-500 hover:text-red-700">Supprimer</button>
                            </div>
                        </article>
                        <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-700">RIB</p>
                                <input type="file" class="text-xs text-primary-500" @change="handleRibUpload">
                            </div>
                            <p class="text-xs text-gray-500">Relevé bancaire officiel</p>
                            <div v-if="form.rib_file" class="mt-2 flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700">
                                <span>{{ form.rib_file.name }}</span>
                                <button type="button" @click="form.rib_file = null" class="text-red-500 hover:text-red-700">Supprimer</button>
                            </div>
                        </article>
                    </div>
                    <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                        <h4 class="text-sm font-semibold text-gray-700">Notes internes</h4>
                        <textarea v-model="form.note" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Demande spécifique, conditions de paiement..."></textarea>
                    </article>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
                <button type="button" @click="goBack" class="px-5 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-100">Annuler</button>
                <button type="submit" :disabled="saving" class="px-5 py-2 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 disabled:opacity-60">{{ saving ? 'Enregistrement...' : 'Enregistrer' }}</button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import {
    XMarkIcon,
    ClipboardDocumentListIcon,
    ClockIcon,
    PhotoIcon,
    CalendarDaysIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const STORAGE_KEY = 'pos_fournisseurs'
const API_BASE = 'http://localhost:8000/api'

const isEditing = ref(false)
const activeTab = ref('informations')
const saving = ref(false)
const supplierPhotoPreview = ref('')
const fournisseur = ref(null)

const form = reactive({
    nom: '',
    prenom: '',
    raison_sociale: '',
    activite: '',
    type: 'particulier',
    statut: 'Actif',
    prefere: false,
    phone: '',
    email: '',
    address: '',
    city: '',
    country: '',
    contact_principal: '',
    site_web: '',
    ice: '',
    reg_commercial: '',
    tva: '',
    if: '',
    note: '',
    is_active: true,
    contract_file: null,
    rib_file: null,
    banque: '',
    iban: ''
})

// API Call
async function fetchFournisseur(id) {
    try {
        const response = await fetch(`${API_BASE}/fournisseurs/${id}`)
        if (response.ok) {
            return await response.json()
        }
    } catch (error) {
        console.error('API Error:', error)
    }
    return null
}

// LocalStorage Fallback
function readStoredFournisseurs() {
    try {
        const data = localStorage.getItem(STORAGE_KEY)
        return data ? JSON.parse(data) : []
    } catch (error) {
        console.error('LocalStorage Error:', error)
        return []
    }
}

function persistFournisseurs(list) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(list || []))
}

// Load fournisseur
async function loadFournisseur() {
    const id = route.params.id
    if (!id) {
        isEditing.value = false
        return
    }

    isEditing.value = true
    let data = await fetchFournisseur(id)

    if (!data) {
        const stored = readStoredFournisseurs()
        data = stored.find(f => String(f.id) === String(id))
    }

    if (data) {
        fournisseur.value = data
        populateForm(data)
        supplierPhotoPreview.value = data.photo_url || data.logo || ''
    }
}

function populateForm(supplier) {
    form.nom = supplier.nom || ''
    form.prenom = supplier.prenom || ''
    form.raison_sociale = supplier.raison_sociale || ''
    form.activite = supplier.activite || ''
    form.type = supplier.type || 'particulier'
    form.statut = supplier.statut || 'Actif'
    form.prefere = supplier.prefere || false
    form.phone = supplier.telephone || supplier.phone || ''
    form.email = supplier.email || ''
    form.address = supplier.address || supplier.adresse || ''
    form.city = supplier.city || supplier.ville || ''
    form.country = supplier.country || ''
    form.contact_principal = supplier.contact_principal || ''
    form.site_web = supplier.site_web || ''
    form.ice = supplier.ice || ''
    form.reg_commercial = supplier.reg_commercial || ''
    form.tva = supplier.tva || ''
    form.if = supplier.if || ''
    form.note = supplier.note || ''
    form.is_active = supplier.is_active !== false
    form.banque = supplier.banque || ''
    form.iban = supplier.iban || ''
    form.contract_file = supplier.contract_file || null
    form.rib_file = supplier.rib_file || null
}

function handlePhotoUpload(event) {
    const file = event.target.files?.[0]
    if (!file) return
    const reader = new FileReader()
    reader.onload = (e) => {
        supplierPhotoPreview.value = e.target?.result || ''
    }
    reader.readAsDataURL(file)
}

function handleContractUpload(event) {
    const file = event.target.files?.[0]
    if (!file) return
    const reader = new FileReader()
    reader.onload = (e) => {
        form.contract_file = {
            name: file.name,
            url: e.target?.result || ''
        }
    }
    reader.readAsDataURL(file)
}

function handleRibUpload(event) {
    const file = event.target.files?.[0]
    if (!file) return
    const reader = new FileReader()
    reader.onload = (e) => {
        form.rib_file = {
            name: file.name,
            url: e.target?.result || ''
        }
    }
    reader.readAsDataURL(file)
}

async function saveFournisseur() {
    saving.value = true
    try {
        const stored = readStoredFournisseurs()
        const existingIndex = fournisseur.value ? stored.findIndex(f => String(f.id) === String(fournisseur.value.id)) : -1
        const existing = existingIndex > -1 ? stored[existingIndex] : null

        const payload = {
            id: fournisseur.value?.id || Date.now(),
            fournisseur_id: fournisseur.value?.fournisseur_id || `FRN-${String(Date.now()).slice(-4)}`,
            name: `${form.nom} ${form.prenom}`.trim(),
            nom: form.nom,
            prenom: form.prenom,
            raison_sociale: form.raison_sociale,
            activite: form.activite,
            type: form.type,
            statut: form.statut,
            prefere: form.prefere,
            telephone: form.phone,
            email: form.email,
            adresse: form.address,
            address: form.address,
            ville: form.city,
            city: form.city,
            country: form.country,
            contact_principal: form.contact_principal,
            site_web: form.site_web,
            ice: form.ice,
            reg_commercial: form.reg_commercial,
            tva: form.tva,
            if: form.if,
            note: form.note,
            is_active: form.is_active,
            photo_url: supplierPhotoPreview.value || existing?.photo_url || '',
            logo: supplierPhotoPreview.value || existing?.logo || '',
            banque: form.banque,
            iban: form.iban,
            contract_file: form.contract_file || existing?.contract_file || null,
            rib_file: form.rib_file || existing?.rib_file || null,
            orders_count: existing?.orders_count || 0,
            total_purchases: existing?.total_purchases || 0,
            commandes: existing?.commandes || []
        }

        if (existingIndex > -1) {
            stored[existingIndex] = payload
        } else {
            stored.unshift(payload)
        }

        persistFournisseurs(stored)
        router.push(`/fournisseurs/${payload.id}`)
    } catch (error) {
        alert('Erreur: ' + error.message)
    } finally {
        saving.value = false
    }
}

function goBack() {
    if (isEditing.value && fournisseur.value) {
        router.push(`/fournisseurs/${fournisseur.value.id}`)
    } else {
        router.push('/fournisseurs')
    }
}

onMounted(() => {
    loadFournisseur()
})
</script>
