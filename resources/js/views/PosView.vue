<template>
    <div ref="posRoot" class="h-screen flex flex-col bg-[#f4f3ef] overflow-hidden">
        <header class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 px-4 py-3 bg-gray-900 text-white">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button
                    @click="toggleAppSidebar"
                    class="p-2 bg-white/10 rounded-lg hover:bg-white/20 transition-colors"
                    type="button"
                    title="Afficher/Masquer le menu"
                >
                    <Bars3Icon class="w-5 h-5" />
                </button>
                <div>
                    <p class="text-xs uppercase text-gray-400">Catégorie active</p>
                    <p class="text-lg font-semibold">{{ selectedCategoryName }}</p>
                    <p class="text-xs text-gray-400">{{ totalArticles }} articles</p>
                </div>
            </div>
            <div class="flex-1 w-full">
                <div class="relative">
                    <input
                        ref="searchField"
                        v-model="searchQuery"
                        type="text"
                        autofocus
                        placeholder="Rechercher par nom ou code-barres"
                        class="w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-2.5 text-sm text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                    <MagnifyingGlassIcon class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" />
                </div>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-end">
                <button
                    @click="toggleMobileCart"
                    class="sm:hidden flex items-center gap-2 px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"
                    type="button"
                >
                    <ShoppingCartIcon class="w-5 h-5" />
                    <span class="text-xs font-semibold">{{ cartStore.items.length }}</span>
                </button>
                <button
                    @click="toggleFullscreen"
                    class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"
                    type="button"
                    :title="isFullscreen ? 'Quitter le plein écran' : 'Plein écran'"
                >
                    <span class="text-lg leading-none">⛶</span>
                </button>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            <div
                v-if="isMobile && uiStore.posSidebarOpen"
                class="fixed inset-0 bg-black/40 z-40"
                @click="uiStore.closePosSidebar()"
            ></div>
            <transition name="fade">
                <aside
                    v-if="effectiveCategoriesDisplayMode === 'sidebar' && (!isMobile || uiStore.posSidebarOpen)"
                    :class="isMobile ? 'fixed inset-y-0 left-0 z-50 w-72 border-r border-gray-200 bg-white flex flex-col shadow-2xl' : 'w-64 border-r border-gray-200 bg-white flex flex-col'"
                >
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Catégories</p>
                        <h3 class="text-base font-semibold text-gray-900">Navigation</h3>
                    </div>
                    <div class="flex-1 overflow-y-auto px-3 py-3 space-y-2">
                        <button
                            v-for="button in categoryButtons"
                            :key="button.id"
                            @click="selectCategory(button.id)"
                            type="button"
                            class="w-full flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-colors"
                            :class="selectedCategoryId === button.id ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-100'"
                        >
                            <span class="text-2xl">{{ button.icon }}</span>
                            <span class="flex-1 text-left truncate">{{ button.label }}</span>
                        </button>
                    </div>
                </aside>
            </transition>

            <div class="flex-1 flex flex-col overflow-hidden">
                <div v-if="effectiveCategoriesDisplayMode === 'top' && !appSidebarOpen" class="border-b border-gray-200 bg-white px-3 py-2">
                        <div class="flex gap-3 overflow-x-auto">
                        <button
                            v-for="button in categoryButtons"
                            :key="button.id + '-top'"
                            @click="selectCategory(button.id)"
                            type="button"
                                class="flex items-center gap-2 rounded-full border px-3 py-2 text-[11px] font-semibold uppercase tracking-wide transition-colors whitespace-nowrap"
                            :class="selectedCategoryId === button.id ? 'border-primary-500 bg-primary-50 text-primary-600' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                        >
                                <span class="text-lg">{{ button.icon }}</span>
                            <span>{{ button.label }}</span>
                        </button>
                    </div>
                </div>
                <div class="flex-1 overflow-hidden p-4 bg-[#f8f8f8]" :class="contentPaddingClass">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
                        <div
                            v-for="article in paginatedArticles"
                            :key="article.id"
                            class="cursor-pointer rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-lg transition-shadow h-full flex flex-col"
                            @click="addToCart(article)"
                        >
                            <div class="aspect-[4/3] rounded-t-2xl bg-gray-100 flex items-center justify-center overflow-hidden">
                                <img
                                    v-if="article.photo"
                                    :src="article.photo"
                                    :alt="article.name"
                                    class="h-full w-full object-cover"
                                >
                                <span v-else class="text-3xl">📦</span>
                            </div>
                            <div class="p-3 space-y-1">
                                <h3 class="text-sm font-semibold text-gray-900 truncate">{{ article.name }}</h3>
                                <p class="text-base font-bold text-primary-600">{{ formatCurrency(article.sell_price) }}</p>
                            </div>
                        </div>
                        <div v-if="paginatedArticles.length === 0" class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white/80 p-6 text-center text-sm text-gray-500">
                            Aucun produit ne correspond à cette recherche.
                        </div>
                    </div>
                </div>

                <div v-if="totalArticles > itemsPerPage" class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200">
                    <p class="text-sm text-gray-500">{{ pageSummary }}</p>
                    <div class="flex items-center gap-2">
                        <button
                            @click="prevPage"
                            :disabled="currentPage === 1"
                            class="px-3 py-1 rounded-lg border border-gray-300 text-sm font-semibold disabled:opacity-40"
                        >
                            Précédent
                        </button>
                        <button
                            @click="nextPage"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1 rounded-lg border border-primary-500 text-sm font-semibold text-primary-600 disabled:opacity-40"
                        >
                            Suivant
                        </button>
                    </div>
                </div>

                <div v-if="effectiveCategoriesDisplayMode === 'bottom'" class="border-t border-gray-200 bg-white px-3 py-2">
                        <div class="flex gap-3 overflow-x-auto">
                        <button
                            v-for="button in categoryButtons"
                            :key="button.id + '-bottom'"
                            @click="selectCategory(button.id)"
                            type="button"
                                class="flex items-center gap-2 rounded-full border px-3 py-2 text-xs font-semibold uppercase tracking-wide transition-colors"
                            :class="selectedCategoryId === button.id ? 'border-primary-500 bg-primary-50 text-primary-600' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                        >
                                <span class="text-lg">{{ button.icon }}</span>
                            <span>{{ button.label }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <section
                class="bg-[#f2f2f4] flex flex-col transition-all duration-300"
                :class="ticketPanelClass"
            >
                <!-- Mobile ticket handle strip — 3 states: collapsed / half / fullscreen -->
                <div
                    class="sm:hidden flex flex-col items-center cursor-pointer select-none transition-all duration-300 shrink-0"
                    :class="isCartExpanded ? 'bg-white border-b border-gray-100' : 'bg-gradient-to-r from-blue-600 to-blue-500 rounded-t-2xl shadow-lg'"
                    @click="toggleMobileCart"
                >
                    <!-- Drag handle pill -->
                    <span
                        class="mt-2.5 block w-12 h-1.5 rounded-full transition-colors duration-300"
                        :class="isCartExpanded ? 'bg-gray-300' : 'bg-white/50'"
                    ></span>

                    <!-- Info row -->
                    <div class="w-full flex items-center justify-between px-4 py-2">
                        <!-- Left: icon + label + badge -->
                        <div class="flex items-center gap-2">
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-full transition-colors duration-300"
                                :class="isCartExpanded ? 'bg-blue-50' : 'bg-white/20'"
                            >
                                <ShoppingCartIcon
                                    class="w-4 h-4 transition-colors duration-300"
                                    :class="isCartExpanded ? 'text-blue-600' : 'text-white'"
                                />
                            </div>
                            <span
                                class="text-sm font-bold transition-colors duration-300"
                                :class="isCartExpanded ? 'text-gray-800' : 'text-white'"
                            >Ticket</span>
                            <span
                                v-if="cartStore.items.length > 0"
                                class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 rounded-full text-[11px] font-bold transition-colors duration-300"
                                :class="isCartExpanded ? 'bg-blue-600 text-white' : 'bg-white text-blue-600'"
                            >{{ cartStore.items.length }}</span>
                        </div>

                        <!-- Right: total + state indicator -->
                        <div class="flex items-center gap-2">
                            <span
                                class="text-sm font-bold transition-colors duration-300"
                                :class="isCartExpanded ? 'text-green-600' : 'text-white'"
                            >{{ formatCurrency(cartStore.total) }}</span>

                            <!-- Chevron: up when collapsed, double-up when half, X when fullscreen -->
                            <div
                                class="flex items-center justify-center w-7 h-7 rounded-full transition-all duration-300"
                                :class="isCartExpanded ? 'bg-gray-100' : 'bg-white/20'"
                            >
                                <!-- Collapsed: single chevron up -->
                                <svg v-if="!isCartExpanded"
                                    class="w-4 h-4 text-white"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
                                </svg>
                                <!-- Half: double chevron up (swipe for more) -->
                                <svg v-else-if="!isCartFullscreen"
                                    class="w-4 h-4 text-gray-500"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 17l7-7 7 7M5 11l7-7 7 7" />
                                </svg>
                                <!-- Fullscreen: X to close -->
                                <svg v-else
                                    class="w-4 h-4 text-gray-500"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Collapsed hint — pulsing dots + label -->
                    <div v-if="!isCartExpanded" class="w-full px-4 pb-2.5 flex items-center justify-center gap-1.5">
                        <span class="relative flex h-2 w-2 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-white/80"></span>
                        </span>
                        <p class="text-[11px] text-white/90 font-semibold tracking-wide">
                            Appuyez ici pour voir le détail du ticket
                        </p>
                        <span class="relative flex h-2 w-2 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-white/80"></span>
                        </span>
                    </div>

                    <!-- Half hint — subtle nudge to go fullscreen -->
                    <div v-if="isCartExpanded && !isCartFullscreen" class="w-full pb-1 flex items-center justify-center">
                        <p class="text-[10px] text-gray-400 tracking-wide">Appuyez encore pour plein écran</p>
                    </div>
                </div>

                <div class="flex-1 flex flex-col overflow-hidden h-full">
                    <!-- Desktop: styled card wrapper -->
                    <div class="hidden sm:flex flex-col p-4 h-full">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 flex flex-col flex-1 overflow-hidden">
                            <!-- Client -->
                            <div class="p-4 border-b border-gray-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-800">
                                        <span>Client</span>
                                        <span class="text-gray-400">:</span>
                                    </div>
                                    <button @click="showCustomerSelector = !showCustomerSelector" class="p-1.5 rounded-lg text-blue-600 hover:text-blue-700 hover:bg-blue-50" type="button" title="Ajouter client">
                                        <UserPlusIcon class="w-5 h-5" />
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500">{{ cartStore.customerId ? cartStore.customerName : 'Aucun client sélectionné' }}</p>
                                <div v-if="showCustomerSelector" class="space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                    <input v-model="customerSearch" type="text" placeholder="Chercher un client..." class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <div class="max-h-32 overflow-y-auto space-y-1">
                                        <button v-for="customer in filteredCustomers" :key="customer.id" @click="selectCustomer(customer)" class="w-full text-left rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-white transition-colors" type="button">{{ customer.name }}</button>
                                        <p v-if="filteredCustomers.length === 0" class="text-xs text-gray-500 px-3 py-2">Aucun client trouvé.</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Service mode -->
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <div class="flex items-center gap-2 overflow-x-auto whitespace-nowrap">
                                    <button v-for="mode in serviceModes" :key="mode.value" @click="serviceMode = mode.value" type="button" class="flex items-center gap-2 rounded-lg border px-3 py-2 text-xs font-semibold transition-colors shrink-0" :class="serviceMode === mode.value ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'">
                                        <span class="text-base">{{ mode.icon }}</span>
                                        <span>{{ mode.label }}</span>
                                    </button>
                                </div>
                            </div>
                            <!-- Articles header -->
                            <div class="px-4 py-2 border-b border-gray-200 flex items-center justify-between text-[11px] uppercase tracking-wide text-gray-500">
                                <span>Articles</span>
                                <span>{{ cartStore.items.length }} articles</span>
                            </div>
                            <!-- Articles list -->
                            <div class="flex-1 overflow-y-auto">
                                <div v-if="cartStore.items.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                                    <ShoppingCartIcon class="w-12 h-12 mb-2" />
                                    <p class="text-sm">Ticket vide</p>
                                </div>
                                <div v-else class="divide-y divide-dashed divide-gray-200">
                                    <div v-for="(item, index) in cartStore.items" :key="index" class="px-4 py-2">
                                        <div class="cursor-pointer" @click="openItemEditModal(index, item)">
                                            <p class="text-sm font-semibold text-gray-900 leading-tight break-words">{{ item.article_name }}</p>
                                        </div>
                                        <div class="mt-1 flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center gap-1 rounded-lg bg-gray-100 px-1.5 py-1">
                                                    <button @click.stop="updateQuantity(index, item.quantity - 1)" class="p-1 rounded-md text-gray-600 hover:text-gray-800" type="button"><MinusIcon class="w-4 h-4" /></button>
                                                    <span class="text-xs font-semibold text-gray-700">x{{ item.quantity }}</span>
                                                    <button @click.stop="updateQuantity(index, item.quantity + 1)" class="p-1 rounded-md text-gray-600 hover:text-gray-800" type="button"><PlusIcon class="w-4 h-4" /></button>
                                                </div>
                                                <span class="text-xs text-gray-500">{{ formatCurrency(item.unit_price + (item.variant_price || 0) + (item.options_price || 0)) }}/pcs</span>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <span class="text-sm font-semibold text-gray-900">{{ formatCurrency(getItemLineTotal(item)) }}</span>
                                                <button @click.stop="removeItem(index)" class="text-red-500 hover:text-red-700" title="Supprimer" type="button"><TrashIcon class="w-4 h-4" /></button>
                                            </div>
                                        </div>
                                        <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                            <span v-if="getVariantDisplay(item)" class="text-[11px] font-semibold text-gray-700 bg-orange-50 border border-orange-200 rounded-full px-2 py-0.5">{{ getVariantDisplay(item).label }} <span class="ml-1 text-orange-600">+{{ formatCurrency(getVariantDisplay(item).price) }}</span></span>
                                            <span v-for="option in getOptionDisplays(item)" :key="option.key" class="text-[11px] font-semibold text-gray-700 bg-blue-50 border border-blue-200 rounded-full px-2 py-0.5">{{ option.label }} <span class="ml-1 text-blue-600">+{{ formatCurrency(option.price) }}</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Totals -->
                            <div class="px-4 py-3 border-t border-gray-200 space-y-2 text-sm">
                                <div class="flex justify-between text-gray-600 border-b border-dashed border-gray-200 pb-2"><span class="font-medium">Total HT :</span><span class="font-semibold text-gray-900">{{ formatCurrency(cartStore.subtotal) }}</span></div>
                                <div class="flex justify-between text-gray-600 border-b border-dashed border-gray-200 pb-2"><span class="font-medium">TVA :</span><span class="font-semibold text-gray-900">{{ formatCurrency(cartStore.taxAmount) }}</span></div>
                                <div class="flex justify-between text-gray-600 border-b border-dashed border-gray-200 pb-2"><span class="font-medium">Remise :</span><span class="font-semibold text-gray-900">{{ formatCurrency(cartStore.discountTotal) }}</span></div>
                                <div class="flex items-baseline justify-between pt-2"><span class="text-lg font-bold text-gray-900">TOTAL TTC :</span><span class="text-2xl font-bold text-green-600">{{ formatCurrency(cartStore.total) }}</span></div>
                            </div>
                            <!-- Buttons -->
                            <div class="px-4 py-3 border-t border-gray-200 space-y-2">
                                <button @click="showPaymentModal = true" :disabled="cartStore.items.length === 0" class="w-full py-3 px-4 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" type="button">PASSER AU PAIEMENT</button>
                                <button @click="saveSale" :disabled="cartStore.items.length === 0" class="w-full py-3 px-4 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" type="button">SAUVEGARDER</button>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile: flat layout (no extra card) -->
                    <div v-show="isCartExpanded" class="sm:hidden flex flex-col flex-1 overflow-hidden bg-white">
                        <!-- Client row (compact) -->
                        <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <UserPlusIcon class="w-4 h-4 text-blue-500 shrink-0" />
                                <p class="text-xs text-gray-500 truncate">{{ cartStore.customerId ? cartStore.customerName : 'Aucun client' }}</p>
                            </div>
                            <button @click="showCustomerSelector = !showCustomerSelector" class="text-[11px] font-semibold text-blue-600 shrink-0 px-2 py-1 rounded-md bg-blue-50" type="button">
                                {{ showCustomerSelector ? 'Fermer' : 'Choisir' }}
                            </button>
                        </div>
                        <div v-if="showCustomerSelector" class="px-3 py-2 border-b border-gray-100 bg-gray-50 space-y-1.5">
                            <input v-model="customerSearch" type="text" placeholder="Chercher un client..." class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="max-h-24 overflow-y-auto space-y-0.5">
                                <button v-for="customer in filteredCustomers" :key="customer.id" @click="selectCustomer(customer)" class="w-full text-left rounded px-3 py-1.5 text-xs text-gray-700 hover:bg-white" type="button">{{ customer.name }}</button>
                                <p v-if="filteredCustomers.length === 0" class="text-xs text-gray-400 px-3 py-1">Aucun client trouvé.</p>
                            </div>
                        </div>

                        <!-- Service mode (compact scrollable chips) -->
                        <div class="px-3 py-1.5 border-b border-gray-100 bg-gray-50">
                            <div class="flex items-center gap-1.5 overflow-x-auto whitespace-nowrap no-scrollbar">
                                <button v-for="mode in serviceModes" :key="mode.value" @click="serviceMode = mode.value" type="button"
                                    class="flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-semibold transition-colors shrink-0"
                                    :class="serviceMode === mode.value ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-200 bg-white text-gray-600'">
                                    <span>{{ mode.icon }}</span><span>{{ mode.label }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Articles header -->
                        <div class="px-3 py-1.5 border-b border-gray-100 flex items-center justify-between text-[10px] uppercase tracking-widest text-gray-400 font-semibold bg-gray-50">
                            <span>Articles</span>
                            <span class="text-blue-500">{{ cartStore.items.length }} article{{ cartStore.items.length !== 1 ? 's' : '' }}</span>
                        </div>

                        <!-- Articles list — takes all remaining space -->
                        <div class="flex-1 overflow-y-auto overscroll-contain">
                            <div v-if="cartStore.items.length === 0" class="flex flex-col items-center justify-center h-full text-gray-300 py-8">
                                <ShoppingCartIcon class="w-10 h-10 mb-2" />
                                <p class="text-xs font-medium">Ticket vide</p>
                            </div>
                            <div v-else class="divide-y divide-gray-100">
                                <div v-for="(item, index) in cartStore.items" :key="index" class="px-3 py-2.5 active:bg-gray-50">
                                    <!-- Article name (tappable to edit) -->
                                    <div class="flex items-start justify-between gap-2 cursor-pointer" @click="openItemEditModal(index, item)">
                                        <p class="text-sm font-semibold text-gray-900 leading-tight flex-1">{{ item.article_name }}</p>
                                        <span class="text-sm font-bold text-gray-900 shrink-0">{{ formatCurrency(getItemLineTotal(item)) }}</span>
                                    </div>
                                    <!-- Qty controls + unit price + trash -->
                                    <div class="mt-1.5 flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <!-- Qty stepper -->
                                            <div class="flex items-center rounded-lg bg-gray-100 overflow-hidden">
                                                <button @click.stop="updateQuantity(index, item.quantity - 1)" class="w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-200 active:bg-gray-300" type="button">
                                                    <MinusIcon class="w-3.5 h-3.5" />
                                                </button>
                                                <span class="px-2 text-xs font-bold text-gray-800 min-w-[24px] text-center">{{ item.quantity }}</span>
                                                <button @click.stop="updateQuantity(index, item.quantity + 1)" class="w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-200 active:bg-gray-300" type="button">
                                                    <PlusIcon class="w-3.5 h-3.5" />
                                                </button>
                                            </div>
                                            <span class="text-[11px] text-gray-400">{{ formatCurrency(item.unit_price + (item.variant_price || 0) + (item.options_price || 0)) }}/u</span>
                                        </div>
                                        <button @click.stop="removeItem(index)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 active:bg-red-200" type="button">
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <!-- Variant / options tags -->
                                    <div v-if="getVariantDisplay(item) || getOptionDisplays(item).length > 0" class="mt-1.5 flex flex-wrap gap-1">
                                        <span v-if="getVariantDisplay(item)" class="text-[10px] font-semibold text-orange-700 bg-orange-50 border border-orange-100 rounded-full px-2 py-0.5">
                                            {{ getVariantDisplay(item).label }} <span class="text-orange-500">+{{ formatCurrency(getVariantDisplay(item).price) }}</span>
                                        </span>
                                        <span v-for="option in getOptionDisplays(item)" :key="option.key" class="text-[10px] font-semibold text-blue-700 bg-blue-50 border border-blue-100 rounded-full px-2 py-0.5">
                                            {{ option.label }} <span class="text-blue-500">+{{ formatCurrency(option.price) }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom bar: totals summary + action buttons (compact) -->
                        <div class="shrink-0 border-t border-gray-200 bg-white">
                            <!-- Mini totals strip -->
                            <div class="px-3 py-1.5 flex items-center justify-between gap-4 text-[11px] text-gray-500 border-b border-dashed border-gray-100">
                                <span>HT <span class="font-semibold text-gray-700">{{ formatCurrency(cartStore.subtotal) }}</span></span>
                                <span>TVA <span class="font-semibold text-gray-700">{{ formatCurrency(cartStore.taxAmount) }}</span></span>
                                <span>Rem. <span class="font-semibold text-gray-700">{{ formatCurrency(cartStore.discountTotal) }}</span></span>
                                <span class="text-sm font-bold text-green-600 ml-auto">{{ formatCurrency(cartStore.total) }}</span>
                            </div>
                            <!-- Action buttons side by side -->
                            <div class="px-3 py-2 flex gap-2">
                                <button @click="saveSale" :disabled="cartStore.items.length === 0"
                                    class="flex-1 py-2.5 text-xs font-bold rounded-xl bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-40 transition-colors"
                                    type="button">
                                    💾 Sauvegarder
                                </button>
                                <button @click="showPaymentModal = true" :disabled="cartStore.items.length === 0"
                                    class="flex-[1.4] py-2.5 text-xs font-bold rounded-xl bg-green-600 text-white hover:bg-green-700 disabled:opacity-40 transition-colors"
                                    type="button">
                                    💳 Paiement
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Payment Modal - Multiple Methods -->
    <PaymentMultiModal 
            v-if="showPaymentModal"
            :total="cartStore.total"
            @close="showPaymentModal = false"
            @complete="completeSale"
        />

        <!-- Calculator Modal -->
        <CalculatorModal 
            v-if="showCalculator"
            @close="showCalculator = false"
            @result="handleCalculatorResult"
        />

        <OptionsModal
            v-if="showOptionsModal && optionsArticle"
            :article="optionsArticle"
            :initial-selections="optionsInitialSelections"
            @close="closeOptionsModal"
            @confirm="handleOptionsConfirm"
        />

        <!-- Selection-First Variants Modal -->
        <SelectVariantsModal
            v-if="showSelectVariantsModal && selectedArticleForVariants"
            :article="selectedArticleForVariants"
            :model-value="selectedVariantId"
            @close="closeSelectVariantsModal"
            @confirm="handleSelectVariantsConfirm"
        />

        <!-- Selection-First Options Modal -->
        <SelectOptionsModal
            v-if="showSelectOptionsModal && optionsArticle"
            :article="optionsArticle"
            :initial-selections="optionsInitialSelections"
            @close="closeSelectOptionsModal"
            @confirm="handleSelectOptionsConfirm"
            @create-option="showCreateOptionForArticle"
        />

        <!-- Need Options Prompt -->
        <div v-if="showNeedOptionsPrompt" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl p-6 max-w-md w-full space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Options manquantes</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Cet article n'a pas encore d'options configurées. Souhaitez-vous en créer une maintenant ?
                    </p>
                </div>
                <div class="flex gap-3 justify-end">
                    <button
                        type="button"
                        @click="cancelNeedOptionsPrompt"
                        class="px-4 py-2 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        @click="promptCreateOptions"
                        class="px-4 py-2 bg-primary-500 text-gray-900 font-semibold rounded-xl hover:bg-primary-600"
                    >
                        Créer une option
                    </button>
                </div>
            </div>
        </div>

        <!-- Notes Modal -->
        <div v-if="showNotesModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-4">📝 Ajouter des notes</h3>
                <textarea 
                    v-model="ticketNotes"
                    placeholder="Notes sur la commande..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 mb-4"
                    rows="4"
                ></textarea>
                <div class="flex gap-2 justify-end">
                    <button 
                        @click="showNotesModal = false"
                        class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100"
                    >
                        Annuler
                    </button>
                    <button 
                        @click="applyNotes"
                        class="px-4 py-2 bg-primary-500 text-gray-900 font-bold rounded-lg hover:bg-primary-600"
                    >
                        Appliquer
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Item Modal -->
        <div v-if="showItemEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl p-6 max-w-2xl w-full mx-4 space-y-4 max-h-[85vh] overflow-y-auto">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Modifier l'article</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ editingItem?.article_name }}</p>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Quantité</span>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="adjustEditQuantity(-1)"
                            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-100"
                        >
                            −
                        </button>
                        <input
                            v-model.number="editQuantity"
                            type="number"
                            min="1"
                            class="w-20 text-center px-2 py-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        <button
                            type="button"
                            @click="adjustEditQuantity(1)"
                            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-100"
                        >
                            +
                        </button>
                    </div>
                </div>
                <div v-if="editingActiveVariants.length" class="space-y-2">
                    <p class="text-sm font-semibold text-gray-700">Variante</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <label
                            v-for="variant in editingActiveVariants"
                            :key="variant.id"
                            class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer"
                            :class="editSelectedVariantId === variant.id ? 'border-primary-500 bg-primary-50' : 'border-gray-200'"
                        >
                            <input
                                type="radio"
                                name="edit-variant"
                                class="w-4 h-4 text-primary-600 border-gray-300"
                                :value="variant.id"
                                v-model="editSelectedVariantId"
                            >
                            <div class="text-sm">
                                <span class="font-medium text-gray-900">
                                    {{ variant.template_name ? `${variant.template_name} · ${variant.template_value}` : variant.name }}
                                </span>
                                <span v-if="Number(variant.price_impact) !== 0" class="ml-2 text-orange-600 font-semibold">
                                    +{{ formatCurrency(variant.price_impact) }}
                                </span>
                            </div>
                        </label>
                    </div>
                </div>
                <div v-if="editingSelectableOptions.length" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-700">Options</p>
                        <span v-if="editMissingRequiredOptions" class="text-xs text-orange-600 font-medium">
                            Sélectionnez toutes les options obligatoires
                        </span>
                    </div>
                    <div v-for="option in editingSelectableOptions" :key="option.id" class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-800">{{ option.name }}</span>
                            <span class="text-xs text-gray-500">
                                {{ option.is_required ? 'Obligatoire' : 'Optionnel' }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <label
                                v-for="variant in option.variants"
                                :key="variant.id"
                                class="flex items-center gap-3 p-2 border rounded-lg bg-white"
                                :class="isEditOptionVariantSelected(option, variant) ? 'border-primary-500 bg-primary-50' : 'border-gray-200'"
                            >
                                <input
                                    type="checkbox"
                                    class="w-4 h-4 text-primary-600 border-gray-300"
                                    :checked="isEditOptionVariantSelected(option, variant)"
                                    @change="toggleEditOptionVariant(option, variant.id)"
                                >
                                <div class="text-sm">
                                    <span class="font-medium text-gray-900">{{ variant.name }}</span>
                                    <span v-if="Number(variant.price_impact) !== 0" class="ml-2 text-blue-600 font-semibold">
                                        +{{ formatCurrency(variant.price_impact) }}
                                    </span>
                                </div>
                            </label>
                        </div>
                        <p v-if="option.is_required && !editHasSelection(option.id)" class="text-xs text-orange-600 mt-2">
                            Choisissez au moins une variante.
                        </p>
                    </div>
                </div>
                <div class="flex gap-2 justify-end">
                    <button
                        type="button"
                        @click="closeItemEditModal"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        @click="applyItemEdit"
                        class="px-4 py-2 bg-primary-500 text-gray-900 font-semibold rounded-lg hover:bg-primary-600"
                    >
                        Appliquer
                    </button>
                </div>
            </div>
        </div>

        <!-- Discount Modal -->
        <div v-if="showDiscountModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-4">🏷️ Appliquer une remise</h3>
                <div class="space-y-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant fixe (DHS)</label>
                        <input 
                            v-model.number="discountAmount"
                            type="number"
                            min="0"
                            placeholder="0.00"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pourcentage (%)</label>
                        <input 
                            v-model.number="discountPercent"
                            type="number"
                            min="0"
                            max="100"
                            placeholder="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        />
                    </div>
                </div>
                <div class="flex gap-2 justify-end">
                    <button 
                        @click="showDiscountModal = false"
                        class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100"
                    >
                        Annuler
                    </button>
                    <button 
                        @click="applyDiscount"
                        class="px-4 py-2 bg-primary-500 text-gray-900 font-bold rounded-lg hover:bg-primary-600"
                    >
                        Appliquer
                    </button>
                </div>
            </div>
        </div>

        <!-- Create Option Modal (Comprehensive) -->
        <div v-if="showCreateOptionModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">⚙️ Créer une nouvelle option</h3>
                    <button 
                        type="button"
                        @click="closeCreateOptionModal"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body - Reusable Form Content -->
                <div class="p-6">
                    <OptionFormContent 
                        :form="newOptionForm"
                        :showPriceField="false"
                        :showSettings="true"
                        :showTypeField="false"
                        :currencyCode="settingsStore.currencyCode"
                    />
                </div>

                <!-- Modal Footer -->
                <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end space-x-3">
                    <button 
                        type="button"
                        @click="closeCreateOptionModal"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100"
                    >
                        Annuler
                    </button>
                    <button 
                        type="button"
                        @click="createQuickOption"
                        :disabled="creatingOption || !isNewOptionValid"
                        class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ creatingOption ? 'Création...' : 'Créer l\'option' }}
                    </button>
                </div>
            </div>
        </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useCartStore } from '../stores/cart'
import { useArticlesStore } from '../stores/articles'
import { useSettingsStore } from '../stores/settings'
import { useCustomersStore } from '../stores/customers'
import { useOfflineStore } from '../stores/offline'
import { useUiStore } from '../stores/ui'
import { salesApi, optionsApi, articlesApi } from '../api'
import PaymentMultiModal from '../components/pos/PaymentMultiModal.vue'
import CalculatorModal from '../components/pos/CalculatorModal.vue'
import OptionsModal from '../components/pos/OptionsModal.vue'
import SelectOptionsModal from '../components/pos/SelectOptionsModal.vue'
import SelectVariantsModal from '../components/pos/SelectVariantsModal.vue'
import OptionFormContent from '../components/forms/OptionFormContent.vue'
import {
    Bars3Icon,
    MagnifyingGlassIcon,
    ShoppingCartIcon,
    MinusIcon,
    PlusIcon,
    TrashIcon,
    UserPlusIcon
} from '@heroicons/vue/24/outline'

const cartStore = useCartStore()
const articlesStore = useArticlesStore()
const settingsStore = useSettingsStore()
const customersStore = useCustomersStore()
const uiStore = useUiStore()
const { appSidebarOpen } = storeToRefs(uiStore)
const { posCategoryDisplayMode } = storeToRefs(settingsStore)
const categoriesDisplayMode = posCategoryDisplayMode
const showPaymentModal = ref(false)
const showCalculator = ref(false)
const showOptionsModal = ref(false)
const showCustomerSelector = ref(false)
const showNotesModal = ref(false)
const showDiscountModal = ref(false)
const showItemEditModal = ref(false)
const showSelectOptionsModal = ref(false)
const showCreateOptionModal = ref(false)
const showNeedOptionsPrompt = ref(false)
const showSelectVariantsModal = ref(false)
const optionsArticle = ref(null)
const selectedArticleForVariants = ref(null)
const selectedVariantId = ref(null)
const optionsInitialSelections = ref([])
const optionsMode = ref('add')
const variantSelectionMode = ref('add')
const editingCartIndex = ref(null)
const editingItem = ref(null)
const editQuantity = ref(1)
const editingArticle = ref(null)
const editSelectedVariantId = ref(null)
const editSelectedOptions = ref([])
const editOptionsPrice = ref(0)
const searchQuery = ref('')
const selectedCategoryId = ref('all')
const customerSearch = ref('')
const ticketNotes = ref('')
const discountAmount = ref(0)
const discountPercent = ref(0)
const currentPage = ref(1)
const itemsPerPage = ref(20)
const creatingOption = ref(false)
const posRoot = ref(null)
const isFullscreen = ref(false)
const screenMode = ref('desktop')
const isCartExpanded = ref(true)
const isCartFullscreen = ref(false)
let viewportResizeHandler = null
const newOptionForm = ref({
    name: '',
    type: 'fixed',
    values: [''],
    variantPrices: [0],
    extra_price: 0,
    is_required: false,
    is_active: true,
})

const categories = computed(() => articlesStore.categories)

const categoryButtons = computed(() => {
    const baseButtons = [
        { id: null, label: 'Favoris', icon: '⭐' },
        { id: 'all', label: 'Tous', icon: '📦' },
    ]

    const dynamicButtons = (categories.value || []).map((category) => ({
        id: category.id,
        label: category.name,
        icon: getCategoryIcon(category.icon),
    }))

    return [...baseButtons, ...dynamicButtons]
})

const isMobile = computed(() => screenMode.value === 'mobile')
const isTablet = computed(() => screenMode.value === 'tablet')
const effectiveCategoriesDisplayMode = computed(() => {
    if (isMobile.value) {
        return 'top'
    }
    if (isTablet.value) {
        return 'bottom'
    }
    return categoriesDisplayMode.value
})
const contentPaddingClass = computed(() => {
    if (!isMobile.value) return ''
    if (!isCartExpanded.value) return 'pb-[88px]'
    if (isCartFullscreen.value) return 'pb-0'
    return 'pb-[55vh]'
})
const ticketPanelClass = computed(() => {
    if (isMobile.value) {
        let h
        if (!isCartExpanded.value) h = 'h-[88px]'
        else if (isCartFullscreen.value) h = 'h-[100dvh]'
        else h = 'h-[60vh]'
        return `fixed inset-x-0 bottom-0 z-50 rounded-t-2xl border-t border-gray-200 shadow-2xl overflow-hidden ${h}`
    }
    if (isTablet.value) {
        return 'w-80 flex-shrink-0 border-l border-gray-200 h-full overflow-hidden'
    }
    return 'w-[380px] flex-shrink-0 border-l border-gray-200 h-full overflow-hidden'
})

const serviceModes = [
    { value: 'dine_in', label: 'Sur place', icon: '🍽️' },
    { value: 'pickup', label: 'A emporter', icon: '🥡' },
    { value: 'delivery', label: 'Livraison', icon: '🚚' },
    { value: 'glovo', label: 'Glovo', icon: '🛵' },
]

const serviceMode = computed({
    get: () => cartStore.deliveryMode,
    set: (value) => cartStore.setDeliveryMode(value),
})

const filteredCustomers = computed(() => {
    const query = customerSearch.value.toLowerCase()
    if (!query) return customersStore.customers
    return customersStore.customers.filter(c => 
        c.name.toLowerCase().includes(query)
    )
})

const filteredArticles = computed(() => {
    let articles = articlesStore.articles

    // If no category selected (Favoris), filter by is_favorite
    // BUT if there are no favorites at all, show all articles so the screen isn't empty
    if (selectedCategoryId.value === null) {
        const favorites = articles.filter(a => a.is_favorite)
        if (favorites.length > 0) {
            articles = favorites
        } else {
            // If no favorites, default to showing all articles
            // so the user doesn't see an empty screen
        }
    } else if (selectedCategoryId.value !== 'all') {
        articles = articles.filter(a => a.category_id === selectedCategoryId.value)
    }

    // Filter by search
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        articles = articles.filter(a => 
            a.name.toLowerCase().includes(query) ||
            a.sku?.toLowerCase().includes(query)
        )
    }

    return articles
})

