<template>
    <teleport to="body">
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" @click="handleClose">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
                <!-- Header -->
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ rule ? 'Modifier' : 'Ajouter' }} règle automatique
                    </h2>
                    <button @click="handleClose" class="p-1 hover:bg-gray-100 rounded-lg">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom de la règle *</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            required
                            placeholder="Ex: Commander farine"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea 
                            v-model="form.description" 
                            rows="2"
                            placeholder="Description de la règle..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        ></textarea>
                    </div>

                    <!-- Condition Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type de condition *</label>
                        <select 
                            v-model="form.condition_type" 
                            required
                            @change="resetConditionData"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            <option value="stock_level">Stock article < minimum</option>
                            <option value="sales_threshold">Ventes article > X</option>
                            <option value="production_event">Production terminée</option>
                            <option value="time_based">Basé sur l'heure</option>
                        </select>
                    </div>

                    <!-- Condition Details (Stock Level) -->
                    <div v-if="form.condition_type === 'stock_level'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Article *</label>
                            <select 
                                v-model="form.condition_data.article_id" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            >
                                <option value="">Sélectionner un article</option>
                                <option v-for="article in articles" :key="article.id" :value="article.id">
                                    {{ article.name }} (Stock: {{ article.stock }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Stock minimum *</label>
                            <input 
                                v-model.number="form.condition_data.minimum_stock" 
                                type="number" 
                                required
                                min="0"
                                placeholder="Ex: 20"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            />
                        </div>
                    </div>

                    <!-- Condition Details (Sales Threshold) -->
                    <div v-if="form.condition_type === 'sales_threshold'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Article *</label>
                            <select 
                                v-model="form.condition_data.article_id" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            >
                                <option value="">Sélectionner un article</option>
                                <option v-for="article in articles" :key="article.id" :value="article.id">
                                    {{ article.name }}
                                </option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Seuil de vente *</label>
                                <input 
                                    v-model.number="form.condition_data.sales_threshold" 
                                    type="number" 
                                    required
                                    min="0"
                                    placeholder="Ex: 100"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Période</label>
                                <select 
                                    v-model="form.condition_data.period" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                >
                                    <option value="today">Aujourd'hui</option>
                                    <option value="week">Cette semaine</option>
                                    <option value="month">Ce mois</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Condition Details (Time Based) -->
                    <div v-if="form.condition_type === 'time_based'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quand? *</label>
                            <select 
                                v-model="form.condition_data.trigger" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            >
                                <option value="end_of_day">Fin de journée (17h)</option>
                                <option value="start_of_day">Début de journée (9h)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Task Details -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tâche créée</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Objet de la tâche *</label>
                                <input 
                                    v-model="form.task_subject" 
                                    type="text" 
                                    required
                                    placeholder="Ex: Commander farine"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea 
                                    v-model="form.task_description" 
                                    rows="3"
                                    placeholder="Description détaillée..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                ></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Priorité</label>
                                    <select 
                                        v-model="form.task_priority" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                    >
                                        <option value="faible">Faible</option>
                                        <option value="moyenne">Moyenne</option>
                                        <option value="urgente">Urgente</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Assigné à *</label>
                                    <select 
                                        v-model="form.assigned_to_employee_id" 
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                    >
                                        <option value="">Sélectionner un employé</option>
                                        <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                                            {{ employee.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recurrence -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Exécution</h3>

                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input 
                                    v-model="form.is_repeatable" 
                                    type="checkbox" 
                                    id="repeatable"
                                    class="w-4 h-4 text-green-600"
                                />
                                <label for="repeatable" class="ml-2 text-sm font-medium text-gray-700">
                                    Répétable automatiquement
                                </label>
                            </div>

                            <div v-if="form.is_repeatable">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fréquence</label>
                                <select 
                                    v-model="form.repeat_interval" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                >
                                    <option value="daily">Quotidienne</option>
                                    <option value="weekly">Hebdomadaire</option>
                                    <option value="monthly">Mensuelle</option>
                                </select>
                            </div>

                            <div class="flex items-center">
                                <input 
                                    v-model="form.is_active" 
                                    type="checkbox" 
                                    id="active"
                                    class="w-4 h-4 text-green-600"
                                />
                                <label for="active" class="ml-2 text-sm font-medium text-gray-700">
                                    Activer cette règle
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 justify-end border-t border-gray-200 pt-6">
                        <button @click="handleClose" type="button" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700">
                            {{ rule ? 'Mettre à jour' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </teleport>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import api from '../../../api'

const props = defineProps({
    rule: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['close', 'saved'])

const articles = ref([])
const employees = ref([])
const loading = ref(false)

const form = ref({
    name: '',
    description: '',
    is_active: true,
    condition_type: 'stock_level',
    condition_data: {
        article_id: '',
        minimum_stock: null,
        sales_threshold: null,
        period: 'today',
        trigger: 'end_of_day'
    },
    task_subject: '',
    task_description: '',
    task_priority: 'moyenne',
    assigned_to_employee_id: '',
    assigned_to_role: '',
    is_repeatable: false,
    repeat_interval: 'daily',
})

watch(() => props.rule, (newRule) => {
    if (newRule) {
        form.value = { ...newRule }
    } else {
        resetForm()
    }
}, { immediate: true })

function resetForm() {
    form.value = {
        name: '',
        description: '',
        is_active: true,
        condition_type: 'stock_level',
        condition_data: {
            article_id: '',
            minimum_stock: null,
            sales_threshold: null,
            period: 'today',
            trigger: 'end_of_day'
        },
        task_subject: '',
        task_description: '',
        task_priority: 'moyenne',
        assigned_to_employee_id: '',
        assigned_to_role: '',
        is_repeatable: false,
        repeat_interval: 'daily',
    }
}

function resetConditionData() {
    form.value.condition_data = {
        article_id: '',
        minimum_stock: null,
        sales_threshold: null,
        period: 'today',
        trigger: 'end_of_day'
    }
}

async function loadArticles() {
    try {
        const response = await api.get('/automation-rules/articles')
        articles.value = response.data
    } catch (error) {
        console.error('Error loading articles:', error)
    }
}

async function loadEmployees() {
    try {
        const response = await api.get('/automation-rules/employees')
        employees.value = response.data
    } catch (error) {
        console.error('Error loading employees:', error)
    }
}

async function handleSubmit() {
    try {
        loading.value = true
        const url = props.rule ? `/automation-rules/${props.rule.id}` : '/automation-rules'
        const method = props.rule ? 'put' : 'post'

        await api[method](url, form.value)
        emit('saved')
    } catch (error) {
        console.error('Error saving rule:', error)
        alert('Erreur lors de la sauvegarde')
    } finally {
        loading.value = false
    }
}

function handleClose() {
    emit('close')
}

onMounted(() => {
    loadArticles()
    loadEmployees()
})
</script>
