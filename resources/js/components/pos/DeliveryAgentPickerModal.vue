<template>
    <div class="fixed inset-0 z-[60]">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[1px]" @click="$emit('close')"></div>

        <div
            class="fixed inset-x-0 bottom-0 z-10 mx-auto flex w-full max-w-lg flex-col rounded-t-[28px] bg-white shadow-2xl sm:max-w-xl"
            style="max-height: min(75vh, 520px);"
        >
            <div class="flex shrink-0 justify-center pt-3 pb-1">
                <div class="h-1 w-12 rounded-full bg-gray-300"></div>
            </div>

            <div class="flex shrink-0 items-center justify-between border-b border-gray-100 px-5 py-3">
                <h2 class="text-lg font-bold text-gray-900">{{ title }}</h2>
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full text-2xl text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                    aria-label="Fermer"
                    @click="$emit('close')"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain">
                <div v-if="loading" class="px-5 py-8 text-center text-sm text-gray-500">
                    Chargement des livreurs...
                </div>

                <div v-else-if="agents.length === 0" class="px-5 py-8 text-center text-sm text-gray-500">
                    {{ emptyMessage }}
                </div>

                <template v-else>
                    <button
                        v-for="(agent, index) in agents"
                        :key="agent.id"
                        type="button"
                        class="flex w-full items-center gap-4 border-b border-gray-100 px-5 py-4 text-left transition active:bg-gray-50 hover:bg-gray-50"
                        :class="String(selectedId) === String(agent.id) ? 'bg-blue-50/60' : ''"
                        @click="$emit('select', agent)"
                    >
                        <span
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                            :class="avatarClass(index)"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2c-3.31 0-6 1.79-6 4v1h12v-1c0-2.21-2.69-4-6-4Z" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1 text-base font-medium text-gray-900">{{ formatLabel(agent) }}</span>
                        <span
                            v-if="String(selectedId) === String(agent.id)"
                            class="shrink-0 text-xs font-semibold text-blue-600"
                        >
                            Sélectionné
                        </span>
                    </button>
                </template>

                <button
                    type="button"
                    class="flex w-full items-center gap-4 px-5 py-4 text-left transition active:bg-gray-50 hover:bg-gray-50"
                    @click="$emit('add')"
                >
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" d="M12 6v12M6 12h12" />
                        </svg>
                    </span>
                    <span class="text-base font-semibold text-blue-600">Ajouter un livreur</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    agents: {
        type: Array,
        default: () => [],
    },
    selectedId: {
        type: [String, Number],
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Choisir un livreur',
    },
    emptyMessage: {
        type: String,
        default: 'Aucun livreur disponible.',
    },
    formatLabel: {
        type: Function,
        default: (agent) => agent?.name || '',
    },
})

defineEmits(['close', 'select', 'add'])

const avatarPalette = [
    'bg-sky-100 text-sky-600',
    'bg-emerald-100 text-emerald-600',
    'bg-violet-100 text-violet-600',
    'bg-amber-100 text-amber-600',
    'bg-rose-100 text-rose-600',
]

function avatarClass(index) {
    return avatarPalette[index % avatarPalette.length]
}
</script>