const totalArticles = computed(() => filteredArticles.value.length)

const totalPages = computed(() => {
    const pages = Math.ceil(totalArticles.value / itemsPerPage.value)
    return pages > 0 ? pages : 1
})

const paginatedArticles = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    return filteredArticles.value.slice(start, start + itemsPerPage.value)
})

const pageSummary = computed(() => {
    if (totalArticles.value === 0) {
        return 'Aucun article'
    }
    const start = (currentPage.value - 1) * itemsPerPage.value + 1
    const end = Math.min(currentPage.value * itemsPerPage.value, totalArticles.value)
    return `Affichage de ${start} à ${end} sur ${totalArticles.value}`
})

const selectedCategoryName = computed(() => {
    if (selectedCategoryId.value === null) return 'Favoris'
    if (selectedCategoryId.value === 'all') return 'Tous les Articles'
    const category = categories.value.find(c => c.id === selectedCategoryId.value)
    return category?.name || 'Articles'
})

const categoryIcons = {
    apple: '🍎',
    cup: '🥤',
    bread: '🥖',
    home: '🏠',
    cookie: '🍪',
    meat: '🥩',
}

const editingItemHasVariants = computed(() => {
    if (!editingItem.value) return false
    if (editingItem.value.selected_variant) return true
    return editingItem.value.article?.has_variants || (editingItem.value.article?.variants?.length || 0) > 0
})

