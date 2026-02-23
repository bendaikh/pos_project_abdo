<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Variantes d’article</h1>
                <p class="text-sm text-gray-500">Affiche les variantes créées pour chaque article.</p>
            </div>
            <button
                type="button"
                @click="router.push('/options')"
                class="px-4 py-2 bg-primary-500 text-gray-900 font-semibold rounded-lg hover:bg-primary-600"
            >
                Retour aux options
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Liste des variantes</h2>

            <div v-if="loading" class="text-sm text-gray-500">Chargement des variantes…</div>
            <div v-else-if="articleVariants.length === 0" class="text-sm text-gray-500">Aucune variante enregistrée.</div>

            <div v-else class="grid gap-3">
                <div
                    v-for="variant in articleVariants"
                    :key="variant.id"
                    class="flex items-center justify-between bg-gray-50 border border-gray-100 rounded-xl px-4 py-3"
                >
                    <div>
                        <p class="font-medium text-gray-900 truncate">{{ variant.name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ variant.articleName }} · Article #{{ variant.articleId }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-primary-600">+{{ formatPrice(variant.priceImpact) }}</p>
                        <p :class="variant.isActive ? 'text-green-600' : 'text-gray-400'" class="text-xs">
                            {{ variant.isActive ? 'Actif' : 'Inactif' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { articlesApi } from '../../api'

const router = useRouter()
const loading = ref(false)
const articles = ref([])

const articleVariants = computed(() => {
    return articles.value.flatMap(article => {
        const variants = Array.isArray(article.variants) ? article.variants : []
        return variants.map(variant => ({
            id: variant.id ?? `${article.id}-${variant.name}`,
            name: variant.name,
            articleName: article.name,
            articleId: article.id,
            priceImpact: Number(variant.price_impact) || 0,
            isActive: variant.is_active ?? true,
        }))
    })
})

const formatPrice = (value) => {
    const amount = Number(value || 0)
    return amount.toFixed(2)
}

async function fetchArticles() {
    loading.value = true
    try {
        const response = await articlesApi.list({ active: true })
        const payload = Array.isArray(response.data?.data) ? response.data.data : response.data
        articles.value = Array.isArray(payload) ? payload : []
    } catch (error) {
        console.error('Error loading articles:', error)
        articles.value = []
    } finally {
        loading.value = false
    }
}

onMounted(fetchArticles)
</script>
