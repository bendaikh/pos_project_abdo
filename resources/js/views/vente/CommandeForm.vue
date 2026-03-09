<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Création de commande</h1>
                <p class="text-gray-500">Commande client avec sélection visuelle des articles.</p>
            </div>
            <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100" @click="goBack">Annuler</button>
        </div>

        <form class="space-y-5" @submit.prevent="submitCommande">
            <section class="bg-white rounded-2xl border border-gray-200 p-4 md:p-5">
                <h2 class="text-sm font-semibold uppercase text-gray-500 tracking-wide mb-4">Zone utilisateur</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Nom</label>
                        <input :value="currentUser.name" type="text" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Poste</label>
                        <input :value="currentUser.role" type="text" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-2xl border border-gray-200 p-4 md:p-5 space-y-4">
                <h2 class="text-sm font-semibold uppercase text-gray-500 tracking-wide">Zone commande</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">N° cmd</label>
                        <input :value="previewOrderNumber" type="text" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Origine</label>
                        <input :value="formatOrigin(form.origin)" type="text" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Date retrait prévue</label>
                        <input v-model="form.pickup_date" type="date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <div class="border border-gray-200 rounded-xl p-3">
                    <div class="flex items-center gap-2 mb-3">
                        <button
                            type="button"
                            class="px-3 py-1.5 rounded-lg border text-sm"
                            :class="clientMode === 'existing' ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300 text-gray-700 hover:bg-gray-100'"
                            @click="setClientMode('existing')"
                        >
                            Client existant
                        </button>
                        <button
                            type="button"
                            class="px-3 py-1.5 rounded-lg border text-sm"
                            :class="clientMode === 'new' ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-gray-300 text-gray-700 hover:bg-gray-100'"
                            @click="setClientMode('new')"
                        >
                            Nouveau client
                        </button>
                    </div>

                    <div v-if="clientMode === 'existing'" class="relative">
                        <label class="block text-sm text-gray-600 mb-1">Rechercher client</label>
                        <div class="relative">
                            <input
                                v-model="customerSearch"
                                type="text"
                                placeholder="Nom / téléphone"
                                class="w-full px-3 py-2 pr-20 border border-gray-300 rounded-lg"
                                @focus="customerSearchOpen = true"
                                @blur="onCustomerSearchBlur"
                                @input="onCustomerSearchInput"
                                @keydown.esc="customerSearchOpen = false"
                            >
                            <button
                                v-if="selectedExistingCustomer"
                                type="button"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-medium text-red-600 hover:text-red-700"
                                @click="clearSelectedCustomer"
                            >
                                Retirer
                            </button>
                        </div>
                        <div v-if="shouldShowCustomerDropdown" class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow max-h-56 overflow-y-auto">
                            <button
                                v-for="customer in filteredCustomers"
                                :key="customer.id"
                                type="button"
                                class="w-full text-left px-3 py-2 hover:bg-gray-50"
                                @mousedown.prevent="selectCustomer(customer)"
                            >
                                <p class="text-sm font-medium text-gray-900">{{ customer.name }}</p>
                                <p class="text-xs text-gray-500">{{ customer.phone || '-' }} - {{ customer.activity || 'Sans activité' }}</p>
                            </button>
                        </div>
                    </div>

                    <div v-if="clientMode === 'existing'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mt-3">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nom client</label>
                            <input :value="selectedExistingCustomer?.name || ''" type="text" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Téléphone</label>
                            <input :value="selectedExistingCustomer?.phone || ''" type="text" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Activité</label>
                            <input :value="selectedExistingCustomer?.activity || ''" type="text" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Email</label>
                            <input :value="selectedExistingCustomer?.email || ''" type="text" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                        </div>
                    </div>

                    <div v-if="clientMode === 'new'" class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nom client *</label>
                            <input v-model="newCustomer.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Téléphone</label>
                            <input v-model="newCustomer.phone" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Activité</label>
                            <input v-model="newCustomer.activity" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Email</label>
                            <input v-model="newCustomer.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm text-gray-600 mb-1">Adresse</label>
                            <input v-model="newCustomer.address" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Détails commande</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Téléphone (commande)</label>
                            <input v-model="form.customer_phone" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Auto-rempli depuis le client">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Activité (commande)</label>
                            <input v-model="form.customer_activity" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Auto-rempli depuis le client">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Adresse</label>
                            <input v-model="form.delivery_address" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Auto-rempli depuis le client">
                        </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Note</label>
                    <textarea v-model="form.notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Zone libre"></textarea>
                </div>
            </section>

            <section class="bg-white rounded-2xl border border-gray-200 p-4 md:p-5 space-y-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <h2 class="text-sm font-semibold uppercase text-gray-500 tracking-wide">Zone article</h2>
                    <div class="flex-1 md:max-w-md">
                        <input
                            v-model="articleSearch"
                            type="text"
                            placeholder="Rechercher un article"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        >
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="category in articleCategories"
                        :key="category.id"
                        type="button"
                        class="px-3 py-1.5 rounded-full border text-sm"
                        :class="selectedCategoryId === category.id ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300 text-gray-700 hover:bg-gray-100'"
                        @click="selectedCategoryId = category.id"
                    >
                        {{ category.name }}
                    </button>
                </div>

                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-7 gap-2">
                    <button
                        v-for="article in filteredArticles"
                        :key="article.id"
                        type="button"
                        class="text-left rounded-lg border border-gray-200 bg-white hover:shadow-md hover:border-blue-300 transition overflow-hidden"
                        @click="addArticleByCard(article)"
                    >
                        <div class="aspect-square bg-gray-100 overflow-hidden flex items-center justify-center">
                            <img v-if="articleImage(article)" :src="articleImage(article)" :alt="article.name" class="w-full h-full object-cover">
                            <span v-else class="text-lg">📦</span>
                        </div>
                        <div class="p-1.5">
                            <p class="text-xs font-semibold text-gray-900 truncate">{{ article.name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ article.category?.name || 'Sans catégorie' }}</p>
                            <p class="text-xs font-bold text-blue-700 mt-0.5">{{ formatCurrency(article.sell_price || 0) }}</p>
                        </div>
                    </button>
                    <div v-if="filteredArticles.length === 0" class="col-span-full border border-dashed border-gray-300 rounded-xl p-6 text-center text-sm text-gray-500">
                        Aucun article trouvé.
                    </div>
                </div>

                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="w-full min-w-[780px]">
                        <thead class="text-xs uppercase text-gray-500 border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Article</th>
                                <th class="px-3 py-2 text-left">Catégorie</th>
                                <th class="px-3 py-2 text-right">Nombre</th>
                                <th class="px-3 py-2 text-right">Prix unitaire</th>
                                <th class="px-3 py-2 text-right">Total</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(line, index) in form.items" :key="line.key">
                                <td class="px-3 py-2 text-sm font-medium text-gray-900">{{ line.article_name }}</td>
                                <td class="px-3 py-2 text-sm text-gray-600">{{ line.category_name || '-' }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" class="w-7 h-7 border border-gray-300 rounded-lg" @click="line.quantity = Math.max(0.001, Number(line.quantity || 0) - 1)">-</button>
                                        <input v-model.number="line.quantity" type="number" min="0.001" step="0.001" class="w-20 text-right px-2 py-1 border border-gray-300 rounded-lg">
                                        <button type="button" class="w-7 h-7 border border-gray-300 rounded-lg" @click="line.quantity = Number(line.quantity || 0) + 1">+</button>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right text-sm">{{ formatCurrency(line.unit_price || 0) }}</td>
                                <td class="px-3 py-2 text-right text-sm font-semibold">{{ formatCurrency(lineTotal(line)) }}</td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" class="text-red-600 hover:text-red-700 text-sm" @click="removeLine(index)">Supprimer</button>
                                </td>
                            </tr>
                            <tr v-if="!form.items.length">
                                <td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500">Aucun article ajouté.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bg-white rounded-2xl border border-gray-200 p-4 md:p-5">
                <h2 class="text-sm font-semibold uppercase text-gray-500 tracking-wide mb-4">Zone ticket</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Montant total</label>
                        <input :value="formatCurrency(totalAmount)" type="text" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Avance</label>
                        <input v-model.number="form.advance_amount" type="number" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Reste à payer</label>
                        <input :value="formatCurrency(remainingAmount)" type="text" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                </div>
            </section>

            <div class="flex gap-3 justify-end">
                <button type="button" class="px-5 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-100" @click="goBack">Annuler</button>
                <button type="submit" :disabled="saving || !canSubmit" class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-40">
                    {{ saving ? 'Enregistrement...' : 'Confirmer la commande' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { articlesApi, commandesApi, customersApi, salesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'

const router = useRouter()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const saving = ref(false)
const articles = ref([])
const customers = ref([])
const customerSearch = ref('')
const customerSearchOpen = ref(false)
const clientMode = ref('existing')
const selectedExistingCustomer = ref(null)
const articleSearch = ref('')
const selectedCategoryId = ref('all')

const newCustomer = reactive({
    name: '',
    phone: '',
    activity: '',
    email: '',
    address: '',
})

const currentUser = computed(() => {
    try {
        const user = JSON.parse(localStorage.getItem('auth_user') || '{}')
        return {
            name: user.name || 'Utilisateur',
            role: user.role || 'Poste non défini',
        }
    } catch {
        return { name: 'Utilisateur', role: 'Poste non défini' }
    }
})

const form = reactive({
    customer_id: null,
    customer_phone: '',
    customer_activity: '',
    origin: 'menu_commande',
    pickup_date: '',
    delivery_address: '',
    notes: '',
    advance_amount: 0,
    items: [],
})

function normalizeCustomer(customer) {
    if (!customer) return null
    const fullName = `${customer.nom || ''} ${customer.prenom || ''}`.trim()
    return {
        ...customer,
        id: customer.id,
        name: customer.name || fullName || customer.raison_sociale || 'Client',
        phone: customer.phone || customer.telephone || customer.mobile || '',
        activity: customer.activity || customer.activite || '',
        email: customer.email || '',
        address: customer.address || customer.adresse || '',
    }
}

function applyCustomerToCommandeDetails(customer) {
    if (!customer) return
    form.customer_phone = customer.phone || customer.telephone || customer.mobile || ''
    form.customer_activity = customer.activity || customer.activite || ''
    form.delivery_address = customer.address || customer.adresse || ''
}

function newLine(article = null) {
    return {
        key: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
        article_id: article?.id || '',
        article_name: article?.name || '',
        category_name: article?.category?.name || article?.category_name || '-',
        quantity: 1,
        unit_price: Number(article?.sell_price || 0),
    }
}

const previewOrderNumber = computed(() => {
    const now = new Date()
    return `CMD-${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}-...`
})

const filteredCustomers = computed(() => {
    if (clientMode.value !== 'existing') return []
    const query = customerSearch.value.trim().toLowerCase()
    if (!query) {
        return customers.value.slice(0, 8)
    }
    return customers.value
        .filter((customer) =>
            customer.name?.toLowerCase().includes(query)
            || customer.phone?.toLowerCase().includes(query)
        )
        .slice(0, 8)
})
const shouldShowCustomerDropdown = computed(() => {
    if (clientMode.value !== 'existing' || !customerSearchOpen.value) return false
    if (!filteredCustomers.value.length) return false
    const selectedName = selectedExistingCustomer.value?.name?.trim().toLowerCase() || ''
    const query = customerSearch.value.trim().toLowerCase()
    return !(selectedExistingCustomer.value && selectedName && query === selectedName)
})

const articleCategories = computed(() => {
    const map = new Map()
    map.set('all', { id: 'all', name: 'Tous' })
    for (const article of articles.value) {
        if (article.category?.id) {
            map.set(article.category.id, { id: article.category.id, name: article.category.name })
        }
    }
    return Array.from(map.values())
})

const filteredArticles = computed(() => {
    const query = articleSearch.value.trim().toLowerCase()
    return articles.value.filter((article) => {
        const categoryOk = selectedCategoryId.value === 'all' || article.category?.id === selectedCategoryId.value
        const searchOk = !query || article.name?.toLowerCase().includes(query)
        return categoryOk && searchOk
    })
})

const totalAmount = computed(() => form.items.reduce((sum, line) => sum + lineTotal(line), 0))
const remainingAmount = computed(() => Math.max(0, totalAmount.value - Number(form.advance_amount || 0)))
const canSubmit = computed(() => {
    const hasArticles = form.items.some((line) => line.article_id && Number(line.quantity) > 0)
    const hasClientName = clientMode.value === 'existing'
        ? Boolean(form.customer_id)
        : Boolean(newCustomer.name.trim())
    return hasArticles && hasClientName
})

function lineTotal(line) {
    return Number(line.quantity || 0) * Number(line.unit_price || 0)
}

function formatOrigin(origin) {
    const map = { pos: 'POS', menu_commande: 'Menu commande', livraison: 'Livraison' }
    return map[origin] || 'POS'
}

function setClientMode(mode) {
    clientMode.value = mode
    if (mode === 'new') {
        selectedExistingCustomer.value = null
        form.customer_id = null
        customerSearch.value = ''
        form.customer_phone = newCustomer.phone
        form.customer_activity = newCustomer.activity
        form.delivery_address = newCustomer.address
        return
    }

    if (mode === 'existing' && selectedExistingCustomer.value) {
        applyCustomerToCommandeDetails(selectedExistingCustomer.value)
    }
}

function clearSelectedCustomer() {
    selectedExistingCustomer.value = null
    form.customer_id = null
    customerSearch.value = ''
    customerSearchOpen.value = false
    form.customer_phone = ''
    form.customer_activity = ''
    form.delivery_address = ''
}

function onCustomerSearchInput() {
    customerSearchOpen.value = true
    if (!selectedExistingCustomer.value) return
    const query = customerSearch.value.trim().toLowerCase()
    const selectedName = selectedExistingCustomer.value.name?.trim().toLowerCase() || ''
    if (query === selectedName) return
    selectedExistingCustomer.value = null
    form.customer_id = null
    form.customer_phone = ''
    form.customer_activity = ''
    form.delivery_address = ''
}

function onCustomerSearchBlur() {
    setTimeout(() => {
        customerSearchOpen.value = false
    }, 120)
}

function removeLine(index) {
    form.items.splice(index, 1)
}

function selectCustomer(customer) {
    const normalizedCustomer = normalizeCustomer(customer)
    selectedExistingCustomer.value = normalizedCustomer
    form.customer_id = normalizedCustomer.id
    applyCustomerToCommandeDetails(normalizedCustomer)
    customerSearch.value = normalizedCustomer.name
    customerSearchOpen.value = false
}

function articleImage(article) {
    return article.photo
        || article.photo_url
        || article.image
        || (Array.isArray(article.photos) && article.photos.length ? article.photos[0]?.url : null)
        || null
}

function addArticleByCard(article) {
    const line = form.items.find((item) => Number(item.article_id) === Number(article.id))
    if (line) {
        line.quantity = Number(line.quantity || 0) + 1
        return
    }
    form.items.push(newLine(article))
}

async function createCustomerIfNeeded() {
    if (clientMode.value !== 'new') return form.customer_id

    const payload = {
        name: newCustomer.name.trim(),
        phone: newCustomer.phone || null,
        activity: newCustomer.activity || null,
        email: newCustomer.email || null,
        address: newCustomer.address || null,
    }

    const { data } = await customersApi.create(payload)
    const created = normalizeCustomer(data)
    customers.value.unshift(created)
    form.customer_id = created.id
    customerSearch.value = created.name
    return created.id
}

async function fetchData() {
    const [articlesRes, customersRes] = await Promise.all([
        articlesApi.list({ paginate: false }),
        customersApi.list({ paginate: false, active: true }),
    ])

    articles.value = Array.isArray(articlesRes.data?.data) ? articlesRes.data.data : (articlesRes.data || [])
    const rawCustomers = Array.isArray(customersRes.data?.data) ? customersRes.data.data : (customersRes.data || [])
    customers.value = rawCustomers.map(normalizeCustomer)
}

watch(
    () => selectedExistingCustomer.value,
    (customer) => {
        if (clientMode.value === 'existing' && customer) {
            applyCustomerToCommandeDetails(customer)
        }
    }
)

async function submitCommande() {
    if (!canSubmit.value) return

    saving.value = true
    try {
        await createCustomerIfNeeded()

        const payload = {
            customer_id: form.customer_id || null,
            customer_activity: clientMode.value === 'new' ? (newCustomer.activity || form.customer_activity) : (form.customer_activity || null),
            origin: form.origin,
            pickup_date: form.pickup_date || null,
            delivery_address: (clientMode.value === 'new' ? newCustomer.address : form.delivery_address) || null,
            notes: form.notes || null,
            order_status: 'confirmee',
            delivery_mode: 'pickup',
            items: form.items
                .filter((line) => line.article_id && Number(line.quantity) > 0)
                .map((line) => ({
                    article_id: line.article_id,
                    quantity: Number(line.quantity),
                    unit_price: Number(line.unit_price || 0),
                })),
        }

        const response = await commandesApi.create(payload)
        const sale = response.data

        if (Number(form.advance_amount || 0) > 0) {
            await salesApi.addPayment(sale.id, {
                payment_type: 'cash',
                amount: Number(form.advance_amount),
                received_amount: Number(form.advance_amount),
                notes: 'Avance à la création de la commande',
            })
        }

        router.push({ name: 'commandes.detail', params: { id: sale.id } })
    } catch (error) {
        console.error('Erreur création commande:', error)
        alert(error.response?.data?.message || 'Impossible de créer la commande.')
    } finally {
        saving.value = false
    }
}

function goBack() {
    router.push({ name: 'commandes' })
}

onMounted(async () => {
    await settingsStore.fetchSettings()
    await fetchData()
})
</script>
