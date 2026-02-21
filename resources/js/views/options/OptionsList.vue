<template>
    <div class="min-h-screen bg-gray-50 p-6">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">⚙️ Options & Variantes</h1>
                <p class="text-gray-500 mt-2">Supplément • Couleur • Taille • Boisson • Extra</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Create Option Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-6">Créer Option</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom *</label>
                                <input 
                                    v-model="form.optionName"
                                    type="text"
                                    placeholder="Supplément"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium"
                                    @keyup.enter="focusNextField('variantName')"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">1ère Valeur *</label>
                                <input 
                                    ref="variantNameInput"
                                    v-model="form.variantName"
                                    type="text"
                                    placeholder="Sauce piquante"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium"
                                    @keyup.enter="focusNextField('variantPrice')"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Prix (DHS) *</label>
                                <input 
                                    ref="variantPriceInput"
                                    v-model.number="form.variantPrice"
                                    type="number"
                                    step="0.01"
                                    placeholder="30.00"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-bold text-lg"
                                    @keyup.enter="createOption"
                                >
                            </div>

                            <button 
                                @click="createOption"
                                :disabled="isCreating || !form.optionName.trim() || !form.variantName.trim() || form.variantPrice === null"
                                class="w-full mt-6 px-6 py-3 bg-primary-500 text-gray-900 font-bold rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all text-lg"
                            >
                                {{ isCreating ? 'Création...' : '✓ Créer' }}
                            </button>

                            <p class="text-xs text-gray-500 text-center mt-4 leading-relaxed">
                                💡 Créez l'option avec sa première valeur. Ajoutez d'autres valeurs ci-contre.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right: Options List -->
                <div class="lg:col-span-2 space-y-4">
                    <div v-if="options.length === 0" class="bg-white rounded-2xl p-12 text-center shadow-sm border border-dashed border-gray-300">
                        <div class="text-5xl mb-4">⚙️</div>
                        <p class="text-gray-600 font-medium">Aucune option</p>
                        <p class="text-gray-400 text-sm mt-1">Commencez par créer une option</p>
                    </div>

                    <div v-for="option in options" :key="option.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Option Header -->
                        <div class="bg-gradient-to-r from-primary-50 to-cyan-50 px-6 py-4 flex items-center justify-between border-b border-gray-100">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ option.name }}</h3>
                                <p class="text-sm text-gray-500 mt-1">{{ option.variants.length }} valeur(s)</p>
                            </div>
                            <button 
                                @click="confirmDeleteOption(option)"
                                class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="Supprimer"
                            >
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Variants List -->
                        <div class="divide-y divide-gray-100">
                            <div 
                                v-for="variant in option.variants" 
                                :key="variant.id"
                                class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors group"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 truncate">{{ variant.name }}</p>
                                </div>
                                <div class="flex items-center gap-4 ml-4">
                                    <div class="text-right min-w-[80px]">
                                        <p class="text-primary-600 font-bold text-lg">{{ formatPrice(variant.price_impact) }}</p>
                                        <p class="text-xs text-gray-500">DHS</p>
                                    </div>
                                    <button 
                                        @click="confirmDeleteVariant(option.id, variant)"
                                        class="p-1.5 text-gray-300 group-hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors opacity-0 group-hover:opacity-100"
                                        title="Supprimer"
                                    >
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Add Variant Form -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                            <div class="flex gap-3 items-end">
                                <div class="flex-1 min-w-0">
                                    <label class="block text-xs font-semibold text-gray-600 mb-2 uppercase">Nouvelle valeur</label>
                                    <input 
                                        v-model="variantForms[option.id].name"
                                        type="text"
                                        placeholder="Ex: Fromage"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm font-medium"
                                        @keyup.enter="addVariant(option.id)"
                                    >
                                </div>
                                <div class="w-24">
                                    <label class="block text-xs font-semibold text-gray-600 mb-2 uppercase">Prix</label>
                                    <input 
                                        v-model.number="variantForms[option.id].price"
                                        type="number"
                                        step="0.01"
                                        placeholder="20"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm font-bold"
                                        @keyup.enter="addVariant(option.id)"
                                    >
                                </div>
                                <button 
                                    @click="addVariant(option.id)"
                                    :disabled="!variantForms[option.id].name.trim() || variantForms[option.id].price === null"
                                    class="px-4 py-2 bg-cyan-500 text-white font-bold rounded-lg hover:bg-cyan-600 disabled:opacity-50 disabled:cursor-not-allowed text-sm transition-all min-w-max"
                                    title="Ajouter variante"
                                >
                                    ➕ Ajouter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="deleteModal.show" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-xl">
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ deleteModal.title }}</h3>
                <p class="text-gray-600 mb-6">{{ deleteModal.message }}</p>
                <div class="flex gap-3">
                    <button 
                        @click="deleteModal.show = false"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button 
                        @click="executeDelete"
                        class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { optionsApi } from '../../api'
