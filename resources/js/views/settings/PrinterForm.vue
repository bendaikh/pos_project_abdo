<template>
    <div class="space-y-6 max-w-7xl mx-auto">
        <div class="flex items-center gap-3">
            <button type="button" class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50" @click="goBack">
                <ArrowLeftIcon class="w-5 h-5 text-gray-600" />
            </button>
            <div>
                <p class="text-sm text-gray-500">Paramètres &gt; Matériel &gt; Imprimantes &gt; {{ isEditing ? 'Modifier' : 'Ajouter imprimante' }}</p>
                <h1 class="text-2xl font-bold text-gray-900">{{ isEditing ? 'Modifier une imprimante' : 'Ajouter une imprimante' }}</h1>
            </div>
        </div>

        <!-- Stepper -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="(step, index) in steps"
                    :key="step.id"
                    type="button"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition"
                    :class="currentStep === index
                        ? 'bg-primary-500 text-gray-900'
                        : index < currentStep ? 'bg-primary-50 text-primary-700' : 'bg-gray-100 text-gray-500'"
                    @click="currentStep = index"
                >
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                        :class="currentStep === index ? 'bg-white/30' : 'bg-white'">{{ index + 1 }}</span>
                    {{ step.label }}
                </button>
            </div>
        </div>

        <div v-if="loading" class="bg-white rounded-xl border p-12 text-center text-gray-500">Chargement...</div>

        <form v-else class="space-y-6" @submit.prevent="save">
            <!-- Step 1: Général -->
            <div v-show="currentStep === 0" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                    <h2 class="font-semibold text-gray-900">Informations générales</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom imprimante <span class="text-red-500">*</span></label>
                        <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Caisse 01">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Marque</label>
                        <select v-model="form.brand" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Sélectionner</option>
                            <option v-for="b in brands" :key="b" :value="b">{{ b }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modèle</label>
                        <select v-model="form.model" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Sélectionner</option>
                            <option v-for="m in modelsForBrand" :key="m" :value="m">{{ m }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type de connexion <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="ct in connectionTypes" :key="ct.value" class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer"
                                :class="form.connection_type === ct.value ? 'border-primary-500 bg-primary-50' : 'border-gray-200'">
                                <input v-model="form.connection_type" type="radio" :value="ct.value" class="text-primary-500">
                                <span class="text-sm">{{ ct.label }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div v-if="['ethernet', 'network'].includes(form.connection_type)" class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                    <h2 class="font-semibold text-gray-900">Paramètres {{ form.connection_type === 'ethernet' ? 'Ethernet' : 'Réseau' }}</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse IP <span class="text-red-500">*</span></label>
                        <input v-model="form.ip_address" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="192.168.1.150">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Masque de sous-réseau</label>
                        <input v-model="form.subnet_mask" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="255.255.255.0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Passerelle</label>
                        <input v-model="form.gateway" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="192.168.1.1">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Port <span class="text-red-500">*</span></label>
                        <input v-model.number="form.port" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="9100">
                    </div>
                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-xs text-blue-700">
                        L'adresse IP doit être de type statique pour une meilleure connexion.
                    </div>
                </div>
                <div v-else class="bg-white rounded-xl border border-gray-200 p-5 flex items-center justify-center text-gray-400 text-sm">
                    Paramètres réseau non requis pour {{ connectionLabel(form.connection_type) }}
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                    <h2 class="font-semibold text-gray-900">Utilisation & Description</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Utilisation <span class="text-red-500">*</span></label>
                        <select v-model="form.usage" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="ticket_client">Ticket client</option>
                            <option value="cuisine">Cuisine</option>
                            <option value="both">Ticket client + Cuisine</option>
                        </select>
                        <p class="text-xs text-blue-600 mt-2 bg-blue-50 border border-blue-100 rounded p-2">
                            Sélectionnez l'utilisation principale de cette imprimante.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (optionnel)</label>
                        <textarea v-model="form.description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Ex : Imprimante principale pour les tickets clients" />
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300">
                        Imprimante active
                    </label>
                </div>
            </div>

            <!-- Step 2: Ticket client -->
            <div v-show="currentStep === 1" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                    <h2 class="font-semibold text-gray-900">Contenu du ticket client</h2>
                    <p class="text-xs text-gray-500">Sélectionnez les informations à imprimer</p>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <div
                            v-for="(field, index) in form.ticket_config.content"
                            :key="field.key"
                            class="flex items-center justify-between p-2 border border-gray-100 rounded-lg hover:bg-gray-50"
                        >
                            <label class="flex items-center gap-2 text-sm cursor-pointer flex-1">
                                <input v-model="field.enabled" type="checkbox" class="rounded border-gray-300">
                                {{ field.label }}
                            </label>
                            <button type="button" class="text-gray-400 hover:text-gray-600 px-1" @click="moveContentField('ticket', index, -1)">☰</button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                    <h2 class="font-semibold text-gray-900">Options d'impression</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Largeur papier</label>
                        <select v-model="form.ticket_config.paper_width" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="58">58 mm</option>
                            <option value="80">80 mm</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de copies</label>
                        <input v-model.number="form.ticket_config.copies" type="number" min="1" max="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alignement</label>
                        <div class="flex gap-2">
                            <button v-for="a in alignments" :key="a" type="button"
                                class="flex-1 py-2 rounded-lg border text-sm"
                                :class="form.ticket_config.alignment === a ? 'border-primary-500 bg-primary-50' : 'border-gray-200'"
                                @click="form.ticket_config.alignment = a">{{ a === 'left' ? 'Gauche' : a === 'center' ? 'Centre' : 'Droite' }}</button>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm"><input v-model="form.ticket_config.auto_cut" type="checkbox" class="rounded"> Découpe automatique</label>
                        <label class="flex items-center gap-2 text-sm"><input v-model="form.ticket_config.open_drawer" type="checkbox" class="rounded"> Ouvrir tiroir caisse après impression</label>
                        <label class="flex items-center gap-2 text-sm"><input v-model="form.ticket_config.auto_print_on_payment" type="checkbox" class="rounded"> Impression automatique après paiement</label>
                    </div>
                    <button type="button" class="w-full py-2 border border-primary-300 text-primary-700 rounded-lg hover:bg-primary-50 text-sm font-medium" @click="testPrint('ticket')">
                        Tester l'impression
                    </button>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Aperçu du ticket client</h2>
                    <div class="bg-gray-50 border border-dashed border-gray-300 rounded-lg p-4 text-xs font-mono space-y-1 max-w-xs mx-auto"
                        :style="{ textAlign: form.ticket_config.alignment }">
                        <p v-if="isTicketFieldEnabled('logo')" class="text-center font-bold">[LOGO]</p>
                        <p v-if="isTicketFieldEnabled('company_name')" class="font-bold">{{ storeName }}</p>
                        <p v-if="isTicketFieldEnabled('address')" class="text-gray-600">{{ storeAddress }}</p>
                        <p v-if="isTicketFieldEnabled('phone')">{{ storePhone }}</p>
                        <p v-if="isTicketFieldEnabled('ice')">ICE: {{ storeIce }}</p>
                        <p v-if="isTicketFieldEnabled('qr_code')" class="text-center">[QR CODE]</p>
                        <hr class="my-2 border-gray-300">
                        <p v-if="isTicketFieldEnabled('datetime')">{{ previewDate }}</p>
                        <p v-if="isTicketFieldEnabled('ticket_number')">Ticket #000125</p>
                        <p v-if="isTicketFieldEnabled('customer_info')">Client: Karim Benali</p>
                        <hr class="my-2 border-gray-300">
                        <p>Café Expresso x2 .... 30,00</p>
                        <p>Salade Marocaine x1 .. 25,00</p>
                        <hr class="my-2 border-gray-300">
                        <p v-if="isTicketFieldEnabled('subtotal')">Sous-total: 127,00 DH</p>
                        <p v-if="isTicketFieldEnabled('discount')">Remise: -5,00 DH</p>
                        <p v-if="isTicketFieldEnabled('tax')">TVA (10%): 12,20 DH</p>
                        <p v-if="isTicketFieldEnabled('total')" class="font-bold">TOTAL: 134,20 DH</p>
                        <p v-if="isTicketFieldEnabled('footer')" class="text-center mt-2">Merci et à bientôt!</p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Cuisine -->
            <div v-show="currentStep === 2" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                    <h2 class="font-semibold text-gray-900">Contenu du ticket cuisine</h2>
                    <p class="text-xs text-gray-500">Sélectionnez les informations à imprimer sur le ticket cuisine</p>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <div
                            v-for="(field, index) in form.kitchen_config.content"
                            :key="field.key"
                            class="flex items-center justify-between p-2 border border-gray-100 rounded-lg"
                        >
                            <label class="flex items-center gap-2 text-sm cursor-pointer flex-1">
                                <input v-model="field.enabled" type="checkbox" class="rounded border-gray-300">
                                {{ field.label }}
                            </label>
                            <button type="button" class="text-gray-400 px-1" @click="moveContentField('kitchen', index, -1)">☰</button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                    <h2 class="font-semibold text-gray-900">Options d'impression cuisine</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Largeur papier</label>
                        <select v-model="form.kitchen_config.paper_width" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="58">58 mm</option>
                            <option value="80">80 mm</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Copies</label>
                        <input v-model.number="form.kitchen_config.copies" type="number" min="1" max="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alignement</label>
                        <div class="flex gap-2">
                            <button v-for="a in alignments" :key="'k'+a" type="button"
                                class="flex-1 py-2 rounded-lg border text-sm"
                                :class="form.kitchen_config.alignment === a ? 'border-primary-500 bg-primary-50' : 'border-gray-200'"
                                @click="form.kitchen_config.alignment = a">{{ a === 'left' ? 'Gauche' : a === 'center' ? 'Centre' : 'Droite' }}</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Espacement entre les lignes</label>
                        <select v-model="form.kitchen_config.line_spacing" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="compact">Compact</option>
                            <option value="normal">Normal</option>
                            <option value="large">Large</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm"><input v-model="form.kitchen_config.auto_print_on_validate" type="checkbox" class="rounded"> Impression automatique dès la validation</label>
                        <label class="flex items-center gap-2 text-sm"><input v-model="form.kitchen_config.group_by_category" type="checkbox" class="rounded"> Grouper les articles par catégorie</label>
                    </div>
                    <button type="button" class="w-full py-2 border border-primary-300 text-primary-700 rounded-lg hover:bg-primary-50 text-sm" @click="testPrint('kitchen')">
                        Tester l'impression
                    </button>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                    <h2 class="font-semibold text-gray-900">Catégories à imprimer</h2>
                    <p class="text-xs text-gray-500">Sélectionnez les catégories imprimées sur cette imprimante cuisine.</p>
                    <input v-model="categorySearch" type="text" placeholder="Rechercher une catégorie..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                        <input v-model="selectAllCategories" type="checkbox" class="rounded" @change="toggleAllCategories">
                        Sélectionner tout
                    </label>
                    <div class="max-h-64 overflow-y-auto space-y-1 border border-gray-100 rounded-lg p-2">
                        <label
                            v-for="cat in filteredCategories"
                            :key="cat.id"
                            class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 cursor-pointer text-sm"
                        >
                            <input
                                type="checkbox"
                                class="rounded border-gray-300"
                                :checked="form.kitchen_config.category_ids.includes(cat.id)"
                                @change="toggleCategory(cat.id)"
                            >
                            {{ cat.name }}
                        </label>
                        <p v-if="!filteredCategories.length" class="text-center text-gray-400 py-4 text-sm">Aucune catégorie</p>
                    </div>
                </div>
            </div>

            <!-- Step 4: Avancé -->
            <div v-show="currentStep === 3" class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl">
                <h2 class="font-semibold text-gray-900 mb-4">Paramètres avancés</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Encodage</label>
                        <select v-model="form.advanced_config.encoding" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="UTF-8">UTF-8</option>
                            <option value="ISO-8859-1">ISO-8859-1</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Page de code</label>
                        <select v-model="form.advanced_config.code_page" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="cp850">CP850</option>
                            <option value="cp437">CP437</option>
                            <option value="windows-1252">Windows-1252</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Densité d'impression</label>
                        <select v-model="form.advanced_config.print_density" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="light">Légère</option>
                            <option value="normal">Normale</option>
                            <option value="dark">Foncée</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Délai d'attente (sec)</label>
                        <input v-model.number="form.advanced_config.timeout_seconds" type="number" min="1" max="60" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tentatives max</label>
                        <input v-model.number="form.advanced_config.max_retries" type="number" min="0" max="10" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <label class="flex items-center gap-2 text-sm"><input v-model="form.advanced_config.beep_on_print" type="checkbox" class="rounded"> Bip à l'impression</label>
                    <label class="flex items-center gap-2 text-sm"><input v-model="form.advanced_config.retry_on_failure" type="checkbox" class="rounded"> Réessayer en cas d'échec</label>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between bg-white rounded-xl border border-gray-200 px-5 py-4 shadow-sm sticky bottom-4">
                <button type="button" class="px-5 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center gap-2" @click="goBack">
                    <XMarkIcon class="w-4 h-4" /> Annuler
                </button>
                <div class="flex gap-2">
                    <button v-if="currentStep > 0" type="button" class="px-5 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50" @click="currentStep--">
                        Précédent
                    </button>
                    <button v-if="currentStep < steps.length - 1" type="button" class="px-5 py-2.5 bg-gray-800 text-white rounded-lg hover:bg-gray-900" @click="currentStep++">
                        Suivant
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-primary-500 text-gray-900 rounded-lg hover:bg-primary-600 font-semibold flex items-center gap-2 disabled:opacity-50" :disabled="saving || !form.name">
                        <CheckIcon class="w-4 h-4" />
                        {{ saving ? 'Enregistrement...' : 'Sauvegarder' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeftIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { categoriesApi, printersApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'

const route = useRoute()
const router = useRouter()
const settingsStore = useSettingsStore()

const isEditing = computed(() => Boolean(route.params.id))
const loading = ref(false)
const saving = ref(false)
const currentStep = ref(0)
const categories = ref([])
const categorySearch = ref('')
const selectAllCategories = ref(false)

const steps = [
    { id: 'general', label: 'Général' },
    { id: 'ticket', label: 'Ticket client' },
    { id: 'kitchen', label: 'Cuisine' },
    { id: 'advanced', label: 'Avancé' },
]

const brands = ['Epson', 'Star', 'Bixolon', 'Citizen', 'Zebra']
const brandModels = {
    Epson: ['TM-T88VI', 'TM-T20III', 'TM-m30II'],
    Star: ['TSP143III', 'TSP654II'],
    Bixolon: ['SRP-350III', 'SRP-Q300'],
    Citizen: ['CT-S651II', 'CT-E351'],
    Zebra: ['ZD410', 'ZD620'],
}
const connectionTypes = [
    { value: 'usb', label: 'USB' },
    { value: 'network', label: 'Réseau' },
    { value: 'ethernet', label: 'Ethernet' },
    { value: 'bluetooth', label: 'Bluetooth' },
]
const alignments = ['left', 'center', 'right']

const form = reactive({
    name: '',
    brand: 'Epson',
    model: 'TM-T88VI',
    connection_type: 'ethernet',
    ip_address: '192.168.1.150',
    subnet_mask: '255.255.255.0',
    gateway: '192.168.1.1',
    port: 9100,
    usage: 'ticket_client',
    description: '',
    is_active: true,
    ticket_config: null,
    kitchen_config: null,
    advanced_config: null,
})

const modelsForBrand = computed(() => brandModels[form.brand] || [])
const storeName = computed(() => settingsStore.storeName || 'RIMAL RESTAURANT')
const storeAddress = computed(() => settingsStore.settings?.general?.store_address || 'Casablanca')
const storePhone = computed(() => settingsStore.settings?.general?.store_phone || '')
const storeIce = computed(() => settingsStore.settings?.general?.store_ice || '')
const previewDate = new Date().toLocaleString('fr-FR')

const filteredCategories = computed(() => {
    const q = categorySearch.value.trim().toLowerCase()
    if (!q) return categories.value
    return categories.value.filter((c) => c.name.toLowerCase().includes(q))
})

function connectionLabel(type) {
    return connectionTypes.find((c) => c.value === type)?.label || type
}

function isTicketFieldEnabled(key) {
    const field = form.ticket_config?.content?.find((f) => f.key === key)
    return field?.enabled !== false
}

function moveContentField(type, index, direction) {
    const config = type === 'ticket' ? form.ticket_config.content : form.kitchen_config.content
    const newIndex = index + direction
    if (newIndex < 0 || newIndex >= config.length) return
    const [item] = config.splice(index, 1)
    config.splice(newIndex, 0, item)
}

function toggleCategory(id) {
    const ids = form.kitchen_config.category_ids
    const idx = ids.indexOf(id)
    if (idx >= 0) ids.splice(idx, 1)
    else ids.push(id)
    selectAllCategories.value = ids.length === categories.value.length
}

function toggleAllCategories() {
    form.kitchen_config.category_ids = selectAllCategories.value
        ? categories.value.map((c) => c.id)
        : []
}

function testPrint(type) {
    alert(`Test d'impression ${type === 'ticket' ? 'ticket client' : 'cuisine'} — la connexion matérielle sera utilisée lors de l'impression réelle.`)
}

function goBack() {
    router.push({ name: 'settings.printers' })
}

async function loadDefaults() {
    const { data } = await printersApi.defaults()
    form.ticket_config = data.ticket_config
    form.kitchen_config = data.kitchen_config
    form.advanced_config = data.advanced_config
}

async function loadPrinter() {
    if (!isEditing.value) {
        await loadDefaults()
        return
    }
    loading.value = true
    try {
        const { data } = await printersApi.get(route.params.id)
        Object.assign(form, {
            name: data.name,
            brand: data.brand || '',
            model: data.model || '',
            connection_type: data.connection_type || 'usb',
            ip_address: data.ip_address || '',
            subnet_mask: data.subnet_mask || '',
            gateway: data.gateway || '',
            port: data.port || 9100,
            usage: data.usage || 'ticket_client',
            description: data.description || '',
            is_active: data.is_active !== false,
            ticket_config: data.ticket_config,
            kitchen_config: data.kitchen_config,
            advanced_config: data.advanced_config,
        })
        if (!form.kitchen_config.category_ids) {
            form.kitchen_config.category_ids = []
        }
    } catch (error) {
        alert('Imprimante introuvable.')
        goBack()
    } finally {
        loading.value = false
    }
}

async function save() {
    if (!form.name) return
    saving.value = true
    try {
        const payload = { ...form }
        if (isEditing.value) {
            await printersApi.update(route.params.id, payload)
        } else {
            await printersApi.create(payload)
        }
        goBack()
    } catch (error) {
        alert(error.response?.data?.message || 'Erreur lors de la sauvegarde.')
    } finally {
        saving.value = false
    }
}

onMounted(async () => {
    await settingsStore.fetchSettings?.()
    try {
        const { data } = await categoriesApi.list({ paginate: false })
        categories.value = Array.isArray(data) ? data : (data.data || [])
        if (!isEditing.value) {
            form.kitchen_config.category_ids = categories.value.map((c) => c.id)
            selectAllCategories.value = true
        }
    } catch {
        categories.value = []
    }
    await loadPrinter()
})
</script>
