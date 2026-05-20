<template>
    <div ref="posRoot" class="flex h-[100dvh] min-h-0 flex-col overflow-hidden bg-[#f4f3ef]">
        <header class="flex gap-3 bg-gray-900 text-white" :class="headerClass">
            <div class="flex items-center" :class="headerInfoWrapClass">
                <button
                    @click="toggleAppSidebar"
                    class="bg-white/10 rounded-lg hover:bg-white/20 transition-colors"
                    :class="headerMenuButtonClass"
                    type="button"
                    title="Afficher/Masquer le menu"
                >
                    <Bars3Icon :class="headerMenuIconClass" />
                </button>
                <div class="min-w-0">
                    <p class="uppercase text-gray-400" :class="headerKickerClass">Catégorie active</p>
                    <p class="truncate font-semibold" :class="headerTitleClass">{{ selectedCategoryName }}</p>
                    <p class="text-gray-400" :class="headerCountClass">{{ totalArticles }} articles</p>
                </div>
            </div>
            <div class="flex-1 min-w-0" :class="headerSearchWrapClass">
                <div class="relative">
                    <input
                        ref="searchField"
                        v-model="searchQuery"
                        type="text"
                        autofocus
                        placeholder="Rechercher par nom ou code-barres"
                        class="w-full border border-gray-700 bg-gray-800 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        :class="headerSearchInputClass"
                    >
                    <MagnifyingGlassIcon class="text-gray-400 absolute top-1/2 -translate-y-1/2" :class="headerSearchIconClass" />
                </div>
            </div>
            <div class="flex items-center justify-between" :class="headerActionsWrapClass">
                <button
                    @click="toggleMobileCart"
                    class="items-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors"
                    :class="[headerCartButtonClass, useBottomSheetCart ? 'flex' : 'hidden']"
                    type="button"
                >
                    <ShoppingCartIcon :class="headerActionIconClass" />
                    <span class="font-semibold" :class="headerActionTextClass">{{ cartStore.items.length }}</span>
                </button>
                <button
                    @click="toggleFullscreen"
                    class="rounded-lg bg-white/10 hover:bg-white/20 transition-colors"
                    :class="headerIconButtonClass"
                    type="button"
                    :title="isFullscreen ? 'Quitter le plein écran' : 'Plein écran'"
                >
                    <span class="leading-none" :class="headerFullscreenIconClass">⛶</span>
                </button>
            </div>
        </header>

        <div class="flex min-h-0 flex-1 overflow-hidden">
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

            <div class="flex min-h-0 flex-1 flex-col overflow-hidden">
                <div v-if="effectiveCategoriesDisplayMode === 'top' && !appSidebarOpen" class="border-b border-gray-200 bg-white px-2 py-1" :class="isTablet && isLandscape ? 'py-0.5' : 'py-1'">
                    <div :class="isTablet && isLandscape ? 'flex gap-1 overflow-x-auto' : 'flex gap-2 overflow-x-auto'">
                        <button
                            v-for="button in categoryButtons"
                            :key="button.id + '-top'"
                            @click="selectCategory(button.id)"
                            type="button"
                            class="flex items-center gap-1 rounded-md border transition-colors shrink-0"
                            :class="[
                                selectedCategoryId === button.id ? 'border-primary-500 bg-primary-50 text-primary-600' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50',
                                isTablet && isLandscape ? 'px-1.5 py-0.5 text-[8px] font-semibold uppercase tracking-wider whitespace-nowrap' : 'px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider whitespace-nowrap'
                            ]"
                        >
                            <span :class="isTablet && isLandscape ? 'text-xs' : 'text-base'">{{ button.icon }}</span>
                            <span>{{ button.label }}</span>
                        </button>
                    </div>
                </div>
                <div class="min-h-0 flex-1 overflow-hidden bg-[#f8f8f8] p-4" :class="contentPaddingClass">
                    <div :class="articleGridClass">
                        <div
                            v-for="article in paginatedArticles"
                            :key="article.id"
                            class="cursor-pointer overflow-hidden border border-gray-200 bg-white transition-shadow hover:shadow-lg"
                            :class="articleCardClass"
                            @click="addToCart(article)"
                        >
                            <div :class="articleMediaClass">
                                <img
                                    v-if="article.photo"
                                    :src="article.photo"
                                    :alt="article.name"
                                    class="h-full w-full object-cover"
                                >
                                <span v-else class="text-3xl">📦</span>
                            </div>
                            <div :class="articleBodyClass">
                                <h3 :class="articleTitleClass">{{ article.name }}</h3>
                                <p :class="articlePriceClass">{{ formatCurrency(article.sell_price) }}</p>
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
                    <div :class="bottomCategoriesRailClass">
                        <button
                            v-for="button in categoryButtons"
                            :key="button.id + '-bottom'"
                            @click="selectCategory(button.id)"
                            type="button"
                            class="flex items-center gap-2 rounded-full border px-3 py-2 text-xs font-semibold uppercase tracking-wide transition-colors"
                            :class="[bottomCategoryButtonClass, selectedCategoryId === button.id ? 'border-primary-500 bg-primary-50 text-primary-600' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50']"
                        >
                            <span class="text-lg">{{ button.icon }}</span>
                            <span>{{ button.label }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <section
                class="flex min-h-0 flex-col bg-[#f2f2f4] transition-all duration-300"
                :class="ticketPanelClass"
            >
                <!-- Mobile ticket handle strip — 3 states: collapsed / half / fullscreen -->
                <div
                    class="flex-col items-center cursor-pointer select-none shrink-0 transition-all duration-300"
                    :class="[useBottomSheetCart ? 'flex' : 'hidden', isCartExpanded ? 'bg-white border-b border-gray-100' : 'bg-gradient-to-r from-blue-600 to-blue-500 rounded-t-2xl shadow-lg']"
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

                <div class="flex h-full min-h-0 flex-1 flex-col overflow-hidden">
                    <!-- Desktop: styled card wrapper -->
                    <div :class="desktopCartLayoutClass">
                        <div class="flex min-h-0 flex-1 flex-col rounded-2xl border border-gray-200 bg-white shadow-sm" :class="desktopCartCardClass">
                            <!-- Client -->
                            <div class="shrink-0 border-b border-gray-200 space-y-3" :class="desktopPanelPaddingClass">
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
                            <div v-if="serviceModesEnabled" class="shrink-0 border-b border-gray-200 bg-gray-50" :class="desktopPanelPaddingClass">
                                <div :class="desktopServiceModesWrapClass">
                                    <button v-for="mode in serviceModes" :key="mode.value" @click="selectServiceMode(mode.value)" type="button" class="flex items-center gap-2 rounded-lg border font-semibold transition-colors shrink-0" :class="[desktopServiceModeButtonClass, serviceMode === mode.value ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300']">
                                        <PlatformBadge v-if="mode.source === 'platform'" :platform="mode.label" size="sm" :official="false" />
                                        <template v-else>
                                            <span class="text-base">{{ mode.icon }}</span>
                                            <span>{{ mode.label }}</span>
                                        </template>
                                    </button>
                                </div>
                                <button
                                    v-if="shouldShowDeliveryAgentSelect"
                                    type="button"
                                    class="mt-3 flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-left text-sm transition hover:border-blue-300 hover:bg-blue-50/40"
                                    @click="openDeliveryAgentPicker"
                                >
                                    <span class="font-medium" :class="cartStore.deliveryAgentLabel ? 'text-gray-900' : 'text-gray-500'">
                                        {{ cartStore.deliveryAgentLabel || deliveryAgentPlaceholder }}
                                    </span>
                                    <span class="text-xs font-semibold text-blue-600">Modifier</span>
                                </button>
                            </div>
                            <!-- Articles header -->
                            <div class="shrink-0 border-b border-gray-200 flex items-center justify-between text-[11px] uppercase tracking-wide text-gray-500" :class="desktopHeaderRowClass">
                                <span>Articles</span>
                                <span>{{ cartStore.items.length }} articles</span>
                            </div>
                            <!-- Articles list -->
                            <div :class="desktopItemsListClass">
                                <div v-if="cartStore.items.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                                    <ShoppingCartIcon class="w-12 h-12 mb-2" />
                                    <p class="text-sm">Ticket vide</p>
                                </div>
                                <div v-else class="divide-y divide-dashed divide-gray-200">
                                    <div v-for="(item, index) in cartStore.items" :key="index" class="px-4 py-2.5">
                                        <div class="flex items-center gap-2 text-sm text-gray-700">
                                            <button type="button" class="min-w-0 flex flex-1 items-center text-left" :title="item.article_name" @click="openItemEditModal(index, item)">
                                                <span class="min-w-0 truncate font-semibold text-gray-900">{{ item.article_name }}</span>
                                            </button>
                                            <div class="flex shrink-0 items-center gap-0.5 rounded-full border border-gray-200 bg-gray-50 px-1 py-0.5">
                                                <button @click.stop="updateQuantity(index, item.quantity - 1)" class="rounded-full p-1 text-gray-500 transition hover:bg-white hover:text-gray-800" type="button"><MinusIcon class="h-3.5 w-3.5" /></button>
                                                <span class="min-w-[34px] text-center text-xs font-semibold text-gray-700">× {{ item.quantity }}</span>
                                                <button @click.stop="updateQuantity(index, item.quantity + 1)" class="rounded-full p-1 text-gray-500 transition hover:bg-white hover:text-gray-800" type="button"><PlusIcon class="h-3.5 w-3.5" /></button>
                                            </div>
                                            <span class="hidden min-w-[24px] flex-1 border-b border-dotted border-gray-300 md:block"></span>
                                            <span class="shrink-0 text-sm font-semibold text-gray-900">{{ formatCurrency(getItemLineTotal(item)) }}</span>
                                            <button @click.stop="removeItem(index)" class="shrink-0 rounded-full p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Supprimer" type="button"><TrashIcon class="h-4 w-4" /></button>
                                        </div>
                                        <div v-if="item.comment || Number(item.discount_amount) > 0 || getVariantDisplay(item) || getOptionDisplays(item).length > 0" class="mt-1 space-y-0.5 pl-2 text-[11px] text-gray-500">
                                            <p v-if="item.comment">- Note: {{ item.comment }}</p>
                                            <p v-if="Number(item.discount_amount) > 0" class="text-rose-600">
                                                - Remise{{ item.applied_discount?.label ? ` : ${item.applied_discount.label}` : '' }}
                                                (-{{ formatCurrency(item.discount_amount) }})
                                            </p>
                                            <p v-if="getVariantDisplay(item)">
                                                - {{ getVariantDisplay(item).label }}
                                            </p>
                                            <p v-for="option in getOptionDisplays(item)" :key="option.key">
                                                - {{ option.label }}
                                                <span v-if="option.price > 0">({{ formatOptionsPrice(option.price) }})</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Totals -->
                            <div class="shrink-0 border-t border-gray-200 space-y-2 text-sm bg-white" :class="desktopPanelPaddingClass">
                                <div class="flex justify-between text-gray-600 border-b border-dashed border-gray-200 pb-2"><span class="font-medium">Total HT :</span><span class="font-semibold text-gray-900">{{ formatCurrency(cartStore.subtotal) }}</span></div>
                                <div class="flex justify-between text-gray-600 border-b border-dashed border-gray-200 pb-2"><span class="font-medium">TVA :</span><span class="font-semibold text-gray-900">{{ formatCurrency(cartStore.taxAmount) }}</span></div>
                                <div class="flex justify-between text-gray-600 border-b border-dashed border-gray-200 pb-2"><span class="font-medium">Remise :</span><span class="font-semibold text-gray-900">{{ formatCurrency(cartStore.discountTotal) }}</span></div>
                                <div class="flex items-baseline justify-between pt-2">
                                    <span class="text-lg font-bold text-gray-900">TOTAL TTC :</span>
                                    <button
                                        type="button"
                                        class="text-2xl font-bold text-green-600 underline decoration-green-600/30 decoration-dotted underline-offset-4 transition hover:text-green-700"
                                        title="Détails du total — remises et taxes"
                                        @click="openTotalDetailsModal"
                                    >
                                        {{ formatCurrency(cartStore.total) }}
                                    </button>
                                </div>
                            </div>
                            <!-- Buttons -->
                            <div class="shrink-0 border-t border-gray-200 space-y-2 bg-white" :class="desktopPanelPaddingClass">
                                <button @click="openPaymentModal" :disabled="cartStore.items.length === 0 || paymentModalLoading" class="w-full py-3 px-4 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" type="button">PASSER AU PAIEMENT</button>
                                <button
                                    @click="handleTicketButtonClick"
                                    :disabled="ticketButtonDisabled"
                                    :title="ticketButtonHint"
                                    class="w-full py-3 px-4 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                                    type="button"
                                >
                                    {{ ticketButtonLabel }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile: flat layout (no extra card) -->
                    <div v-show="useBottomSheetCart && isCartExpanded" class="flex min-h-0 flex-1 flex-col overflow-hidden bg-white">
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
                        <div v-if="serviceModesEnabled" class="px-3 py-1.5 border-b border-gray-100 bg-gray-50">
                            <div class="flex items-center gap-1.5 overflow-x-auto whitespace-nowrap no-scrollbar">
                                <button v-for="mode in serviceModes" :key="mode.value" @click="selectServiceMode(mode.value)" type="button"
                                    class="flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-semibold transition-colors shrink-0"
                                    :class="serviceMode === mode.value ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-200 bg-white text-gray-600'">
                                    <PlatformBadge v-if="mode.source === 'platform'" :platform="mode.label" size="sm" :official="false" />
                                    <template v-else>
                                        <span>{{ mode.icon }}</span>
                                        <span>{{ mode.label }}</span>
                                    </template>
                                </button>
                            </div>
                            <button
                                v-if="shouldShowDeliveryAgentSelect && cartStore.deliveryAgentLabel"
                                type="button"
                                class="mt-2 flex w-full items-center justify-between rounded-lg border border-blue-100 bg-blue-50/60 px-3 py-2 text-left text-[11px] transition active:bg-blue-50"
                                @click="openDeliveryAgentPicker"
                            >
                                <span class="truncate font-semibold text-gray-900">{{ cartStore.deliveryAgentLabel }}</span>
                                <span class="shrink-0 font-semibold text-blue-600">Changer</span>
                            </button>
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
                            <div v-else class="divide-y divide-dashed divide-gray-200">
                                <div v-for="(item, index) in cartStore.items" :key="index" class="px-3 py-2.5 active:bg-gray-50">
                                    <div class="flex items-center gap-1.5 text-sm text-gray-700">
                                        <button type="button" class="min-w-0 flex flex-1 items-center text-left" :title="item.article_name" @click="openItemEditModal(index, item)">
                                            <span class="min-w-0 truncate font-semibold text-gray-900">{{ item.article_name }}</span>
                                        </button>
                                        <div class="flex shrink-0 items-center gap-0.5 rounded-full border border-gray-200 bg-gray-50 px-1 py-0.5">
                                            <button @click.stop="updateQuantity(index, item.quantity - 1)" class="rounded-full p-1 text-gray-500 transition hover:bg-white hover:text-gray-800 active:bg-white" type="button">
                                                <MinusIcon class="h-3 w-3" />
                                            </button>
                                            <span class="min-w-[30px] text-center text-[11px] font-semibold text-gray-700">× {{ item.quantity }}</span>
                                            <button @click.stop="updateQuantity(index, item.quantity + 1)" class="rounded-full p-1 text-gray-500 transition hover:bg-white hover:text-gray-800 active:bg-white" type="button">
                                                <PlusIcon class="h-3 w-3" />
                                            </button>
                                        </div>
                                        <span class="hidden min-w-[18px] flex-1 border-b border-dotted border-gray-300 sm:block"></span>
                                        <span class="shrink-0 text-sm font-bold text-gray-900">{{ formatCurrency(getItemLineTotal(item)) }}</span>
                                        <button @click.stop="removeItem(index)" class="shrink-0 rounded-full p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-600 active:bg-red-50" type="button">
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <div v-if="item.comment || Number(item.discount_amount) > 0 || getVariantDisplay(item) || getOptionDisplays(item).length > 0" class="mt-1 space-y-0.5 pl-1.5 text-[10px] text-gray-500">
                                        <p v-if="item.comment">- Note: {{ item.comment }}</p>
                                        <p v-if="Number(item.discount_amount) > 0" class="text-rose-600">
                                            - Remise{{ item.applied_discount?.label ? ` : ${item.applied_discount.label}` : '' }}
                                            (-{{ formatCurrency(item.discount_amount) }})
                                        </p>
                                        <p v-if="getVariantDisplay(item)">
                                            - {{ getVariantDisplay(item).label }}
                                        </p>
                                        <p v-for="option in getOptionDisplays(item)" :key="option.key">
                                            - {{ option.label }}
                                            <span v-if="option.price > 0">({{ formatOptionsPrice(option.price) }})</span>
                                        </p>
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
                                <button
                                    type="button"
                                    class="text-sm font-bold text-green-600 ml-auto underline decoration-green-600/30 decoration-dotted underline-offset-2 transition hover:text-green-700"
                                    title="Détails du total — remises et taxes"
                                    @click="openTotalDetailsModal"
                                >
                                    {{ formatCurrency(cartStore.total) }}
                                </button>
                            </div>
                            <!-- Action buttons side by side -->
                            <div class="px-3 py-2 flex gap-2">
                                <button @click="handleTicketButtonClick"
                                    :disabled="ticketButtonDisabled"
                                    :title="ticketButtonHint"
                                    class="flex-1 py-2.5 text-xs font-bold rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                                    type="button">
                                    {{ ticketButtonLabel }}
                                </button>
                                <button @click="openPaymentModal" :disabled="cartStore.items.length === 0 || paymentModalLoading"
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
            :sale="paymentModalSale"
            total-mode="gross"
            @close="closePaymentModal"
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

        <SaveTicketModal
            v-if="showSaveTicketModal"
            :cart-data="cartStore.getCartData()"
            :cart-items="cartStore.items"
            :default-customer-id="cartStore.customerId"
            :default-customer-name="cartStore.customerName"
            :default-delivery-mode="cartStore.deliveryMode"
            :default-notes="ticketNotes || cartStore.notes"
            :saved-tickets="savedTickets"
            :saved-tickets-loading="savedTicketsLoading"
            :current-sale-id="cartStore.currentSaleId"
            @close="showSaveTicketModal = false"
            @refresh-tickets="fetchSavedTickets"
            @saved="handleTicketSaved"
        />

        <OpenTicketsModal
            v-if="showOpenTicketsModal"
            :saved-tickets="savedTickets"
            :saved-tickets-loading="savedTicketsLoading"
            :loading-saved-ticket-id="loadingSavedTicketId"
            :deleting-saved-ticket-id="deletingSavedTicketId"
            :current-sale-id="cartStore.currentSaleId"
            :current-service-mode="cartStore.deliveryMode"
            @close="showOpenTicketsModal = false"
            @refresh-tickets="fetchSavedTickets"
            @delete-ticket="deleteSavedTicket"
            @load-ticket="handleOpenTicketModalLoad"
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
            <div class="bg-white rounded-2xl shadow-xl p-6 max-w-2xl w-full mx-4 space-y-5 max-h-[85vh] overflow-y-auto">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Modifier l'article</h3>
                        <p class="mt-1 text-base font-semibold text-slate-800">{{ editingItem?.article_name }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ getItemUnitPriceLabel(editingItem) }}/u</p>
                    </div>
                    <button
                        type="button"
                        @click="closeItemEditModal"
                        class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                    >
                        ✕
                    </button>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-medium text-gray-700">Quantité</span>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="adjustEditQuantity(-1)"
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 transition hover:bg-gray-100"
                            >
                                <MinusIcon class="h-4 w-4" />
                            </button>
                            <input
                                ref="editQuantityInput"
                                v-model.number="editQuantity"
                                type="number"
                                min="1"
                                inputmode="numeric"
                                class="w-24 rounded-xl border border-gray-300 bg-white px-3 py-2 text-center text-lg font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                @focus="selectEditQuantity"
                                @click="selectEditQuantity"
                            >
                            <button
                                type="button"
                                @click="adjustEditQuantity(1)"
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 transition hover:bg-gray-100"
                            >
                                <PlusIcon class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Commentaire</label>
                    <textarea
                        v-model.trim="editItemComment"
                        rows="3"
                        placeholder="Ajouter un commentaire pour cet article..."
                        class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    ></textarea>
                    <p class="text-xs text-gray-500">Le commentaire est enregistré dans le panier et prêt pour un futur support complet côté ticket.</p>
                </div>

                <div class="space-y-3 border-t border-slate-200 pt-4">
                    <div class="flex items-center justify-between gap-3">
                        <label class="block text-sm font-medium text-gray-700">Remise</label>
                        <span v-if="editDiscountPreview > 0" class="text-sm font-semibold text-rose-600">
                            -{{ formatCurrency(editDiscountPreview) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500">Remise appliquée uniquement sur cet article (ligne du ticket).</p>

                    <div
                        v-if="!customListsStore.discountEnabled"
                        class="rounded-xl border border-dashed border-amber-200 bg-amber-50 px-3 py-3 text-sm text-amber-800"
                    >
                        Les remises sont désactivées. Activez la liste dans
                        <strong>Paramètres → Listes personnalisées → Remises</strong>, puis enregistrez.
                    </div>
                    <div
                        v-else-if="customListsStore.activeDiscounts.length === 0"
                        class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-3 text-sm text-slate-600"
                    >
                        Aucune remise active. Créez-en une dans
                        <strong>Paramètres → Listes personnalisées → Remises</strong>, cochez <strong>Actif</strong>, puis cliquez <strong>Enregistrer</strong>.
                    </div>
                    <div v-else class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <label
                            class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 transition"
                            :class="!editSelectedDiscountId ? 'border-primary-500 bg-primary-50' : 'border-gray-200 bg-white'"
                        >
                            <input
                                v-model="editSelectedDiscountId"
                                type="radio"
                                class="h-4 w-4 border-gray-300 text-primary-600"
                                value=""
                            >
                            <span class="text-sm font-medium text-gray-800">Aucune remise</span>
                        </label>
                        <label
                            v-for="discount in customListsStore.activeDiscounts"
                            :key="discount.id"
                            class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 transition"
                            :class="editSelectedDiscountId === discount.id ? 'border-primary-500 bg-primary-50' : 'border-gray-200 bg-white'"
                        >
                            <input
                                v-model="editSelectedDiscountId"
                                type="radio"
                                class="h-4 w-4 border-gray-300 text-primary-600"
                                :value="discount.id"
                            >
                            <div class="min-w-0 text-sm">
                                <p class="font-medium text-gray-900">{{ discount.label }}</p>
                                <p class="text-xs text-gray-500">{{ formatDiscountSummary(discount) }}</p>
                            </div>
                        </label>
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
                                    <span class="ml-2 text-orange-600 font-semibold">{{ formatCurrency(variant.price_impact) }}</span>
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
                <div class="flex gap-2 justify-end border-t border-slate-200 pt-4">
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

        <DeliveryAgentPickerModal
            v-if="showDeliveryAgentPicker"
            :agents="visibleDeliveryAgents"
            :selected-id="cartStore.deliveryAgentId"
            :loading="deliveryAgentsLoading"
            :title="deliveryAgentPickerTitle"
            :empty-message="deliveryAgentEmptyMessage"
            :format-label="formatDeliveryAgentLabel"
            @close="showDeliveryAgentPicker = false"
            @select="selectDeliveryAgentFromPicker"
            @add="goToAddDeliveryAgent"
        />

        <TotalDetailsModal
            v-if="showTotalDetailsModal"
            :subtotal="cartStore.subtotal"
            :discounts="customListsStore.activeDiscounts"
            :taxes="customListsStore.activeTaxes"
            :settings-tax-enabled="settingsStore.taxEnabled"
            :settings-tax-rate="settingsStore.taxRate"
            :settings-tax-name="settingsStore.taxName"
            :initial-discount-ids="totalDetailsInitialDiscountIds"
            :initial-tax-ids="totalDetailsInitialTaxIds"
            :initial-comment="cartStore.notes"
            :format-currency="formatCurrency"
            @close="showTotalDetailsModal = false"
            @apply="applyTotalDetails"
        />

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
import { ref, computed, nextTick, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useCartStore } from '../stores/cart'
import { useArticlesStore } from '../stores/articles'
import { useCustomListsStore, calculateLineDiscountAmount, formatDiscountSummary, SETTINGS_TAX_ID } from '../stores/customLists'
import { useSettingsStore } from '../stores/settings'
import { useOfflineStore } from '../stores/offline'
import { useUiStore } from '../stores/ui'
import { salesApi, optionsApi, articlesApi, customersApi, deliveryAgentsApi } from '../api'
import PaymentMultiModal from '../components/pos/PaymentMultiModal.vue'
import TotalDetailsModal from '../components/pos/TotalDetailsModal.vue'
import CalculatorModal from '../components/pos/CalculatorModal.vue'
import OptionsModal from '../components/pos/OptionsModal.vue'
import SaveTicketModal from '../components/pos/SaveTicketModal.vue'
import OpenTicketsModal from '../components/pos/OpenTicketsModal.vue'
import DeliveryAgentPickerModal from '../components/pos/DeliveryAgentPickerModal.vue'
import SelectOptionsModal from '../components/pos/SelectOptionsModal.vue'
import SelectVariantsModal from '../components/pos/SelectVariantsModal.vue'
import OptionFormContent from '../components/forms/OptionFormContent.vue'
import PlatformBadge from '../components/common/PlatformBadge.vue'
import {
    Bars3Icon,
    MagnifyingGlassIcon,
    ShoppingCartIcon,
    MinusIcon,
    PlusIcon,
    TrashIcon,
    UserPlusIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const cartStore = useCartStore()
const articlesStore = useArticlesStore()
const customListsStore = useCustomListsStore()
const settingsStore = useSettingsStore()
const offlineStore = useOfflineStore()
const uiStore = useUiStore()
const { appSidebarOpen } = storeToRefs(uiStore)
const { posCategoryDisplayMode } = storeToRefs(settingsStore)
const categoriesDisplayMode = posCategoryDisplayMode
const showPaymentModal = ref(false)
const paymentModalLoading = ref(false)
const paymentModalSale = ref(null)
const showCalculator = ref(false)
const showOptionsModal = ref(false)
const showSaveTicketModal = ref(false)
const showOpenTicketsModal = ref(false)
const showCustomerSelector = ref(false)
const showDeliveryAgentPicker = ref(false)
const showNotesModal = ref(false)
const showDiscountModal = ref(false)
const showTotalDetailsModal = ref(false)
const totalDetailsInitialDiscountIds = ref([])
const totalDetailsInitialTaxIds = ref([])
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
const editItemComment = ref('')
const editSelectedDiscountId = ref('')
const editingArticle = ref(null)
const editSelectedVariantId = ref(null)
const editSelectedOptions = ref([])
const editOptionsPrice = ref(0)
const editQuantityInput = ref(null)
const searchQuery = ref('')
const selectedCategoryId = ref('all')
const customerSearch = ref('')
const posCustomers = ref([])
const posCustomersLoading = ref(false)
const deliveryAgents = ref([])
const deliveryAgentsLoading = ref(false)
const savedTickets = ref([])
const savedTicketsLoading = ref(false)
const loadingSavedTicketId = ref(null)
const deletingSavedTicketId = ref(null)
const ticketNotes = ref('')
const discountAmount = ref(0)
const discountPercent = ref(0)
const loadedTicketSnapshot = ref(null)
const loadedTicketDetails = ref(null)
const currentPage = ref(1)
const itemsPerPage = ref(20)
const creatingOption = ref(false)
const posRoot = ref(null)
const isFullscreen = ref(false)
const viewportWidth = ref(0)
const viewportHeight = ref(0)
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
const isLandscape = computed(() => viewportWidth.value > viewportHeight.value)
const isPhoneLandscape = computed(() => isMobile.value && isLandscape.value)
const isPhonePortrait = computed(() => isMobile.value && !isLandscape.value)
const isTabletPortrait = computed(() => isTablet.value && !isLandscape.value)
const isSmallPhone = computed(() => viewportWidth.value > 0 && viewportWidth.value < 430)
const isCompactViewport = computed(() => viewportHeight.value > 0 && viewportHeight.value < 820)
const useBottomSheetCart = computed(() => isMobile.value)
const useSideCartLayout = computed(() => !isMobile.value)
const effectiveCategoriesDisplayMode = computed(() => {
    if (isMobile.value) {
        return 'top'
    }
    if (isTablet.value) {
        return 'bottom'
    }
    return categoriesDisplayMode.value
})
const headerClass = computed(() => {
    if (isPhoneLandscape.value) {
        return 'flex-row items-center px-3 py-2 gap-2'
    }

    return useBottomSheetCart.value
        ? 'flex-col px-4 py-3'
        : 'flex-row items-center px-3 py-2.5 sm:gap-3 lg:px-4 lg:py-3'
})
const headerInfoWrapClass = computed(() => {
    if (isPhoneLandscape.value) {
        return 'min-w-0 flex-shrink gap-2'
    }

    return isMobile.value ? 'w-full min-w-0 gap-3' : 'w-full min-w-0 sm:w-auto gap-3'
})
const headerMenuButtonClass = computed(() => {
    return isPhoneLandscape.value ? 'p-1.5' : 'p-2'
})
const headerMenuIconClass = computed(() => {
    return isPhoneLandscape.value ? 'w-4 h-4' : 'w-5 h-5'
})
const headerKickerClass = computed(() => {
    return isPhoneLandscape.value ? 'text-[9px] tracking-[0.18em]' : 'text-xs'
})
const headerTitleClass = computed(() => {
    return isPhoneLandscape.value ? 'text-base leading-tight' : 'text-lg'
})
const headerCountClass = computed(() => {
    return isPhoneLandscape.value ? 'text-[10px]' : 'text-xs'
})
const headerSearchWrapClass = computed(() => {
    return isPhoneLandscape.value ? 'max-w-none' : ''
})
const headerSearchInputClass = computed(() => {
    return isPhoneLandscape.value
        ? 'rounded-lg px-3 py-1.5 pr-9 text-xs'
        : 'rounded-xl px-4 py-2.5 text-sm'
})
const headerSearchIconClass = computed(() => {
    return isPhoneLandscape.value ? 'right-2.5 w-4 h-4' : 'right-3 w-5 h-5'
})
const headerActionsWrapClass = computed(() => {
    if (isPhoneLandscape.value) {
        return 'gap-1.5 w-auto flex-shrink-0'
    }

    return useBottomSheetCart.value ? 'gap-2 w-full' : 'gap-2 w-full sm:w-auto sm:justify-end'
})
const headerCartButtonClass = computed(() => {
    return isPhoneLandscape.value ? 'gap-1.5 px-2.5 py-1.5' : 'gap-2 px-3 py-2'
})
const headerIconButtonClass = computed(() => {
    return isPhoneLandscape.value ? 'p-1.5' : 'p-2'
})
const headerActionIconClass = computed(() => {
    return isPhoneLandscape.value ? 'w-4 h-4' : 'w-5 h-5'
})
const headerActionTextClass = computed(() => {
    return isPhoneLandscape.value ? 'text-[11px]' : 'text-xs'
})
const headerFullscreenIconClass = computed(() => {
    return isPhoneLandscape.value ? 'text-base' : 'text-lg'
})
const contentPaddingClass = computed(() => {
    if (!useBottomSheetCart.value) return ''
    if (!isCartExpanded.value) return 'pb-[88px]'
    if (isCartFullscreen.value) return 'pb-0'
    return 'pb-[55vh]'
})
const articleGridClass = computed(() => {
    if (isPhoneLandscape.value) {
        return 'grid h-full min-h-0 grid-cols-3 gap-2 overflow-y-auto pr-1 content-start'
    }

    if (useBottomSheetCart.value) {
        return 'grid h-full min-h-0 grid-cols-1 gap-4 overflow-y-auto pr-1 content-start'
    }

    if (isTablet.value) {
        if (isLandscape.value) {
            return 'grid h-full min-h-0 grid-cols-4 gap-2.5 overflow-y-auto pr-1'
        }
        const gapClass = isCompactViewport.value ? 'gap-2' : 'gap-2.5'
        return `grid h-full min-h-0 grid-cols-3 gap-2 overflow-y-auto pr-1 ${gapClass}`
    }

    const gapClass = isCompactViewport.value ? 'gap-2.5' : 'gap-3'
    return `grid h-full min-h-0 grid-cols-4 grid-rows-5 auto-rows-fr ${gapClass}`
})
const articleCardClass = computed(() => {
    if (isPhonePortrait.value) {
        return 'flex min-h-[128px] flex-row rounded-[24px] shadow-md active:scale-[0.99]'
    }

    if (isPhoneLandscape.value) {
        return 'flex min-h-[140px] flex-col rounded-[20px] shadow-sm active:scale-[0.99]'
    }

    return 'flex h-full min-h-0 flex-col rounded-2xl shadow-sm'
})
const articleMediaClass = computed(() => {
    if (isPhonePortrait.value) {
        return 'flex h-full w-[118px] min-w-[118px] items-center justify-center overflow-hidden rounded-l-[24px] bg-gray-100'
    }

    if (isPhoneLandscape.value) {
        return 'aspect-[16/9] rounded-t-[20px] bg-gray-100 flex items-center justify-center overflow-hidden'
    }

    if (isTablet.value) {
        if (isLandscape.value) {
            const height = isCompactViewport.value ? 'min-h-[52px]' : 'min-h-[56px]'
            return `flex min-h-0 flex-1 items-center justify-center overflow-hidden bg-gray-100 ${height}`
        }
        const height = isCompactViewport.value ? 'min-h-[64px]' : 'min-h-[80px]'
        return `flex min-h-0 flex-1 items-center justify-center overflow-hidden bg-gray-100 ${height}`
    }

    const compactHeight = isCompactViewport.value
        ? 'min-h-[56px]'
        : 'min-h-[88px]'
    return `flex min-h-0 flex-1 items-center justify-center overflow-hidden bg-gray-100 ${compactHeight}`
})
const articleBodyClass = computed(() => {
    if (isPhonePortrait.value) {
        return 'flex min-w-0 flex-1 flex-col justify-between space-y-2 p-4'
    }

    if (isPhoneLandscape.value) {
        return 'space-y-1.5 border-t border-gray-100 p-2.5'
    }

    if (isTablet.value) {
        if (isLandscape.value) {
            return 'space-y-0.5 border-t border-gray-100 px-1.5 py-1'
        }
        return isCompactViewport.value
            ? 'space-y-0.5 border-t border-gray-100 px-2 py-1.5'
            : 'space-y-0.5 border-t border-gray-100 px-2 py-2'
    }

    return isCompactViewport.value
        ? 'space-y-0.5 border-t border-gray-100 px-2 py-1.5'
        : 'space-y-1 border-t border-gray-100 px-3 py-2.5'
})
const articleTitleClass = computed(() => {
    if (isPhoneLandscape.value) {
        return 'line-clamp-2 text-xs font-semibold leading-snug text-gray-900'
    }

    if (isPhonePortrait.value) {
        return 'line-clamp-2 text-lg font-semibold leading-snug text-gray-900'
    }

    if (isTablet.value) {
        if (isLandscape.value) {
            return 'line-clamp-1 text-[10px] font-semibold leading-tight text-gray-900'
        }
        return 'line-clamp-2 text-xs font-semibold leading-tight text-gray-900'
    }

    return isCompactViewport.value
        ? 'overflow-hidden text-xs font-semibold leading-tight text-gray-900'
        : 'overflow-hidden text-sm font-semibold leading-tight text-gray-900'
})
const articlePriceClass = computed(() => {
    if (isPhoneLandscape.value) {
        return 'text-sm font-bold text-primary-600'
    }

    if (isPhonePortrait.value) {
        return 'text-xl font-bold text-primary-600'
    }

    if (isTablet.value) {
        if (isLandscape.value) {
            return 'text-[10px] font-bold text-primary-600'
        }
        return 'text-xs font-bold text-primary-600'
    }

    return isCompactViewport.value
        ? 'text-sm font-bold text-primary-600'
        : 'text-base font-bold text-primary-600'
})
const bottomCategoriesRailClass = computed(() => {
    return 'grid max-h-[112px] grid-flow-col grid-rows-2 auto-cols-max gap-2 overflow-x-auto pb-1'
})
const bottomCategoryButtonClass = computed(() => {
    return isCompactViewport.value ? 'min-w-[140px] justify-start' : 'min-w-[156px] justify-start'
})
const desktopCartLayoutClass = computed(() => {
    if (!useSideCartLayout.value) {
        return 'hidden'
    }

    return isCompactViewport.value || isPhoneLandscape.value
        ? 'flex h-full min-h-0 flex-col p-3'
        : 'flex h-full min-h-0 flex-col p-4'
})
const desktopCartCardClass = computed(() => {
    return 'overflow-hidden'
})
const desktopPanelPaddingClass = computed(() => {
    return isTabletPortrait.value || isPhoneLandscape.value ? 'px-3 py-2.5' : 'px-4 py-3'
})
const desktopHeaderRowClass = computed(() => {
    return isTabletPortrait.value || isPhoneLandscape.value ? 'px-3 py-2' : 'px-4 py-2'
})
const desktopServiceModesWrapClass = computed(() => {
    return isTabletPortrait.value || isPhoneLandscape.value
        ? 'flex items-center gap-2 overflow-x-auto whitespace-nowrap no-scrollbar'
        : 'flex items-center gap-2 overflow-x-auto whitespace-nowrap'
})
const desktopServiceModeButtonClass = computed(() => {
    return isTabletPortrait.value || isPhoneLandscape.value
        ? 'px-2.5 py-2 text-[11px]'
        : 'px-3 py-2 text-xs'
})
const desktopItemsListClass = computed(() => {
    if (isTablet.value || isPhoneLandscape.value) {
        return 'flex-1 min-h-[180px] overflow-y-auto'
    }

    return 'flex-1 min-h-[220px] overflow-y-auto'
})
const ticketPanelClass = computed(() => {
    if (useBottomSheetCart.value) {
        let h
        if (!isCartExpanded.value) h = 'h-[88px]'
        else if (isCartFullscreen.value) h = 'h-[100dvh]'
        else h = 'h-[60vh]'
        return `fixed inset-x-0 bottom-0 z-50 rounded-t-2xl border-t border-gray-200 shadow-2xl overflow-hidden ${h}`
    }
    if (isPhoneLandscape.value) {
        return 'w-[320px] flex-shrink-0 border-l border-gray-200 h-full overflow-hidden'
    }
    if (isTablet.value) {
        const widthClass = isLandscape.value || isCompactViewport.value ? 'w-[320px]' : 'w-[340px]'
        return `${widthClass} flex-shrink-0 border-l border-gray-200 h-full overflow-hidden`
    }
    return `${isCompactViewport.value ? 'w-[350px]' : 'w-[380px]'} flex-shrink-0 border-l border-gray-200 h-full overflow-hidden`
})

const serviceModes = computed(() => {
    return customListsStore.activeServiceModes.map((mode) => ({
        ...mode,
        icon: getServiceModeIcon(mode),
    }))
})

const serviceModesEnabled = computed(() => {
    return customListsStore.serviceModeEnabled && serviceModes.value.length > 0
})

const serviceMode = computed({
    get: () => cartStore.deliveryMode,
    set: (value) => cartStore.setDeliveryMode(value),
})

const selectedServiceModeMeta = computed(() => customListsStore.getServiceModeMeta(serviceMode.value))

const editLineSubtotalBeforeDiscount = computed(() => {
    const unitPrice = editSelectedVariantId.value
        ? Number(editingArticle.value?.variants?.find((variant) => variant.id === editSelectedVariantId.value)?.price_impact) || 0
        : Number(editingArticle.value?.sell_price) || Number(editingItem.value?.unit_price) || 0
    const optionsPrice = Number(editOptionsPrice.value) || 0
    const quantity = Math.max(1, Number(editQuantity.value) || 1)
    return (unitPrice + optionsPrice) * quantity
})

const editDiscountPreview = computed(() => {
    if (!editSelectedDiscountId.value) return 0
    const discount = customListsStore.activeDiscounts.find(
        (item) => String(item.id) === String(editSelectedDiscountId.value)
    )
    return calculateLineDiscountAmount(discount, editLineSubtotalBeforeDiscount.value)
})
const selectedPlatformModeKey = computed(() => normalizePlatformKey(serviceMode.value))
const matchesPlatformMode = computed(() => {
    return deliveryAgents.value.some((agent) => (
        agent.type === 'platform'
        && normalizePlatformKey(agent.platform_name) === selectedPlatformModeKey.value
    ))
})

const shouldShowDeliveryAgentSelect = computed(() => {
    return serviceModesEnabled.value
        && (selectedServiceModeMeta.value.requires_delivery_agent || matchesPlatformMode.value)
})

const visibleDeliveryAgents = computed(() => {
    if (selectedServiceModeMeta.value.requires_delivery_agent) {
        return deliveryAgents.value.filter((agent) => agent.type === 'internal')
    }

    if (matchesPlatformMode.value) {
        return deliveryAgents.value.filter((agent) => (
            agent.type === 'platform'
            && normalizePlatformKey(agent.platform_name) === selectedPlatformModeKey.value
        ))
    }

    return []
})

const deliveryAgentPlaceholder = computed(() => {
    if (selectedServiceModeMeta.value.requires_delivery_agent) {
        return visibleDeliveryAgents.value.length ? 'Choisir un livreur' : 'Aucun livreur interne disponible'
    }

    if (matchesPlatformMode.value) {
        return visibleDeliveryAgents.value.length
            ? `Choisir un livreur ${serviceMode.value}`
            : `Aucun livreur ${serviceMode.value} disponible`
    }

    return 'Aucun livreur'
})

const deliveryAgentPickerTitle = computed(() => {
    if (matchesPlatformMode.value && serviceMode.value) {
        return `Choisir un livreur ${serviceMode.value}`
    }

    return 'Choisir un livreur'
})

const deliveryAgentEmptyMessage = computed(() => {
    if (selectedServiceModeMeta.value.requires_delivery_agent) {
        return 'Aucun livreur interne disponible.'
    }

    if (matchesPlatformMode.value) {
        return `Aucun livreur ${serviceMode.value} disponible.`
    }

    return 'Aucun livreur disponible.'
})

const filteredCustomers = computed(() => {
    const query = customerSearch.value.trim().toLowerCase()
    const list = Array.isArray(posCustomers.value) ? posCustomers.value : []
    if (!query) return list.slice(0, 20)
    return list
        .filter((customer) => {
            const name = String(customer?.name || '').toLowerCase()
            const phone = String(customer?.phone || '').toLowerCase()
            return name.includes(query) || phone.includes(query)
        })
        .slice(0, 20)
})

function setDeliveryAgentSelection(agent) {
    cartStore.setDeliveryAgent(agent ? {
        id: agent.id,
        label: formatDeliveryAgentLabel(agent),
        name: agent.name,
    } : null)
}

function openDeliveryAgentPicker() {
    showDeliveryAgentPicker.value = true
}

function selectDeliveryAgentFromPicker(agent) {
    setDeliveryAgentSelection(agent)
    showDeliveryAgentPicker.value = false
}

function goToAddDeliveryAgent() {
    showDeliveryAgentPicker.value = false
    router.push({ name: 'livreurs.create' })
}

async function selectServiceMode(modeValue) {
    serviceMode.value = modeValue
    await nextTick()

    if (shouldShowDeliveryAgentSelect.value) {
        openDeliveryAgentPicker()
        return
    }

    showDeliveryAgentPicker.value = false
}

function clearActiveTicketSelection() {
    showSaveTicketModal.value = false
    showOpenTicketsModal.value = false
    showCustomerSelector.value = false
    showDeliveryAgentPicker.value = false
    showPaymentModal.value = false
    ticketNotes.value = ''
    discountAmount.value = 0
    discountPercent.value = 0
    loadedTicketSnapshot.value = null
    loadedTicketDetails.value = null
    cartStore.clearCart()
}

function normalizePosCustomer(customer) {
    if (!customer) return null
    const fullName = `${customer.nom || ''} ${customer.prenom || ''}`.trim()
    return {
        ...customer,
        id: customer.id,
        name: customer.name || fullName || customer.raison_sociale || 'Client',
        phone: customer.phone || customer.telephone || customer.mobile || '',
    }
}

function formatDeliveryAgentLabel(agent) {
    if (!agent) return ''
    if (agent.type === 'platform' && agent.platform_name) {
        return agent.name && agent.name !== agent.platform_name
            ? `${agent.name} · ${agent.platform_name}`
            : agent.platform_name
    }

    return agent.name
}

function isSavedTicket(sale) {
    const type = String(sale?.ticket_type || '').toLowerCase()
    const origin = String(sale?.origin || '').toLowerCase()

    return ['liste', 'personnalise', 'commande'].includes(type)
        || origin === 'menu_commande'
        || origin === 'livraison'
}

function getSavedTicketTitle(ticket) {
    return ticket?.ticket_name || ticket?.reference || `Ticket #${ticket?.id || '-'}`
}

function formatSavedTicketType(ticket) {
    const type = String(ticket?.ticket_type || '').toLowerCase()
    if (type === 'liste') return ticket?.ticket_group ? `Liste · ${ticket.ticket_group}` : 'Ticket liste'
    if (type === 'personnalise') return ticket?.ticket_group ? `Personnalisé · ${ticket.ticket_group}` : 'Ticket personnalisé'
    if (type === 'commande' || ['menu_commande', 'livraison'].includes(String(ticket?.origin || '').toLowerCase())) {
        return 'Commande'
    }
    return 'Ticket'
}

function formatSavedTicketDate(ticket) {
    const source = ticket?.updated_at || ticket?.created_at
    if (!source) return '-'

    return new Date(source).toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })
}

function isSavedTicketActive(ticketId) {
    return Number(cartStore.currentSaleId || 0) === Number(ticketId || 0)
}

const activeSavedTicket = computed(() => {
    return savedTickets.value.find((ticket) => isSavedTicketActive(ticket.id)) || null
})

const hasSavedTickets = computed(() => savedTickets.value.length > 0)

const isPreparingNewPosOrder = computed(() => {
    return cartStore.items.length > 0 && !cartStore.currentSaleId
})

const hasActiveSavedTicketChanges = computed(() => {
    if (!cartStore.currentSaleId || !loadedTicketSnapshot.value) {
        return false
    }

    return buildCurrentTicketSnapshot() !== loadedTicketSnapshot.value
})

const canSaveCurrentTicket = computed(() => {
    if (cartStore.currentSaleId) {
        return true
    }

    return cartStore.items.length > 0
})

const ticketButtonDisabled = computed(() => {
    if (!cartStore.currentSaleId && cartStore.items.length === 0) {
        return false
    }

    return !canSaveCurrentTicket.value
})

const ticketButtonHint = computed(() => {
    if (cartStore.currentSaleId) {
        if (hasActiveSavedTicketChanges.value && cartStore.items.length > 0) {
            return 'Enregistrer les modifications sur le ticket actuel'
        }

        return 'Fermer le ticket déjà sauvegardé'
    }

    if (cartStore.items.length === 0) {
        return 'Afficher les tickets enregistrés'
    }

    return 'Sauvegarder le ticket en cours'
})

const ticketButtonLabel = computed(() => {
    return cartStore.currentSaleId || cartStore.items.length > 0 ? 'Sauvegarder' : 'Tickets enregistrés'
})

async function fetchSavedTickets() {
    if (savedTicketsLoading.value) return
    if (!offlineStore.isOnline || localStorage.getItem('offline_guest_mode') === 'true') {
        savedTickets.value = []
        return
    }

    savedTicketsLoading.value = true
    try {
        const { data } = await salesApi.pending()
        const rows = Array.isArray(data) ? data : (data?.data || [])
        savedTickets.value = rows
            .filter(isSavedTicket)
            .sort((a, b) => new Date(b.updated_at || b.created_at || 0) - new Date(a.updated_at || a.created_at || 0))
    } catch (error) {
        console.error('Failed to fetch saved tickets:', error)
        savedTickets.value = []
    } finally {
        savedTicketsLoading.value = false
    }
}

async function loadSavedTicket(ticketId) {
    if (!ticketId || loadingSavedTicketId.value === ticketId) return false

    const hasDifferentCart = cartStore.items.length > 0 && Number(cartStore.currentSaleId || 0) !== Number(ticketId)
    if (hasDifferentCart && !confirm('Le ticket en cours sera remplacé. Continuer ?')) {
        return false
    }

    loadingSavedTicketId.value = ticketId
    try {
        const { data } = await salesApi.get(ticketId)
        cartStore.hydrateFromSale(data)
        ticketNotes.value = data?.notes || ''
        discountAmount.value = Number(data?.discount_amount) || 0
        discountPercent.value = Number(data?.discount_percent) || 0
        loadedTicketSnapshot.value = buildSaleSnapshot(data)
        loadedTicketDetails.value = {
            id: data?.id || null,
            origin: data?.origin || 'pos',
            ticket_type: data?.ticket_type || null,
            ticket_name: data?.ticket_name || null,
            ticket_group: data?.ticket_group || null,
            order_status: data?.order_status || 'confirmee',
        }
        showCustomerSelector.value = false
        showPaymentModal.value = false
        paymentModalSale.value = null

        if (isMobile.value) {
            isCartExpanded.value = true
            isCartFullscreen.value = false
        }
        return true
    } catch (error) {
        console.error('Failed to load saved ticket:', error)
        alert(error.response?.data?.message || "Impossible d'ouvrir ce ticket.")
        return false
    } finally {
        loadingSavedTicketId.value = null
    }
}

async function deleteSavedTicket(ticketId) {
    if (!ticketId || deletingSavedTicketId.value === ticketId) return
    if (!confirm('Supprimer ce ticket sauvegardé ?')) {
        return
    }

    deletingSavedTicketId.value = ticketId
    try {
        await salesApi.delete(ticketId)

        savedTickets.value = savedTickets.value.filter((ticket) => Number(ticket.id) !== Number(ticketId))

        if (Number(cartStore.currentSaleId || 0) === Number(ticketId)) {
            clearActiveTicketSelection()
        }
    } catch (error) {
        console.error('Failed to delete saved ticket:', error)
        alert(error.response?.data?.message || "Impossible de supprimer ce ticket.")
    } finally {
        deletingSavedTicketId.value = null
    }
}

function getServiceModeIcon(mode) {
    if (mode?.operational_mode === 'delivery') return '🚚'
    if (mode?.operational_mode === 'dine_in') return '🍽️'
    return '🥡'
}

function normalizePlatformKey(value) {
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '')
}

function ensureValidServiceModeSelection() {
    if (!serviceModesEnabled.value) {
        return
    }

    const currentSelection = customListsStore.findServiceMode(serviceMode.value, { includeInactive: false })
    if (!currentSelection) {
        serviceMode.value = customListsStore.defaultServiceModeValue()
    }
}

async function fetchDeliveryAgents() {
    if (deliveryAgentsLoading.value) return
    deliveryAgentsLoading.value = true

    try {
        const response = await deliveryAgentsApi.list({
            paginate: false,
            active: true,
        })
        const payload = response.data
        const rows = Array.isArray(payload) ? payload : (payload?.data || [])
        deliveryAgents.value = rows
    } catch (error) {
        console.error('Failed to fetch delivery agents:', error)
        deliveryAgents.value = []
    } finally {
        deliveryAgentsLoading.value = false
    }
}

function syncSelectedCustomerWithLiveList() {
    if (!cartStore.customerId) return
    const matchedCustomer = posCustomers.value.find((customer) => Number(customer.id) === Number(cartStore.customerId))
    if (!matchedCustomer) {
        cartStore.setCustomer(null, 'Client Anonyme')
        return
    }
    if (cartStore.customerName !== matchedCustomer.name) {
        cartStore.setCustomer(matchedCustomer.id, matchedCustomer.name)
    }
}

async function fetchPosCustomers() {
    if (posCustomersLoading.value) return
    posCustomersLoading.value = true
    try {
        const response = await customersApi.list({
            paginate: false,
            active: true,
            with_stats: true,
            _ts: Date.now(), // cache-busting to avoid stale SW/api cache
        })
        const payload = response.data
        const rawCustomers = Array.isArray(payload) ? payload : (payload?.data || [])
        posCustomers.value = rawCustomers.map(normalizePosCustomer).filter(Boolean)
        syncSelectedCustomerWithLiveList()
    } catch (error) {
        console.error('Failed to fetch POS customers:', error)
        posCustomers.value = []
        cartStore.setCustomer(null, 'Client Anonyme')
    } finally {
        posCustomersLoading.value = false
    }
}

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
    if (!useBottomSheetCart.value) return
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
    const height = Math.round(
        window.visualViewport?.height || document.documentElement.clientHeight || window.innerHeight
    )
    const shortEdge = Math.min(width, height)

    viewportWidth.value = width
    viewportHeight.value = height

    if (shortEdge < 768) {
        screenMode.value = 'mobile'
    } else if (width < 1280) {
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

function getItemBaseUnitPrice(item) {
    if (item?.selected_variant) {
        return Number(item.selected_variant.price_impact ?? item.variant_price ?? item.unit_price) || 0
    }

    return Number(item?.unit_price) || 0
}

function getItemUnitPrice(item) {
    const unitPrice = getItemBaseUnitPrice(item)
    const optionsPrice = Number(item?.options_price) || 0
    return unitPrice + optionsPrice
}

function getItemUnitPriceLabel(item) {
    return formatCurrency(getItemUnitPrice(item))
}

function getItemLineTotal(item) {
    const unitPrice = getItemBaseUnitPrice(item)
    const optionsPrice = Number(item.options_price) || 0
    const quantity = Number(item.quantity) || 0
    const discount = Number(item.discount_amount) || 0
    return (unitPrice + optionsPrice) * quantity - discount
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
    return { label: name }
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
    await customListsStore.fetchList('remises', { force: true })
    editingCartIndex.value = index
    editingItem.value = item
    editQuantity.value = Number(item.quantity) || 1
    editItemComment.value = String(item.comment || '').trim()
    editSelectedDiscountId.value = item.applied_discount?.id ?? ''
    editingArticle.value = await resolveEditableArticle(item)
    editSelectedVariantId.value = item.selected_variant?.id || null
    if (!editSelectedVariantId.value && Array.isArray(editingArticle.value?.variants)) {
        const matchedVariant = editingArticle.value.variants.find((variant) => (
            Number(variant.price_impact) === Number(item.unit_price || 0)
        ))
        editSelectedVariantId.value = matchedVariant?.id || null
    }
    editSelectedOptions.value = Array.isArray(item.selected_options)
        ? JSON.parse(JSON.stringify(item.selected_options))
        : []
    updateEditOptionsPrice()
    showItemEditModal.value = true
    await nextTick()
    selectEditQuantity()
}

function closeItemEditModal() {
    showItemEditModal.value = false
    editingItem.value = null
    editItemComment.value = ''
    editSelectedDiscountId.value = ''
    editingArticle.value = null
    editSelectedVariantId.value = null
    editSelectedOptions.value = []
    editOptionsPrice.value = 0
    editingCartIndex.value = null
}

function selectEditQuantity(event = null) {
    const target = event?.target || editQuantityInput.value
    requestAnimationFrame(() => {
        target?.select?.()
    })
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
        const resolvedUnitPrice = selectedVariant
            ? Number(selectedVariant.price_impact) || 0
            : Number(editingArticle.value.sell_price) || 0
        item.selected_variant = selectedVariant
        item.unit_price = resolvedUnitPrice
        item.variant_price = selectedVariant ? Number(selectedVariant.price_impact) || 0 : 0
        item.selected_options = normalizeSelectedOptions(editSelectedOptions.value)
        item.options_price = Number(editOptionsPrice.value) || 0
        item.comment = editItemComment.value || ''
        const selectedDiscount = editSelectedDiscountId.value !== '' && editSelectedDiscountId.value != null
            ? customListsStore.activeDiscounts.find(
                (discount) => String(discount.id) === String(editSelectedDiscountId.value)
            ) || null
            : null
        cartStore.setItemDiscount(editingCartIndex.value, selectedDiscount)
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

function openSaveTicketModal() {
    if (!canSaveCurrentTicket.value) {
        return
    }

    cartStore.setNotes(ticketNotes.value)
    showSaveTicketModal.value = true
    fetchSavedTickets()
}

function openTicketsModal() {
    showOpenTicketsModal.value = true
    fetchSavedTickets()
}

function handleTicketButtonClick() {
    if (cartStore.currentSaleId) {
        handleCurrentSavedTicketAction()
        return
    }

    if (cartStore.items.length > 0) {
        openSaveTicketModal()
        return
    }

    openTicketsModal()
}

async function handleOpenTicketModalLoad(ticketId) {
    const loaded = await loadSavedTicket(ticketId)
    if (loaded) {
        showOpenTicketsModal.value = false
    }
}

function handleTicketSaved() {
    clearActiveTicketSelection()
    fetchSavedTickets()
}

async function handleCurrentSavedTicketAction() {
    if (!cartStore.currentSaleId) {
        return
    }

    if (!hasActiveSavedTicketChanges.value || cartStore.items.length === 0) {
        clearActiveTicketSelection()
        await fetchSavedTickets()
        return
    }

    const payload = {
        ...cartStore.getCartData(),
        origin: loadedTicketDetails.value?.origin || activeSavedTicket.value?.origin || 'pos',
        ticket_type: loadedTicketDetails.value?.ticket_type || activeSavedTicket.value?.ticket_type || null,
        ticket_name: loadedTicketDetails.value?.ticket_name || activeSavedTicket.value?.ticket_name || null,
        ticket_group: loadedTicketDetails.value?.ticket_group || activeSavedTicket.value?.ticket_group || null,
        order_status: loadedTicketDetails.value?.order_status || activeSavedTicket.value?.order_status || 'confirmee',
        notes: ticketNotes.value || cartStore.notes || '',
    }

    try {
        await salesApi.update(cartStore.currentSaleId, payload)
        clearActiveTicketSelection()
        await fetchSavedTickets()
    } catch (error) {
        console.error('Failed to update current ticket:', error)
        alert(error.response?.data?.message || "Impossible d'enregistrer ce ticket.")
    }
}

async function openPaymentModal() {
    if (cartStore.items.length === 0 || paymentModalLoading.value) {
        return
    }

    paymentModalLoading.value = true
    try {
        if (offlineStore.isOnline && cartStore.currentSaleId) {
            const { data } = await salesApi.get(cartStore.currentSaleId)
            paymentModalSale.value = data
        } else {
            paymentModalSale.value = null
        }

        showPaymentModal.value = true
    } catch (error) {
        console.error('Failed to prepare payment modal:', error)
        alert(error.response?.data?.message || "Impossible d'ouvrir le paiement.")
    } finally {
        paymentModalLoading.value = false
    }
}

function closePaymentModal() {
    showPaymentModal.value = false
    paymentModalSale.value = null
}

async function completeSale(payments) {
    const SALES_STORAGE_KEY = 'pos_sales'
    
    try {
        const normalizePaymentType = (type) => {
            if (type === 'check') return 'cheque'
            return type
        }

        const normalizedPayments = (payments || []).map((payment) => ({
            ...payment,
            payment_type: normalizePaymentType(payment.payment_type || payment.type),
        }))

        const data = cartStore.getCartData()
        const saleData = {
            ...data,
            payments: normalizedPayments, // Multiple payments
            status: 'completed',
        }

        // Check if customer_id is from localStorage (large timestamp number > 1 billion)
        const isLocalStorageCustomer = data.customer_id > 1000000000

        // If offline or using localStorage customer, save to localStorage directly
        if (!offlineStore.isOnline || isLocalStorageCustomer) {
            // Save to localStorage for customers page
            const existingSales = localStorage.getItem(SALES_STORAGE_KEY)
            const sales = existingSales ? JSON.parse(existingSales) : []
            const completeSaleData = {
                id: Date.now(),
                customer_id: data.customer_id,
                items_count: data.items_count || data.items?.length || 0,
                subtotal: data.subtotal || 0,
                tax: data.tax || 0,
                total: data.total || 0,
                discount_amount: data.discount_amount || 0,
                discount_percent: data.discount_percent || 0,
                payment_method: normalizedPayments?.[0]?.payment_type || 'cash',
                status: 'completed',
                date: new Date().toISOString(),
            }
            console.log('DEBUG PosView saveSale:', completeSaleData)
            sales.push(completeSaleData)
            localStorage.setItem(SALES_STORAGE_KEY, JSON.stringify(sales))
            
            cartStore.clearCart()
            loadedTicketSnapshot.value = null
            loadedTicketDetails.value = null
            showPaymentModal.value = false
            paymentModalSale.value = null
            await fetchSavedTickets()
            
            if (!offlineStore.isOnline) {
                alert('Vente sauvegardée hors ligne! Elle sera synchronisée automatiquement.')
            } else {
                alert('Vente complétée avec succès!')
            }
            return
        }

        // If online with API customer, process normally.
        // Always sync the pending sale with the current cart before adding payments
        // so backend totals match the cart total used in the payment modal.
        if (!cartStore.currentSaleId) {
            const response = await salesApi.create(data)
            cartStore.setSaleId(response.data.id)
        } else {
            await salesApi.update(cartStore.currentSaleId, data)
        }

        // Add all payments
        for (const payment of normalizedPayments) {
            await salesApi.addPayment(cartStore.currentSaleId, payment)
        }

        // Complete sale
        await salesApi.complete(cartStore.currentSaleId)

        // Save to localStorage for customers page
        const existingSales = localStorage.getItem(SALES_STORAGE_KEY)
        const sales = existingSales ? JSON.parse(existingSales) : []
        const completeSaleData = {
            id: cartStore.currentSaleId,
            ...saleData,
            date: new Date().toISOString(),
        }
        sales.push(completeSaleData)
        localStorage.setItem(SALES_STORAGE_KEY, JSON.stringify(sales))

        // Clear cart
        cartStore.clearCart()
        loadedTicketSnapshot.value = null
        loadedTicketDetails.value = null
        showPaymentModal.value = false
        paymentModalSale.value = null
        await fetchSavedTickets()

        alert('Vente complétée avec succès!')
    } catch (error) {
        console.error('Failed to complete sale:', error)
        paymentModalSale.value = null
        if (cartStore.currentSaleId) {
            try {
                const { data } = await salesApi.get(cartStore.currentSaleId)
                if (!Array.isArray(data?.payments) || data.payments.length === 0) {
                    cartStore.setSaleId(null)
                }
            } catch {
                cartStore.setSaleId(null)
            }
        }
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
            item.unit_price = Number(selectedVariant.price_impact) || 0
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

function openTotalDetailsModal() {
    totalDetailsInitialDiscountIds.value = [...cartStore.appliedCartDiscountIds]

    if (cartStore.appliedCartTaxIds.length > 0) {
        totalDetailsInitialTaxIds.value = [...cartStore.appliedCartTaxIds]
    } else if (customListsStore.defaultTaxes.length > 0) {
        totalDetailsInitialTaxIds.value = customListsStore.defaultTaxes.map((tax) => tax.id)
    } else if (settingsStore.taxEnabled && settingsStore.taxRate > 0) {
        totalDetailsInitialTaxIds.value = [SETTINGS_TAX_ID]
    } else {
        totalDetailsInitialTaxIds.value = []
    }

    showTotalDetailsModal.value = true
}

function applyTotalDetails({ discountIds = [], taxIds = [], comment = '' } = {}) {
    cartStore.setAppliedCartAdjustments({ discountIds, taxIds })
    cartStore.setNotes(comment)
    ticketNotes.value = comment
    showTotalDetailsModal.value = false
}

function resetCart() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser le ticket?')) {
        clearActiveTicketSelection()
    }
}

function normalizeSnapshotItems(items = []) {
    return (items || []).map((item) => ({
        article_id: Number(item?.article_id || 0),
        quantity: Number(item?.quantity || 0),
        unit_price: Number(item?.unit_price || 0),
        variant_price: Number(item?.variant_price || 0),
        options_price: Number(item?.options_price || 0),
        discount_amount: Number(item?.discount_amount || 0),
        comment: String(item?.comment || '').trim(),
        selected_options: Array.isArray(item?.selected_options)
            ? JSON.parse(JSON.stringify(item.selected_options))
            : [],
    }))
}

function buildCurrentTicketSnapshot() {
    return JSON.stringify({
        sale_id: Number(cartStore.currentSaleId || 0) || null,
        customer_id: cartStore.customerId || null,
        service_mode: cartStore.deliveryMode || '',
        delivery_agent_id: cartStore.deliveryAgentId || null,
        discount_amount: Number(cartStore.discountAmount || 0),
        discount_percent: Number(cartStore.discountPercent || 0),
        notes: String(cartStore.notes || '').trim(),
        items: normalizeSnapshotItems(cartStore.items),
    })
}

function buildSaleSnapshot(sale) {
    return JSON.stringify({
        sale_id: Number(sale?.id || 0) || null,
        customer_id: sale?.customer_id || sale?.customer?.id || null,
        service_mode: customListsStore.findServiceMode(
            sale?.service_mode || sale?.delivery_mode,
            { includeInactive: true }
        )?.value || customListsStore.defaultServiceModeValue(),
        delivery_agent_id: sale?.delivery_agent_id || sale?.delivery_agent?.id || null,
        discount_amount: Number(sale?.discount_amount || 0),
        discount_percent: Number(sale?.discount_percent || 0),
        notes: String(sale?.notes || '').trim(),
        items: normalizeSnapshotItems(sale?.items || []),
    })
}

watch([searchQuery, selectedCategoryId], () => {
    currentPage.value = 1
})

watch(showCustomerSelector, async (open) => {
    if (!open) return
    await fetchPosCustomers()
})

watch(useBottomSheetCart, (value) => {
    if (value) {
        isCartExpanded.value = false
        isCartFullscreen.value = false
    } else {
        uiStore.closePosSidebar()
        isCartExpanded.value = true
        isCartFullscreen.value = false
    }
})

watch(serviceMode, () => {
    if (!shouldShowDeliveryAgentSelect.value) {
        showDeliveryAgentPicker.value = false
        cartStore.setDeliveryAgent(null)
        return
    }

    const matchesCurrentAgent = visibleDeliveryAgents.value.some(
        (agent) => String(agent.id) === String(cartStore.deliveryAgentId || '')
    )

    if (!matchesCurrentAgent) {
        cartStore.setDeliveryAgent(null)
    }
})

watch(visibleDeliveryAgents, (agents) => {
    if (!shouldShowDeliveryAgentSelect.value) return

    const matchesCurrentAgent = agents.some(
        (agent) => String(agent.id) === String(cartStore.deliveryAgentId || '')
    )

    if (!matchesCurrentAgent) {
        cartStore.setDeliveryAgent(null)
    }
})

watch(serviceModes, () => {
    ensureValidServiceModeSelection()
}, { deep: true })

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
    await Promise.all([
        customListsStore.fetchList('mode_de_service', { force: true }),
        customListsStore.fetchList('remises', { force: true }),
        customListsStore.fetchList('taxes', { force: true }),
    ])
    ensureValidServiceModeSelection()
    await articlesStore.refresh()
    await fetchPosCustomers()
    await fetchDeliveryAgents()
    await fetchSavedTickets()
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