import { TrashIcon } from '@heroicons/vue/24/outline'

const options = ref([])
const isCreating = ref(false)
const variantForms = reactive({})

const form = reactive({
    optionName: '',
    variantName: '',
    variantPrice: null,
})

const deleteModal = reactive({
    show: false,
    title: '',
    message: '',
    type: null,
    optionId: null,
    variant: null,
})

const variantNameInput = ref(null)
const variantPriceInput = ref(null)

const formatPrice = (price) => {
    if (!price && price !== 0) return '0.00'
    return parseFloat(price).toFixed(2)
}

function focusNextField(field) {
    if (field === 'variantName') variantNameInput.value?.focus()
    if (field === 'variantPrice') variantPriceInput.value?.focus()
}

async function createOption() {
    const optionName = form.optionName.trim()
    const variantName = form.variantName.trim()
    const variantPrice = form.variantPrice

    if (!optionName || !variantName || variantPrice === null) {
        alert('Remplissez tous les champs')
        return
    }

    isCreating.value = true
    try {
        const response = await optionsApi.create({
            name: optionName,
            type: 'fixed',
            values: [variantName],
            extra_price: variantPrice,
            is_active: true,
            is_required: false
        })

        const newOption = response.data
        newOption.variants = [{
            id: 1,
            name: variantName,
            price_impact: variantPrice
        }]
        options.value.push(newOption)
        variantForms[newOption.id] = { name: '', price: null }

        // Reset
        form.optionName = ''
        form.variantName = ''
        form.variantPrice = null
    } catch (error) {
        console.error('Error creating option:', error)
        alert('Erreur lors de la création')
    } finally {
        isCreating.value = false
    }
}

async function addVariant(optionId) {
    const form = variantForms[optionId]
    if (!form.name.trim() || form.price === null) {
        alert('Remplissez nom et prix')
        return
    }

    try {
        const option = options.value.find(o => o.id === optionId)
        if (!option) return

        // Add to UI
        option.variants.push({
            id: Date.now(),
            name: form.name.trim(),
            price_impact: form.price
        })

        // Update backend
        const values = option.variants.map(v => v.name)
        await optionsApi.update(optionId, {
            name: option.name,
            type: 'fixed',
            values,
            extra_price: form.price,
            is_active: true,
            is_required: false
        })

        // Reset
        variantForms[optionId].name = ''
        variantForms[optionId].price = null
    } catch (error) {
        console.error('Error adding variant:', error)
        alert('Erreur')
    }
}

function confirmDeleteVariant(optionId, variant) {
    deleteModal.show = true
    deleteModal.type = 'variant'
    deleteModal.optionId = optionId
    deleteModal.variant = variant
    deleteModal.title = 'Supprimer la variante'
    deleteModal.message = `Supprimer "${variant.name}" ?`
}

function confirmDeleteOption(option) {
    deleteModal.show = true
    deleteModal.type = 'option'
    deleteModal.optionId = option.id
    deleteModal.title = 'Supprimer l\'option'
    deleteModal.message = `Supprimer "${option.name}" et ses variantes ?`
}

async function executeDelete() {
    try {
        if (deleteModal.type === 'variant') {
            const option = options.value.find(o => o.id === deleteModal.optionId)
            if (!option) return

            const idx = option.variants.findIndex(v => v.id === deleteModal.variant.id)
            if (idx > -1) option.variants.splice(idx, 1)

            const values = option.variants.map(v => v.name)
            const price = option.variants.length ? option.variants[0].price_impact : 0
            
            await optionsApi.update(deleteModal.optionId, {
                name: option.name,
                type: 'fixed',
                values: values.length ? values : [''],
                extra_price: price,
                is_active: true,
                is_required: false
            })
        } else if (deleteModal.type === 'option') {
            await optionsApi.delete(deleteModal.optionId)
            options.value = options.value.filter(o => o.id !== deleteModal.optionId)
            delete variantForms[deleteModal.optionId]
        }
    } catch (error) {
        console.error('Error:', error)
        alert('Erreur')
    } finally {
        deleteModal.show = false
    }
}

async function fetchOptions() {
    try {
        const response = await optionsApi.list()
        options.value = response.data.map(opt => ({
            ...opt,
            variants: opt.values?.map((val, idx) => ({
                id: idx,
                name: val,
                price_impact: opt.extra_price || 0
            })) || []
        }))

        options.value.forEach(opt => {
            if (!variantForms[opt.id]) {
                variantForms[opt.id] = { name: '', price: null }
            }
        })
    } catch (error) {
        console.error('Error fetching options:', error)
    }
}

onMounted(fetchOptions)
</script>

<style scoped>
button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
