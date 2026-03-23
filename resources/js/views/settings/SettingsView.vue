<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Paramètres</h1>
            <p class="text-gray-500">Configurez les paramètres de votre point de vente</p>
        </div>

        <!-- Tabs Navigation -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex border-b border-gray-200 overflow-x-auto">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors"
                    :class="activeTab === tab.id ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                >
                    {{ tab.label }}
                </button>
            </div>

            <div class="p-6">
                <!-- Infos Générales -->
                <div v-show="activeTab === 'general'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <BuildingStorefrontIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Infos Générales
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du magasin *</label>
                            <input v-model="settings.store_name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom de votre magasin">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                            <input v-model="settings.store_city" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ville">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                            <input v-model="settings.store_address" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Adresse complète">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                            <input v-model="settings.store_country" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Pays">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                            <input v-model="settings.store_phone" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="+212 600 000 000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="settings.store_email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="contact@magasin.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ICE (Identifiant Commun de l'Entreprise)</label>
                            <input v-model="settings.store_ice" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="000000000000000">
                        </div>
                    </div>
                </div>

                <!-- Matériel -->
                <div v-show="activeTab === 'material'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <ComputerDesktopIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Matériel
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Imprimante</label>
                            <select v-model="settings.printer_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Sélectionner une imprimante</option>
                                <option value="thermal_80">Thermique 80mm</option>
                                <option value="thermal_58">Thermique 58mm</option>
                                <option value="a4">A4 Standard</option>
                                <option value="none">Aucune</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom/Port de l'imprimante</label>
                            <input v-model="settings.printer_name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="USB001, COM1, etc.">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Scanner</label>
                            <select v-model="settings.scanner_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Sélectionner un scanner</option>
                                <option value="usb">USB (Code-barres)</option>
                                <option value="bluetooth">Bluetooth</option>
                                <option value="camera">Caméra</option>
                                <option value="none">Aucun</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tiroir-caisse</label>
                            <select v-model="settings.cash_drawer" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Sélectionner</option>
                                <option value="connected">Connecté à l'imprimante</option>
                                <option value="usb">USB indépendant</option>
                                <option value="manual">Manuel</option>
                                <option value="none">Aucun</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Écran client</label>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" v-model="settings.customer_display" value="enabled" class="mr-2 text-primary-500">
                                    <span class="text-sm">Activé</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" v-model="settings.customer_display" value="disabled" class="mr-2 text-primary-500">
                                    <span class="text-sm">Désactivé</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Devise -->
                <div v-show="activeTab === 'currency'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <CurrencyDollarIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Devise
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pays de devise</label>
                            <select v-model="settings.currency_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="MA">Maroc</option>
                                <option value="FR">France</option>
                                <option value="US">États-Unis</option>
                                <option value="GB">Royaume-Uni</option>
                                <option value="AE">Émirats Arabes Unis</option>
                                <option value="SA">Arabie Saoudite</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Code de devise</label>
                            <input v-model="settings.currency_code" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="MAD, EUR, USD...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Symbole</label>
                            <input v-model="settings.currency_symbol" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="DH, €, $...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Position du symbole</label>
                            <select v-model="settings.currency_position" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="before">Avant le montant ($ 100)</option>
                                <option value="after">Après le montant (100 DH)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Format du Reçu -->
                <div v-show="activeTab === 'receipt'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <DocumentTextIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Format du Reçu
                    </h2>
                    
                    <!-- Logo -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3">Logo</h3>
                        <div class="flex items-center space-x-4">
                            <div class="w-24 h-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50">
                                <img v-if="settings.receipt_logo" :src="settings.receipt_logo" alt="Logo" class="max-w-full max-h-full object-contain">
                                <PhotoIcon v-else class="w-8 h-8 text-gray-400" />
                            </div>
                            <div class="flex-1">
                                <input type="file" ref="logoInput" @change="handleLogoUpload" accept="image/*" class="hidden">
                                <button @click="$refs.logoInput.click()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                                    Choisir un logo
                                </button>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG jusqu'à 2MB. Recommandé: 200x200px</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="settings.receipt_show_logo" class="mr-2 text-primary-500 rounded">
                                <span class="text-sm text-gray-700">Afficher le logo sur le reçu</span>
                            </label>
                        </div>
                    </div>

                    <!-- En-tête -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">En-tête</label>
                        <textarea v-model="settings.receipt_header" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Texte affiché en haut du reçu..."></textarea>
                    </div>

                    <!-- Pied de page -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pied de page</label>
                        <textarea v-model="settings.receipt_footer" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Merci pour votre visite!"></textarea>
                    </div>

                    <!-- Note -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                        <textarea v-model="settings.receipt_note" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Note additionnelle sur le reçu..."></textarea>
                    </div>

                    <!-- QR Code -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3">QR Code</h3>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="settings.receipt_show_qr" class="mr-2 text-primary-500 rounded">
                                <span class="text-sm text-gray-700">Afficher un QR code sur le reçu</span>
                            </label>
                            <div v-if="settings.receipt_show_qr">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contenu du QR code</label>
                                <input v-model="settings.receipt_qr_content" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="URL, numéro de transaction, etc.">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POS -->
                <div v-show="activeTab === 'pos'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <ComputerDesktopIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Affichage POS
                    </h2>
                    <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                        <p class="text-sm text-gray-700 font-medium">Emplacement des catégories</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 border border-gray-200 rounded-lg px-4 py-3 cursor-pointer hover:border-primary-300">
                                <input type="radio" v-model="settings.pos_categories_display_mode" value="sidebar" class="text-primary-500">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Barre latérale gauche</p>
                                    <p class="text-xs text-gray-500">Affiche les catégories dans le panneau gauche, comme sur la borne.</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 border border-gray-200 rounded-lg px-4 py-3 cursor-pointer hover:border-primary-300">
                                <input type="radio" v-model="settings.pos_categories_display_mode" value="bottom" class="text-primary-500">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Barre inférieure</p>
                                    <p class="text-xs text-gray-500">Place les catégories en bas pour maximiser l'espace produits.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div v-show="activeTab === 'custom_lists'" class="space-y-5">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <QueueListIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Listes personnalisées
                    </h2>

                    <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-200 bg-gradient-to-r from-slate-50 to-white">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Mode de service</p>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900">Gestion dynamique des modes de service</h3>
                            <p class="mt-1 text-sm text-slate-500">Activez, réorganisez et enrichissez la liste utilisée dans le POS et la création de commande.</p>
                        </div>

                        <div class="p-5 space-y-5">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Activer la gestion des modes de service</p>
                                    <p class="text-sm text-slate-500">Si cette option est désactivée, le champ n’apparaît plus dans le POS ni dans la création de commande.</p>
                                </div>
                                <button
                                    type="button"
                                    class="inline-flex h-8 w-14 items-center rounded-full transition-colors"
                                    :class="serviceModeSettings.is_active ? 'bg-emerald-500 justify-end' : 'bg-slate-300 justify-start'"
                                    @click="serviceModeSettings.is_active = !serviceModeSettings.is_active"
                                >
                                    <span class="mx-1 h-6 w-6 rounded-full bg-white shadow"></span>
                                </button>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="item in serviceModeSettings.items"
                                    :key="item.id"
                                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm space-y-4"
                                >
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start">
                                        <div class="flex-1 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                            <label class="block">
                                                <span class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Mode</span>
                                                <input v-model.trim="item.label" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ex: Sur place">
                                            </label>
                                            <label class="block">
                                                <span class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Valeur</span>
                                                <input v-model.trim="item.value" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ex: Sur place">
                                            </label>
                                            <label class="block">
                                                <span class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Type opérationnel</span>
                                                <select v-model="item.operational_mode" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                                    <option v-for="option in serviceModeOperationalOptions" :key="option.value" :value="option.value">
                                                        {{ option.label }}
                                                    </option>
                                                </select>
                                            </label>
                                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                                                <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                                    <input v-model="item.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                    Actif
                                                </label>
                                                <label class="mt-2 flex items-center gap-2 text-sm font-medium text-slate-700">
                                                    <input v-model="item.requires_delivery_agent" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                    Demande livreur
                                                </label>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50" @click="moveServiceMode(item.id, -1)">Monter</button>
                                            <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50" @click="moveServiceMode(item.id, 1)">Descendre</button>
                                            <button type="button" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50" @click="removeServiceMode(item.id)">Supprimer</button>
                                        </div>
                                    </div>

                                    <div class="grid gap-4 xl:grid-cols-[1fr,1fr]">
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900">Tickets sans groupe</p>
                                                    <p class="text-xs text-slate-500">Tickets simples visibles directement dans le popup POS.</p>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100" @click="addTicketToServiceMode(item.id)">+ Ticket</button>
                                                    <button type="button" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800" @click="addPredefinedTickets(item.id)">+ Ajout tickets prédéfinis</button>
                                                </div>
                                            </div>

                                            <div
                                                v-if="predefinedTicketForms[predefinedTicketFormKey(item.id)]"
                                                class="rounded-xl border border-slate-200 bg-white p-3"
                                            >
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Ajout rapide</p>
                                                <div class="mt-3 grid gap-3 md:grid-cols-3">
                                                    <label class="block">
                                                        <span class="mb-1 block text-xs font-medium text-slate-600">Préfixe</span>
                                                        <input
                                                            v-model.trim="predefinedTicketForms[predefinedTicketFormKey(item.id)].prefix"
                                                            type="text"
                                                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                            placeholder="Ex: Table"
                                                            @keydown.enter.prevent="applyPredefinedTickets(item.id)"
                                                        >
                                                    </label>
                                                    <label class="block">
                                                        <span class="mb-1 block text-xs font-medium text-slate-600">Début</span>
                                                        <input
                                                            v-model.number="predefinedTicketForms[predefinedTicketFormKey(item.id)].start"
                                                            type="number"
                                                            min="1"
                                                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                            @keydown.enter.prevent="applyPredefinedTickets(item.id)"
                                                        >
                                                    </label>
                                                    <label class="block">
                                                        <span class="mb-1 block text-xs font-medium text-slate-600">Fin</span>
                                                        <input
                                                            v-model.number="predefinedTicketForms[predefinedTicketFormKey(item.id)].end"
                                                            type="number"
                                                            min="1"
                                                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                            @keydown.enter.prevent="applyPredefinedTickets(item.id)"
                                                        >
                                                    </label>
                                                </div>
                                                <div class="mt-3 flex flex-wrap justify-end gap-2">
                                                    <button
                                                        type="button"
                                                        class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                                                        @click="closePredefinedTicketForm(item.id)"
                                                    >
                                                        Annuler
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800"
                                                        @click="applyPredefinedTickets(item.id)"
                                                    >
                                                        Générer les tickets
                                                    </button>
                                                </div>
                                            </div>

                                            <div v-if="item.tickets_without_group.length" class="space-y-2">
                                                <div
                                                    v-for="ticket in item.tickets_without_group"
                                                    :key="ticket.id"
                                                    class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-3 md:flex-row md:items-center"
                                                >
                                                    <input v-model.trim="ticket.label" type="text" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom du ticket">
                                                    <label class="flex items-center gap-2 text-sm text-slate-700">
                                                        <input v-model="ticket.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                        Actif
                                                    </label>
                                                    <div class="flex items-center gap-2">
                                                        <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50" @click="moveServiceModeTicket(item.id, ticket.id, -1)">Monter</button>
                                                        <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50" @click="moveServiceModeTicket(item.id, ticket.id, 1)">Descendre</button>
                                                        <button type="button" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50" @click="removeServiceModeTicket(item.id, ticket.id)">Supprimer</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <p v-else class="rounded-xl border border-dashed border-slate-300 px-3 py-4 text-center text-sm text-slate-500">Aucun ticket sans groupe.</p>
                                        </div>

                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900">Groupes de tickets</p>
                                                    <p class="text-xs text-slate-500">Créez des groupes comme Salle, VIP ou Terrasse, puis ajoutez les tickets associés.</p>
                                                </div>
                                                <button type="button" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800" @click="addTicketGroupToServiceMode(item.id)">+ Groupe</button>
                                            </div>

                                            <div v-if="item.ticket_groups.length" class="space-y-3">
                                                <div
                                                    v-for="group in item.ticket_groups"
                                                    :key="group.id"
                                                    class="rounded-xl border border-slate-200 bg-white p-3 space-y-3"
                                                >
                                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                                                        <input v-model.trim="group.label" type="text" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom du groupe">
                                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                                            <input v-model="group.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                            Actif
                                                        </label>
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50" @click="moveTicketGroup(item.id, group.id, -1)">Monter</button>
                                                            <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50" @click="moveTicketGroup(item.id, group.id, 1)">Descendre</button>
                                                            <button type="button" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50" @click="removeTicketGroup(item.id, group.id)">Supprimer</button>
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50" @click="addTicketToServiceMode(item.id, group.id)">+ Ticket</button>
                                                        <button type="button" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800" @click="addPredefinedTickets(item.id, group.id)">+ Ajout tickets prédéfinis</button>
                                                    </div>

                                                    <div
                                                        v-if="predefinedTicketForms[predefinedTicketFormKey(item.id, group.id)]"
                                                        class="rounded-lg border border-slate-200 bg-slate-50 p-3"
                                                    >
                                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Ajout rapide dans ce groupe</p>
                                                        <div class="mt-3 grid gap-3 md:grid-cols-3">
                                                            <label class="block">
                                                                <span class="mb-1 block text-xs font-medium text-slate-600">Préfixe</span>
                                                                <input
                                                                    v-model.trim="predefinedTicketForms[predefinedTicketFormKey(item.id, group.id)].prefix"
                                                                    type="text"
                                                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                                    placeholder="Ex: Table"
                                                                    @keydown.enter.prevent="applyPredefinedTickets(item.id, group.id)"
                                                                >
                                                            </label>
                                                            <label class="block">
                                                                <span class="mb-1 block text-xs font-medium text-slate-600">Début</span>
                                                                <input
                                                                    v-model.number="predefinedTicketForms[predefinedTicketFormKey(item.id, group.id)].start"
                                                                    type="number"
                                                                    min="1"
                                                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                                    @keydown.enter.prevent="applyPredefinedTickets(item.id, group.id)"
                                                                >
                                                            </label>
                                                            <label class="block">
                                                                <span class="mb-1 block text-xs font-medium text-slate-600">Fin</span>
                                                                <input
                                                                    v-model.number="predefinedTicketForms[predefinedTicketFormKey(item.id, group.id)].end"
                                                                    type="number"
                                                                    min="1"
                                                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                                    @keydown.enter.prevent="applyPredefinedTickets(item.id, group.id)"
                                                                >
                                                            </label>
                                                        </div>
                                                        <div class="mt-3 flex flex-wrap justify-end gap-2">
                                                            <button
                                                                type="button"
                                                                class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-white"
                                                                @click="closePredefinedTicketForm(item.id, group.id)"
                                                            >
                                                                Annuler
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800"
                                                                @click="applyPredefinedTickets(item.id, group.id)"
                                                            >
                                                                Générer les tickets
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div v-if="group.tickets.length" class="space-y-2">
                                                        <div
                                                            v-for="ticket in group.tickets"
                                                            :key="ticket.id"
                                                            class="flex flex-col gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3 md:flex-row md:items-center"
                                                        >
                                                            <input v-model.trim="ticket.label" type="text" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom du ticket">
                                                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                                                <input v-model="ticket.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                                Actif
                                                            </label>
                                                            <div class="flex items-center gap-2">
                                                                <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-white" @click="moveServiceModeTicket(item.id, ticket.id, -1, group.id)">Monter</button>
                                                                <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-white" @click="moveServiceModeTicket(item.id, ticket.id, 1, group.id)">Descendre</button>
                                                                <button type="button" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50" @click="removeServiceModeTicket(item.id, ticket.id, group.id)">Supprimer</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <p v-else class="rounded-lg border border-dashed border-slate-300 px-3 py-4 text-center text-sm text-slate-500">Aucun ticket dans ce groupe.</p>
                                                </div>
                                            </div>
                                            <p v-else class="rounded-xl border border-dashed border-slate-300 px-3 py-4 text-center text-sm text-slate-500">Aucun groupe configuré.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Ajouter un mode personnalisé</label>
                                <div class="flex flex-col gap-3 md:flex-row">
                                    <input
                                        v-model.trim="newServiceModeLabel"
                                        type="text"
                                        placeholder="Ex: Drive, Click & Collect"
                                        class="flex-1 rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        @keydown.enter.prevent="addCustomServiceMode"
                                    >
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800"
                                        @click="addCustomServiceMode"
                                    >
                                        <PlusIcon class="w-4 h-4" />
                                        + Ajouter
                                    </button>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button
                                    type="button"
                                    class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-medium hover:bg-slate-50"
                                    @click="resetServiceModeForm"
                                >
                                    Annuler
                                </button>
                                <button
                                    type="button"
                                    class="px-5 py-2.5 rounded-xl bg-primary-500 text-gray-900 font-medium hover:bg-primary-600 disabled:opacity-50"
                                    :disabled="customListSaving || serviceModeSettings.items.length === 0"
                                    @click="saveServiceModeList"
                                >
                                    {{ customListSaving ? 'Enregistrement...' : 'Enregistrer' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Taxes -->
                <div v-show="activeTab === 'taxes'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <ReceiptPercentIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Taxes
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <input v-model="settings.tax_enabled" type="checkbox" id="tax_enabled" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <label for="tax_enabled" class="text-sm font-medium text-gray-700">Activer les taxes</label>
                        </div>
                        <div v-if="settings.tax_enabled" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom de la taxe</label>
                                <input v-model="settings.tax_name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="TVA">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Taux de taxe (%)</label>
                                <input v-model.number="settings.tax_rate" type="number" min="0" max="100" step="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commissions -->
                <div v-show="activeTab === 'commissions'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <BanknotesIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Commissions
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <input v-model="settings.commission_enabled" type="checkbox" id="commission_enabled" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <label for="commission_enabled" class="text-sm font-medium text-gray-700">Activer les commissions</label>
                        </div>
                        <div v-if="settings.commission_enabled" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ID de Commission</label>
                                <input v-model="settings.commission_id" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="COM-001">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Montant / Pourcentage</label>
                                <div class="flex space-x-2">
                                    <input v-model.number="settings.commission_amount" type="number" min="0" step="0.01" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <select v-model="settings.commission_type" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="percentage">%</option>
                                        <option value="fixed">Fixe</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Abonnement -->
                <div v-show="activeTab === 'subscription'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <CreditCardIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Abonnement
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type d'Abonnement</label>
                            <select v-model="settings.subscription_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="free">Gratuit</option>
                                <option value="basic">Basic</option>
                                <option value="pro">Professionnel</option>
                                <option value="enterprise">Entreprise</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Montant</label>
                            <input v-model.number="settings.subscription_amount" type="number" min="0" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Durée</label>
                            <select v-model="settings.subscription_duration" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="monthly">Mensuel</option>
                                <option value="quarterly">Trimestriel</option>
                                <option value="yearly">Annuel</option>
                                <option value="lifetime">À vie</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date d'expiration</label>
                            <input v-model="settings.subscription_expiry" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <!-- Subscription Status Card -->
                    <div class="mt-4 p-4 rounded-lg" :class="subscriptionStatusClass">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium">Statut de l'abonnement</p>
                                <p class="text-sm opacity-80">{{ subscriptionStatusText }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-medium" :class="subscriptionBadgeClass">
                                {{ settings.subscription_type === 'free' ? 'Gratuit' : 'Actif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Automatisation -->
                <div v-show="activeTab === 'automation'" class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <CogIcon class="w-5 h-5 mr-2 text-primary-500" />
                        Tâches automatiques
                    </h2>
                    <p class="text-gray-600 mb-4">Créez automatiquement des tâches selon des conditions. Configurez les règles ci-dessous.</p>
                    <AutomationRules />
                </div>
            </div>
        </div>
        <div v-if="activeTab !== 'custom_lists'" class="flex justify-end space-x-3">
            <button 
                @click="resetSettings"
                class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
            >
                Réinitialiser
            </button>
            <button 
                @click="saveSettings"
                :disabled="saving"
                class="px-6 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50"
            >
                {{ saving ? 'Enregistrement...' : 'Enregistrer les modifications' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { customListsApi, settingsApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { useCustomListsStore } from '../../stores/customLists'
import AutomationRules from './AutomationRules.vue'
import {
    BuildingStorefrontIcon,
    PlusIcon,
    ComputerDesktopIcon,
    CurrencyDollarIcon,
    DocumentTextIcon,
    PhotoIcon,
    QueueListIcon,
    ReceiptPercentIcon,
    BanknotesIcon,
    CreditCardIcon,
    CogIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const customListsStore = useCustomListsStore()
const saving = ref(false)
const activeTab = ref('general')
const customListSaving = ref(false)
const newServiceModeLabel = ref('')

const tabs = [
    { id: 'general', label: 'Infos Générales' },
    { id: 'material', label: 'Matériel' },
    { id: 'currency', label: 'Devise' },
    { id: 'receipt', label: 'Format du Reçu' },
    { id: 'pos', label: 'POS' },
    { id: 'custom_lists', label: 'Listes personnalisées' },
    { id: 'taxes', label: 'Taxes' },
    { id: 'commissions', label: 'Commissions' },
    { id: 'subscription', label: 'Abonnement' },
    { id: 'automation', label: 'Automatisation' },
]

const settings = reactive({
    // Infos Générales
    store_name: '',
    store_city: '',
    store_address: '',
    store_country: 'Maroc',
    store_phone: '',
    store_email: '',
    store_ice: '',
    
    // Matériel
    printer_type: '',
    printer_name: '',
    scanner_type: '',
    cash_drawer: '',
    customer_display: 'disabled',
    
    // Devise
    currency_country: 'MA',
    currency_code: 'MAD',
    currency_symbol: 'DH',
    currency_position: 'after',
    
    // Format du Reçu
    receipt_logo: '',
    receipt_show_logo: true,
    receipt_header: '',
    receipt_footer: 'Merci pour votre visite!',
    receipt_note: '',
    receipt_show_qr: false,
    receipt_qr_content: '',

    // POS
    pos_categories_display_mode: 'sidebar',
    
    // Taxes
    tax_enabled: true,
    tax_name: 'TVA',
    tax_rate: 20,
    
    // Commissions
    commission_enabled: false,
    commission_id: '',
    commission_amount: 0,
    commission_type: 'percentage',
    
    // Abonnement
    subscription_type: 'free',
    subscription_amount: 0,
    subscription_duration: 'monthly',
    subscription_expiry: '',
})

const serviceModeSettings = reactive({
    is_active: true,
    items: [],
})
const predefinedTicketForms = reactive({})

const serviceModeOperationalOptions = [
    { value: 'dine_in', label: 'Sur place' },
    { value: 'pickup', label: 'Emporté' },
    { value: 'delivery', label: 'Livraison' },
]

const subscriptionStatusClass = computed(() => {
    if (settings.subscription_type === 'free') return 'bg-gray-100 text-gray-800'
    return 'bg-green-100 text-green-800'
})

const subscriptionBadgeClass = computed(() => {
    if (settings.subscription_type === 'free') return 'bg-gray-200 text-gray-700'
    return 'bg-green-200 text-green-700'
})

const subscriptionStatusText = computed(() => {
    if (settings.subscription_type === 'free') return 'Vous utilisez la version gratuite'
    if (settings.subscription_expiry) {
        const expiry = new Date(settings.subscription_expiry)
        return `Expire le ${expiry.toLocaleDateString('fr-FR')}`
    }
    return 'Abonnement actif'
})

function handleLogoUpload(event) {
    const file = event.target.files[0]
    if (file) {
        const reader = new FileReader()
        reader.onload = (e) => {
            settings.receipt_logo = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

async function loadSettings() {
    try {
        const response = await settingsApi.all()
        const data = response.data
        
        // Flatten the grouped settings
        Object.entries(data).forEach(([group, values]) => {
            Object.entries(values).forEach(([key, value]) => {
                if (key in settings) {
                    settings[key] = value
                }
            })
        })
    } catch (error) {
        console.error('Failed to load settings:', error)
    }
}

function hydrateServiceModeForm(list) {
    Object.keys(predefinedTicketForms).forEach((key) => {
        delete predefinedTicketForms[key]
    })
    serviceModeSettings.is_active = list?.is_active !== false
    serviceModeSettings.items = [...(list?.items || [])]
        .map((item, index) => ({
            id: item.id ?? createDraftId('mode'),
            label: item.label || item.value || '',
            value: item.value || item.label || '',
            is_active: item.is_active !== false,
            sort_order: Number(item.sort_order ?? index + 1),
            operational_mode: item.operational_mode || 'pickup',
            requires_delivery_agent: item.requires_delivery_agent === true,
            tickets_without_group: normalizeTickets(item.tickets_without_group || []),
            ticket_groups: normalizeTicketGroups(item.ticket_groups || []),
        }))
        .sort((a, b) => a.sort_order - b.sort_order)
}

async function loadServiceModeList() {
    const list = await customListsStore.fetchList('mode_de_service', { force: true })
    hydrateServiceModeForm(list)
}

function reindexServiceModeItems() {
    serviceModeSettings.items = serviceModeSettings.items.map((item, index) => ({
        ...item,
        sort_order: index + 1,
    }))
}

function normalizeTickets(tickets) {
    return [...tickets]
        .map((ticket, index) => ({
            id: ticket.id ?? createDraftId('ticket'),
            label: ticket.label || '',
            is_active: ticket.is_active !== false,
            sort_order: Number(ticket.sort_order ?? index + 1),
        }))
        .sort((a, b) => a.sort_order - b.sort_order)
}

function normalizeTicketGroups(groups) {
    return [...groups]
        .map((group, index) => ({
            id: group.id ?? createDraftId('group'),
            label: group.label || '',
            is_active: group.is_active !== false,
            sort_order: Number(group.sort_order ?? index + 1),
            tickets: normalizeTickets(group.tickets || []),
        }))
        .sort((a, b) => a.sort_order - b.sort_order)
}

function createDraftId(prefix) {
    return `${prefix}-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`
}

function predefinedTicketFormKey(modeId, groupId = null) {
    return groupId ? `${modeId}::${groupId}` : `${modeId}::root`
}

function createPredefinedTicketForm() {
    return {
        prefix: 'Table',
        start: 1,
        end: 5,
    }
}

function moveInArray(list, fromIndex, toIndex) {
    if (fromIndex < 0 || toIndex < 0 || fromIndex >= list.length || toIndex >= list.length) {
        return list
    }

    const clone = [...list]
    const [item] = clone.splice(fromIndex, 1)
    clone.splice(toIndex, 0, item)
    return clone
}

function reindexTickets(tickets) {
    return tickets.map((ticket, index) => ({
        ...ticket,
        sort_order: index + 1,
    }))
}

function reindexTicketGroups(groups) {
    return groups.map((group, index) => ({
        ...group,
        sort_order: index + 1,
        tickets: reindexTickets(group.tickets || []),
    }))
}

function getServiceModeItem(modeId) {
    return serviceModeSettings.items.find((item) => String(item.id) === String(modeId)) || null
}

function getTicketGroup(modeId, groupId) {
    return getServiceModeItem(modeId)?.ticket_groups.find((group) => String(group.id) === String(groupId)) || null
}

function addCustomServiceMode() {
    const label = newServiceModeLabel.value.trim()

    if (!label) {
        return
    }

    const exists = serviceModeSettings.items.some(
        (item) => item.label.trim().toLowerCase() === label.toLowerCase()
    )

    if (exists) {
        alert('Ce mode existe déjà dans la liste.')
        return
    }

    serviceModeSettings.items.push({
        id: createDraftId('mode'),
        label,
        value: label,
        is_active: true,
        sort_order: serviceModeSettings.items.length + 1,
        operational_mode: 'pickup',
        requires_delivery_agent: false,
        tickets_without_group: [],
        ticket_groups: [],
    })
    reindexServiceModeItems()
    newServiceModeLabel.value = ''
}

function removeServiceMode(modeId) {
    serviceModeSettings.items = serviceModeSettings.items.filter((item) => String(item.id) !== String(modeId))
    reindexServiceModeItems()
}

function moveServiceMode(modeId, direction) {
    const sourceIndex = serviceModeSettings.items.findIndex((item) => String(item.id) === String(modeId))
    const targetIndex = sourceIndex + direction
    serviceModeSettings.items = moveInArray(serviceModeSettings.items, sourceIndex, targetIndex)
    reindexServiceModeItems()
}

function addTicketToServiceMode(modeId, groupId = null) {
    const serviceMode = getServiceModeItem(modeId)
    if (!serviceMode) {
        return
    }

    const ticket = {
        id: createDraftId('ticket'),
        label: '',
        is_active: true,
        sort_order: 0,
    }

    if (!groupId) {
        serviceMode.tickets_without_group = reindexTickets([
            ...serviceMode.tickets_without_group,
            ticket,
        ])
        return
    }

    const group = getTicketGroup(modeId, groupId)
    if (!group) return

    group.tickets = reindexTickets([
        ...group.tickets,
        ticket,
    ])
}

function addTicketGroupToServiceMode(modeId) {
    const serviceMode = getServiceModeItem(modeId)
    if (!serviceMode) {
        return
    }

    serviceMode.ticket_groups = reindexTicketGroups([
        ...serviceMode.ticket_groups,
        {
            id: createDraftId('group'),
            label: '',
            is_active: true,
            sort_order: 0,
            tickets: [],
        },
    ])
}

function addPredefinedTickets(modeId, groupId = null) {
    predefinedTicketForms[predefinedTicketFormKey(modeId, groupId)] = createPredefinedTicketForm()
}

function closePredefinedTicketForm(modeId, groupId = null) {
    delete predefinedTicketForms[predefinedTicketFormKey(modeId, groupId)]
}

function applyPredefinedTickets(modeId, groupId = null) {
    const form = predefinedTicketForms[predefinedTicketFormKey(modeId, groupId)]
    if (!form) {
        return
    }

    const prefix = String(form.prefix || '').trim()
    const start = Number(form.start)
    const end = Number(form.end)

    if (!prefix) {
        alert('Veuillez renseigner un préfixe pour les tickets prédéfinis.')
        return
    }

    if (!Number.isInteger(start) || !Number.isInteger(end) || start <= 0 || end < start) {
        alert('La plage définie est invalide.')
        return
    }

    for (let value = start; value <= end; value += 1) {
        appendPredefinedTicket(modeId, `${prefix} ${value}`, groupId)
    }

    closePredefinedTicketForm(modeId, groupId)
}

function appendPredefinedTicket(modeId, label, groupId = null) {
    const serviceMode = getServiceModeItem(modeId)
    if (!serviceMode) {
        return
    }

    const nextTicket = {
        id: createDraftId('ticket'),
        label,
        is_active: true,
        sort_order: 0,
    }

    if (!groupId) {
        const exists = serviceMode.tickets_without_group.some(
            (ticket) => ticket.label.trim().toLowerCase() === label.trim().toLowerCase()
        )
        if (exists) return

        serviceMode.tickets_without_group = reindexTickets([
            ...serviceMode.tickets_without_group,
            nextTicket,
        ])
        return
    }

    const group = getTicketGroup(modeId, groupId)
    if (!group) return

    const exists = group.tickets.some(
        (ticket) => ticket.label.trim().toLowerCase() === label.trim().toLowerCase()
    )
    if (exists) return

    group.tickets = reindexTickets([
        ...group.tickets,
        nextTicket,
    ])
}

function removeServiceModeTicket(modeId, ticketId, groupId = null) {
    const serviceMode = getServiceModeItem(modeId)
    if (!serviceMode) {
        return
    }

    if (!groupId) {
        serviceMode.tickets_without_group = reindexTickets(
            serviceMode.tickets_without_group.filter((ticket) => String(ticket.id) !== String(ticketId))
        )
        return
    }

    const group = getTicketGroup(modeId, groupId)
    if (!group) return
    group.tickets = reindexTickets(
        group.tickets.filter((ticket) => String(ticket.id) !== String(ticketId))
    )
}

function moveServiceModeTicket(modeId, ticketId, direction, groupId = null) {
    const serviceMode = getServiceModeItem(modeId)
    if (!serviceMode) {
        return
    }

    if (!groupId) {
        const sourceIndex = serviceMode.tickets_without_group.findIndex((ticket) => String(ticket.id) === String(ticketId))
        serviceMode.tickets_without_group = reindexTickets(
            moveInArray(serviceMode.tickets_without_group, sourceIndex, sourceIndex + direction)
        )
        return
    }

    const group = getTicketGroup(modeId, groupId)
    if (!group) return

    const sourceIndex = group.tickets.findIndex((ticket) => String(ticket.id) === String(ticketId))
    group.tickets = reindexTickets(
        moveInArray(group.tickets, sourceIndex, sourceIndex + direction)
    )
}

function removeTicketGroup(modeId, groupId) {
    const serviceMode = getServiceModeItem(modeId)
    if (!serviceMode) {
        return
    }

    serviceMode.ticket_groups = reindexTicketGroups(
        serviceMode.ticket_groups.filter((group) => String(group.id) !== String(groupId))
    )
}

function moveTicketGroup(modeId, groupId, direction) {
    const serviceMode = getServiceModeItem(modeId)
    if (!serviceMode) {
        return
    }

    const sourceIndex = serviceMode.ticket_groups.findIndex((group) => String(group.id) === String(groupId))
    serviceMode.ticket_groups = reindexTicketGroups(
        moveInArray(serviceMode.ticket_groups, sourceIndex, sourceIndex + direction)
    )
}

function resetServiceModeForm() {
    newServiceModeLabel.value = ''
    loadServiceModeList()
}

function resetSettings() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser les paramètres ?')) {
        loadSettings()
    }
}

async function saveSettings() {
    saving.value = true
    try {
        const settingsArray = Object.entries(settings).map(([key, value]) => {
            let type = 'string'
            let group = 'general'
            
            if (typeof value === 'boolean') type = 'boolean'
            else if (typeof value === 'number') type = 'number'
            
            if (key.startsWith('currency_')) group = 'currency'
            else if (key.startsWith('tax_')) group = 'tax'
            else if (key.startsWith('receipt_')) group = 'receipt'
            else if (key.startsWith('store_')) group = 'general'
            else if (key.startsWith('printer_') || key.startsWith('scanner_') || key.startsWith('cash_') || key.startsWith('customer_')) group = 'material'
            else if (key.startsWith('pos_')) group = 'pos'
            else if (key.startsWith('commission_')) group = 'commission'
            else if (key.startsWith('subscription_')) group = 'subscription'
            
            return { key, value, type, group }
        })
        
        await settingsApi.update(settingsArray)
        settingsStore.loaded = false
        await settingsStore.fetchSettings()
        alert('Paramètres enregistrés avec succès!')
    } catch (error) {
        console.error('Failed to save settings:', error)
        alert('Erreur lors de l\'enregistrement')
    } finally {
        saving.value = false
    }
}

async function saveServiceModeList() {
    customListSaving.value = true

    try {
        const payload = {
            is_active: serviceModeSettings.is_active,
            items: serviceModeSettings.items.map((item, index) => {
                const parsedId = Number(item.id)

                return {
                    id: Number.isInteger(parsedId) && parsedId > 0 ? parsedId : undefined,
                    label: item.label.trim(),
                    value: (item.value || item.label).trim(),
                    is_active: item.is_active !== false,
                    operational_mode: item.operational_mode || 'pickup',
                    requires_delivery_agent: item.requires_delivery_agent === true,
                    sort_order: index + 1,
                    tickets_without_group: (item.tickets_without_group || [])
                        .filter((ticket) => ticket.label.trim() !== '')
                        .map((ticket, ticketIndex) => {
                            const ticketId = Number(ticket.id)

                            return {
                                id: Number.isInteger(ticketId) && ticketId > 0 ? ticketId : undefined,
                                label: ticket.label.trim(),
                                is_active: ticket.is_active !== false,
                                sort_order: ticketIndex + 1,
                            }
                        }),
                    ticket_groups: (item.ticket_groups || [])
                        .filter((group) => group.label.trim() !== '')
                        .map((group, groupIndex) => {
                            const groupId = Number(group.id)

                            return {
                                id: Number.isInteger(groupId) && groupId > 0 ? groupId : undefined,
                                label: group.label.trim(),
                                is_active: group.is_active !== false,
                                sort_order: groupIndex + 1,
                                tickets: (group.tickets || [])
                                    .filter((ticket) => ticket.label.trim() !== '')
                                    .map((ticket, ticketIndex) => {
                                        const ticketId = Number(ticket.id)

                                        return {
                                            id: Number.isInteger(ticketId) && ticketId > 0 ? ticketId : undefined,
                                            label: ticket.label.trim(),
                                            is_active: ticket.is_active !== false,
                                            sort_order: ticketIndex + 1,
                                        }
                                    }),
                            }
                        }),
                }
            }),
        }

        const { data } = await customListsApi.update('mode_de_service', payload)
        customListsStore.setList('mode_de_service', data)
        hydrateServiceModeForm(data)
        alert('Modes de service enregistrés avec succès!')
    } catch (error) {
        console.error('Failed to save service modes:', error)
        alert(error.response?.data?.message || 'Erreur lors de l\'enregistrement des modes de service.')
    } finally {
        customListSaving.value = false
    }
}

onMounted(async () => {
    await Promise.all([
        loadSettings(),
        loadServiceModeList(),
    ])
})
</script>