const editingItemHasOptions = computed(() => {
    if (!editingItem.value) return false
    if (editingItem.value.selected_options?.length) return true
    return editingItem.value.article?.has_options || (editingItem.value.article?.options?.length || 0) > 0
})

const editingActiveVariants = computed(() => {
    return getActiveVariants(editingArticle.value)
})

const editingSelectableOptions = computed(() => {
    return (editingArticle.value?.options || [])
        .filter(option => option.is_active !== false)
        .map(option => {
            const activeVariants = (option.variants || []).filter(variant => variant.is_active !== false)
            const fallbackVariants = !activeVariants.length && Array.isArray(option.values)
                ? option.values.map((value, index) => ({
                    id: `value-${option.id}-${index}`,
                    name: value,
                    price_impact: Number(option.extra_price) || 0,
                    is_active: true,
                }))
                : []
            return {
                ...option,
                variants: activeVariants.length > 0 ? activeVariants : fallbackVariants,
            }
        })
        .filter(option => option.variants && option.variants.length > 0)
})

const editMissingRequiredOptions = computed(() => {
    return editingSelectableOptions.value.some(option => option.is_required && !editHasSelection(option.id))
})

function getCategoryIcon(icon) {
    return categoryIcons[icon] || '📦'
}

function toggleAppSidebar() {
    uiStore.toggleAppSidebar()
    if (isMobile.value && uiStore.appSidebarOpen) {
        uiStore.closePosSidebar()
    }
}

