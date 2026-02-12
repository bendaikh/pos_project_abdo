<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 p-6">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm mb-6">
            <router-link to="/dashboard" class="text-primary-400 hover:text-primary-300 flex items-center">
                <HomeIcon class="w-4 h-4 mr-1" />
                Accueil
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-500" />
            <router-link to="/bon-commande" class="text-primary-400 hover:text-primary-300">
                Achats
            </router-link>
            <ChevronRightIcon class="w-4 h-4 text-gray-500" />
            <span class="text-gray-400">Bon de commande</span>
        </div>

        <!-- Main Card -->
        <div class="bg-gray-800 bg-opacity-80 border border-gray-700 rounded-xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <ClipboardDocumentListIcon class="w-8 h-8 text-primary-400" />
                    <h1 class="text-2xl font-bold text-primary-400">Bon de commande</h1>
                </div>
                <div class="flex space-x-3">
                    <button @click="printBonCommande" class="px-4 py-2 bg-gray-700 text-white font-medium rounded-lg hover:bg-gray-600 flex items-center border border-gray-600">
                        <PrinterIcon class="w-5 h-5 mr-2" />
                        Imprimer
                    </button>
                    <button @click="sendToSupplier" class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 flex items-center">
                        <PaperAirplaneIcon class="w-5 h-5 mr-2" />
                        Envoyer au fournisseur
                    </button>
                </div>
            </div>

            <!-- Bon de Commande Info Section -->
            <div class="p-6 border-b border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Side -->
                    <div class="space-y-4">
                        <div>
                            <h2 class="text-3xl font-bold text-primary-300">{{ bonCommande.numero }}</h2>
                            <p class="text-gray-400 mt-1">Bon de commande</p>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex items-center space-x-2">
                                <CalendarIcon class="w-5 h-5 text-gray-400" />
                                <span class="text-gray-400">Date:</span>
                                <span class="text-white font-medium">{{ formatDate(bonCommande.date) }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <TruckIcon class="w-5 h-5 text-gray-400" />
                                <span class="text-gray-400">Livraison prévue:</span>
                                <span class="text-white font-medium">{{ formatDate(bonCommande.date_livraison_prevue) }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-400">Statut:</span>
                                <span :class="getStatusClass(bonCommande.statut)" class="px-3 py-1 text-sm font-medium rounded-full">
                                    {{ getStatusLabel(bonCommande.statut) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Fournisseur -->
                    <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4 border border-gray-600">
                        <h3 class="text-lg font-bold text-primary-400 mb-3 flex items-center">
                            <BuildingOfficeIcon class="w-5 h-5 mr-2" />
                            Fournisseur
                        </h3>
                        <div class="space-y-2">
                            <div>
                                <p class="text-gray-400 text-sm">Rechercher un fournisseur...</p>
                            </div>
                            <div class="bg-gray-800 bg-opacity-50 rounded-lg p-3 border border-gray-600 mt-2">
                                <div class="flex items-start space-x-3">
                                    <div class="w-12 h-12 bg-gray-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <BuildingOfficeIcon class="w-6 h-6 text-gray-400" />
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-white font-bold">{{ bonCommande.fournisseur.name }}</h4>
                                        <p class="text-gray-400 text-sm">ICE : {{ bonCommande.fournisseur.ice }}</p>
                                        <p class="text-gray-400 text-sm">{{ bonCommande.fournisseur.adresse }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Articles Section -->
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-primary-400 flex items-center">
                        <ShoppingCartIcon class="w-6 h-6 mr-2" />
                        Articles
                    </h3>
                    <button class="text-sm px-3 py-1 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 border border-gray-600 flex items-center">
                        Rechercher un article...
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">ID Article</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Article</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Désignation</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Quantité</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Prix unitaire (Dhs)</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Total estimé (Dhs)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <tr v-for="item in bonCommande.articles" :key="item.id" class="hover:bg-gray-700 hover:bg-opacity-30">
                                <td class="px-4 py-3 text-sm font-medium text-primary-300">{{ item.id }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-10 h-10 bg-gray-700 rounded flex items-center justify-center">
                                            <span class="text-xs text-gray-400">📦</span>
                                        </div>
                                        <span class="text-white text-sm">{{ item.nom }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-300">{{ item.designation }}</td>
                                <td class="px-4 py-3 text-center text-sm font-medium text-white">{{ item.quantite }}</td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-white">{{ formatCurrency(item.prix_unitaire) }}</td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-primary-300">{{ formatCurrency(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-end">
                    <button class="px-4 py-2 bg-primary-600 text-gray-900 rounded-lg hover:bg-primary-500 flex items-center">
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Ajouter un article
                    </button>
                </div>
            </div>

            <!-- Totaux Section -->
            <div class="p-6 border-b border-gray-700">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-400 mb-2">Quantité totale:</h3>
                        <p class="text-2xl font-bold text-white">{{ bonCommande.quantite_totale }}</p>
                    </div>
                    <div class="flex-1 text-right">
                        <h3 class="text-lg font-bold text-gray-400 mb-2">Total général:</h3>
                        <p class="text-3xl font-bold text-primary-300">{{ formatCurrency(bonCommande.montant_total) }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions Section -->
            <div class="p-6 bg-gray-900 bg-opacity-50 flex justify-between items-center">
                <button @click="cancelBonCommande" class="px-6 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 border border-gray-600 flex items-center">
                    <XMarkIcon class="w-5 h-5 mr-2" />
                    Annuler
                </button>
                <button @click="sendBonCommande" class="px-6 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                    <PaperAirplaneIcon class="w-5 h-5 mr-2" />
                    Envoyer au fournisseur
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useSettingsStore } from '../../stores/settings'
import {
    HomeIcon,
    ChevronRightIcon,
    ClipboardDocumentListIcon,
    BuildingOfficeIcon,
    ShoppingCartIcon,
    CalendarIcon,
    TruckIcon,
    PrinterIcon,
    PaperAirplaneIcon,
    PlusIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const bonCommande = ref({})

function formatDate(date) {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('fr-FR')
}

function getStatusClass(status) {
    const classes = {
        'brouillon': 'bg-gray-700 bg-opacity-50 text-gray-400 border border-gray-600',
        'envoyée': 'bg-blue-900 bg-opacity-50 text-blue-400 border border-blue-700',
        'confirmée': 'bg-purple-900 bg-opacity-50 text-purple-400 border border-purple-700',
        'en_cours': 'bg-yellow-900 bg-opacity-50 text-yellow-400 border border-yellow-700',
        'reçue': 'bg-green-900 bg-opacity-50 text-green-400 border border-green-700',
        'annulée': 'bg-red-900 bg-opacity-50 text-red-400 border border-red-700'
    }
    return classes[status] || 'bg-gray-700 bg-opacity-50 text-gray-400 border border-gray-600'
}

function getStatusLabel(status) {
    const labels = {
        'brouillon': 'Brouillon',
        'envoyée': 'Envoyée',
        'confirmée': 'Confirmée',
        'en_cours': 'En cours',
        'reçue': 'Reçue',
        'annulée': 'Annulée'
    }
    return labels[status] || status
}

function printBonCommande() {
    window.print()
}

function sendToSupplier() {
    alert('Envoi du bon de commande au fournisseur par email...')
}

function cancelBonCommande() {
    router.push('/bon-commande')
}

function sendBonCommande() {
    alert('Bon de commande envoyé avec succès!')
    router.push('/bon-commande')
}

onMounted(() => {
    // Demo data - In production, fetch from API using route.params.id
    bonCommande.value = {
        numero: 'BC-2026-001',
        date: '2026-02-01',
        date_livraison_prevue: '2026-02-15',
        statut: 'envoyée',
        fournisseur: {
            name: 'Electronix SARL',
            ice: '001234567890001',
            adresse: '23, Rue de l\'Electronique, Casablanca',
            ville: 'Casablanca'
        },
        articles: [
            {
                id: '12345',
                nom: 'Casque sans fil',
                designation: 'Casque audio sans fil de Hquer qualité avec réduction de bruit et autonomie de 20 heures',
                quantite: 30,
                prix_unitaire: 320.00,
                total: 9600.00
            },
            {
                id: '67890',
                nom: 'Souris sans fil',
                designation: 'Souris sans fil ergonomique avec batterie rechargeable',
                quantite: 50,
                prix_unitaire: 150.00,
                total: 7500.00
            },
            {
                id: '13440',
                nom: 'Adil Messioul',
                designation: 'Clavier mécanique rétroéclairé avec touches anti-ghosting',
                quantite: 20,
                prix_unitaire: 480.00,
                total: 9600.00
            }
        ],
        quantite_totale: 80,
        montant_total: 171000.00
    }

    // Calculate totals
    bonCommande.value.quantite_totale = bonCommande.value.articles.reduce((sum, item) => sum + item.quantite, 0)
    bonCommande.value.montant_total = bonCommande.value.articles.reduce((sum, item) => sum + item.total, 0)
})
</script>
