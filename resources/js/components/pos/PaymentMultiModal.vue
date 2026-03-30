<template>
    <div class="fixed inset-0 z-50 overflow-hidden">
        <div class="flex min-h-screen items-end justify-center p-2 sm:items-center sm:p-3 md:p-4">
            <div class="fixed inset-0 bg-slate-900/45 backdrop-blur-[1px]" @click="$emit('close')"></div>

            <div class="relative z-10 mx-auto flex max-h-[90vh] w-full max-w-[calc(100vw-1rem)] flex-col overflow-hidden rounded-[24px] border border-slate-200 bg-[#f6f8fc] shadow-[0_24px_80px_rgba(15,23,42,0.22)] sm:max-w-[calc(100vw-1.5rem)] sm:rounded-[28px] lg:max-w-6xl xl:max-w-7xl">
                <div class="sticky top-0 z-20 shrink-0 border-b border-slate-200 bg-white px-3 py-2 sm:px-4 sm:py-3">
                    <div class="flex items-center justify-between gap-3">
                        <button
                            type="button"
                            @click="$emit('close')"
                            class="inline-flex items-center gap-1 rounded-lg border border-transparent px-2 py-1 text-xs font-semibold text-sky-700 transition-colors hover:border-sky-100 hover:bg-sky-50"
                        >
                            <ArrowLeftIcon class="h-4 w-4" />
                            Retour
                        </button>
                        <div class="min-w-0 text-right">
                            <p class="text-[9px] font-semibold uppercase tracking-[0.24em] text-slate-400">Caisse</p>
                            <p class="truncate text-xs font-semibold text-slate-900 sm:text-sm">Paiement</p>
                        </div>
                    </div>
                </div>

                <div class="grid min-h-0 flex-1 overflow-hidden xl:grid-cols-[280px_minmax(0,1fr)]">
                    <aside class="hidden min-h-0 border-b border-slate-200 bg-slate-50/90 xl:block xl:border-b-0 xl:border-r">
                        <div class="flex h-full flex-col p-3 sm:p-4 md:p-4 lg:p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-400">Modes</p>
                                    <h3 class="mt-0.5 text-xs font-semibold text-slate-900">Paiement</h3>
                                </div>
                                <div class="rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">
                                    {{ paymentMethods.length }}
                                </div>
                            </div>

                            <div v-if="paymentMethods.length" class="mt-3 min-h-0 flex-1">
                                <div class="flex gap-2 overflow-x-auto pb-1 pr-1 xl:hidden">
                                    <button
                                        v-for="method in paymentMethods"
                                        :key="`${method.id}-mobile`"
                                        type="button"
                                        @click="selectMethod(method)"
                                        class="w-[calc(50%-0.25rem)] shrink-0 rounded-[16px] border px-3 py-3 text-left transition-all duration-150 sm:w-[calc(50%-0.375rem)]"
                                        :class="selectedMethod?.id === method.id
                                            ? 'border-sky-300 bg-white shadow-md ring-2 ring-sky-100'
                                            : 'border-slate-200 bg-white/80 hover:border-slate-300 hover:bg-white'"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg"
                                                :class="selectedMethod?.id === method.id ? 'bg-sky-50' : 'bg-slate-100'"
                                            >
                                                {{ method.icon }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-semibold text-slate-900">{{ method.label }}</p>
                                                <p class="mt-1 text-[11px] font-medium text-slate-500">{{ paymentTimingLabel(method) }}</p>
                                                <p class="mt-1.5 text-base font-semibold text-slate-800">{{ formatCurrency(getMethodAssignedAmount(method)) }}</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>

                                <div class="hidden h-full overflow-y-auto pr-1 xl:block">
                                    <div class="grid grid-cols-1 gap-2.5">
                                        <button
                                            v-for="method in paymentMethods"
                                            :key="`${method.id}-desktop`"
                                            type="button"
                                            @click="selectMethod(method)"
                                            class="w-full rounded-[18px] border px-4 py-4 text-left transition-all duration-150"
                                            :class="selectedMethod?.id === method.id
                                                ? 'border-sky-300 bg-white shadow-md ring-2 ring-sky-100'
                                                : 'border-slate-200 bg-white/80 hover:border-slate-300 hover:bg-white'"
                                        >
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-xl"
                                                    :class="selectedMethod?.id === method.id ? 'bg-sky-50' : 'bg-slate-100'"
                                                >
                                                    {{ method.icon }}
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-base font-semibold text-slate-900">{{ method.label }}</p>
                                                    <p class="mt-1 text-xs font-medium text-slate-500">{{ paymentTimingLabel(method) }}</p>
                                                    <p class="mt-1.5 text-lg font-semibold text-slate-800">{{ formatCurrency(getMethodAssignedAmount(method)) }}</p>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="mt-3 rounded-[16px] border border-dashed border-slate-300 bg-white px-3 py-4 text-center text-xs text-slate-500">
                                Aucun mode
                            </div>
                        </div>
                    </aside>

                    <section class="flex min-h-0 flex-col">
                        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-3 sm:p-4 md:p-5">
                            <div class="rounded-[18px] border border-slate-200 bg-white p-3 shadow-sm xl:hidden">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-400">Modes</p>
                                        <h3 class="mt-0.5 text-xs font-semibold text-slate-900">Paiement</h3>
                                    </div>
                                    <div class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                                        {{ paymentMethods.length }}
                                    </div>
                                </div>

                                <div v-if="paymentMethods.length" class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    <button
                                        v-for="method in paymentMethods"
                                        :key="`${method.id}-compact`"
                                        type="button"
                                        @click="selectMethod(method)"
                                        class="w-full rounded-[16px] border px-3 py-3 text-left transition-all duration-150"
                                        :class="selectedMethod?.id === method.id
                                            ? 'border-sky-300 bg-sky-50 shadow-sm ring-2 ring-sky-100'
                                            : 'border-slate-200 bg-slate-50/70 hover:border-slate-300 hover:bg-white'"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg"
                                                :class="selectedMethod?.id === method.id ? 'bg-white text-sky-700' : 'bg-white text-slate-700'"
                                            >
                                                {{ method.icon }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-semibold text-slate-900">{{ method.label }}</p>
                                                <p class="mt-1 text-[11px] font-medium text-slate-500">{{ paymentTimingLabel(method) }}</p>
                                                <p class="mt-1.5 text-base font-semibold text-slate-800">{{ formatCurrency(getMethodAssignedAmount(method)) }}</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>

                                <div v-else class="mt-3 rounded-[14px] border border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-center text-xs text-slate-500">
                                    Aucun mode
                                </div>
                            </div>

                            <div class="mt-3 grid gap-2 sm:gap-3" :class="coveredByExistingPayments > 0 ? 'grid-cols-2 xl:grid-cols-4' : 'grid-cols-2 lg:grid-cols-3'">
                                <div class="rounded-[16px] border border-slate-200 bg-white p-3 shadow-sm sm:rounded-[18px] sm:p-4 lg:rounded-[20px]">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">À payer</p>
                                    <p class="mt-1.5 text-lg font-semibold tracking-tight text-sky-700 sm:text-xl lg:mt-2 lg:text-2xl">{{ formatCurrency(payableTotal) }}</p>
                                </div>
                                <div v-if="coveredByExistingPayments > 0" class="rounded-[16px] border border-violet-200 bg-violet-50 p-3 shadow-sm sm:rounded-[18px] sm:p-4 lg:rounded-[20px]">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-violet-600">Déjà couvert</p>
                                    <p class="mt-1.5 text-lg font-semibold text-violet-700 sm:text-xl lg:mt-2 lg:text-2xl">{{ formatCurrency(coveredByExistingPayments) }}</p>
                                </div>
                                <div class="rounded-[16px] border sm:rounded-[18px] lg:rounded-[20px]" :class="remaining <= 0 ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50'">
                                    <div class="p-3 shadow-sm sm:p-4">
                                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em]" :class="remaining <= 0 ? 'text-emerald-600' : 'text-amber-600'">Reste</p>
                                        <p class="mt-1.5 text-lg font-semibold tracking-tight sm:text-xl lg:mt-2 lg:text-2xl" :class="remaining <= 0 ? 'text-emerald-700' : 'text-amber-700'">
                                            {{ formatCurrency(remaining) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="rounded-[16px] border border-emerald-200 bg-emerald-50 p-3 shadow-sm sm:rounded-[18px] sm:p-4 lg:rounded-[20px]">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-600">Affecté</p>
                                    <p class="mt-1.5 text-lg font-semibold text-emerald-700 sm:text-xl lg:mt-2 lg:text-2xl">{{ formatCurrency(totalPaid) }}</p>
                                </div>
                            </div>

                            <div v-if="selectedMethod" class="mt-3 rounded-[18px] border border-slate-200 bg-white p-3 shadow-sm sm:mt-4 sm:rounded-[20px] sm:p-4 lg:rounded-[22px] lg:p-5">
                                <div class="mb-3 flex flex-col gap-3 border-b border-slate-200/80 pb-3 sm:mb-4 sm:flex-row sm:items-center sm:justify-between sm:gap-3 sm:pb-4">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/80 text-xl shadow-sm sm:h-11 sm:w-11 sm:text-2xl md:h-12 md:w-12">
                                            {{ selectedMethod.icon }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <h4 class="text-base font-semibold text-slate-900 sm:text-lg">{{ selectedMethod.label }}</h4>
                                                <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold sm:px-2.5 sm:text-xs" :class="selectedMethodTheme.chipClass">
                                                    {{ paymentTimingLabel(selectedMethod) }}
                                                </span>
                                            </div>
                                            <p class="mt-1 hidden text-sm text-slate-500 xl:block">{{ selectedMethod.description }}</p>
                                        </div>
                                    </div>
                                    <div class="hidden rounded-xl border border-white/70 bg-white/80 px-3 py-2.5 text-right shadow-sm shrink-0 sm:block">
                                        <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-slate-400">Montant</p>
                                        <p class="mt-1 text-base font-semibold text-slate-900 sm:text-lg">{{ formatCurrency(paymentForm.amount || 0) }}</p>
                                    </div>
                                </div>

                                <div v-if="paymentNotice" class="mb-4 rounded-[16px] border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-medium text-sky-800">
                                    {{ paymentNotice }}
                                </div>

                                <div v-else-if="usesSingleModeDirectFlow" class="mb-4 rounded-[16px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                    Le mode de paiement actif est deja selectionne. Utilisez directement <span class="font-semibold text-slate-900">Valider le paiement</span>.
                                </div>

                                <div class="grid gap-2 sm:gap-3 xl:grid-cols-[minmax(0,1fr)_200px] 2xl:grid-cols-[minmax(0,1fr)_220px]">
                                    <div class="space-y-2 sm:space-y-2.5">
                                        <div class="grid gap-1.5 xl:hidden" :class="selectedMethod.id === 'cash' ? 'grid-cols-2 sm:grid-cols-3' : 'grid-cols-2'">
                                            <div class="rounded-[10px] border border-white/70 bg-white/80 px-2 py-1.5 sm:rounded-[12px] sm:px-2.5 sm:py-2">
                                                <p class="text-[8px] font-semibold uppercase tracking-[0.12em] text-slate-400">Affecté</p>
                                                <p class="mt-0.5 text-xs font-semibold text-slate-900 sm:text-sm">{{ formatCurrency(getMethodAssignedAmount(selectedMethod)) }}</p>
                                            </div>
                                            <div class="rounded-[10px] border border-white/70 bg-white/80 px-2 py-1.5 sm:rounded-[12px] sm:px-2.5 sm:py-2">
                                                <p class="text-[8px] font-semibold uppercase tracking-[0.12em] text-slate-400">Reste</p>
                                                <p class="mt-0.5 text-xs font-semibold sm:text-sm" :class="remaining <= 0 ? 'text-emerald-700' : 'text-amber-700'">{{ formatCurrency(remaining) }}</p>
                                            </div>
                                            <div v-if="selectedMethod.id === 'cash'" class="rounded-[10px] border border-white/70 bg-white/80 px-2 py-1.5 sm:rounded-[12px] sm:px-2.5 sm:py-2">
                                                <p class="text-[8px] font-semibold uppercase tracking-[0.12em] text-slate-400">Monnaie</p>
                                                <p class="mt-0.5 text-xs font-semibold text-slate-900 sm:text-sm">
                                                    {{ formatCurrency(calculateChange) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="rounded-[14px] border border-slate-200 bg-white p-3 shadow-sm sm:rounded-[16px] sm:p-4">
                                            <label class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Montant à encaisser</label>
                                            <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                                                <input
                                                    v-model.number="paymentForm.amount"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    :max="remaining + 0.01"
                                                    placeholder="0.00"
                                                    class="h-12 flex-1 rounded-[14px] border border-slate-300 bg-slate-50 px-4 text-lg font-semibold text-slate-900 outline-none transition focus:border-sky-300 focus:bg-white focus:ring-2 focus:ring-sky-100 sm:h-14 sm:text-xl sm:focus:ring-4"
                                                    @click="selectAllInputValue"
                                                    @focus="selectAllInputValue"
                                                >
                                                <div class="flex min-w-[120px] items-center justify-center rounded-[14px] border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-700 sm:min-w-[140px] sm:text-base">
                                                    {{ formatCurrency(paymentForm.amount || 0) }}
                                                </div>
                                            </div>
                                            <p v-if="amountExceedsRemaining" class="mt-2 text-sm font-medium text-amber-700">
                                                Dépasse le reste ({{ formatCurrency(remaining) }})
                                            </p>
                                        </div>

                                        <template v-if="selectedMethod.id === 'cash'">
                                            <div class="space-y-2 sm:space-y-2.5 xl:hidden">
                                                <div class="rounded-[14px] border border-slate-200 bg-white p-3 shadow-sm sm:rounded-[16px] sm:p-4">
                                                    <label class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Montant reçu</label>
                                                    <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                                                        <input
                                                            v-model.number="paymentForm.received_amount"
                                                            type="number"
                                                            step="0.01"
                                                            min="0"
                                                            placeholder="0.00"
                                                            class="h-12 flex-1 rounded-[14px] border border-slate-300 bg-slate-50 px-4 text-lg font-semibold text-slate-900 outline-none transition focus:border-sky-300 focus:bg-white focus:ring-2 focus:ring-sky-100 sm:h-14 sm:text-xl sm:focus:ring-4"
                                                            @click="selectAllInputValue"
                                                            @focus="selectAllInputValue"
                                                        >
                                                        <div class="flex min-w-[120px] items-center justify-center rounded-[14px] border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-700 sm:min-w-[140px] sm:text-base">
                                                            {{ settingsStore.currencyCode }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="rounded-[12px] border border-emerald-200 bg-emerald-50 p-2.5 shadow-sm sm:rounded-[14px] sm:p-3">
                                                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-600">Monnaie</p>
                                                    <p class="mt-1.5 text-base font-semibold text-emerald-700 sm:text-lg">{{ formatCurrency(calculateChange) }}</p>
                                                </div>
                                            </div>

                                            <div class="rounded-[14px] border border-slate-200 bg-white p-3 shadow-sm sm:rounded-[16px] sm:p-4">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Montants rapides</p>
                                                <div v-if="quickCashAmounts.length" class="mt-3 grid grid-cols-2 gap-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-3 2xl:grid-cols-4">
                                                    <button
                                                        v-for="amount in quickCashAmounts"
                                                        :key="amount"
                                                        type="button"
                                                        @click="paymentForm.received_amount = amount"
                                                        class="rounded-[14px] border border-slate-200 bg-slate-50 px-3 py-3 text-base font-semibold text-slate-700 transition hover:border-sky-300 hover:bg-sky-50 hover:text-sky-700 sm:text-lg"
                                                    >
                                                        {{ formatCurrency(amount) }}
                                                    </button>
                                                </div>
                                                <p v-else class="mt-3 text-sm text-slate-500">
                                                    Aucun montant rapide n'est affiche. Configurez les billets et la monnaie dans Parametres &gt; Devise.
                                                </p>
                                            </div>
                                        </template>

                                        <div v-if="selectedMethod.id !== 'cash' && hasVisibleExtraFields" class="rounded-[14px] border border-slate-200 bg-white p-3 shadow-sm sm:rounded-[16px] sm:p-3.5">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Infos</p>
                                            <div class="mt-2.5 grid grid-cols-1 gap-2.5 md:grid-cols-2">
                                                <div v-if="selectedMethod.show_transaction_number">
                                                    <label class="mb-1 block text-xs font-medium text-slate-700">{{ transactionFieldLabel }} *</label>
                                                    <input
                                                        v-model="paymentForm.transaction_number"
                                                        type="text"
                                                        :placeholder="transactionFieldPlaceholder"
                                                        class="w-full rounded-[10px] border border-slate-300 bg-slate-50 px-3 py-2 text-xs text-slate-900 outline-none transition focus:border-sky-300 focus:bg-white focus:ring-2 focus:ring-sky-100 sm:rounded-[12px]"
                                                    >
                                                </div>
                                                <div v-if="selectedMethod.show_piece_number">
                                                    <label class="mb-1 block text-xs font-medium text-slate-700 sm:mb-2 sm:text-sm">{{ pieceFieldLabel }} *</label>
                                                    <input
                                                        v-model="paymentForm.piece_number"
                                                        type="text"
                                                        :placeholder="pieceFieldPlaceholder"
                                                        class="w-full rounded-[10px] border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-sky-300 focus:bg-white focus:ring-2 focus:ring-sky-100 sm:rounded-[18px] sm:px-4 sm:py-3 sm:focus:ring-4"
                                                    >
                                                </div>
                                                <div v-if="selectedMethod.show_issue_date">
                                                    <label class="mb-1 block text-xs font-medium text-slate-700 sm:mb-2 sm:text-sm">Date d'émission *</label>
                                                    <input
                                                        v-model="paymentForm.issue_date"
                                                        type="date"
                                                        class="w-full rounded-[10px] border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-sky-300 focus:bg-white focus:ring-2 focus:ring-sky-100 sm:rounded-[18px] sm:px-4 sm:py-3 sm:focus:ring-4"
                                                    >
                                                </div>
                                                <div v-if="selectedMethod.show_due_date">
                                                    <label class="mb-1 block text-xs font-medium text-slate-700 sm:mb-2 sm:text-sm">Date d'échéance *</label>
                                                    <input
                                                        v-model="paymentForm.due_date"
                                                        type="date"
                                                        :min="paymentForm.issue_date || undefined"
                                                        class="w-full rounded-[10px] border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-sky-300 focus:bg-white focus:ring-2 focus:ring-sky-100 sm:rounded-[18px] sm:px-4 sm:py-3 sm:focus:ring-4"
                                                    >
                                                </div>
                                                <div v-if="selectedMethod.show_bank_name" class="md:col-span-2">
                                                    <label class="mb-1 block text-xs font-medium text-slate-700 sm:mb-2 sm:text-sm">{{ bankFieldLabel }} *</label>
                                                    <input
                                                        v-model="paymentForm.bank_name"
                                                        type="text"
                                                        :placeholder="bankFieldPlaceholder"
                                                        class="w-full rounded-[10px] border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-sky-300 focus:bg-white focus:ring-2 focus:ring-sky-100 sm:rounded-[18px] sm:px-4 sm:py-3 sm:focus:ring-4"
                                                    >
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="selectedMethod.show_notes" class="rounded-[12px] border border-slate-200 bg-white p-2.5 shadow-sm sm:rounded-[14px] sm:p-3">
                                            <label class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">Note</label>
                                            <textarea
                                                v-model="paymentForm.notes"
                                                placeholder="Remarques..."
                                                rows="3"
                                                class="mt-1.5 max-h-28 w-full resize-none overflow-y-auto rounded-[10px] border border-slate-300 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-900 outline-none transition focus:border-sky-300 focus:bg-white focus:ring-2 focus:ring-sky-100 sm:mt-2 sm:rounded-[12px] sm:px-3 sm:py-2 sm:text-sm sm:focus:ring-4"
                                            ></textarea>
                                        </div>

                                        <div v-if="selectedMethod.paymentTiming === 'deferred'" class="rounded-[12px] border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-800 sm:rounded-[14px] sm:px-3.5 sm:py-3 sm:text-sm">
                                            Ce mode de paiement est différé et sera suivi dans le module Encaissement.
                                        </div>

                                        <p class="rounded-[10px] border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[9px] text-slate-500 sm:rounded-[12px] sm:px-3 sm:py-2 sm:text-[10px]">
                                            Validez quand le ticket est soldé avec le bouton du bas.
                                        </p>
                                    </div>

                                    <div class="hidden space-y-3 sm:space-y-4 xl:block">
                                        <div class="overflow-hidden rounded-[18px] border border-slate-900/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.15)] sm:rounded-[24px] lg:rounded-[28px]" :class="selectedMethodTheme.previewShell">
                                            <div class="p-4 text-white sm:p-5">
                                                <div class="flex items-center justify-between gap-4">
                                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold backdrop-blur-sm sm:px-3 sm:text-xs" :class="selectedMethodTheme.previewBadgeClass">
                                                        {{ selectedMethodTheme.previewBadge }}
                                                    </span>
                                                    <span class="text-3xl sm:text-4xl">{{ selectedMethod.icon }}</span>
                                                </div>
                                                <div class="mt-6 sm:mt-8 lg:mt-10">
                                                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-white/70">{{ selectedMethodTheme.previewTitle }}</p>
                                                    <p class="mt-2.5 text-3xl font-semibold tracking-tight sm:mt-3 sm:text-4xl">{{ formatCurrency(paymentForm.amount || 0) }}</p>
                                                    <p class="mt-2.5 max-w-xs text-xs leading-5 text-white/80 sm:mt-3 sm:text-sm sm:leading-6">{{ selectedMethodTheme.previewText }}</p>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 border-t border-white/10 bg-black/10 text-white/90">
                                                <div class="px-4 py-3 sm:px-5 sm:py-4">
                                                    <p class="text-[11px] uppercase tracking-[0.18em] text-white/60">Affecté</p>
                                                    <p class="mt-1.5 text-lg font-semibold sm:mt-2 sm:text-xl">{{ formatCurrency(getMethodAssignedAmount(selectedMethod)) }}</p>
                                                </div>
                                                <div class="border-l border-white/10 px-4 py-3 sm:px-5 sm:py-4">
                                                    <p class="text-[11px] uppercase tracking-[0.18em] text-white/60">Reste</p>
                                                    <p class="mt-1.5 text-lg font-semibold sm:mt-2 sm:text-xl">{{ formatCurrency(remaining) }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                                            <div class="rounded-[16px] border border-slate-200 bg-white p-3 shadow-sm sm:rounded-[18px] sm:p-4 lg:rounded-[22px]">
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Résumé méthode</p>
                                                <p class="mt-2 text-base font-semibold text-slate-900 sm:mt-3 sm:text-lg">{{ selectedMethod.label }}</p>
                                                <p class="mt-1 text-xs text-slate-500 sm:text-sm">{{ selectedMethod.description }}</p>
                                            </div>
                                            <div class="rounded-[16px] border border-slate-200 bg-white p-3 shadow-sm sm:rounded-[18px] sm:p-4 lg:rounded-[22px]">
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Validation directe</p>
                                                <p class="mt-2 text-xs leading-5 text-slate-600 sm:mt-3 sm:text-sm sm:leading-6">
                                                    Utilisez <span class="font-semibold text-slate-900">Valider le paiement</span> directement pour le montant restant complet.
                                                </p>
                                            </div>
                                            <div v-if="selectedMethod.id === 'cash'" class="rounded-[16px] border border-emerald-200 bg-emerald-50 p-3 shadow-sm sm:rounded-[18px] sm:p-4 lg:rounded-[22px]">
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">Monnaie estimée</p>
                                                <p class="mt-2 text-2xl font-semibold text-emerald-700 sm:mt-3 sm:text-3xl">{{ formatCurrency(calculateChange) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="payments.length > 0" class="mt-2.5 rounded-[12px] border border-slate-200 bg-white p-2.5 shadow-sm sm:mt-3 sm:rounded-[14px] sm:p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-slate-400">Paiements</p>
                                        <h4 class="mt-0.5 text-xs font-semibold text-slate-900 sm:mt-1 sm:text-sm">{{ payments.length }} règlement{{ payments.length > 1 ? 's' : '' }}</h4>
                                    </div>
                                    <div class="rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-semibold text-slate-600 sm:px-2.5 sm:text-[10px]">
                                        {{ formatCurrency(totalPaid) }}
                                    </div>
                                </div>

                                <div class="mt-2 space-y-1.5">
                                    <div
                                        v-for="(payment, index) in payments"
                                        :key="index"
                                        class="flex flex-col gap-1.5 rounded-[10px] border border-slate-200 bg-slate-50 px-2 py-2 sm:flex-row sm:items-center sm:gap-2 sm:rounded-[12px] sm:px-3 sm:py-2.5"
                                    >
                                        <div class="flex min-w-0 flex-1 items-center gap-2">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-sm shadow-sm sm:h-9 sm:w-9 sm:rounded-xl sm:text-base">
                                                {{ findMethodIcon(payment) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-xs font-semibold text-slate-900 sm:text-sm">
                                                    {{ payment.display_label || getMethodLabel(payment.payment_type, payment.transfer_mode) }}
                                                </p>
                                                <p class="truncate text-[10px] text-slate-500 sm:text-xs">
                                                    {{ payment.transaction_number || payment.reference || 'Aucune réf.' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between gap-2 sm:justify-end">
                                            <p class="text-sm font-semibold text-slate-900 sm:text-base">{{ formatCurrency(payment.amount) }}</p>
                                            <button
                                                type="button"
                                                @click="removePayment(index)"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 bg-white text-rose-500 transition hover:bg-rose-50 sm:h-9 sm:w-9 sm:rounded-xl"
                                                title="Supprimer"
                                            >
                                                <TrashIcon class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sticky bottom-0 z-20 shrink-0 border-t border-slate-200 bg-white px-2.5 py-2.5 sm:px-4 sm:py-3 md:px-5 lg:px-6">
                            <div class="grid min-w-0 grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-2.5">
                                <button
                                    type="button"
                                    @click="$emit('close')"
                                    class="min-w-0 rounded-[12px] border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 sm:rounded-[14px] sm:px-4 sm:py-2.5 sm:text-sm md:rounded-[16px] md:px-5 md:py-3 lg:text-base"
                                >
                                    Annuler
                                </button>
                                <button
                                    v-if="selectedMethod"
                                    type="button"
                                    @click="addPayment"
                                    :disabled="!canAddPayment"
                                    class="min-w-0 rounded-[12px] bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-50 sm:rounded-[14px] sm:px-4 sm:py-2.5 sm:text-sm md:rounded-[16px] md:px-5 md:py-3 lg:text-base"
                                >
                                    <span class="inline-flex items-center justify-center gap-1.5">
                                        <PlusIcon class="h-4 w-4 sm:h-5 sm:w-5" />
                                        Ajouter ce paiement
                                    </span>
                                </button>
                                <button
                                    type="button"
                                    @click="confirmPayments"
                                    :disabled="!canConfirmPayments"
                                    class="col-span-2 min-w-0 rounded-[12px] bg-emerald-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-50 sm:col-span-1 sm:rounded-[14px] sm:px-4 sm:py-2.5 sm:text-sm md:rounded-[16px] md:px-5 md:py-3 lg:text-base"
                                >
                                    <span class="inline-flex items-center justify-center gap-1.5">
                                        <CheckIcon class="h-4 w-4 sm:h-5 sm:w-5" />
                                        <span class="hidden sm:inline">{{ confirmLabelText }}</span>
                                        <span class="sm:hidden">Valider</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useSettingsStore } from '../../stores/settings'
import { useCustomListsStore } from '../../stores/customLists'
import {
    ArrowLeftIcon,
    PlusIcon,
    TrashIcon,
    CheckIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    total: {
        type: Number,
        required: true
    },
    sale: {
        type: Object,
        default: null
    },
    allowPartialConfirmation: {
        type: Boolean,
        default: false
    },
    totalMode: {
        type: String,
        default: 'remaining'
    },
    confirmLabel: {
        type: String,
        default: 'Valider le paiement'
    }
})

const emit = defineEmits(['close', 'complete'])

const settingsStore = useSettingsStore()
const customListsStore = useCustomListsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const saleSummary = computed(() => props.sale?.payment_summary || null)
const paidConfirmedAmount = computed(() => Number(saleSummary.value?.paid_confirmed_amount || 0))
const pendingCollectionAmount = computed(() => Number(saleSummary.value?.pending_collection_amount || 0))
const coveredByExistingPayments = computed(() => {
    if (props.totalMode !== 'gross') {
        return 0
    }

    return normalizeAmount(paidConfirmedAmount.value + pendingCollectionAmount.value)
})

const selectedMethod = ref(null)
const payments = ref([])
const paymentForm = ref(getEmptyForm())
const paymentNotice = ref('')

const paymentMethods = computed(() => {
    return customListsStore.activePaymentModes.map((item, index) => ({
        id: createMethodId(item, index),
        paymentType: item.payment_type || 'other',
        transferMode: item.transfer_mode || null,
        label: item.label,
        description: describePaymentMethod(item),
        icon: iconForPaymentMethod(item),
        isDefault: item.is_default === true,
        paymentTiming: item.payment_timing === 'deferred' ? 'deferred' : 'immediate',
        show_transaction_number: item.show_transaction_number === true,
        show_piece_number: item.show_piece_number === true,
        show_issue_date: item.show_issue_date === true,
        show_due_date: item.show_due_date === true,
        show_bank_name: item.show_bank_name === true,
        show_notes: item.show_notes !== false,
    }))
})

function getEmptyForm() {
    return {
        amount: 0,
        payment_type: null,
        received_amount: null,
        transaction_number: '',
        piece_number: '',
        issue_date: '',
        bank_name: '',
        due_date: '',
        notes: ''
    }
}

function normalizeAmount(value) {
    return Math.round(Number(value || 0) * 100) / 100
}

const totalPaid = computed(() => normalizeAmount(
    payments.value.reduce((sum, payment) => sum + normalizeAmount(payment.amount), 0)
))
const payableTotal = computed(() => {
    const normalizedTotal = normalizeAmount(props.total)

    if (props.totalMode === 'gross') {
        return normalizeAmount(Math.max(0, normalizedTotal - coveredByExistingPayments.value))
    }

    return normalizedTotal
})
const remaining = computed(() => normalizeAmount(Math.max(0, payableTotal.value - totalPaid.value)))
const amountExceedsRemaining = computed(() => {
    return normalizeAmount(paymentForm.value.amount) > remaining.value + 0.00001
})

const calculateChange = computed(() => {
    if (!paymentForm.value.received_amount || !paymentForm.value.amount) return 0
    return Math.max(0, paymentForm.value.received_amount - paymentForm.value.amount)
})

const quickCashAmounts = computed(() => {
    return settingsStore.quickCashDenominations
})

function matchesMethod(payment, method) {
    const paymentType = payment.payment_type || payment.type
    const normalizedType = paymentType === 'check' ? 'cheque' : paymentType

    if (method.paymentType === 'virement' || ['simple_transfer', 'instant_transfer'].includes(method.id)) {
        const methodTransferMode = method.transferMode || (method.id === 'instant_transfer' ? 'instant' : 'simple')
        const paymentTransferMode = normalizedType === 'instant_transfer'
            ? 'instant'
            : (normalizedType === 'simple_transfer' ? 'simple' : (payment.transfer_mode || 'simple'))

        return (normalizedType === 'virement' || normalizedType === 'simple_transfer' || normalizedType === 'instant_transfer')
            && methodTransferMode === paymentTransferMode
    }

    return method.paymentType === normalizedType
}

function getMethodAssignedAmount(method) {
    return payments.value.reduce((sum, payment) => {
        if (!matchesMethod(payment, method)) {
            return sum
        }

        return sum + normalizeAmount(payment.amount)
    }, 0)
}

function paymentTimingLabel(method) {
    return method?.paymentTiming === 'deferred' ? 'Différé' : 'Immédiat'
}

function findMethodIcon(payment) {
    const method = paymentMethods.value.find((item) => matchesMethod(payment, item))
    return method?.icon || '🧾'
}

function resolveMethodTheme(method) {
    if (!method) {
        return {
            panelShell: 'border-slate-200 bg-white',
            chipClass: 'bg-slate-100 text-slate-600',
            previewShell: 'bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700',
            previewBadgeClass: 'bg-white/15 text-white',
            previewBadge: 'Paiement',
            previewTitle: 'Validation',
            previewText: 'Paiement prêt à être enregistré.',
            description: 'Renseignez les informations nécessaires puis enregistrez ou validez.',
        }
    }

    if (method.id === 'cash') {
        return {
            panelShell: 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-emerald-100/80',
            chipClass: 'bg-emerald-100 text-emerald-700',
            previewShell: 'bg-gradient-to-br from-emerald-700 via-emerald-600 to-emerald-500',
            previewBadgeClass: 'bg-white/15 text-white',
            previewBadge: 'Espèces',
            previewTitle: 'Station caisse',
            previewText: 'Encaissement immédiat avec billets reçus et calcul automatique de la monnaie.',
            description: 'Saisissez le montant reçu ou utilisez les montants rapides pour aller plus vite en caisse.',
        }
    }

    if (method.id === 'card') {
        return {
            panelShell: 'border-blue-200 bg-gradient-to-br from-blue-50 via-white to-sky-100/70',
            chipClass: 'bg-blue-100 text-blue-700',
            previewShell: 'bg-gradient-to-br from-blue-900 via-blue-700 to-sky-500',
            previewBadgeClass: 'bg-white/15 text-white',
            previewBadge: 'Carte',
            previewTitle: 'Terminal carte',
            previewText: 'Confirmez la référence de transaction avant validation du règlement.',
            description: 'Paiement électronique immédiat avec référence de transaction.',
        }
    }

    if (method.id === 'mobile') {
        return {
            panelShell: 'border-fuchsia-200 bg-gradient-to-br from-fuchsia-50 via-white to-violet-100/70',
            chipClass: 'bg-fuchsia-100 text-fuchsia-700',
            previewShell: 'bg-gradient-to-br from-fuchsia-900 via-violet-700 to-fuchsia-500',
            previewBadgeClass: 'bg-white/15 text-white',
            previewBadge: 'Mobile',
            previewTitle: 'Paiement mobile',
            previewText: 'Gardez le numéro de transaction à portée de main pour un encaissement rapide.',
            description: 'Paiement mobile avec saisie de la référence de transaction.',
        }
    }

    if (method.paymentType === 'cheque') {
        return {
            panelShell: 'border-amber-200 bg-gradient-to-br from-amber-50 via-white to-orange-100/70',
            chipClass: 'bg-amber-100 text-amber-700',
            previewShell: 'bg-gradient-to-br from-amber-900 via-amber-700 to-orange-500',
            previewBadgeClass: 'bg-white/15 text-white',
            previewBadge: 'Chèque',
            previewTitle: 'Instrument différé',
            previewText: 'Renseignez les références et dates avant l’enregistrement pour le suivi d’encaissement.',
            description: 'Paiement différé avec numéro de chèque, banque et dates de suivi.',
        }
    }

    if (method.paymentType === 'credit') {
        return {
            panelShell: 'border-indigo-200 bg-gradient-to-br from-indigo-50 via-white to-slate-100',
            chipClass: 'bg-indigo-100 text-indigo-700',
            previewShell: 'bg-gradient-to-br from-indigo-900 via-slate-800 to-indigo-500',
            previewBadgeClass: 'bg-white/15 text-white',
            previewBadge: 'Crédit',
            previewTitle: 'Lettre de change',
            previewText: 'Préparez le dossier de crédit avec la pièce et l’échéance de règlement.',
            description: 'Paiement différé type crédit ou lettre de change avec pièce justificative.',
        }
    }

    if (method.paymentType === 'virement') {
        return {
            panelShell: 'border-cyan-200 bg-gradient-to-br from-cyan-50 via-white to-sky-100/70',
            chipClass: 'bg-cyan-100 text-cyan-700',
            previewShell: 'bg-gradient-to-br from-cyan-900 via-sky-700 to-cyan-500',
            previewBadgeClass: 'bg-white/15 text-white',
            previewBadge: method.transferMode === 'instant' ? 'Virement instantané' : 'Virement simple',
            previewTitle: 'Transfert bancaire',
            previewText: 'Utilisez la référence bancaire pour rattacher le règlement au bon transfert.',
            description: method.transferMode === 'instant'
                ? 'Paiement par virement instantané avec validation immédiate.'
                : 'Virement bancaire simple avec référence et éventuel suivi.',
        }
    }

    return {
        panelShell: 'border-slate-200 bg-gradient-to-br from-slate-50 via-white to-slate-100',
        chipClass: 'bg-slate-100 text-slate-700',
        previewShell: 'bg-gradient-to-br from-slate-900 via-slate-800 to-slate-600',
        previewBadgeClass: 'bg-white/15 text-white',
        previewBadge: method.label,
        previewTitle: 'Mode personnalisé',
        previewText: 'Complétez les informations demandées puis validez selon votre flux de caisse.',
        description: method.description || 'Mode personnalisé.',
    }
}

function getDraftPayment() {
    if (!selectedMethod.value) {
        return null
    }

    const amount = normalizeAmount(paymentForm.value.amount)
    if (amount <= 0 || amountExceedsRemaining.value) {
        return null
    }

    const requiredFields = [
        !selectedMethod.value.show_transaction_number || !!String(paymentForm.value.transaction_number || '').trim(),
        !selectedMethod.value.show_piece_number || !!String(paymentForm.value.piece_number || '').trim(),
        !selectedMethod.value.show_issue_date || !!paymentForm.value.issue_date,
        !selectedMethod.value.show_due_date || !!paymentForm.value.due_date,
        !selectedMethod.value.show_bank_name || !!String(paymentForm.value.bank_name || '').trim(),
    ]

    if (!requiredFields.every(Boolean)) {
        return null
    }

    const receivedAmount = selectedMethod.value.id === 'cash'
        ? normalizeAmount(paymentForm.value.received_amount)
        : null

    if (selectedMethod.value.id === 'cash' && receivedAmount < amount) {
        return null
    }

    const apiPaymentType = ['simple_transfer', 'instant_transfer'].includes(selectedMethod.value.id)
        ? selectedMethod.value.id
        : selectedMethod.value.paymentType
    const transferMode = selectedMethod.value.id === 'simple_transfer'
        ? 'simple'
        : (selectedMethod.value.id === 'instant_transfer' ? 'instant' : null)

    return {
        payment_type: apiPaymentType,
        transfer_mode: transferMode,
        amount,
        received_amount: receivedAmount,
        change_amount: selectedMethod.value.id === 'cash' ? calculateChange.value : 0,
        transaction_number: String(paymentForm.value.transaction_number || '').trim(),
        piece_number: String(paymentForm.value.piece_number || '').trim(),
        issue_date: paymentForm.value.issue_date,
        bank_name: String(paymentForm.value.bank_name || '').trim(),
        due_date: paymentForm.value.due_date,
        reference: selectedMethod.value.id === 'credit'
            ? String(paymentForm.value.piece_number || '').trim()
            : String(paymentForm.value.transaction_number || '').trim() || String(paymentForm.value.piece_number || '').trim(),
        notes: encodePaymentModeLabel(selectedMethod.value.label, selectedMethod.value.paymentTiming, paymentForm.value.notes),
        display_label: selectedMethod.value.label,
    }
}

const draftPayment = computed(() => getDraftPayment())
const selectedMethodTheme = computed(() => resolveMethodTheme(selectedMethod.value))
const hasSingleActivePaymentMode = computed(() => paymentMethods.value.length === 1)
const usesSingleModeDirectFlow = computed(() => hasSingleActivePaymentMode.value && !props.allowPartialConfirmation)
const draftMatchesRemaining = computed(() => {
    if (!draftPayment.value) {
        return false
    }

    return Math.abs(normalizeAmount(draftPayment.value.amount) - remaining.value) <= 0.00001
})
const remainingAfterDirectConfirmation = computed(() => {
    if (!draftPayment.value) {
        return remaining.value
    }

    return Math.max(0, remaining.value - normalizeAmount(draftPayment.value.amount))
})
const canAddPayment = computed(() => !!draftPayment.value)
const canConfirmPayments = computed(() => {
    if (payments.value.length > 0) {
        return props.allowPartialConfirmation || remaining.value <= 0.00001
    }

    return draftMatchesRemaining.value && remainingAfterDirectConfirmation.value <= 0.00001
})

const confirmLabelText = computed(() => props.confirmLabel || 'Valider le paiement')
const singleModeInfoMessage = computed(() => {
    if (!usesSingleModeDirectFlow.value || !selectedMethod.value) {
        return ''
    }

    return `Le mode de paiement actif est : ${selectedMethod.value.label}. Pour ajouter d'autres modes, accedez a Parametres > Modes de paiement.`
})

const hasVisibleExtraFields = computed(() => {
    if (!selectedMethod.value) return false

    return [
        selectedMethod.value.show_transaction_number,
        selectedMethod.value.show_piece_number,
        selectedMethod.value.show_issue_date,
        selectedMethod.value.show_due_date,
        selectedMethod.value.show_bank_name,
    ].some(Boolean)
})

const transactionFieldLabel = computed(() => {
    if (selectedMethod.value?.paymentType === 'virement') return 'N° de transaction'
    return 'N° transaction'
})

const transactionFieldPlaceholder = computed(() => {
    if (selectedMethod.value?.paymentType === 'virement') return 'N° opération bancaire'
    return 'Ex: 12345678, ABC123XYZ'
})

const pieceFieldLabel = computed(() => {
    if (selectedMethod.value?.paymentType === 'credit') return 'N° pièce'
    return 'N° pièce'
})

const pieceFieldPlaceholder = computed(() => {
    if (selectedMethod.value?.paymentType === 'credit') return 'Référence dossier / effet / LCN'
    return 'CIN / justificatif'
})

const bankFieldLabel = computed(() => 'Banque')
const bankFieldPlaceholder = computed(() => 'Nom de la banque')

function normalizeKey(value) {
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
}

function createMethodId(item, index) {
    if (item.payment_type === 'cash') return 'cash'
    if (item.payment_type === 'card') return 'card'
    if (item.payment_type === 'mobile') return 'mobile'
    if (item.payment_type === 'credit') return 'credit'
    if (item.payment_type === 'virement' && item.transfer_mode === 'instant') return 'instant_transfer'
    if (item.payment_type === 'virement') return 'simple_transfer'

    return `other:${normalizeKey(item.label || item.value || index)}`
}

function iconForPaymentMethod(item) {
    if (item.payment_type === 'cash') return '💵'
    if (item.payment_type === 'card') return '💳'
    if (item.payment_type === 'mobile') return '📱'
    if (item.payment_type === 'virement' && item.transfer_mode === 'instant') return '⚡'
    if (item.payment_type === 'virement') return '🏦'
    if (item.payment_type === 'credit') return '📋'
    return '🧾'
}

function describePaymentMethod(item) {
    if (item.payment_type === 'cash') return 'Paiement en liquide'
    if (item.payment_type === 'card') return 'Carte bancaire'
    if (item.payment_type === 'mobile') return 'Paiement mobile'
    if (item.payment_type === 'virement' && item.transfer_mode === 'instant') return 'Transfert bancaire rapide'
    if (item.payment_type === 'virement') return 'Transfert bancaire standard'
    if (item.payment_type === 'credit') return 'Paiement différé'
    return 'Mode personnalisé'
}

function encodePaymentModeLabel(label, paymentTiming, notes) {
    const cleanNotes = String(notes || '')
        .replace(/\[PAYMENT_TIMING:[^\]]+\]\s*/g, '')
        .replace(/\[PAYMENT_MODE_LABEL:[^\]]+\]\s*/g, '')
        .trim()
    const cleanLabel = String(label || '').trim()
    const timing = paymentTiming === 'deferred' ? 'deferred' : 'immediate'
    const timingMarker = `[PAYMENT_TIMING:${timing}]`

    if (!cleanLabel) {
        return cleanNotes ? `${timingMarker} ${cleanNotes}` : timingMarker
    }

    const marker = `[PAYMENT_MODE_LABEL:${cleanLabel}]`
    return cleanNotes ? `${marker} ${timingMarker} ${cleanNotes}` : `${marker} ${timingMarker}`
}

function selectDefaultMethod() {
    if (!paymentMethods.value.length) {
        selectedMethod.value = null
        paymentForm.value = getEmptyForm()
        return
    }

    const configuredDefault = paymentMethods.value.find((method) => method.isDefault)
    const cashMethod = paymentMethods.value.find((method) => method.paymentType === 'cash')
    selectMethod(configuredDefault || cashMethod || paymentMethods.value[0])
}

function selectMethod(method) {
    paymentNotice.value = ''
    selectedMethod.value = method
    const suggestedAmount = normalizeAmount(remaining.value)
    paymentForm.value = {
        ...getEmptyForm(),
        payment_type: method.paymentType,
        amount: suggestedAmount,
        received_amount: method.id === 'cash' ? suggestedAmount : null,
    }
}

function addPayment() {
    if (!draftPayment.value) return
    if (usesSingleModeDirectFlow.value) {
        paymentNotice.value = singleModeInfoMessage.value
        return
    }

    payments.value.push(draftPayment.value)

    selectDefaultMethod()
}

function removePayment(index) {
    payments.value.splice(index, 1)

    if (selectedMethod.value) {
        selectMethod(selectedMethod.value)
    } else {
        selectDefaultMethod()
    }
}

function getMethodLabel(type, transferMode = null) {
    const method = paymentMethods.value.find((item) => {
        if (item.paymentType !== type) {
            return false
        }

        if (item.paymentType !== 'virement') {
            return true
        }

        return (item.transferMode || 'simple') === (transferMode || 'simple')
    })

    return method?.label || type
}

function confirmPayments() {
    if (!canConfirmPayments.value) return

    paymentNotice.value = ''
    if (payments.value.length > 0) {
        emit('complete', payments.value)
        return
    }

    emit('complete', [draftPayment.value])
}

function selectAllInputValue(event) {
    event?.target?.select?.()
}

watch(paymentMethods, () => {
    if (!paymentMethods.value.length) {
        selectedMethod.value = null
        paymentForm.value = getEmptyForm()
        return
    }

    const stillExists = paymentMethods.value.find((method) => method.id === selectedMethod.value?.id)
    if (!stillExists) {
        selectDefaultMethod()
    }
}, { immediate: true })

onMounted(async () => {
    await Promise.all([
        customListsStore.fetchList('mode_de_paiement', { force: true }),
        settingsStore.fetchSettings(),
    ])
    selectDefaultMethod()
})
</script>