function toggleMobileCart() {
    if (!isMobile.value) return
    if (!isCartExpanded.value) {
        // collapsed → half
        isCartExpanded.value = true
        isCartFullscreen.value = false
    } else if (!isCartFullscreen.value) {
        // half → fullscreen
        isCartFullscreen.value = true
    } else {
        // fullscreen → collapsed
        isCartExpanded.value = false
        isCartFullscreen.value = false
    }
}

function updateScreenMode() {
    const width = Math.round(
        window.visualViewport?.width || document.documentElement.clientWidth || window.innerWidth
    )
    if (width < 768) {
        screenMode.value = 'mobile'
    } else if (width < 1024) {
        screenMode.value = 'tablet'
    } else {
        screenMode.value = 'desktop'
    }
}

function handleFullscreenChange() {
    isFullscreen.value = !!document.fullscreenElement
    document.body.classList.toggle('overflow-hidden', isFullscreen.value)
}

async function toggleFullscreen() {
    try {
        if (!document.fullscreenElement) {
            await posRoot.value?.requestFullscreen()
        } else {
            await document.exitFullscreen()
        }
    } catch (error) {
        console.error('Fullscreen toggle failed:', error)
    }
}

function formatCurrency(amount) {
    return settingsStore.formatCurrency(amount)
}

