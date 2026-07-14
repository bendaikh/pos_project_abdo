<template>
    <div class="space-y-6">
        <div class="pdv-page-hero pdv-page-hero--blue">
            <div>
                <p class="pdv-kicker">Administration · Hub</p>
                <h1 class="pdv-title">Menu PDV</h1>
                <p class="pdv-subtitle">Accès rapide aux fonctions liées aux points de vente</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <router-link
                v-for="item in menuItems"
                :key="item.to"
                :to="item.to"
                class="menu-card group"
                :class="item.accent"
            >
                <div class="menu-card-icon" :class="item.iconBg">
                    <component :is="item.icon" class="w-6 h-6" :class="item.iconColor" />
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-white group-hover:text-primary-300 transition-colors">{{ item.title }}</h3>
                    <p class="text-sm text-text-secondary mt-1">{{ item.desc }}</p>
                </div>
                <span class="menu-card-arrow">→</span>
            </router-link>
        </div>

        <div class="surface-card p-5">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="text-lg font-semibold text-white">PDV actifs</h2>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-primary-500/15 text-primary-300">
                    {{ stores.length }} magasin{{ stores.length > 1 ? 's' : '' }}
                </span>
            </div>
            <div v-if="loading" class="text-text-secondary text-sm">Chargement...</div>
            <div v-else-if="stores.length === 0" class="text-text-secondary text-sm">Aucun PDV actif</div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div
                    v-for="store in stores"
                    :key="store.id"
                    class="store-chip"
                >
                    <div class="min-w-0">
                        <p class="font-mono text-xs text-primary-400">{{ store.code || '—' }}</p>
                        <p class="font-medium text-white truncate">{{ store.name }}</p>
                        <p class="text-xs text-text-secondary truncate">{{ store.city || '—' }} · {{ store.display_owner_name || store.owner_name || '—' }}</p>
                    </div>
                    <router-link to="/fiche-pdv" class="store-chip-link">Fiche</router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import {
    BuildingStorefrontIcon,
    BanknotesIcon,
    ScaleIcon,
    ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline'
import { storesApi } from '../../api'

const stores = ref([])
const loading = ref(false)

const menuItems = [
    {
        to: '/fiche-pdv',
        title: 'Fiche PDV',
        desc: 'Créer et gérer les fiches points de vente',
        icon: BuildingStorefrontIcon,
        iconBg: 'bg-primary-500/15',
        iconColor: 'text-primary-400',
        accent: 'menu-card--green',
    },
    {
        to: '/etat-paiement-pdv',
        title: 'État paiement PDV',
        desc: 'Suivre les règlements et échéances',
        icon: BanknotesIcon,
        iconBg: 'bg-accent-500/15',
        iconColor: 'text-accent-400',
        accent: 'menu-card--orange',
    },
    {
        to: '/balance-pdv',
        title: 'Balance PDV',
        desc: 'Consulter le solde et les totaux',
        icon: ScaleIcon,
        iconBg: 'bg-info-500/15',
        iconColor: 'text-info-400',
        accent: 'menu-card--blue',
    },
    {
        to: '/dashboard',
        title: 'Dashboard',
        desc: 'Vue d’ensemble de l’activité',
        icon: ClipboardDocumentListIcon,
        iconBg: 'bg-white/10',
        iconColor: 'text-white',
        accent: 'menu-card--slate',
    },
]

onMounted(async () => {
    loading.value = true
    try {
        const { data } = await storesApi.list()
        stores.value = Array.isArray(data) ? data : []
    } catch (e) {
        stores.value = []
    } finally {
        loading.value = false
    }
})
</script>

<style scoped>
.pdv-page-hero {
    border-radius: 1.25rem;
    padding: 1.35rem 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.pdv-page-hero--blue {
    background: linear-gradient(135deg, #141625 0%, #1a1a38 45%, #142838 120%);
    border-color: rgba(34, 211, 238, 0.3);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
}
.pdv-kicker {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #22D3EE;
    margin-bottom: 0.35rem;
}
.pdv-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.02em;
}
.pdv-subtitle { margin-top: 0.25rem; color: #94A3B8; font-size: 0.9rem; }

.menu-card {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.15rem 1.2rem;
    border-radius: 1rem;
    text-decoration: none;
    background: rgba(18, 22, 30, 0.94);
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition: all 0.22s ease;
}
.menu-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 28px rgba(0, 0, 0, 0.35);
}
.menu-card--green:hover  { border-color: rgba(34, 211, 238, 0.45); }
.menu-card--orange:hover { border-color: rgba(251, 146, 60, 0.45); }
.menu-card--blue:hover   { border-color: rgba(34, 211, 238, 0.45); }
.menu-card--slate:hover  { border-color: rgba(255, 255, 255, 0.2); }

.menu-card-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.menu-card-arrow {
    margin-left: auto;
    color: #5c6675;
    font-weight: 700;
    transition: color 0.2s, transform 0.2s;
}
.menu-card:hover .menu-card-arrow {
    color: #22D3EE;
    transform: translateX(3px);
}

.store-chip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    border-radius: 0.85rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.03);
    padding: 0.85rem 1rem;
    transition: border-color 0.2s, background 0.2s;
}
.store-chip:hover {
    border-color: rgba(34, 211, 238, 0.3);
    background: rgba(34, 211, 238, 0.06);
}
.store-chip-link {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #FB923C;
    text-decoration: none;
}
.store-chip-link:hover { color: #FB923C; }
</style>
