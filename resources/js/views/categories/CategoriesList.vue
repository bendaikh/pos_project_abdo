<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Famille Produit</h1>
                <p class="text-gray-500">Gérez les familles de vos produits</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouvelle famille
            </button>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
                v-for="category in categories" 
                :key="category.id"
                class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div 
                            class="w-12 h-12 rounded-xl flex items-center justify-center"
                            :style="{ backgroundColor: category.color + '20' }"
                        >
                            <span class="text-2xl">{{ getCategoryIcon(category.icon) }}</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ category.name }}</h3>
                            <p class="text-sm text-gray-500">{{ category.articles_count || 0 }} articles</p>
                        </div>
                    </div>
                    <div class="flex space-x-1">
                        <button @click="openForm(category)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                            <PencilIcon class="w-4 h-4" />
                        </button>
                        <button @click="confirmDelete(category)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50">
                            <TrashIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                <p v-if="category.description" class="mt-3 text-sm text-gray-500">{{ category.description }}</p>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="categories.length === 0" class="text-center py-12">
            <FolderIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900">Aucune famille</h3>
            <p class="text-gray-500">Commencez par créer une famille pour organiser vos produits.</p>
        </div>

        <!-- Category Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingCategory ? 'Modifier la famille' : 'Nouvelle famille' }}
                </h3>
                
                <form @submit.prevent="saveCategory" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input 
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea 
                            v-model="form.description"
                            rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Couleur</label>
                        <input 
                            v-model="form.color"
                            type="color"
                            class="w-full h-10 rounded-lg cursor-pointer"
                        >
                    </div>

                    <!-- Tabs for Icon/Image selection -->
                    <div class="border-b border-gray-200">
                        <div class="flex space-x-4">
                            <button
                                type="button"
                                @click="displayMode = 'icon'"
                                class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
                                :class="displayMode === 'icon' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            >
                                🎨 Icône
                            </button>
                            <button
                                type="button"
                                @click="displayMode = 'image'"
                                class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
                                :class="displayMode === 'image' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            >
                                🖼️ Image
                            </button>
                        </div>
                    </div>

                    <!-- Icon Section -->
                    <div v-if="displayMode === 'icon'" class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700">Sélectionner une icône</label>
                        <div class="grid grid-cols-6 gap-2">
                            <button
                                v-for="(emoji, key) in iconOptions"
                                :key="key"
                                type="button"
                                @click="form.icon = key; form.photo = null"
                                class="p-2 text-xl rounded-lg border-2 transition-colors"
                                :class="form.icon === key && !form.photo ? 'border-primary-500 bg-primary-100' : 'border-gray-200 hover:border-gray-300'"
                            >
                                {{ emoji }}
                            </button>
                        </div>
                    </div>

                    <!-- Image Section -->
                    <div v-if="displayMode === 'image'" class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700">Télécharger une image</label>
                        <div v-if="form.photo" class="mb-3">
                            <img :src="form.photo" alt="Category" class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                        </div>
                        <input 
                            ref="photoInput"
                            type="file"
                            accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                            @change="handlePhotoUpload"
                        >
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG ou GIF (max 5MB)</p>
                        <button 
                            v-if="form.photo"
                            type="button"
                            @click="form.photo = null; form.icon = null"
                            class="text-sm text-red-600 hover:text-red-700"
                        >
                            Supprimer l'image
                        </button>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="button" @click="showForm = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" :disabled="saving" class="flex-1 px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50">
                            {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer la famille</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer "{{ categoryToDelete?.name }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button @click="deleteCategory" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { categoriesApi } from '../../api'
import { PlusIcon, PencilIcon, TrashIcon, FolderIcon } from '@heroicons/vue/24/outline'

const categories = ref([])
const showForm = ref(false)
const showDeleteModal = ref(false)
const editingCategory = ref(null)
const categoryToDelete = ref(null)
const saving = ref(false)
const displayMode = ref('icon')
const photoInput = ref(null)

const iconOptions = {
    apple: '🍎',
    cup: '🥤',
    bread: '🥖',
    home: '🏠',
    cookie: '🍪',
    meat: '🥩',
    fish: '🐟',
    cheese: '🧀',
    milk: '🥛',
    egg: '🥚',
    vegetable: '🥬',
    candy: '🍬',
}

const form = reactive({
    name: '',
    description: '',
    color: '#06b6d4',
    icon: 'apple',
    photo: null,
})

function getCategoryIcon(icon) {
    return iconOptions[icon] || '📦'
}

function handlePhotoUpload(event) {
    const file = event.target.files?.[0]
    if (file) {
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Fichier trop volumineux (max 5MB)')
            return
        }

        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Veuillez sélectionner une image')
            return
        }

        // Convert to data URL
        const reader = new FileReader()
        reader.onload = (e) => {
            form.photo = e.target?.result
        }
        reader.readAsDataURL(file)
    }
}

function openForm(category = null) {
    editingCategory.value = category
    displayMode.value = 'icon'
    if (category) {
        form.name = category.name
        form.description = category.description || ''
        form.color = category.color || '#22c55e'
        form.icon = category.icon || 'apple'
        form.photo = category.photo || null
        // If photo exists, show image mode
        if (form.photo) {
            displayMode.value = 'image'
        }
    } else {
        form.name = ''
        form.description = ''
        form.color = '#22c55e'
        form.icon = 'apple'
        form.photo = null
    }
    showForm.value = true
}

function confirmDelete(category) {
    categoryToDelete.value = category
    showDeleteModal.value = true
}

async function saveCategory() {
    saving.value = true
    try {
        if (editingCategory.value) {
            const response = await categoriesApi.update(editingCategory.value.id, form)
            const index = categories.value.findIndex(c => c.id === editingCategory.value.id)
            if (index > -1) categories.value[index] = response.data
        } else {
            const response = await categoriesApi.create(form)
            categories.value.push(response.data)
        }
        showForm.value = false
    } catch (error) {
        console.error('Failed to save category:', error)
        alert('Erreur lors de l\'enregistrement')
    } finally {
        saving.value = false
    }
}

async function deleteCategory() {
    try {
        await categoriesApi.delete(categoryToDelete.value.id)
        categories.value = categories.value.filter(c => c.id !== categoryToDelete.value.id)
        showDeleteModal.value = false
    } catch (error) {
        console.error('Failed to delete category:', error)
        alert('Erreur lors de la suppression')
    }
}

async function fetchCategories() {
    try {
        const response = await categoriesApi.list({ with_count: true })
        categories.value = response.data
    } catch (error) {
        console.error('Failed to fetch categories:', error)
    }
}

onMounted(fetchCategories)
</script>
