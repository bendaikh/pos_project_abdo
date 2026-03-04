<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Magasins</h1>
                <p class="text-gray-500">Gérez vos points de vente et stocks</p>
            </div>
            <button @click="openMagasinForm" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouveau Magasin
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Nombre de magasins</p>
                <p class="text-2xl font-bold text-gray-900">{{ magasins.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Magasins actifs</p>
                <p class="text-2xl font-bold text-green-600">{{ activeCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Stock total</p>
                <p class="text-2xl font-bold text-primary-600">{{ totalStock }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Responsables</p>
                <p class="text-2xl font-bold text-blue-600">{{ responsablesCount }}</p>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
            <input
                v-model="search"
                type="text"
                placeholder="Rechercher par nom, ville ou responsable..."
                class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
            </select>
        </div>

        <!-- Magasins Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-if="magasins.length === 0" class="col-span-full text-center py-12">
                <BuildingOfficeIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                <p class="text-gray-500">Aucun magasin créé</p>
            </div>
            <div v-for="magasin in filteredMagasins" :key="magasin.id" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                <div class="h-32 bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                    <BuildingOfficeIcon class="w-16 h-16 text-white opacity-50" />
                </div>
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ magasin.nom }}</h3>
                            <p class="text-sm text-gray-600">{{ magasin.ville }}</p>
                        </div>
                        <span :class="['px-2 py-1 text-xs font-medium rounded-full', magasin.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800']">
                            {{ magasin.status === 'active' ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>

                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p><span class="font-medium">Responsable:</span> {{ magasin.responsable }}</p>
                        <p><span class="font-medium">Articles:</span> {{ magasin.article_count }}</p>
                        <p><span class="font-medium">Stock:</span> {{ magasin.stock_count }}</p>
                        <p><span class="font-medium">Adresse:</span> {{ magasin.adresse }}</p>
                    </div>

                    <div class="flex gap-2">
                        <button @click="viewMagasin(magasin)" class="flex-1 px-3 py-2 bg-primary-50 text-primary-600 rounded-lg font-medium text-sm hover:bg-primary-100">
                            Détails
                        </button>
                        <button @click="editMagasin(magasin)" class="px-3 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                            <PencilIcon class="w-4 h-4" />
                        </button>
                        <button @click="deleteMagasin(magasin)" class="px-3 py-2 border border-red-300 text-red-600 rounded-lg text-sm hover:bg-red-50">
                            <TrashIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    BuildingOfficeIcon
} from '@heroicons/vue/24/outline'

const search = ref('')
const filterStatus = ref('')

const magasins = ref([
    {
        id: 1,
        nom: 'Magasin Principal',
        ville: 'Casablanca',
        responsable: 'Ahmed Ben Ahmed',
        article_count: 245,
        stock_count: 1250,
        adresse: '123 rue de la Paix, Casablanca',
        status: 'active'
    },
    {
        id: 2,
        nom: 'Magasin Nord',
        ville: 'Rabat',
        responsable: 'Fatima Zahra',
        article_count: 180,
        stock_count: 890,
        adresse: '456 avenue Royale, Rabat',
        status: 'active'
    },
    {
        id: 3,
        nom: 'Entrepôt Stock',
        ville: 'Fès',
        responsable: 'Mohamed Ali',
        article_count: 320,
        stock_count: 2100,
        adresse: '789 boulevard Hassan II, Fès',
        status: 'active'
    }
])

const filteredMagasins = computed(() => {
    let result = magasins.value

    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(m =>
            m.nom?.toLowerCase().includes(query) ||
            m.ville?.toLowerCase().includes(query) ||
            m.responsable?.toLowerCase().includes(query)
        )
    }

    if (filterStatus.value) {
        result = result.filter(m => m.status === filterStatus.value)
    }

    return result
})

const activeCount = computed(() => magasins.value.filter(m => m.status === 'active').length)
const totalStock = computed(() => magasins.value.reduce((sum, m) => sum + (m.stock_count || 0), 0))
const responsablesCount = computed(() => new Set(magasins.value.map(m => m.responsable)).size)

function viewMagasin(magasin) {
    console.log('View magasin:', magasin)
}

function editMagasin(magasin) {
    console.log('Edit magasin:', magasin)
}

function deleteMagasin(magasin) {
    const index = magasins.value.findIndex(m => m.id === magasin.id)
    if (index > -1) {
        magasins.value.splice(index, 1)
    }
}

function openMagasinForm() {
    console.log('Open magasin form')
}
</script>
