<template>
    <span
        class="inline-flex max-w-full items-center gap-1.5 rounded-full border font-semibold"
        :class="[sizeClasses.wrapper, palette.wrapper]"
    >
        <span
            class="inline-flex shrink-0 items-center justify-center rounded-full font-black"
            :class="[sizeClasses.logo, palette.logo]"
        >
            {{ badge.monogram }}
        </span>
        <span class="truncate">{{ badge.label }}</span>
        <span
            v-if="official"
            class="inline-flex shrink-0 items-center rounded-full border px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-[0.18em]"
            :class="palette.official"
        >
            Officiel
        </span>
    </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    platform: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'md',
    },
    official: {
        type: Boolean,
        default: true,
    },
})

const PLATFORM_STYLES = {
    glovo: {
        label: 'Glovo',
        monogram: 'G',
        wrapper: 'border-[#eadf7a] bg-[#fff9d9] text-[#08756f]',
        logo: 'bg-[#f5d94b] text-[#08514d]',
        official: 'border-[#eadf7a] bg-white/85 text-[#08756f]',
    },
    ubereats: {
        label: 'Uber Eats',
        monogram: 'UE',
        wrapper: 'border-[#bfe9d5] bg-[#f2fff8] text-[#0f6a49]',
        logo: 'bg-[#111827] text-[#4ade80]',
        official: 'border-[#bfe9d5] bg-white/90 text-[#0f6a49]',
    },
    jumiafood: {
        label: 'Jumia Food',
        monogram: 'JF',
        wrapper: 'border-[#ffd7b3] bg-[#fff6ec] text-[#c35612]',
        logo: 'bg-[#f68b1e] text-white',
        official: 'border-[#ffd7b3] bg-white/90 text-[#c35612]',
    },
    talabat: {
        label: 'Talabat',
        monogram: 'T',
        wrapper: 'border-[#ffd5c7] bg-[#fff3ee] text-[#e0612a]',
        logo: 'bg-[#ff5a1f] text-white',
        official: 'border-[#ffd5c7] bg-white/90 text-[#e0612a]',
    },
    deliveroo: {
        label: 'Deliveroo',
        monogram: 'D',
        wrapper: 'border-[#b9f1ea] bg-[#ecfffc] text-[#008b84]',
        logo: 'bg-[#00c7b1] text-white',
        official: 'border-[#b9f1ea] bg-white/90 text-[#008b84]',
    },
    careem: {
        label: 'Careem',
        monogram: 'C',
        wrapper: 'border-[#cde9b8] bg-[#f7ffef] text-[#5a8f1d]',
        logo: 'bg-[#84c225] text-white',
        official: 'border-[#cde9b8] bg-white/90 text-[#5a8f1d]',
    },
}

const SIZE_STYLES = {
    sm: {
        wrapper: 'px-2 py-1 text-[11px]',
        logo: 'h-4 w-4 text-[9px]',
    },
    md: {
        wrapper: 'px-2.5 py-1 text-xs',
        logo: 'h-5 w-5 text-[10px]',
    },
}

function normalizePlatformKey(value) {
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '')
}

const badge = computed(() => {
    const rawLabel = String(props.platform || '').trim()
    const key = normalizePlatformKey(rawLabel)

    if (PLATFORM_STYLES[key]) {
        return PLATFORM_STYLES[key]
    }

    const fallbackLabel = rawLabel || 'Plateforme'
    return {
        label: fallbackLabel,
        monogram: fallbackLabel.slice(0, 2).toUpperCase(),
        wrapper: 'border-slate-200 bg-slate-100 text-slate-700',
        logo: 'bg-slate-700 text-white',
        official: 'border-slate-200 bg-white/90 text-slate-700',
    }
})

const sizeClasses = computed(() => SIZE_STYLES[props.size] || SIZE_STYLES.md)
const palette = computed(() => badge.value)
</script>