function getItemLineTotal(item) {
    const unitPrice = Number(item.unit_price) || 0
    const variantPrice = Number(item.variant_price) || 0
    const optionsPrice = Number(item.options_price) || 0
    const quantity = Number(item.quantity) || 0
    const discount = Number(item.discount_amount) || 0
    return (unitPrice + variantPrice + optionsPrice) * quantity - discount
}

function formatOptionsPrice(amount) {
    const numeric = Number(amount || 0)
    if (numeric > 0) {
        return `+${formatCurrency(numeric)}`
    }
    return formatCurrency(numeric)
}

function getVariantDisplay(item) {
    if (!item?.selected_variant) return null
    const variant = item.selected_variant
    const name = variant.template_name && variant.template_value
        ? `${variant.template_name} · ${variant.template_value}`
        : (variant.name || variant.template_value || null)
    if (!name) return null
    const price = Number(variant.price_impact ?? item.variant_price ?? 0) || 0
    return { label: name, price }
}

function getOptionDisplays(item) {
    const selections = item?.selected_options || []
    const chips = []
    selections.forEach((option) => {
        const optionName = option.option_name || ''
        ;(option.variants || []).forEach((variant) => {
            const variantName = variant.name || variant.template_value || ''
            if (!optionName && !variantName) return
            const label = optionName && variantName
                ? `${optionName} · ${variantName}`
                : (optionName || variantName)
            chips.push({
                key: `${option.option_id}-${variant.id}`,
                label,
                price: Number(variant.price_impact) || 0,
            })
        })
    })
    return chips
}

