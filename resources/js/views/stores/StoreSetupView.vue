<template>
    <div class="min-h-[70vh] flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-xl bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
            <div class="mb-6">
                <p class="text-sm font-semibold uppercase tracking-wide text-primary-600">Configuration initiale</p>
                <h1 class="mt-2 text-2xl font-bold text-gray-900">Créer mon point de vente</h1>
                <p class="mt-2 text-gray-500">
                    En tant que propriétaire, créez votre PDV pour isoler vos utilisateurs, fournisseurs, stock, clients, modes de paiement et charges.
                </p>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du point de vente *</label>
                    <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ex: Magasin Centre Ville">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                        <input v-model="form.code" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Auto si vide">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input v-model="form.phone" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input v-model="form.address" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                        <input v-model="form.city" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                        <input v-model="form.country" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>

                <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

                <button type="submit" :disabled="saving" class="w-full px-4 py-3 bg-primary-500 text-gray-900 font-semibold rounded-lg hover:bg-primary-600 disabled:opacity-60">
                    {{ saving ? 'Création...' : 'Créer mon PDV' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { storesApi, authApi } from '../../api'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const saving = ref(false)
const error = ref('')

const form = reactive({
    name: '',
    code: '',
    phone: '',
    address: '',
    city: '',
    country: '',
})

async function submit() {
    error.value = ''
    saving.value = true
    try {
        const { data } = await storesApi.create({ ...form })
        authStore.setCurrentStore(data)
        authStore.needsStoreSetup = false
        const me = await authApi.user()
        authStore.applyAuthPayload(me.data)
        router.push('/dashboard')
    } catch (e) {
        error.value = e.response?.data?.message
            || Object.values(e.response?.data?.errors || {})?.[0]?.[0]
            || 'Impossible de créer le point de vente.'
    } finally {
        saving.value = false
    }
}
</script>
