<template>
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/45 backdrop-blur-[2px]" @click="$emit('close')"></div>

        <div class="relative z-10 w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="relative border-b border-slate-100 px-5 py-4">
                <h2 class="text-center text-lg font-bold text-slate-900">Détails du total</h2>
                <button
                    type="button"
                    class="absolute right-4 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-2xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    @click="$emit('close')"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="px-5 py-4">
                <div class="flex items-center justify-between rounded-xl bg-blue-50 px-4 py-3">
                    <span class="text-sm font-medium text-slate-700">Total à payer</span>
                    <span class="text-2xl font-bold text-blue-600">{{ formatCurrency(previewTotal) }}</span>
                </div>

                <section v-if="discounts.length > 0" class="mt-5">
                    <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-800">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-violet-100 text-violet-600">%</span>
                        Remises
                    </div>
                    <div class="divide-y divide-slate-100 rounded-xl border border-slate-200">
                        <label
                            v-for="discount in discounts"
                            :key="discount.id"
                            class="flex cursor-pointer items-center gap-3 px-4 py-3 transition hover:bg-slate-50"
                        >
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-sm font-bold text-violet-600">%</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-slate-900">{{ discount.label }}</p>
                                <p class="text-sm font-semibold text-violet-600">{{ formatDiscountSummary(discount) }}</p>
                            </div>
                            <input
                                v-model="draftDiscountIds"
                                type="checkbox"
                                class="sr-only"
                                :value="String(discount.id)"
                            >
                            <span
                                class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors"
                                :class="isDiscountSelected(discount.id) ? 'bg-emerald-500' : 'bg-slate-300'"
                            >
                                <span
                                    class="inline-block h-5 w-5 translate-y-0.5 rounded-full bg-white shadow transition-transform"
                                    :class="isDiscountSelected(discount.id) ? 'translate-x-5' : 'translate-x-0.5'"
                                ></span>
                            </span>
                        </label>
                    </div>
                </section>

                <section v-if="displayTaxes.length > 0" class="mt-5">
                    <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-800">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">📄</span>
                        Taxes
                    </div>
                    <div class="divide-y divide-slate-100 rounded-xl border border-slate-200">
                        <label
                            v-for="tax in displayTaxes"
                            :key="tax.id"
                            class="flex cursor-pointer items-center gap-3 px-4 py-3 transition hover:bg-slate-50"
                        >
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-sm font-bold text-emerald-600">%</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-slate-900">{{ tax.label }}</p>
                                <p class="text-sm font-semibold text-emerald-600">{{ formatTaxSummary(tax) }}</p>
                            </div>
                            <input
                                v-model="draftTaxIds"
                                type="checkbox"
                                class="sr-only"
                                :value="String(tax.id)"
                            >
                            <span
                                class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors"
                                :class="isTaxSelected(tax.id) ? 'bg-emerald-500' : 'bg-slate-300'"
                            >
                                <span
                                    class="inline-block h-5 w-5 translate-y-0.5 rounded-full bg-white shadow transition-transform"
                                    :class="isTaxSelected(tax.id) ? 'translate-x-5' : 'translate-x-0.5'"
                                ></span>
                            </span>
                        </label>
                    </div>
                </section>

                <p
                    v-if="discounts.length === 0 && displayTaxes.length === 0"
                    class="mt-5 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500"
                >
                    Aucune remise ou taxe active dans les paramètres.
                </p>

                <section class="mt-5">
                    <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-800">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-blue-100 text-blue-600">💬</span>
                        Commentaire
                    </div>
                    <textarea
                        v-model="draftComment"
                        rows="3"
                        placeholder="Ajouter un commentaire..."
                        class="w-full resize-none rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    ></textarea>
                </section>
            </div>

            <div class="flex gap-3 border-t border-slate-100 px-5 py-4">
                <button
                    type="button"
                    class="flex-1 rounded-xl border border-blue-500 px-4 py-2.5 text-sm font-semibold text-blue-600 transition hover:bg-blue-50"
                    @click="$emit('close')"
                >
                    Annuler
                </button>
                <button
                    type="button"
                    class="flex-1 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                    @click="handleApply"
                >
                    Appliquer
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
    calculateLineDiscountAmount,
    calculateTaxAmount,
    formatDiscountSummary,
    formatTaxSummary,
    SETTINGS_TAX_ID,
} from '../../stores/customLists'

const props = defineProps({
    subtotal: {
        type: Number,
        default: 0,
    },
    discounts: {
        type: Array,
        default: () => [],
    },
    taxes: {
        type: Array,
        default: () => [],
    },
    settingsTaxEnabled: {
        type: Boolean,
        default: false,
    },
    settingsTaxRate: {
        type: Number,
        default: 0,
    },
    settingsTaxName: {
        type: String,
        default: 'TVA',
    },
    initialDiscountIds: {
        type: Array,
        default: () => [],
    },
    initialTaxIds: {
        type: Array,
        default: () => [],
    },
    initialComment: {
        type: String,
        default: '',
    },
    formatCurrency: {
        type: Function,
        required: true,
    },
})

const emit = defineEmits(['close', 'apply'])

const draftDiscountIds = ref([])
const draftTaxIds = ref([])
const draftComment = ref('')

watch(
    () => [props.initialDiscountIds, props.initialTaxIds, props.initialComment],
    () => {
        draftDiscountIds.value = props.initialDiscountIds.map(String)
        draftTaxIds.value = props.initialTaxIds.map(String)
        draftComment.value = props.initialComment || ''
    },
    { immediate: true }
)

function isDiscountSelected(id) {
    return draftDiscountIds.value.includes(String(id))
}

function isTaxSelected(id) {
    return draftTaxIds.value.includes(String(id))
}

const displayTaxes = computed(() => {
    if (props.taxes.length > 0) {
        return props.taxes
    }

    if (props.settingsTaxEnabled && Number(props.settingsTaxRate) > 0) {
        return [{
            id: SETTINGS_TAX_ID,
            label: props.settingsTaxName || 'TVA',
            tax_type: 'percentage',
            tax_rate: Number(props.settingsTaxRate) || 0,
            tax_is_default: true,
        }]
    }

    return []
})

const previewCartDiscount = computed(() => {
    let runningSubtotal = Math.max(0, Number(props.subtotal) || 0)
    let totalDiscount = 0

    for (const id of draftDiscountIds.value) {
        const discount = props.discounts.find((item) => String(item.id) === String(id))
        if (!discount) continue
        const amount = calculateLineDiscountAmount(discount, runningSubtotal)
        totalDiscount += amount
        runningSubtotal = Math.max(0, runningSubtotal - amount)
    }

    return Math.round(totalDiscount * 100) / 100
})

const previewAfterDiscount = computed(() => {
    return Math.max(0, (Number(props.subtotal) || 0) - previewCartDiscount.value)
})

const previewTax = computed(() => {
    const base = previewAfterDiscount.value
    if (base <= 0 || draftTaxIds.value.length === 0) {
        return 0
    }

    return draftTaxIds.value.reduce((sum, id) => {
        const tax = displayTaxes.value.find((item) => String(item.id) === String(id))
        return sum + calculateTaxAmount(tax, base)
    }, 0)
})

const previewTotal = computed(() => {
    return Math.round((previewAfterDiscount.value + previewTax.value) * 100) / 100
})

function handleApply() {
    const taxIds = draftTaxIds.value.filter((id) => String(id) !== SETTINGS_TAX_ID)

    emit('apply', {
        discountIds: [...draftDiscountIds.value],
        taxIds,
        comment: draftComment.value.trim(),
    })
}
</script>