function getActiveVariants(article) {
    return (article?.variants || []).filter(v => v.is_active !== false)
}

async function resolveEditableArticle(item) {
    if (!item) return null
    const baseArticle = item.article || item
    const needsVariants = baseArticle?.has_variants && !baseArticle?.variants
    const needsOptions = baseArticle?.has_options && !baseArticle?.options
    if (needsVariants || needsOptions) {
        try {
            const response = await articlesApi.get(baseArticle.id)
            return response.data || baseArticle
        } catch (error) {
            console.error('Failed to load article for edit:', error)
            return baseArticle
        }
    }
    return baseArticle
}

async function openItemEditModal(index, item) {
    editingCartIndex.value = index
    editingItem.value = item
    editQuantity.value = Number(item.quantity) || 1
    editingArticle.value = await resolveEditableArticle(item)
    editSelectedVariantId.value = item.selected_variant?.id || null
    editSelectedOptions.value = Array.isArray(item.selected_options)
        ? JSON.parse(JSON.stringify(item.selected_options))
        : []
    updateEditOptionsPrice()
    showItemEditModal.value = true
}

function closeItemEditModal() {
    showItemEditModal.value = false
    editingItem.value = null
    editingArticle.value = null
    editSelectedVariantId.value = null
    editSelectedOptions.value = []
    editOptionsPrice.value = 0
    editingCartIndex.value = null
}

function adjustEditQuantity(delta) {
    const next = Number(editQuantity.value || 1) + delta
    editQuantity.value = Math.max(1, next)
}

