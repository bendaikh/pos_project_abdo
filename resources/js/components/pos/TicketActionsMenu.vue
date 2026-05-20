<template>
    <div class="relative" ref="menuRef">
        <button
            type="button"
            class="flex items-center justify-center w-8 h-8 rounded-lg border transition-colors duration-300"
            :class="expanded
                ? 'border-blue-200 bg-white text-gray-700 hover:bg-gray-50'
                : 'border-white/40 bg-white/10 text-white hover:bg-white/20'"
            title="Actions ticket"
            @click.stop="toggleMenu"
        >
            <EllipsisVerticalIcon class="w-5 h-5" />
        </button>

        <div
            v-if="open"
            class="absolute right-0 top-full z-50 mt-1 min-w-[220px] overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-lg"
            @click.stop
        >
            <button
                v-for="action in actions"
                :key="action.id"
                type="button"
                class="flex w-full items-center gap-3 border-b border-gray-100 px-4 py-2.5 text-left text-sm text-gray-800 last:border-b-0 hover:bg-gray-50"
                @click="selectAction(action.id)"
            >
                <component :is="action.icon" class="h-5 w-5 shrink-0" :class="action.iconClass" />
                <span>{{ action.label }}</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, h } from 'vue'
import {
    EllipsisVerticalIcon,
    TrashIcon,
    PrinterIcon,
    UserIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline'

defineProps({
    expanded: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['action'])

const open = ref(false)
const menuRef = ref(null)

const ChefHatIcon = {
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            fill: 'none',
            viewBox: '0 0 24 24',
            stroke: 'currentColor',
            class: 'h-5 w-5 text-orange-500',
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '1.8',
                d: 'M6 14h12l-1 6H7l-1-6zm1-2c0-2.2 1.8-4 4-4 .7 0 1.4.2 2 .5C13.6 8.2 14.8 8 16 8c2.2 0 4 1.8 4 4M8 12h8',
            }),
        ])
    },
}

const TableIcon = {
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            fill: 'none',
            viewBox: '0 0 24 24',
            stroke: 'currentColor',
            class: 'h-5 w-5 text-violet-500',
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '1.8',
                d: 'M4 10h16M6 10v8m12-8v8M8 18h8M7 6h2m6 0h2',
            }),
        ])
    },
}

const actions = [
    { id: 'clear', label: 'Effacer ticket', icon: TrashIcon, iconClass: 'text-red-500' },
    { id: 'print-addition', label: 'Imprimer l\'addition', icon: PrinterIcon, iconClass: 'text-green-600' },
    { id: 'reprint-kitchen', label: 'Réimprimer cuisine', icon: ChefHatIcon, iconClass: '' },
    { id: 'move-table', label: 'Déplacer table', icon: TableIcon, iconClass: '' },
    { id: 'assign-user', label: 'Affecter à utilisateur', icon: UserIcon, iconClass: 'text-teal-600' },
    { id: 'history', label: 'Historique ticket', icon: ClockIcon, iconClass: 'text-violet-700' },
]

function toggleMenu() {
    open.value = !open.value
}

function closeMenu() {
    open.value = false
}

function selectAction(actionId) {
    closeMenu()
    emit('action', actionId)
}

function handleClickOutside(event) {
    if (menuRef.value && !menuRef.value.contains(event.target)) {
        closeMenu()
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})

defineExpose({ closeMenu })
</script>
