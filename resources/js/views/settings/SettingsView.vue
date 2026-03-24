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
                    <div class="rounded-2xl border border-slate-200 bg-gradient-to-r from-primary-50 to-blue-50 p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold text-gray-900 mb-2 flex items-center">
                                    <QueueListIcon class="w-6 h-6 mr-2 text-primary-600" />
                                    Listes personnalisées
                                </h2>
                                <p class="text-slate-700 font-medium mb-3">Structure simplifiée du POS</p>
                                <p class="text-sm text-slate-600 leading-relaxed">Configurez les trois éléments clés du système POS de manière indépendante :</p>
                                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                                    <li class="flex items-center gap-2">
                                        <span class="inline-block w-2 h-2 bg-primary-500 rounded-full"></span>
                                        <strong>Tickets globaux</strong> - Listes prédéfinies pour le POS (Tables, Commandes spéciales, etc.)
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                                        <strong>Modes de service</strong> - Contextes d'opération (Sur place, Emporté, Livraison, Drive, etc.)
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="inline-block w-2 h-2 bg-emerald-500 rounded-full"></span>
                                        <strong>Modes de paiement</strong> - Méthodes de paiement disponibles (Espèces, Carte, Chèque, etc.)
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                        <div class="border-b border-gray-200 px-5">
                            <div class="flex gap-2 overflow-x-auto py-4">
                                <button
                                    v-for="tab in customListTabs"
                                    :key="tab.id"
                                    type="button"
                                    class="whitespace-nowrap rounded-lg px-6 py-3 text-sm font-semibold transition-all duration-200"
                                    :class="activeCustomListTab === tab.id 
                                        ? 'bg-primary-500 text-white shadow-md' 
                                        : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200'"
                                    @click="activeCustomListTab = tab.id"
                                >
                                    <span v-if="tab.id === 'tickets'">🎫 {{ tab.label }}</span>
                                    <span v-else-if="tab.id === 'service_modes'">⚙️ {{ tab.label }}</span>
                                    <span v-else>💳 {{ tab.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="p-6 space-y-6">
                            <template v-if="activeCustomListTab === 'tickets'">
                                <div class="rounded-xl border-l-4 border-l-primary-500 border border-slate-200 bg-primary-50 p-4">
                                    <p class="text-sm font-bold text-primary-900">🎫 Tickets prédéfinis globaux</p>
                                    <p class="mt-2 text-sm text-primary-700">Utilisés directement dans le workflow enregistrer / ouvrir ticket du POS, sans lien avec les modes de service. Exemples : Tables (1-10), Commandes téléphone, Livraison directe, etc.</p>
                                </div>

                                <div class="space-y-6">
                                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                        <div class="flex items-start gap-3 mb-5 pb-4 border-b border-slate-200">
                                            <span class="text-2xl">📋</span>
                                            <div class="flex-1">
                                                <p class="text-base font-bold text-slate-900">Tickets simples</p>
                                                <p class="text-xs text-slate-500 mt-1">Exemples : Ahmed, Client 1, Livraison express, Commande Web.</p>
                                            </div>
                                        </div>

                                        <div v-if="predefinedTicketSettings.items.filter((item) => item.kind === 'ticket').length" class="space-y-2">
                                            <div
                                                v-for="ticket in predefinedTicketSettings.items.filter((item) => item.kind === 'ticket')"
                                                :key="ticket.id"
                                                class="flex flex-col gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3 md:flex-row md:items-center"
                                            >
                                                <input v-model.trim="ticket.label" type="text" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom du ticket">
                                                <div class="flex items-center gap-2 shrink-0">
                                                    <label class="flex items-center gap-2 text-sm text-slate-700 whitespace-nowrap">
                                                        <input v-model="ticket.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                        Actif
                                                    </label>
                                                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-white transition-colors" @click="movePredefinedTicket(ticket.id, -1)">↑</button>
                                                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-white transition-colors" @click="movePredefinedTicket(ticket.id, 1)">↓</button>
                                                    <button type="button" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors" @click="removePredefinedTicket(ticket.id)">✕</button>
                                                </div>
                                            </div>
                                        </div>
                                        <p v-else class="rounded-xl border border-dashed border-slate-300 px-3 py-4 text-center text-sm text-slate-500">Aucun ticket simple.</p>

                                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 mt-4">
                                            <label class="block text-sm font-semibold text-slate-900 mb-3">➕ Ajouter un ticket</label>
                                            <div class="flex flex-col gap-3 md:flex-row">
                                                <input
                                                    v-model.trim="newPredefinedTicketLabel"
                                                    type="text"
                                                    placeholder="Ex: Table 1, Ahmed, Commande Web"
                                                    class="flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                    @keydown.enter.prevent="addStandalonePredefinedTicket"
                                                >
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-500 px-6 py-2.5 text-sm font-semibold text-white hover:bg-primary-600 transition-colors shrink-0"
                                                    @click="addStandalonePredefinedTicket"
                                                >
                                                    <PlusIcon class="w-4 h-4" />
                                                    Ajouter
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                        <div class="flex items-start gap-3 mb-5 pb-4 border-b border-slate-200">
                                            <span class="text-2xl">📂</span>
                                            <div class="flex-1">
                                                <p class="text-base font-bold text-slate-900">Groupes de tickets</p>
                                                <p class="text-xs text-slate-500 mt-1">Exemples : Salle A, VIP, Terrasse, Zone externe.</p>
                                            </div>
                                        </div>

                                        <div v-if="predefinedTicketSettings.items.filter((item) => item.kind === 'group').length" class="space-y-3">
                                            <div
                                                v-for="group in predefinedTicketSettings.items.filter((item) => item.kind === 'group')"
                                                :key="group.id"
                                                class="rounded-lg border border-slate-200 bg-slate-50 p-3 space-y-3"
                                            >
                                                <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                                    <input v-model.trim="group.label" type="text" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom du groupe">
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <label class="flex items-center gap-2 text-sm text-slate-700 whitespace-nowrap">
                                                            <input v-model="group.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                            Actif
                                                        </label>
                                                        <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-white transition-colors" @click="movePredefinedTicket(group.id, -1)">↑</button>
                                                        <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-white transition-colors" @click="movePredefinedTicket(group.id, 1)">↓</button>
                                                        <button type="button" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors" @click="removePredefinedTicket(group.id)">✕</button>
                                                    </div>
                                                </div>

                                                <div class="flex justify-end">
                                                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-white" @click="addTicketToGroup(group.id)">+ Ticket</button>
                                                </div>

                                                <div v-if="group.tickets.length" class="space-y-2">
                                                    <div
                                                        v-for="ticket in group.tickets"
                                                        :key="ticket.id"
                                                        class="flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-3 md:flex-row md:items-center"
                                                    >
                                                        <input v-model.trim="ticket.label" type="text" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom du ticket">
                                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                                            <input v-model="ticket.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                            Actif
                                                        </label>
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50" @click="moveTicketInGroup(group.id, ticket.id, -1)">Monter</button>
                                                            <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50" @click="moveTicketInGroup(group.id, ticket.id, 1)">Descendre</button>
                                                            <button type="button" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50" @click="removeTicketFromGroup(group.id, ticket.id)">Supprimer</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p v-else class="rounded-lg border border-dashed border-slate-300 px-3 py-4 text-center text-sm text-slate-500">Aucun ticket dans ce groupe.</p>
                                            </div>
                                        </div>
                                        <p v-else class="rounded-xl border border-dashed border-slate-300 px-3 py-4 text-center text-sm text-slate-500">Aucun groupe.</p>

                                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 mt-4">
                                            <label class="block text-sm font-semibold text-slate-900 mb-3">➕ Ajouter un groupe</label>
                                            <div class="flex flex-col gap-3 md:flex-row">
                                                <input
                                                    v-model.trim="newPredefinedGroupLabel"
                                                    type="text"
                                                    placeholder="Ex: Salle A, VIP, Terrasse"
                                                    class="flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                    @keydown.enter.prevent="addPredefinedGroup"
                                                >
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-500 px-6 py-2.5 text-sm font-semibold text-white hover:bg-primary-600 transition-colors shrink-0"
                                                    @click="addPredefinedGroup"
                                                >
                                                    <PlusIcon class="w-4 h-4" />
                                                    Ajouter
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                                    <button type="button" class="px-6 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition-colors" @click="resetPredefinedTicketForm">Annuler</button>
                                    <button type="button" class="px-6 py-2.5 rounded-lg bg-primary-500 text-white font-semibold hover:bg-primary-600 disabled:opacity-50 transition-colors" :disabled="isSavingCustomList('tickets')" @click="savePredefinedTicketsList">
                                        {{ isSavingCustomList('tickets') ? '⏳ Enregistrement...' : '✓ Enregistrer' }}
                                    </button>
                                </div>
                            </template>

                            <template v-else-if="activeCustomListTab === 'service_modes'">
                                <div class="rounded-xl border-l-4 border-l-blue-500 border border-slate-200 bg-blue-50 p-4">
                                    <p class="text-sm font-bold text-blue-900">⚙️ Modes de service visibles dans le POS</p>
                                    <p class="mt-2 text-sm text-blue-700">Définissez les contextes d'opération disponibles. Ces modes n'affectent pas les tickets globaux. Exemples : Sur place, Emporté, Livraison, Drive, Click & Collect.</p>
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                    <div class="space-y-3">
                                        <div v-if="serviceModeSettings.items.length === 0" class="text-center py-8">
                                            <p class="text-slate-500 text-sm">Aucun mode de service configuré. Créez le premier en bas.</p>
                                        </div>
                                        <div
                                            v-for="item in serviceModeSettings.items"
                                            :key="item.id"
                                            class="flex flex-col gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 md:flex-row md:items-center hover:bg-slate-100 transition-colors"
                                        >
                                            <input v-model.trim="item.label" type="text" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom du mode (ex: Sur place)">
                                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 shrink-0">
                                                <input v-model="item.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                <span class="whitespace-nowrap">Afficher dans le POS</span>
                                            </label>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-white transition-colors" @click="moveServiceMode(item.id, -1)">↑ Monter</button>
                                                <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-white transition-colors" @click="moveServiceMode(item.id, 1)">↓ Descendre</button>
                                                <button type="button" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors" @click="removeServiceMode(item.id)">✕ Supprimer</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 mt-4">
                                    <label class="block text-sm font-semibold text-slate-900 mb-3">➕ Ajouter un mode de service</label>
                                    <div class="flex flex-col gap-3 md:flex-row">
                                        <input
                                            v-model.trim="newServiceModeLabel"
                                            type="text"
                                            placeholder="Exemple : Drive, Click & Collect, Catering"
                                            class="flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                            @keydown.enter.prevent="addCustomServiceMode"
                                        >
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-500 px-6 py-2.5 text-sm font-semibold text-white hover:bg-primary-600 transition-colors"
                                            @click="addCustomServiceMode"
                                        >
                                            <PlusIcon class="w-4 h-4" />
                                            Ajouter
                                        </button>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                                    <button type="button" class="px-6 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition-colors" @click="resetServiceModeForm">Annuler</button>
                                    <button type="button" class="px-6 py-2.5 rounded-lg bg-blue-500 text-white font-semibold hover:bg-blue-600 disabled:opacity-50 transition-colors" :disabled="isSavingCustomList('service_modes')" @click="saveServiceModeList">
                                        {{ isSavingCustomList('service_modes') ? '⏳ Enregistrement...' : '✓ Enregistrer' }}
                                    </button>
                                </div>
                            </template>

                            <template v-else>
                                <div class="rounded-xl border-l-4 border-l-emerald-500 border border-slate-200 bg-emerald-50 p-4">
                                    <p class="text-sm font-bold text-emerald-900">💳 Modes de paiement visibles dans le POS</p>
                                    <p class="mt-2 text-sm text-emerald-700">Configurez les méthodes de paiement acceptées. Sélectionnez un mode par défaut pour les nouvelles transactions. Exemples : Espèces, Carte bancaire, Chèque, Virement, Bon cadeau.</p>
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                    <div class="space-y-3">
                                        <div v-if="paymentModeSettings.items.length === 0" class="text-center py-8">
                                            <p class="text-slate-500 text-sm">Aucun mode de paiement configuré. Créez le premier en bas.</p>
                                        </div>
                                        <div
                                            v-for="item in paymentModeSettings.items"
                                            :key="item.id"
                                            class="flex flex-col gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 md:flex-row md:items-center hover:bg-slate-100 transition-colors"
                                        >
                                            <input v-model.trim="item.label" type="text" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom du mode (ex: Espèces)">
                                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 shrink-0">
                                                <input v-model="item.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                <span class="whitespace-nowrap">Afficher</span>
                                            </label>
                                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 shrink-0">
                                                <input :checked="item.is_default" type="radio" name="payment_default" class="h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500" @change="setDefaultPaymentMode(item.id)">
                                                <span class="whitespace-nowrap">Par défaut</span>
                                            </label>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-white transition-colors" @click="movePaymentMode(item.id, -1)">↑ Monter</button>
                                                <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-white transition-colors" @click="movePaymentMode(item.id, 1)">↓ Descendre</button>
                                                <button type="button" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors" @click="removePaymentMode(item.id)">✕ Supprimer</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 mt-4">
                                    <label class="block text-sm font-semibold text-slate-900 mb-3">➕ Ajouter un mode de paiement</label>
                                    <div class="flex flex-col gap-3 md:flex-row">
                                        <input
                                            v-model.trim="newPaymentModeLabel"
                                            type="text"
                                            placeholder="Exemple : Espèces, Carte bancaire, Chèque, Virement, Bon cadeau"
                                            class="flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                            @keydown.enter.prevent="addPaymentMode"
                                        >
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-500 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-600 transition-colors"
                                            @click="addPaymentMode"
                                        >
                                            <PlusIcon class="w-4 h-4" />
                                            Ajouter
                                        </button>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                                    <button type="button" class="px-6 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition-colors" @click="resetPaymentModeForm">Annuler</button>
                                    <button type="button" class="px-6 py-2.5 rounded-lg bg-emerald-500 text-white font-semibold hover:bg-emerald-600 disabled:opacity-50 transition-colors" :disabled="isSavingCustomList('payment_modes')" @click="savePaymentModeList">
                                        {{ isSavingCustomList('payment_modes') ? '⏳ Enregistrement...' : '✓ Enregistrer' }}
                                    </button>
                                </div>
                            </template>
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
const savingCustomListTab = ref('')
const activeCustomListTab = ref('tickets')
const newServiceModeLabel = ref('')
const newPredefinedTicketLabel = ref('')
const newPredefinedGroupLabel = ref('')
const newPaymentModeLabel = ref('')

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

const customListTabs = [
    { id: 'tickets', label: 'Tickets prédéfinis' },
    { id: 'service_modes', label: 'Mode de service' },
    { id: 'payment_modes', label: 'Mode de paiement' },
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

const predefinedTicketSettings = reactive({
    is_active: true,
    items: [],
})
const serviceModeSettings = reactive({
    is_active: true,
    items: [],
})
const paymentModeSettings = reactive({
    is_active: true,
    items: [],
})

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

function createDraftId(prefix) {
    return `${prefix}-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`
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

function reindexEntries(items) {
    return items.map((item, index) => ({
        ...item,
        sort_order: index + 1,
    }))
}

function normalizeTicketEntries(tickets) {
    return [...tickets]
        .map((ticket, index) => ({
            id: ticket.id ?? createDraftId('ticket'),
            label: ticket.label || '',
            is_active: ticket.is_active !== false,
            sort_order: Number(ticket.sort_order ?? index + 1),
        }))
        .sort((a, b) => a.sort_order - b.sort_order)
}

function normalizePredefinedTicketItems(items) {
    return [...items]
        .map((item, index) => ({
            id: item.id ?? createDraftId(item.kind === 'group' ? 'group' : 'ticket'),
            label: item.label || item.value || '',
            value: item.value || item.label || '',
            is_active: item.is_active !== false,
            sort_order: Number(item.sort_order ?? index + 1),
            kind: item.kind === 'group' ? 'group' : 'ticket',
            tickets: normalizeTicketEntries(item.tickets || []),
        }))
        .sort((a, b) => a.sort_order - b.sort_order)
}

function normalizeServiceModeItems(items) {
    return [...items]
        .map((item, index) => ({
            id: item.id ?? createDraftId('mode'),
            label: item.label || item.value || '',
            value: item.value || item.label || '',
            is_active: item.is_active !== false,
            sort_order: Number(item.sort_order ?? index + 1),
        }))
        .sort((a, b) => a.sort_order - b.sort_order)
}

function normalizePaymentModeItems(items) {
    return [...items]
        .map((item, index) => ({
            id: item.id ?? createDraftId('payment'),
            label: item.label || item.value || '',
            value: item.value || item.label || '',
            is_active: item.is_active !== false,
            sort_order: Number(item.sort_order ?? index + 1),
            is_default: item.is_default === true,
        }))
        .sort((a, b) => a.sort_order - b.sort_order)
}

function hydratePredefinedTicketForm(list) {
    predefinedTicketSettings.is_active = list?.is_active !== false
    predefinedTicketSettings.items = normalizePredefinedTicketItems(list?.items || [])
}

function hydrateServiceModeForm(list) {
    serviceModeSettings.is_active = list?.is_active !== false
    serviceModeSettings.items = normalizeServiceModeItems(list?.items || [])
}

function hydratePaymentModeForm(list) {
    paymentModeSettings.is_active = list?.is_active !== false
    paymentModeSettings.items = normalizePaymentModeItems(list?.items || [])

    if (!paymentModeSettings.items.some((item) => item.is_default) && paymentModeSettings.items.length > 0) {
        paymentModeSettings.items[0].is_default = true
    }
}

async function loadCustomLists() {
    const [ticketsList, serviceModesList, paymentModesList] = await Promise.all([
        customListsStore.fetchList('tickets_predefinis', { force: true }),
        customListsStore.fetchList('mode_de_service', { force: true }),
        customListsStore.fetchList('mode_de_paiement', { force: true }),
    ])

    hydratePredefinedTicketForm(ticketsList)
    hydrateServiceModeForm(serviceModesList)
    hydratePaymentModeForm(paymentModesList)
}

function inferServiceModeMeta(label) {
    const normalized = String(label || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase()

    if (normalized === 'livraison') {
        return { operational_mode: 'delivery', requires_delivery_agent: true }
    }

    if (normalized === 'sur place') {
        return { operational_mode: 'dine_in', requires_delivery_agent: false }
    }

    return { operational_mode: 'pickup', requires_delivery_agent: false }
}

function inferPaymentModeMeta(label) {
    const normalized = String(label || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase()

    if (['espece', 'especes', 'cash', 'liquide'].includes(normalized)) {
        return { payment_type: 'cash', transfer_mode: null }
    }

    if (normalized.includes('carte') || normalized.includes('card')) {
        return { payment_type: 'card', transfer_mode: null }
    }

    if (normalized.includes('mobile')) {
        return { payment_type: 'mobile', transfer_mode: null }
    }

    if ((normalized.includes('instant') || normalized.includes('instantane'))
        && (normalized.includes('virement') || normalized.includes('transfer'))) {
        return { payment_type: 'virement', transfer_mode: 'instant' }
    }

    if (normalized.includes('virement') || normalized.includes('transfer')) {
        return { payment_type: 'virement', transfer_mode: 'simple' }
    }

    if (normalized.includes('credit') || normalized.includes('lcn')) {
        return { payment_type: 'credit', transfer_mode: null }
    }

    return { payment_type: 'other', transfer_mode: null }
}

function isSavingCustomList(tabId) {
    return savingCustomListTab.value === tabId
}

function getPredefinedTicketItem(itemId) {
    return predefinedTicketSettings.items.find((item) => String(item.id) === String(itemId)) || null
}

function addStandalonePredefinedTicket() {
    const label = newPredefinedTicketLabel.value.trim()
    if (!label) return

    const exists = predefinedTicketSettings.items.some((item) => item.kind === 'ticket' && item.label.trim().toLowerCase() === label.toLowerCase())
    if (exists) {
        alert('Ce ticket existe déjà.')
        return
    }

    predefinedTicketSettings.items = reindexEntries([
        ...predefinedTicketSettings.items,
        {
            id: createDraftId('ticket'),
            label,
            value: label,
            is_active: true,
            sort_order: 0,
            kind: 'ticket',
            tickets: [],
        },
    ])
    newPredefinedTicketLabel.value = ''
}

function addPredefinedGroup() {
    const label = newPredefinedGroupLabel.value.trim()
    if (!label) return

    const exists = predefinedTicketSettings.items.some((item) => item.kind === 'group' && item.label.trim().toLowerCase() === label.toLowerCase())
    if (exists) {
        alert('Ce groupe existe déjà.')
        return
    }

    predefinedTicketSettings.items = reindexEntries([
        ...predefinedTicketSettings.items,
        {
            id: createDraftId('group'),
            label,
            value: label,
            is_active: true,
            sort_order: 0,
            kind: 'group',
            tickets: [],
        },
    ])
    newPredefinedGroupLabel.value = ''
}

function removePredefinedTicket(itemId) {
    predefinedTicketSettings.items = reindexEntries(
        predefinedTicketSettings.items.filter((item) => String(item.id) !== String(itemId))
    )
}

function movePredefinedTicket(itemId, direction) {
    const sourceIndex = predefinedTicketSettings.items.findIndex((item) => String(item.id) === String(itemId))
    predefinedTicketSettings.items = reindexEntries(
        moveInArray(predefinedTicketSettings.items, sourceIndex, sourceIndex + direction)
    )
}

function addTicketToGroup(groupId) {
    const group = getPredefinedTicketItem(groupId)
    if (!group || group.kind !== 'group') return

    group.tickets = reindexEntries([
        ...(group.tickets || []),
        {
            id: createDraftId('ticket'),
            label: '',
            is_active: true,
            sort_order: 0,
        },
    ])
}

function removeTicketFromGroup(groupId, ticketId) {
    const group = getPredefinedTicketItem(groupId)
    if (!group || group.kind !== 'group') return

    group.tickets = reindexEntries(
        (group.tickets || []).filter((ticket) => String(ticket.id) !== String(ticketId))
    )
}

function moveTicketInGroup(groupId, ticketId, direction) {
    const group = getPredefinedTicketItem(groupId)
    if (!group || group.kind !== 'group') return

    const sourceIndex = (group.tickets || []).findIndex((ticket) => String(ticket.id) === String(ticketId))
    group.tickets = reindexEntries(
        moveInArray(group.tickets || [], sourceIndex, sourceIndex + direction)
    )
}

function addCustomServiceMode() {
    const label = newServiceModeLabel.value.trim()
    if (!label) return

    const exists = serviceModeSettings.items.some((item) => item.label.trim().toLowerCase() === label.toLowerCase())
    if (exists) {
        alert('Ce mode existe déjà dans la liste.')
        return
    }

    serviceModeSettings.items = reindexEntries([
        ...serviceModeSettings.items,
        {
            id: createDraftId('mode'),
            label,
            value: label,
            is_active: true,
            sort_order: 0,
        },
    ])
    newServiceModeLabel.value = ''
}

function removeServiceMode(modeId) {
    serviceModeSettings.items = reindexEntries(
        serviceModeSettings.items.filter((item) => String(item.id) !== String(modeId))
    )
}

function moveServiceMode(modeId, direction) {
    const sourceIndex = serviceModeSettings.items.findIndex((item) => String(item.id) === String(modeId))
    serviceModeSettings.items = reindexEntries(
        moveInArray(serviceModeSettings.items, sourceIndex, sourceIndex + direction)
    )
}

function addPaymentMode() {
    const label = newPaymentModeLabel.value.trim()
    if (!label) return

    const exists = paymentModeSettings.items.some((item) => item.label.trim().toLowerCase() === label.toLowerCase())
    if (exists) {
        alert('Ce mode de paiement existe déjà.')
        return
    }

    paymentModeSettings.items = reindexEntries([
        ...paymentModeSettings.items,
        {
            id: createDraftId('payment'),
            label,
            value: label,
            is_active: true,
            sort_order: 0,
            is_default: paymentModeSettings.items.length === 0,
        },
    ])
    newPaymentModeLabel.value = ''
}

function removePaymentMode(modeId) {
    paymentModeSettings.items = reindexEntries(
        paymentModeSettings.items.filter((item) => String(item.id) !== String(modeId))
    )

    if (!paymentModeSettings.items.some((item) => item.is_default) && paymentModeSettings.items.length > 0) {
        paymentModeSettings.items[0].is_default = true
    }
}

function movePaymentMode(modeId, direction) {
    const sourceIndex = paymentModeSettings.items.findIndex((item) => String(item.id) === String(modeId))
    paymentModeSettings.items = reindexEntries(
        moveInArray(paymentModeSettings.items, sourceIndex, sourceIndex + direction)
    )
}

function setDefaultPaymentMode(modeId) {
    paymentModeSettings.items = paymentModeSettings.items.map((item) => ({
        ...item,
        is_default: String(item.id) === String(modeId),
    }))
}

function resetPredefinedTicketForm() {
    newPredefinedTicketLabel.value = ''
    newPredefinedGroupLabel.value = ''
    customListsStore.fetchList('tickets_predefinis', { force: true }).then(hydratePredefinedTicketForm)
}

function resetServiceModeForm() {
    newServiceModeLabel.value = ''
    customListsStore.fetchList('mode_de_service', { force: true }).then(hydrateServiceModeForm)
}

function resetPaymentModeForm() {
    newPaymentModeLabel.value = ''
    customListsStore.fetchList('mode_de_paiement', { force: true }).then(hydratePaymentModeForm)
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

async function saveCustomList(tabId, listName, payload, hydrateForm, successMessage) {
    savingCustomListTab.value = tabId

    try {
        const { data } = await customListsApi.update(listName, payload)
        customListsStore.setList(listName, data)
        hydrateForm(data)
        alert(successMessage)
    } catch (error) {
        console.error(`Failed to save custom list "${listName}":`, error)
        alert(error.response?.data?.message || 'Erreur lors de l\'enregistrement.')
    } finally {
        savingCustomListTab.value = ''
    }
}

async function savePredefinedTicketsList() {
    const payload = {
        is_active: true,
        items: predefinedTicketSettings.items
            .filter((item) => item.label.trim() !== '')
            .map((item, index) => {
                const itemId = Number(item.id)

                return {
                    id: Number.isInteger(itemId) && itemId > 0 ? itemId : undefined,
                    label: item.label.trim(),
                    value: item.label.trim(),
                    is_active: item.is_active !== false,
                    sort_order: index + 1,
                    kind: item.kind,
                    tickets: item.kind === 'group'
                        ? (item.tickets || [])
                            .filter((ticket) => ticket.label.trim() !== '')
                            .map((ticket, ticketIndex) => {
                                const ticketId = Number(ticket.id)

                                return {
                                    id: Number.isInteger(ticketId) && ticketId > 0 ? ticketId : undefined,
                                    label: ticket.label.trim(),
                                    is_active: ticket.is_active !== false,
                                    sort_order: ticketIndex + 1,
                                }
                            })
                        : [],
                }
            }),
    }

    await saveCustomList(
        'tickets',
        'tickets_predefinis',
        payload,
        hydratePredefinedTicketForm,
        'Tickets prédéfinis enregistrés avec succès!'
    )
}

async function saveServiceModeList() {
    const payload = {
        is_active: true,
        items: serviceModeSettings.items
            .filter((item) => item.label.trim() !== '')
            .map((item, index) => {
                const itemId = Number(item.id)
                const meta = inferServiceModeMeta(item.label)

                return {
                    id: Number.isInteger(itemId) && itemId > 0 ? itemId : undefined,
                    label: item.label.trim(),
                    value: item.label.trim(),
                    is_active: item.is_active !== false,
                    sort_order: index + 1,
                    operational_mode: meta.operational_mode,
                    requires_delivery_agent: meta.requires_delivery_agent,
                }
            }),
    }

    await saveCustomList(
        'service_modes',
        'mode_de_service',
        payload,
        hydrateServiceModeForm,
        'Modes de service enregistrés avec succès!'
    )
}

async function savePaymentModeList() {
    const filteredItems = paymentModeSettings.items.filter((item) => item.label.trim() !== '')

    if (filteredItems.length > 0 && !filteredItems.some((item) => item.is_default)) {
        filteredItems[0].is_default = true
    }

    const payload = {
        is_active: true,
        items: filteredItems.map((item, index) => {
            const itemId = Number(item.id)
            const meta = inferPaymentModeMeta(item.label)

            return {
                id: Number.isInteger(itemId) && itemId > 0 ? itemId : undefined,
                label: item.label.trim(),
                value: item.label.trim(),
                is_active: item.is_active !== false,
                sort_order: index + 1,
                payment_type: meta.payment_type,
                transfer_mode: meta.transfer_mode,
                is_default: item.is_default === true,
            }
        }),
    }

    await saveCustomList(
        'payment_modes',
        'mode_de_paiement',
        payload,
        hydratePaymentModeForm,
        'Modes de paiement enregistrés avec succès!'
    )
}

onMounted(async () => {
    await Promise.all([
        loadSettings(),
        loadCustomLists(),
    ])
})
</script>