function applyItemEdit() {
    if (editingCartIndex.value === null) return
    if (editingActiveVariants.value.length && !editSelectedVariantId.value) {
        alert('Veuillez sélectionner une variante')
        return
    }
    if (editMissingRequiredOptions.value) {
        alert('Sélectionnez toutes les options obligatoires')
        return
    }
    const qty = Math.max(1, Number(editQuantity.value) || 1)
    cartStore.updateItemQuantity(editingCartIndex.value, qty)
    const item = cartStore.items[editingCartIndex.value]
    if (item && editingArticle.value) {
        const selectedVariant = editingArticle.value.variants?.find(v => v.id === editSelectedVariantId.value) || null
        item.selected_variant = selectedVariant
        item.variant_price = selectedVariant ? Number(selectedVariant.price_impact) || 0 : 0
        item.selected_options = normalizeSelectedOptions(editSelectedOptions.value)
        item.options_price = Number(editOptionsPrice.value) || 0
        item.total = getItemLineTotal(item)
    }
    closeItemEditModal()
}

function isEditOptionVariantSelected(option, variant) {
    return editSelectedOptions.value.some(
        (sel) => sel.option_id === option.id && sel.variants.some((v) => v.id === variant.id)
    )
}

function editHasSelection(optionId) {
    return editSelectedOptions.value.some((sel) => sel.option_id === optionId && sel.variants.length > 0)
}

function toggleEditOptionVariant(option, variantId) {
    const variant = option.variants.find(v => v.id === variantId)
    if (!variant) return

    let optionSelection = editSelectedOptions.value.find((sel) => sel.option_id === option.id)
    if (!optionSelection) {
        optionSelection = {
            option_id: option.id,
            option_name: option.name,
            type: option.type,
            variants: [],
        }
        editSelectedOptions.value.push(optionSelection)
    }

    const variantIndex = optionSelection.variants.findIndex((v) => v.id === variantId)
    if (variantIndex >= 0) {
        optionSelection.variants.splice(variantIndex, 1)
    } else {
        optionSelection.variants.push({
            id: variant.id,
            name: variant.name,
            price_impact: variant.price_impact,
        })
    }
    updateEditOptionsPrice()
}

function updateEditOptionsPrice() {
    editOptionsPrice.value = editSelectedOptions.value.reduce((total, optionSelection) => {
        const variantsTotal = optionSelection.variants.reduce((sum, variant) => {
            return sum + (Number(variant.price_impact) || 0)
        }, 0)
        return total + variantsTotal
    }, 0)
}

function selectCategory(categoryId) {
    selectedCategoryId.value = categoryId
    if (isMobile.value) {
        uiStore.closePosSidebar()
    }
}

function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value += 1
    }
}

function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value -= 1
    }
}

async function addToCart(article) {
    // First, check if article has variants (mandatory single-choice)
    const hasVariants = article.has_variants || (Array.isArray(article.variants) && article.variants.length > 0)
    
    // Then, check if article has options (optional multi-choice)
    const hasOptions = article.has_options || (Array.isArray(article.options) && article.options.length > 0)

    // Load full article data if needed
    let fullArticle = article
    if ((hasVariants || hasOptions) && !article.variants && !article.options) {
        try {
            const response = await articlesApi.get(article.id)
            fullArticle = response.data || article
        } catch (error) {
            console.error('Failed to load article:', error)
        }
    }

    const activeVariants = getActiveVariants(fullArticle)
    const hasActiveVariants = activeVariants.length > 0

    // If article has active variants, show variant selection modal first
    if (hasActiveVariants) {
        showSelectVariantsModal.value = true
        selectedArticleForVariants.value = fullArticle
        variantSelectionMode.value = 'add'
        return
    }

    // No variants configured -> add directly to cart (options can be edited manually)
    cartStore.addItem(fullArticle)
}

function updateQuantity(index, quantity) {
    cartStore.updateItemQuantity(index, quantity)
}

function removeItem(index) {
    cartStore.removeItem(index)
}

async function saveSale() {
    try {
        const data = cartStore.getCartData()
        const response = await salesApi.create(data)
        cartStore.setSaleId(response.data.id)
        alert('Vente sauvegardée!')
    } catch (error) {
        console.error('Failed to save sale:', error)
        alert('Erreur lors de la sauvegarde')
    }
}

async function completeSale(payments) {
    const offlineStore = useOfflineStore()
    
    try {
        const data = cartStore.getCartData()
        const saleData = {
            ...data,
            payments: payments, // Multiple payments
            status: 'completed',
        }

        // If offline, save to pending queue
        if (!offlineStore.isOnline) {
            const result = await offlineStore.savePendingSale(saleData)
            if (result.success) {
                cartStore.clearCart()
                showPaymentModal.value = false
                alert('Vente sauvegardée hors ligne! Elle sera synchronisée automatiquement.')
            } else {
                throw new Error('Erreur lors de la sauvegarde hors ligne')
            }
            return
        }

        // If online, process normally
        if (!cartStore.currentSaleId) {
            const response = await salesApi.create(data)
            cartStore.setSaleId(response.data.id)
        }

        // Add all payments
        for (const payment of payments) {
            await salesApi.addPayment(cartStore.currentSaleId, payment)
        }

        // Complete sale
        await salesApi.complete(cartStore.currentSaleId)

        // Clear cart
        cartStore.clearCart()
        showPaymentModal.value = false

        alert('Vente complétée avec succès!')
    } catch (error) {
        console.error('Failed to complete sale:', error)
        alert('Erreur lors du paiement: ' + (error.response?.data?.message || error.message))
    }
}

function handleCalculatorResult(result) {
    // Handle calculator result
    showCalculator.value = false
}

function closeOptionsModal() {
    showOptionsModal.value = false
    optionsArticle.value = null
    optionsInitialSelections.value = []
    optionsMode.value = 'add'
    editingCartIndex.value = null
}

function closeSelectVariantsModal() {
    showSelectVariantsModal.value = false
    selectedArticleForVariants.value = null
    selectedVariantId.value = null
    variantSelectionMode.value = 'add'
}

function handleSelectVariantsConfirm({ variantId, selectedOptions = [], optionsPrice = 0 }) {
    if (!selectedArticleForVariants.value) return

    const selectedVariant = selectedArticleForVariants.value.variants?.find(v => v.id === variantId)
    if (!selectedVariant) return

    const normalizedOptions = normalizeSelectedOptions(selectedOptions)
    if (variantSelectionMode.value === 'edit' && editingCartIndex.value !== null) {
        const item = cartStore.items[editingCartIndex.value]
        if (item) {
            item.selected_variant = selectedVariant
            item.variant_price = Number(selectedVariant.price_impact) || 0
            item.selected_options = normalizedOptions
            item.options_price = Number(optionsPrice) || 0
            item.total = getItemLineTotal(item)
        }
    } else {
        cartStore.addItem(
            selectedArticleForVariants.value,
            1,
            normalizedOptions,
            optionsPrice,
            selectedVariant
        )
    }
    closeSelectVariantsModal()
}

function closeSelectOptionsModal() {
    showSelectOptionsModal.value = false
    optionsArticle.value = null
    optionsMode.value = 'add'
    editingCartIndex.value = null
}

function cancelNeedOptionsPrompt() {
    showNeedOptionsPrompt.value = false
    optionsArticle.value = null
}

