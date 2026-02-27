<template>
    <div class="space-y-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des pertes</h1>
                <p class="text-gray-500">Déclarez les pertes de stock, contrôlez l'impact financier et suivez l'historique.</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm w-full md:w-auto">
                <p class="text-xs uppercase text-gray-500 tracking-wide">Référence perte</p>
                <div class="mt-1 flex items-center gap-3">
                    <p class="text-lg font-semibold text-gray-900">{{ lossReference || '---' }}</p>
                    <button
                        type="button"
                        class="p-2 rounded-lg border border-gray-200 text-gray-500 hover:text-primary-600 hover:border-primary-200"
                        @click="fetchReference"
                        :disabled="loadingReference"
                    >
                        <ArrowPathIcon class="w-5 h-5" :class="{ 'animate-spin': loadingReference }" />
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Informations principales</h2>
                            <p class="text-sm text-gray-500">Renseignez les informations de la déclaration de perte.</p>
                        </div>
                        <div class="flex items-center text-xs text-gray-500 gap-1">
                            <InformationCircleIcon class="w-4 h-4" />
                            Champs obligatoires marqués d'une *
                        </div>
                    </div>
                    <div class="space-y-5">
                        <div class="rounded-xl border border-gray-100 p-4 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Sous-section 1 • Chronologie & site</p>
                                <span class="text-xs text-gray-500">Pré-rempli mais ajustable</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                                    <input
                                        v-model="declarationForm.loss_date"
                                        type="date"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                </div>
                                <div v-if="storeSelectionEnabled">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Magasin</label>
                                    <select
                                        v-model="declarationForm.store_id"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                        <option value="">Sélectionner</option>
                                        <option v-for="store in storeOptions" :key="store.id" :value="store.id">
                                            {{ store.name }}
                                        </option>
                                    </select>
                                </div>
                                <div v-else class="md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Magasin</label>
                                    <div class="px-4 py-2 border border-dashed border-gray-300 rounded-lg text-gray-600 text-sm bg-white">
                                        {{ storeOptions[0]?.name || 'Non défini' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Sous-section 2 • Responsable</p>
                                <span class="text-xs text-red-500" v-if="!hasResponsible">Requis</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsable *</label>
                                    <select
                                        v-model="responsibleSelector"
                                        @change="handleResponsibleChange"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                        <option value="">Sélectionner un responsable</option>
                                        <option
                                            v-for="employee in employees"
                                            :key="employee.id"
                                            :value="String(employee.id)"
                                        >
                                            {{ employee.full_name || employee.name }}
                                        </option>
                                        <option value="manual">Autre (saisie libre)</option>
                                    </select>
                                </div>
                                <div v-if="manualResponsible">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du responsable</label>
                                    <input
                                        v-model="declarationForm.responsible_name"
                                        type="text"
                                        placeholder="Nom et prénom"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                </div>
                            </div>
                            <p v-if="!hasResponsible" class="text-xs text-red-500 mt-2">Responsable requis.</p>
                        </div>

                        <div class="rounded-xl border border-gray-100 p-4">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Sous-section 3 • Notes & contexte</p>
                            <textarea
                                v-model="declarationForm.notes"
                                rows="3"
                                placeholder="Motif, observations..."
                                class="w-full mt-3 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                            ></textarea>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Sélection des articles</h2>
                            <p class="text-sm text-gray-500">Procédez étape par étape pour ajouter les lignes de perte.</p>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <ExclamationTriangleIcon class="w-4 h-4 text-amber-500" />
                            La quantité perdue ne peut pas dépasser le stock disponible.
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="rounded-xl border border-gray-200 p-4">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Étape 1 • Recherche & filtres</p>
                                <span class="text-xs text-gray-400">Nom, SKU ou catégorie</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
                                <div class="md:col-span-2 relative">
                                    <MagnifyingGlassIcon class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                                    <input
                                        v-model="articleSearch"
                                        type="text"
                                        placeholder="Rechercher un article par nom ou SKU"
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                </div>
                                <div>
                                    <select
                                        v-model="articleCategory"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    >
                                        <option value="">Toutes les catégories</option>
                                        <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-dashed border-primary-200 p-4 bg-primary-50/40">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-semibold text-primary-700 uppercase tracking-wide">Étape 2 • Suggestions rapides</p>
                                <span class="text-xs text-primary-600">{{ articleSuggestions.length }} proposition(s)</span>
                            </div>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <div
                                    v-for="article in articleSuggestions"
                                    :key="article.id"
                                    class="flex items-center gap-3 bg-white rounded-lg border border-gray-200 px-4 py-2 shadow-sm"
                                >
                                    <img
                                        :src="articleImage(article)"
                                        alt="Illustration article"
                                        class="w-14 h-14 rounded-lg object-cover border border-gray-200"
                                    >
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate">{{ article.name }}</p>
                                        <p class="text-xs text-gray-500">Stock: {{ article.stock_quantity ?? 0 }} {{ article.unit || '' }}</p>
                                    </div>
                                    <button
                                        type="button"
                                        class="px-3 py-1 text-sm font-medium rounded-lg"
                                        :class="articleAlreadySelected(article.id) ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-primary-500 text-gray-900 hover:bg-primary-600'"
                                        :disabled="articleAlreadySelected(article.id)"
                                        @click="addArticle(article)"
                                    >
                                        Ajouter
                                    </button>
                                </div>
                                <div v-if="articleSuggestions.length === 0" class="col-span-full text-sm text-gray-500 text-center py-4">
                                    Aucun article ne correspond aux critères. Ajustez la recherche.
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Étape 3 • Articles sélectionnés</p>
                                <span class="text-xs text-gray-500">{{ selectedLines.length }} ligne(s)</span>
                            </div>
                            <div class="hidden md:block overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Article</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock dispo.</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité perdue</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Restant (preview)</th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100" v-if="selectedLines.length">
                                        <tr v-for="line in selectedLines" :key="line.article.id" class="hover:bg-gray-50">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    <img
                                                        :src="articleImage(line.article)"
                                                        alt="Photo article"
                                                        class="w-12 h-12 rounded-lg object-cover border border-gray-200"
                                                    >
                                                    <div>
                                                        <p class="font-medium text-gray-900">{{ line.article.name }}</p>
                                                        <p class="text-xs text-gray-500">{{ line.article.category?.name || 'Sans catégorie' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ line.article.stock_quantity ?? 0 }} {{ line.article.unit || '' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        type="button"
                                                        class="p-1 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100"
                                                        @click="decreaseQuantity(line)"
                                                        :disabled="line.quantity <= 1"
                                                    >
                                                        <MinusIcon class="w-4 h-4" />
                                                    </button>
                                                    <input
                                                        v-model.number="line.quantity"
                                                        @change="normalizeQuantity(line)"
                                                        type="number"
                                                        min="1"
                                                        :max="line.article.stock_quantity || undefined"
                                                        class="w-20 text-center px-2 py-1 border border-gray-300 rounded-lg"
                                                    >
                                                    <button
                                                        type="button"
                                                        class="p-1 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100"
                                                        @click="increaseQuantity(line)"
                                                        :disabled="line.article.stock_quantity !== null && line.article.stock_quantity !== undefined && line.quantity >= line.article.stock_quantity"
                                                    >
                                                        <PlusIcon class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <select
                                                    v-model="line.loss_type"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                                                >
                                                    <option v-for="lt in lossTypes" :key="lt.value" :value="lt.value">{{ lt.label }}</option>
                                                </select>
                                            </td>
                                            <td class="px-4 py-3 text-sm" :class="previewRemaining(line) < 0 ? 'text-red-600' : 'text-gray-700'">
                                                {{ Math.max(previewRemaining(line), 0) }} {{ line.article.unit || '' }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center text-sm text-red-500 hover:text-red-600"
                                                    @click="removeLine(line.article.id)"
                                                >
                                                    <TrashIcon class="w-4 h-4 mr-1" />
                                                    Retirer
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                                Aucun article sélectionné. Utilisez la recherche ci-dessus pour ajouter des articles à la perte.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="md:hidden divide-y divide-gray-100">
                                <div v-if="!selectedLines.length" class="px-4 py-6 text-center text-sm text-gray-500">
                                    Aucun article sélectionné. Utilisez la recherche ci-dessus pour ajouter des articles à la perte.
                                </div>
                                <div v-for="line in selectedLines" :key="`mobile-${line.article.id}`" class="px-4 py-4 space-y-3">
                                    <div class="flex items-center gap-3">
                                        <img
                                            :src="articleImage(line.article)"
                                            alt="Photo article"
                                            class="w-14 h-14 rounded-lg object-cover border border-gray-200"
                                        >
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 truncate">{{ line.article.name }}</p>
                                            <p class="text-xs text-gray-500">Stock: {{ line.article.stock_quantity ?? 0 }} {{ line.article.unit || '' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500">Quantité perdue</span>
                                        <div class="flex items-center gap-2">
                                            <button
                                                type="button"
                                                class="p-1 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100"
                                                @click="decreaseQuantity(line)"
                                                :disabled="line.quantity <= 1"
                                            >
                                                <MinusIcon class="w-4 h-4" />
                                            </button>
                                            <input
                                                v-model.number="line.quantity"
                                                @change="normalizeQuantity(line)"
                                                type="number"
                                                min="1"
                                                :max="line.article.stock_quantity || undefined"
                                                class="w-20 text-center px-2 py-1 border border-gray-300 rounded-lg"
                                            >
                                            <button
                                                type="button"
                                                class="p-1 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100"
                                                @click="increaseQuantity(line)"
                                                :disabled="line.article.stock_quantity !== null && line.article.stock_quantity !== undefined && line.quantity >= line.article.stock_quantity"
                                            >
                                                <PlusIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500">Type</span>
                                        <select
                                            v-model="line.loss_type"
                                            class="w-40 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                                        >
                                            <option v-for="lt in lossTypes" :key="lt.value" :value="lt.value">{{ lt.label }}</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500">Restant (preview)</span>
                                        <span :class="previewRemaining(line) < 0 ? 'text-red-600 text-sm' : 'text-gray-700 text-sm'">
                                            {{ Math.max(previewRemaining(line), 0) }} {{ line.article.unit || '' }}
                                        </span>
                                    </div>
                                    <button
                                        type="button"
                                        class="inline-flex items-center text-sm text-red-500 hover:text-red-600"
                                        @click="removeLine(line.article.id)"
                                    >
                                        <TrashIcon class="w-4 h-4 mr-1" />
                                        Retirer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900">Résumé automatique</h2>
                    <div class="grid grid-cols-1 gap-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Total quantité perdue</p>
                                <p class="text-2xl font-bold text-gray-900">{{ summary.totalQuantity }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Impact financier estimé</p>
                                <p class="text-2xl font-bold text-secondary-600">{{ formatCurrency(summary.totalCost) }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">Stock restant après validation</p>
                            <ul class="space-y-1 max-h-40 overflow-y-auto">
                                <li
                                    v-for="line in selectedLines"
                                    :key="`preview-${line.article.id}`"
                                    class="flex items-center justify-between text-sm"
                                    :class="previewRemaining(line) < 0 ? 'text-red-600' : 'text-gray-700'"
                                >
                                    <span class="truncate mr-2">{{ line.article.name }}</span>
                                    <span>{{ Math.max(previewRemaining(line), 0) }} {{ line.article.unit || '' }}</span>
                                </li>
                                <li v-if="selectedLines.length === 0" class="text-sm text-gray-500">Ajoutez des articles pour voir la projection.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <button
                            type="button"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                            @click="resetDeclaration"
                            :disabled="selectedLines.length === 0 && !declarationForm.notes && !declarationForm.responsible_name && !declarationForm.responsible_employee_id"
                        >
                            Annuler
                        </button>
                        <button
                            type="button"
                            class="w-full px-4 py-2 rounded-lg text-gray-900 font-semibold"
                            :class="canSubmit ? 'bg-primary-500 hover:bg-primary-600' : 'bg-gray-200 text-gray-500 cursor-not-allowed'"
                            :disabled="!canSubmit || !canValidate || submitting"
                            @click="showConfirmModal = true"
                        >
                            <ClipboardDocumentCheckIcon class="w-5 h-5 inline mr-2" />
                            Valider la perte
                        </button>
                        <p v-if="!canValidate" class="text-xs text-red-500 text-center">Seuls les utilisateurs autorisés (manager ou plus) peuvent valider.</p>
                    </div>
                </section>

                <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-3">
                    <h2 class="text-lg font-semibold text-gray-900">Bonnes pratiques</h2>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li>• Utilisez les raccourcis clavier ↑ ↓ pour ajuster rapidement les quantités.</li>
                        <li>• Vérifiez les stocks critiques avant validation pour éviter les ruptures.</li>
                        <li>• Documentez la cause exacte pour faciliter l'analyse de rentabilité.</li>
                    </ul>
                </section>
            </aside>
        </div>

        <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-900/60" @click="showConfirmModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10 space-y-4">
                <div class="flex items-center gap-3">
                    <ShieldExclamationIcon class="w-10 h-10 text-amber-500" />
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirmer la validation</h3>
                        <p class="text-sm text-gray-600">Cette action déduira immédiatement les quantités du stock. Continuer ?</p>
                    </div>
                </div>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• {{ summary.totalQuantity }} unités seront déduites.</li>
                    <li>• Impact financier estimé: {{ formatCurrency(summary.totalCost) }}.</li>
                    <li>• Opération enregistrée avec la référence {{ lossReference || 'en cours' }}.</li>
                </ul>
                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="button"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                        @click="showConfirmModal = false"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        class="flex-1 px-4 py-2 bg-primary-500 text-gray-900 rounded-lg font-semibold hover:bg-primary-600"
                        @click="submitLoss"
                        :disabled="submitting"
                    >
                        Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { articlesApi, categoriesApi, employeesApi, lossesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { useAuthStore } from '../../stores/auth'
import {
    ArrowPathIcon,
    ClipboardDocumentCheckIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    MinusIcon,
    PlusIcon,
    TrashIcon,
    MagnifyingGlassIcon,
    ShieldExclamationIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const authStore = useAuthStore()

const lossReference = ref('')
const loadingReference = ref(false)
const submitting = ref(false)
const showConfirmModal = ref(false)
const manualResponsible = ref(false)
const responsibleSelector = ref('')
const selectedLines = ref([])
const articleSearch = ref('')
const articleCategory = ref('')

const employees = ref([])
const articles = ref([])
const categories = ref([])
const placeholderImage = 'https://placehold.co/96x96?text=IMG'

const declarationForm = reactive({
    loss_date: new Date().toISOString().slice(0, 10),
    responsible_employee_id: '',
    responsible_name: '',
    store_id: '',
    notes: ''
})

const lossTypes = [
    { value: 'loss', label: 'Perte' },
    { value: 'breakage', label: 'Casse' },
    { value: 'expiration', label: 'Péremption' },
    { value: 'theft', label: 'Vol' }
]

const formatCurrency = (value) => settingsStore.formatCurrency(Number(value || 0))

const storeOptions = computed(() => {
    const general = settingsStore.settings.general || {}
    if (Array.isArray(general.stores)) {
        return general.stores
    }
    if (general.store_name) {
        return [{ id: general.default_store_id || 1, name: general.store_name }]
    }
    return []
})

const multiStoreEnabled = computed(() => {
    const general = settingsStore.settings.general || {}
    const flag = general.multi_store_enabled
    const enabled = flag === true || flag === 1 || flag === '1'
    return enabled && storeOptions.value.length > 0
})

const storeSelectionEnabled = computed(() => storeOptions.value.length > 0)

const articleSuggestions = computed(() => {
    let list = articles.value
    if (articleCategory.value) {
        const categoryId = Number(articleCategory.value)
        list = list.filter(article => article.category_id === categoryId)
    }
    if (articleSearch.value) {
        const query = articleSearch.value.toLowerCase()
        list = list.filter(article => {
            return (
                article.name?.toLowerCase().includes(query) ||
                article.sku?.toLowerCase().includes(query)
            )
        })
    }
    const selectedIds = selectedLines.value.map(line => line.article.id)
    list = list.filter(article => !selectedIds.includes(article.id))
    return list.slice(0, 8)
})

const hasResponsible = computed(() => {
    if (manualResponsible.value) {
        return Boolean(declarationForm.responsible_name?.trim())
    }
    return Boolean(declarationForm.responsible_employee_id)
})

function resolveUnitCost(article) {
    if (!article) {
        return 0
    }
    const costFields = ['cost_basis', 'buy_price', 'purchase_price', 'cost_price', 'average_cost']
    for (const field of costFields) {
        const value = article[field]
        if (value !== undefined && value !== null && value !== '') {
            return Number(value)
        }
    }
    return 0
}

const summary = computed(() => {
    const totalQuantity = selectedLines.value.reduce((sum, line) => sum + Number(line.quantity || 0), 0)
    const totalCost = selectedLines.value.reduce((sum, line) => {
        const unitCost = resolveUnitCost(line.article)
        return sum + Number(line.quantity || 0) * unitCost
    }, 0)
    return { totalQuantity, totalCost }
})

const canValidate = computed(() => {
    const role = authStore.user?.role
    return ['superadmin', 'admin', 'manager'].includes(role)
})

const canSubmit = computed(() => {
    return (
        hasResponsible.value &&
        selectedLines.value.length > 0 &&
        summary.value.totalQuantity > 0 &&
        !submitting.value
    )
})

function articleImage(article) {
    if (!article) {
        return placeholderImage
    }
    if (article.photo) {
        return article.photo
    }
    if (article.photo_url) {
        return article.photo_url
    }
    if (Array.isArray(article.photos) && article.photos.length) {
        return article.photos[0].photo_url || article.photos[0].url || placeholderImage
    }
    if (article.image_url) {
        return article.image_url
    }
    return placeholderImage
}

function articleAlreadySelected(articleId) {
    return selectedLines.value.some(line => line.article.id === articleId)
}

function addArticle(article) {
    if (!article || articleAlreadySelected(article.id)) {
        return
    }
    selectedLines.value.push({
        article,
        quantity: 1,
        loss_type: 'loss'
    })
}

function removeLine(articleId) {
    selectedLines.value = selectedLines.value.filter(line => line.article.id !== articleId)
}

function increaseQuantity(line) {
    if (line.article.stock_quantity == null) {
        line.quantity += 1
        return
    }
    if (line.quantity < line.article.stock_quantity) {
        line.quantity += 1
    }
}

function decreaseQuantity(line) {
    if (line.quantity > 1) {
        line.quantity -= 1
    }
}

function normalizeQuantity(line) {
    if (!line.quantity || line.quantity < 1) {
        line.quantity = 1
    }
    const max = line.article.stock_quantity
    if (max != null && line.quantity > max) {
        line.quantity = max
    }
}

function previewRemaining(line) {
    const stock = Number(line.article.stock_quantity ?? 0)
    return stock - Number(line.quantity || 0)
}

function resetDeclaration() {
    declarationForm.loss_date = new Date().toISOString().slice(0, 10)
    declarationForm.responsible_employee_id = ''
    declarationForm.responsible_name = ''
    declarationForm.store_id = ''
    declarationForm.notes = ''
    responsibleSelector.value = ''
    manualResponsible.value = false
    selectedLines.value = []
}

function handleResponsibleChange() {
    if (responsibleSelector.value === 'manual') {
        manualResponsible.value = true
        declarationForm.responsible_employee_id = ''
    } else {
        manualResponsible.value = false
        declarationForm.responsible_name = ''
        declarationForm.responsible_employee_id = responsibleSelector.value ? Number(responsibleSelector.value) : ''
    }
}

async function fetchReference() {
    loadingReference.value = true
    try {
        const response = await lossesApi.reference()
        lossReference.value = response.data.reference
    } catch (error) {
        console.error('Failed to fetch loss reference', error)
    } finally {
        loadingReference.value = false
    }
}

async function fetchEmployees() {
    try {
        const response = await employeesApi.list({ per_page: 200 })
        employees.value = Array.isArray(response.data) ? response.data : (response.data.data || [])
    } catch (error) {
        console.error('Failed to load employees', error)
    }
}

async function fetchCategories() {
    try {
        const response = await categoriesApi.list({ per_page: 200 })
        categories.value = Array.isArray(response.data) ? response.data : (response.data.data || [])
    } catch (error) {
        console.error('Failed to load categories', error)
    }
}

async function fetchArticles() {
    try {
        const response = await articlesApi.list({ manage_stock: true, per_page: 500 })
        articles.value = Array.isArray(response.data) ? response.data : (response.data.data || [])
    } catch (error) {
        console.error('Failed to load articles', error)
    }
}

async function submitLoss() {
    if (!canSubmit.value || !canValidate.value) {
        return
    }
    submitting.value = true
    try {
        const payload = {
            loss_date: declarationForm.loss_date,
            responsible_employee_id: declarationForm.responsible_employee_id || null,
            responsible_name: manualResponsible.value ? declarationForm.responsible_name : null,
            store_id: storeSelectionEnabled.value ? declarationForm.store_id || null : null,
            notes: declarationForm.notes || null,
            items: selectedLines.value.map(line => ({
                article_id: line.article.id,
                quantity: Number(line.quantity || 0),
                loss_type: line.loss_type,
            }))
        }
        await lossesApi.create(payload)
        showConfirmModal.value = false
        resetDeclaration()
        await Promise.all([fetchReference(), fetchArticles()])
        window.alert('Perte enregistrée avec succès')
    } catch (error) {
        console.error('Failed to submit loss', error)
        window.alert(error.response?.data?.message || 'Erreur lors de la validation de la perte')
    } finally {
        submitting.value = false
    }
}

onMounted(async () => {
    await settingsStore.fetchSettings()
    await Promise.all([
        fetchReference(),
        fetchEmployees(),
        fetchCategories(),
        fetchArticles()
    ])
})
</script>