function promptCreateOptions() {
    showNeedOptionsPrompt.value = false
    showCreateOptionModal.value = true
}

function handleSelectOptionsConfirm({ selectedOptions, optionsPrice }) {
    if (!optionsArticle.value) return

    if (optionsMode.value === 'edit' && editingCartIndex.value !== null) {
        cartStore.updateItemOptions(editingCartIndex.value, selectedOptions, optionsPrice)
    } else {
        cartStore.addItem(optionsArticle.value, 1, selectedOptions, optionsPrice)
    }

    closeSelectOptionsModal()
}

function showCreateOptionForArticle() {
    showSelectOptionsModal.value = false
    showCreateOptionModal.value = true
}

function handleOptionsConfirm({ selectedOptions, optionsPrice }) {
    if (!optionsArticle.value) return
    const normalized = normalizeSelectedOptions(selectedOptions)
    if (optionsMode.value === 'edit' && editingCartIndex.value !== null) {
        cartStore.updateItemOptions(editingCartIndex.value, normalized, optionsPrice)
    } else {
        cartStore.addItem(optionsArticle.value, 1, normalized, optionsPrice)
    }
    closeOptionsModal()
}

function normalizeSelectedOptions(selectedOptions) {
    if (!Array.isArray(selectedOptions)) return selectedOptions
    return selectedOptions
        .map((option) => ({
            ...option,
            variants: [...(option.variants || [])].sort((a, b) => a.id - b.id),
        }))
        .sort((a, b) => a.option_id - b.option_id)
}

function getActiveOptions(article) {
    return (article.options || [])
        .filter((option) => option.is_active)
        .map((option) => ({
            ...option,
            variants: (option.variants || []).filter((variant) => variant.is_active),
        }))
        .filter((option) => option.variants.length > 0)
}

function openOptionsModal(article, activeOptions, initialSelections, mode, index) {
    optionsArticle.value = {
        ...article,
        options: activeOptions,
    }
    optionsInitialSelections.value = initialSelections || []
    optionsMode.value = mode
    editingCartIndex.value = index
    showOptionsModal.value = true
}

function editItemOptions(index, item) {
    if (!item.article) return
    
    // Check if article has options
    const hasOptions = item.article.options && item.article.options.length > 0
    
    if (hasOptions) {
        // Show selection modal for editing
        showSelectOptionsModal.value = true
        optionsArticle.value = item.article
        optionsMode.value = 'edit'
        editingCartIndex.value = index
        optionsInitialSelections.value = item.selected_options || []
        return
    }
    
    // No options to edit
    console.warn('This article has no options')
}

function selectCustomer(customer) {
    cartStore.setCustomer(customer.id, customer.name)
    showCustomerSelector.value = false
    customerSearch.value = ''
}

function applyNotes() {
    cartStore.setNotes(ticketNotes.value)
    showNotesModal.value = false
}

function applyDiscount() {
    cartStore.setDiscount(discountAmount.value, discountPercent.value)
    showDiscountModal.value = false
}

function resetCart() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser le ticket?')) {
        cartStore.clearCart()
        ticketNotes.value = ''
        discountAmount.value = 0
        discountPercent.value = 0
    }
}

watch([searchQuery, selectedCategoryId], () => {
    currentPage.value = 1
})

watch(isMobile, (value) => {
    if (value) {
        isCartExpanded.value = false
        isCartFullscreen.value = false
    } else {
        uiStore.closePosSidebar()
        isCartExpanded.value = true
        isCartFullscreen.value = false
    }
})

watch([filteredArticles, itemsPerPage], () => {
    if (currentPage.value > totalPages.value) {
        currentPage.value = totalPages.value
    }
    if (currentPage.value < 1) {
        currentPage.value = 1
    }
})

const isNewOptionValid = computed(() => {
    return newOptionForm.value.name.trim() && 
           newOptionForm.value.values.length > 0 && 
           newOptionForm.value.values.some(v => v.trim())
})

function closeCreateOptionModal() {
    showCreateOptionModal.value = false
    editingCartIndex.value = null
    optionsArticle.value = null
    // Reset form
    newOptionForm.value = {
        name: '',
        type: 'fixed',
        values: [''],
        variantPrices: [0],
        extra_price: 0,
        is_required: false,
        is_active: true,
    }
}

async function createQuickOption() {
    if (!isNewOptionValid.value) return
    
    creatingOption.value = true
    const articleContext = optionsArticle.value
    try {
        // Build value/price pairs and filter empty values
        const valuesWithPrices = newOptionForm.value.values.map((value, index) => ({
            value: value.trim(),
            price: Number(newOptionForm.value.variantPrices?.[index]) || 0,
        })).filter((item) => item.value)

        const validValues = valuesWithPrices.map((item) => item.value)
        const optionData = {
            name: newOptionForm.value.name.trim(),
            type: newOptionForm.value.type,
            values: validValues,
            extra_price: newOptionForm.value.extra_price,
            is_required: newOptionForm.value.is_required,
            is_active: newOptionForm.value.is_active,
        }
        
        const response = await optionsApi.create(optionData)
        console.log('Option créée:', response.data)
        
        // Create variants for each value with corresponding prices
        for (let i = 0; i < valuesWithPrices.length; i++) {
            const variantData = {
                name: valuesWithPrices[i].value,
                price_impact: valuesWithPrices[i].price,
                is_active: true,
            }
            try {
                await optionsApi.createVariant(response.data.id, variantData)
            } catch (error) {
                console.error(`Failed to create variant ${i + 1}:`, error)
            }
        }
        
        // Close modal and reset
        closeCreateOptionModal()
        
        // Refresh article data if we're creating for a specific article
        if (articleContext) {
            try {
                const existingOptionIds = Array.isArray(articleContext.options)
                    ? articleContext.options.map((option) => option.id)
                    : []

                const updatedOptionIds = Array.from(new Set([
                    ...existingOptionIds,
                    response.data.id,
                ]))

                await articlesApi.update(articleContext.id, {
                    options: updatedOptionIds,
                    has_options: true,
                })

                const articleResponse = await articlesApi.get(articleContext.id)
                optionsArticle.value = articleResponse.data
                showSelectOptionsModal.value = true
            } catch (error) {
                console.error('Failed to refresh article:', error)
            }
        }
        
        // Show success message
        alert(`Option "${response.data.name}" créée avec succès!`)
    } catch (error) {
        console.error('Erreur création option:', error)
        alert('Erreur lors de la création de l\'option: ' + (error.response?.data?.message || error.message))
    } finally {
        creatingOption.value = false
    }
}

onMounted(async () => {
    updateScreenMode()
    viewportResizeHandler = () => updateScreenMode()
    window.addEventListener('resize', viewportResizeHandler)
    window.visualViewport?.addEventListener('resize', viewportResizeHandler)
    document.addEventListener('fullscreenchange', handleFullscreenChange)
    handleFullscreenChange()
    await settingsStore.fetchSettings()
    await articlesStore.refresh()
    await customersStore.fetchCustomers()
})

onUnmounted(() => {
    if (viewportResizeHandler) {
        window.removeEventListener('resize', viewportResizeHandler)
        window.visualViewport?.removeEventListener('resize', viewportResizeHandler)
    }
    document.removeEventListener('fullscreenchange', handleFullscreenChange)
    document.body.classList.remove('overflow-hidden')
})
</script>
