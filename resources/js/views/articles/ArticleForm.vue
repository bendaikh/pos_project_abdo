<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Modifier l\'Article' : 'Nouvel Article' }}</h1>
                <p class="text-gray-500">{{ isEdit ? 'Modifiez les informations de l\'article' : 'Ajoutez un nouveau produit à votre inventaire' }}</p>
            </div>
            <router-link to="/articles" class="text-gray-500 hover:text-gray-700">
                <XMarkIcon class="w-6 h-6" />
            </router-link>
        </div>
        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Image & Identifiers Section (TOP) -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">📸 Image & Identifiants rapides</h2>
                <p class="text-sm text-gray-500 mb-6">Concentrez-vous sur l'image centrale tout en gardant SKU, couleur et code-barres visibles.</p>

                <!-- Centered Image Box -->
                <div class="flex justify-center mb-6">
                    <input 
                        ref="imageInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="handleImageUpload"
                    >
                    
                    <!-- Empty State -->
                    <div v-if="form.photos.length === 0 || !form.photos.find(p => p.is_primary && p.photo_url)" 
                         @click="$refs.imageInput.click()"
                         class="w-full max-w-[250px] aspect-square bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border-2 border-dashed border-amber-300 flex items-center justify-center cursor-pointer hover:from-amber-100 hover:to-orange-100 transition-all">
                        <div class="text-center">
                            <svg class="mx-auto h-20 w-20 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
                            </svg>
                            <p class="text-base font-semibold text-gray-600">Ajouter une image</p>
                            <p class="text-sm text-gray-500 mt-1">Cliquez pour télécharger ou glissez ici</p>
                        </div>
                    </div>
                    
                    <!-- Image Display -->
                    <div v-else class="w-full max-w-[250px] aspect-square rounded-2xl overflow-hidden bg-gray-100 relative shadow-md">
                        <img 
                            :src="primaryPhoto?.photo_url"
                            class="w-full h-full object-cover cursor-pointer transition duration-200"
                            @click="$refs.imageInput.click()"
                            @error="$event.target.src='https://via.placeholder.com/400'"
                        >
                        <!-- Delete Button Overlay -->
                        <button 
                            type="button"
                            @click.stop="removePhoto(form.photos.findIndex(p => p.is_primary))"
                            class="absolute top-3 right-3 bg-red-500 hover:bg-red-600 text-white rounded-full p-2.5 shadow-lg"
                            title="Supprimer l'image"
                        >
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Quick Identifiers Row -->
                <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-4 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 mb-2">Couleur</p>
                            <div class="w-full flex items-center gap-2 border border-gray-200 bg-white rounded-2xl px-3 py-3 text-sm focus-within:border-primary-300 focus-within:ring-2 focus-within:ring-primary-400 h-12">
                                <input 
                                    v-model="form.color"
                                    type="text"
                                    placeholder="Ex: Rouge, Bleu, #FF5733"
                                    class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm"
                                >
                                <button 
                                    type="button"
                                    @click="showColorPicker = !showColorPicker"
                                    class="flex-none w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-xs bg-white"
                                    :style="form.color && isHexColor(form.color) ? { backgroundColor: form.color, borderColor: form.color } : {}"
                                >
                                    🎨
                                </button>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 mb-2">ID Article *</p>
                            <input 
                                v-model="form.sku"
                                type="text"
                                placeholder="Ex: ART-001"
                                class="w-full px-3 py-3 border border-gray-200 rounded-2xl bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 font-medium h-12"
                                required
                            >
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 mb-2">Code-barres / QR Code</p>
                            <input 
                                ref="barcodeScanner"
                                v-model="form.barcode"
                                type="text"
                                placeholder="Scannez le code barre ici"
                                @keyup.enter="focusNextField"
                                class="w-full px-3 py-3 border border-yellow-300 rounded-2xl bg-white text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 font-mono h-12 mb-1"
                            >
                            <p class="text-xs text-gray-500">💡 Lecteur USB</p>
                        </div>
                    </div>
                    <transition name="fade">
                        <div v-if="showColorPicker" class="border border-gray-200 rounded-2xl bg-white p-3 shadow-lg">
                            <div class="grid grid-cols-6 gap-1 mb-2">
                                <button 
                                    v-for="color in standardColors"
                                    :key="color"
                                    type="button"
                                    @click="form.color = color; showColorPicker = false"
                                    class="w-8 h-8 rounded border-2 transition-all"
                                    :style="{ backgroundColor: color, borderColor: form.color === color ? '#000' : '#ddd' }"
                                ></button>
                            </div>
                            <input 
                                v-model="form.color"
                                type="color"
                                class="w-full h-10 rounded-2xl border border-gray-200"
                            >
                        </div>
                    </transition>
                </div>
            </div>

            <!-- Basic Information Section -->

            <!-- Basic Information Section -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">📋 Informations de base</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'article *</label>
                        <input 
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                            placeholder="Ex: Œufs, Pain, Lait, Tomates"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <span>Catégorie</span>
                            <router-link to="/categories" class="ml-2 text-xs text-primary-600 hover:text-primary-700">
                                Gérer
                            </router-link>
                        </label>
                        <select 
                            v-model="form.category_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                            <option :value="null">Sélectionner une catégorie</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea 
                            v-model="form.description"
                            rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                            placeholder="Description détaillée de l'article..."
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unité de mesure</label>
                        <select 
                            v-model="form.unit"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                            <option value="piece">Pièce</option>
                            <option value="kg">Kilogramme (kg)</option>
                            <option value="g">Gramme (g)</option>
                            <option value="l">Litre (L)</option>
                            <option value="ml">Millilitre (ml)</option>
                            <option value="m">Mètre (M)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Pricing Section -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">💰 Tarification</h2>
                
                <div class="space-y-4">
                    <!-- Price Type Toggle -->
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input 
                                v-model="form.price_type"
                                type="radio"
                                value="fixed"
                                class="w-4 h-4 text-primary-600 border-gray-300"
                            >
                            <span class="text-sm font-medium text-gray-700">Prix fixe</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input 
                                v-model="form.price_type"
                                type="radio"
                                value="variable"
                                class="w-4 h-4 text-primary-600 border-gray-300"
                            >
                            <span class="text-sm font-medium text-gray-700">Prix variable</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Prix de vente {{ form.price_type === 'variable' ? '(suggéré)' : '*' }}
                            </label>
                            <div class="relative">
                                <input 
                                    v-model.number="form.sell_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :required="form.price_type === 'fixed'"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">{{ settingsStore.currencyCode }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prix d'achat</label>
                            <div class="relative">
                                <input 
                                    v-model.number="form.buy_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    @input="calculateMargin"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">{{ settingsStore.currencyCode }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Marge (%)</label>
                            <div class="relative">
                                <input 
                                    :value="marginPercentage"
                                    type="text"
                                    readonly
                                    class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-medium"
                                >
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="marginPercentage !== '-'" class="p-3 rounded-lg text-sm" :class="getMarginClass()">
                        Bénéfice par unité: <span class="font-medium">{{ formatCurrency((form.sell_price || 0) - (form.buy_price || 0)) }}</span>
                    </div>
                </div>
            </div>

            <!-- Stock Management Section -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">📦 Gestion du stock</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <label for="manage_stock" class="text-sm font-medium text-gray-700">
                                Activer la gestion de stock
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Suivre l'inventaire de cet article</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                v-model="form.manage_stock"
                                type="checkbox"
                                id="manage_stock"
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>

                    <transition name="fade">
                        <div v-if="form.manage_stock" class="space-y-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantité en stock actuel</label>
                                    <input 
                                        v-model.number="form.stock_quantity"
                                        type="number"
                                        min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        placeholder="0"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Seuil minimum d'alerte</label>
                                    <input 
                                        v-model.number="form.stock_alert_threshold"
                                        type="number"
                                        min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        placeholder="10"
                                    >
                                </div>
                            </div>
                            <div v-if="form.stock_quantity <= form.stock_alert_threshold" class="flex items-center space-x-2 text-sm text-orange-700 bg-orange-100 p-2 rounded">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Stock en dessous du seuil d'alerte</span>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>

            <!-- Article Options Section -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">⚙️ Options avancées</h2>
                
                <div class="space-y-4">
                    <!-- Composite Article -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <label for="is_composite" class="text-sm font-medium text-gray-700">
                                Article composite
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Cet article est composé de plusieurs articles</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                v-model="form.is_composite"
                                type="checkbox"
                                id="is_composite"
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>

                    <!-- Taxes -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <label for="has_tax" class="text-sm font-medium text-gray-700">
                                Taxes applicables
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Appliquer la taxe sur cet article</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                v-model="form.has_tax"
                                type="checkbox"
                                id="has_tax"
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Status & Visibility Section -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">✅ Statut & Visibilité</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <label for="is_active" class="text-sm font-medium text-gray-700">
                                Article actif
                            </label>
                            <p class="text-xs text-gray-500 mt-1">L'article est disponible dans le système</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                v-model="form.is_active"
                                type="checkbox"
                                id="is_active"
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <label for="is_on_sale" class="text-sm font-medium text-gray-700">
                                Article mis en vente
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Visible dans le point de vente</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                v-model="form.is_on_sale"
                                type="checkbox"
                                id="is_on_sale"
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <label for="is_favorite" class="text-sm font-medium text-gray-700">
                                Marquer comme favori
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Apparaît en premier dans le POS</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                v-model="form.is_favorite"
                                type="checkbox"
                                id="is_favorite"
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Product Variants Section -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">📏 Variantes Produit</h2>
                        <p class="text-sm text-gray-500 mt-1">Ajoutez un ou plusieurs modèles de variantes et ajustez les coûts, prix et identifiants directement.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <label for="has_variants" class="text-sm font-medium text-gray-700">
                                Activer les variantes
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Chaque article peut proposer un ou plusieurs groupes de variantes.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                v-model="form.has_variants"
                                type="checkbox"
                                id="has_variants"
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>

                    <transition name="fade">
                        <div v-if="form.has_variants" class="space-y-6">
                            <div class="space-y-3">
                                <label class="block text-xs uppercase tracking-wide text-gray-500 font-semibold">Ajouter un modèle de variantes</label>
                                <div class="grid gap-3 md:grid-cols-[2fr_auto] items-end">
                                    <select
                                        v-model="variantTemplatePickerId"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white"
                                    >
                                        <option value="" disabled>Choisir un modèle de variante</option>
                                        <option
                                            v-for="template in availableVariantTemplates"
                                            :key="template.id"
                                            :value="template.id"
                                        >
                                            {{ template.name }}
                                        </option>
                                        <option v-if="availableVariantTemplates.length === 0" disabled>
                                            {{ variantTemplates.length > 0 ? 'Tous les modèles sont sélectionnés' : 'Aucun modèle importé' }}
                                        </option>
                                    </select>
                                    <button
                                        type="button"
                                        @click="addVariantTemplate(variantTemplatePickerId)"
                                        :disabled="!variantTemplatePickerId"
                                        class="w-full md:w-auto px-4 py-3 bg-primary-500 text-white font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50"
                                    >
                                        Ajouter un modèle
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500">Les valeurs associées apparaîtront dans les cartes ci-dessous.</p>
                            </div>

                            <div v-if="activeVariantTemplates.length > 0" class="space-y-4">
                                <div
                                    v-for="template in activeVariantTemplates"
                                    :key="template.id"
                                    class="space-y-4 p-4 border border-gray-200 rounded-2xl bg-white shadow-sm"
                                >
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ template.name }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ getVisibleVariantValues(template.id).length }} valeur(s) active(s)</p>
                                        </div>
                                        <button
                                            type="button"
                                            class="text-xs font-semibold text-red-600 hover:text-red-800"
                                            @click="removeVariantTemplate(template.id)"
                                        >
                                            <XMarkIcon class="w-4 h-4 inline" /> Retirer le modèle
                                        </button>
                                    </div>

                                    <div v-if="getVisibleVariantValues(template.id).length" class="space-y-3 max-h-96 overflow-y-auto pr-1">
                                        <div 
                                            v-for="value in getVisibleVariantValues(template.id)" 
                                            :key="value"
                                            class="space-y-4 p-4 border border-gray-200 rounded-2xl bg-white shadow-sm"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900">{{ value }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">Variante {{ template.name }}</p>
                                                </div>
                                                <button
                                                    type="button"
                                                    class="flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-800"
                                                    @click="removeVariantValue(template.id, value)"
                                                >
                                                    <XMarkIcon class="w-3.5 h-3.5" />
                                                    Supprimer
                                                </button>
                                            </div>
                                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                                <label class="text-sm text-gray-700 space-y-1">
                                                    <span class="text-xs uppercase tracking-wide text-gray-500">Impact sur le prix ({{ settingsStore.currencyCode }})</span>
                                                    <input
                                                        v-model.number="getVariantValue(template.id, value).price"
                                                        type="number"
                                                        step="0.01"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                        placeholder="0"
                                                    >
                                                </label>
                                                <label class="text-sm text-gray-700 space-y-1">
                                                    <span class="text-xs uppercase tracking-wide text-gray-500">Coût ({{ settingsStore.currencyCode }})</span>
                                                    <input
                                                        v-model.number="getVariantValue(template.id, value).cost"
                                                        type="number"
                                                        step="0.01"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                        placeholder="0"
                                                    >
                                                </label>
                                                <label class="text-sm text-gray-700 space-y-1">
                                                    <span class="text-xs uppercase tracking-wide text-gray-500">ID variante</span>
                                                    <input
                                                        v-model="getVariantValue(template.id, value).sku"
                                                        type="text"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                        placeholder="ART-001"
                                                    >
                                                </label>
                                                <label class="text-sm text-gray-700 space-y-1">
                                                    <span class="text-xs uppercase tracking-wide text-gray-500">Code-barres</span>
                                                    <input
                                                        v-model="getVariantValue(template.id, value).barcode"
                                                        type="text"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                        placeholder="0123456789"
                                                    >
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="p-3 rounded-2xl border border-dashed border-gray-200 text-sm text-gray-500">
                                        Aucune valeur disponible pour ce modèle. Ajoutez en depuis les templates ou depuis les données existantes.
                                    </div>
                                </div>
                            </div>

                            <div v-else class="p-4 rounded-2xl border border-dashed border-gray-200 text-sm text-gray-500">
                                Sélectionnez un modèle de variante pour les faire apparaître ici.
                            </div>
                        </div>
                    </transition>
                </div>
            </div>

            <!-- Article Options Section (Multi-Choice, Reusable) -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">🎁 Options Additionnelles</h2>
                        <p class="text-sm text-gray-500 mt-1">Sélection multiple (suppléments, garnitures, etc.)</p>
                    </div>
                    <button 
                        type="button"
                        @click="showOptionModal = true"
                        class="text-sm text-primary-600 hover:text-primary-700 font-medium hover:underline"
                    >
                        + Créer une option
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <label for="has_options" class="text-sm font-medium text-gray-700">
                                Activer les options
                            </label>
                            <p class="text-xs text-gray-500 mt-1">L'utilisateur peut sélectionner zéro ou plusieurs options</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                v-model="form.has_options"
                                type="checkbox"
                                id="has_options"
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>

                    <transition name="fade">
                        <div v-if="form.has_options" class="space-y-3">
                            <p class="text-sm text-gray-600">Sélectionnez les options disponibles pour cet article :</p>
                            
                            <div v-if="options.length === 0" class="text-sm text-gray-500 italic p-4 bg-gray-50 rounded-lg border border-gray-200">
                                Aucune option disponible. 
                                <button 
                                    type="button"
                                    @click="showOptionModal = true"
                                    class="text-primary-600 hover:underline font-medium"
                                >
                                    Créez-en une
                                </button> 
                                d'abord.
                            </div>
                            
                            <div v-else class="space-y-2 max-h-80 overflow-y-auto p-2">
                                <label 
                                    v-for="option in options" 
                                    :key="option.id"
                                    class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-all"
                                    :class="selectedOptions.includes(option.id) ? 'border-primary-500 bg-primary-50 shadow-sm' : 'border-gray-200'"
                                >
                                    <input 
                                        v-model="selectedOptions"
                                        type="checkbox"
                                        :value="option.id"
                                        class="mt-1 w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                                    >
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="font-medium text-gray-900">{{ option.name }}</p>
                                            <span 
                                                class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-700"
                                            >
                                                Choix multiples
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">{{ formatOptionVariants(option) }}</p>
                                        <p v-if="optionExtraPriceLabel(option)" class="text-xs text-gray-600 mt-1 font-medium">
                                            Prix supplémentaire: {{ optionExtraPriceLabel(option) }}
                                        </p>
                                    </div>
                                </label>
                            </div>
                            
                            <div v-if="selectedOptions.length > 0" class="mt-3 p-3 bg-primary-50 rounded-lg border border-primary-200">
                                <p class="text-sm text-primary-800 font-medium">
                                    {{ selectedOptions.length }} option(s) sélectionnée(s)
                                </p>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>

            <!-- Advanced Management -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <button 
                    type="button"
                    class="w-full flex items-center justify-between text-left"
                    @click="advancedOpen = !advancedOpen"
                >
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">🔧 Gestion avancée</h2>
                        <p class="text-sm text-gray-500 mt-1">Lots, sérialisation et historique des prix (facultatif).</p>
                    </div>
                    <span class="text-sm font-medium text-primary-600">{{ advancedOpen ? 'Masquer' : 'Afficher' }}</span>
                </button>
                <transition name="fade">
                    <div v-if="advancedOpen" class="mt-4 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lots & dates d’expiration</label>
                            <textarea
                                v-model="form.lot_info"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Ex: Lot A - 24/04/2026 ; Lot B - 05/06/2026"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sérialisation (IMEI, numéro de série)</label>
                            <textarea
                                v-model="form.serial_info"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Listez les numéros ou instructions de sérialisation"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Historique des prix</label>
                            <textarea
                                v-model="form.price_history_notes"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Ajoutez un suivi ou des commentaires sur les ajustements"
                            ></textarea>
                        </div>
                    </div>
                </transition>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <router-link to="/articles" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Annuler
                </router-link>
                <button 
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50"
                >
                    {{ saving ? 'Enregistrement...' : (isEdit ? 'Mettre à jour' : 'Créer l\'article') }}
                </button>
            </div>
        </form>


        <!-- Create Option Modal -->
        <transition name="fade">
            <div v-if="showOptionModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Créer une nouvelle option</h3>
                        <button 
                            type="button"
                            @click="showOptionModal = false"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>

                    <!-- Modal Body - Reusable Form Content -->
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nom *</label>
                            <input
                                v-model="newOption.optionName"
                                type="text"
                                placeholder="Supplément"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">1ère Valeur *</label>
                            <input
                                v-model="newOption.variantName"
                                type="text"
                                placeholder="Sauce piquante"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Prix ({{ settingsStore.currencyCode }}) *</label>
                            <input
                                v-model.number="newOption.variantPrice"
                                type="number"
                                step="0.01"
                                placeholder="30.00"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-bold text-lg"
                            >
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end space-x-3">
                        <button 
                            type="button"
                            @click="showOptionModal = false"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100"
                        >
                            Annuler
                        </button>
                        <button 
                            type="button"
                            @click="createNewOption"
                            :disabled="creatingOption || !newOption.optionName.trim() || !newOption.variantName.trim() || newOption.variantPrice === null"
                            class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ creatingOption ? 'Création...' : 'Créer l\'option' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>


<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { articlesApi, categoriesApi, optionsApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { useVariantTemplatesStore } from '../../stores/variantTemplates'

const route = useRoute()
const router = useRouter()
const settingsStore = useSettingsStore()

const isEdit = computed(() => !!route.params.id)
const categories = ref([])
const options = ref([])
const variantTemplatesStore = useVariantTemplatesStore()
const variantTemplates = computed(() => variantTemplatesStore.templates)
const variantTemplatePickerId = ref('')
const activeVariantTemplateIds = ref([])
const variantMatrix = reactive({})
const removedVariantValues = reactive({})
const activeVariantTemplates = computed(() =>
    variantTemplates.value.filter((template) => activeVariantTemplateIds.value.includes(template.id))
)
const availableVariantTemplates = computed(() =>
    variantTemplates.value.filter((template) => !activeVariantTemplateIds.value.includes(template.id))
)
const showColorPicker = ref(false)
const selectedOptions = ref([])
const saving = ref(false)
const showOptionModal = ref(false)
const creatingOption = ref(false)
const advancedOpen = ref(false)
const standardColors = ['#FF0000', '#00A854', '#0050B3', '#FAAD14', '#F5222D', '#722ED1']

const newOption = reactive({
    optionName: '',
    variantName: '',
    variantPrice: null,
})

const form = reactive({
    name: '',
    sku: '',
    barcode: '',
    description: '',
    category_id: null,
    subcategory_id: null,
    sell_price: 0,
    buy_price: 0,
    unit: 'piece',
    manage_stock: false,
    stock_quantity: 0,
    stock_alert_threshold: 10,
    photo: '',
    is_favorite: false,
    is_active: true,
    has_options: false,
    is_on_sale: true,
    is_composite: false,
    has_tax: true,
    color: '',
    price_type: 'fixed',
    photos: [],
    lot_info: '',
    serial_info: '',
    price_history_notes: '',
})

const primaryPhoto = computed(() => {
    const main = form.photos.find((p) => p.is_primary && p.photo_url)
    if (main) return main
    return form.photos[0] || null
})

const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const marginPercentage = computed(() => {
    if (!form.buy_price || form.buy_price <= 0 || !form.sell_price || form.sell_price <= 0) {
        return '-'
    }
    const margin = ((form.sell_price - form.buy_price) / form.buy_price) * 100
    return margin.toFixed(2)
})

function isHexColor(color) {
    return /^#[0-9A-F]{6}$/i.test(color)
}

function calculateMargin() {
    // The margin percentage is derived from its computed value
}

function getMarginClass() {
    if (marginPercentage.value === '-') return 'bg-gray-100 text-gray-700'
    const margin = parseFloat(marginPercentage.value)
    if (margin < 10) return 'bg-red-100 text-red-700'
    if (margin < 30) return 'bg-orange-100 text-orange-700'
    return 'bg-green-100 text-green-700'
}

function formatOptionVariants(option) {
    if (option.variants && option.variants.length > 0) {
        return option.variants.map((variant) => variant.name).join(', ')
    }
    if (option.values && option.values.length > 0) {
        return option.values.join(', ')
    }
    return 'Aucune valeur'
}

function optionExtraPriceLabel(option) {
    if (!option.variants || option.variants.length === 0) return null
    const prices = option.variants
        .map((variant) => Number(variant.price_impact) || 0)
        .filter((price) => price > 0)

    if (prices.length === 0) return null
    const minPrice = Math.min(...prices)
    const maxPrice = Math.max(...prices)
    if (minPrice === maxPrice) {
        return `+${formatCurrency(minPrice)}`
    }
    return `+${formatCurrency(minPrice)} → ${formatCurrency(maxPrice)}`
}

function resetVariantMatrix() {
    Object.keys(variantMatrix).forEach((templateId) => {
        delete variantMatrix[templateId]
    })
}

function removePhoto(index) {
    if (index < 0 || index >= form.photos.length) return
    const wasPrimary = form.photos[index].is_primary
    form.photos.splice(index, 1)
    if (wasPrimary && form.photos.length > 0) {
        form.photos[0].is_primary = true
    }
    form.photos.forEach((photo, idx) => {
        photo.sort_order = idx
    })
}

function ensureVariantValueEntry(templateId, value) {
    if (!templateId || !value) return null
    if (!variantMatrix[templateId]) {
        variantMatrix[templateId] = {}
    }
    if (!variantMatrix[templateId][value]) {
        variantMatrix[templateId][value] = {
            price: 0,
            cost: 0,
            sku: null,
            barcode: null,
            is_active: true,
        }
    }
    return variantMatrix[templateId][value]
}

function ensureCurrentVariantRows() {
    activeVariantTemplateIds.value.forEach((templateId) => {
        getVisibleVariantValues(templateId).forEach((value) => {
            ensureVariantValueEntry(templateId, value)
        })
    })
}

function removeVariantValue(templateId, value) {
    if (!templateId || !value) return
    if (!removedVariantValues[templateId]) {
        removedVariantValues[templateId] = []
    }
    if (!removedVariantValues[templateId].includes(value)) {
        removedVariantValues[templateId].push(value)
    }
    if (!variantMatrix[templateId]) return
    delete variantMatrix[templateId][value]
    if (Object.keys(variantMatrix[templateId]).length === 0) {
        delete variantMatrix[templateId]
    }
}

function isVariantValueRemoved(templateId, value) {
    const removed = removedVariantValues[templateId] || []
    return removed.includes(value)
}

function getTemplateById(templateId) {
    return variantTemplates.value.find((template) => template.id === templateId)
}

function getVisibleVariantValues(templateId) {
    const template = getTemplateById(templateId)
    const templateValues = template?.values || []
    const matrixValues = Object.keys(variantMatrix[templateId] || {})
    const uniqueValues = Array.from(new Set([...templateValues, ...matrixValues]))
    const removed = removedVariantValues[templateId] || []
    return uniqueValues.filter((value) => value && !removed.includes(value))
}

function addVariantTemplate(templateId) {
    if (!templateId) return
    if (!activeVariantTemplateIds.value.includes(templateId)) {
        activeVariantTemplateIds.value.push(templateId)
    }
    if (!removedVariantValues[templateId]) {
        removedVariantValues[templateId] = []
    }
    ensureCurrentVariantRows()
    variantTemplatePickerId.value = ''
}

function removeVariantTemplate(templateId) {
    activeVariantTemplateIds.value = activeVariantTemplateIds.value.filter((id) => id !== templateId)
    delete removedVariantValues[templateId]
    if (variantMatrix[templateId]) {
        delete variantMatrix[templateId]
    }
}

function populateVariantMatrixFromArticle(variants = []) {
    resetVariantMatrix()
    const detectedTemplateIds = new Set()
    variants.forEach((variant) => {
        const templateMatch = variantTemplates.value.find((template) => template.name === variant.template_name)
        const templateId = templateMatch?.id || variant.template_id || ''
        if (!templateId) return
        detectedTemplateIds.add(templateId)
        const value = variant.template_value || variant.name || ''
        if (!value) return
        const entry = ensureVariantValueEntry(templateId, value)
        entry.price = Number(variant.price_impact) || 0
        entry.cost = Number(variant.cost_price) || 0
        entry.sku = variant.sku || null
        entry.barcode = variant.barcode || null
        entry.is_active = variant.is_active !== false
    })

    if (detectedTemplateIds.size > 0) {
        activeVariantTemplateIds.value = Array.from(detectedTemplateIds)
    } else if (variantTemplates.value.length > 0) {
        activeVariantTemplateIds.value = [variantTemplates.value[0].id]
    }
    ensureCurrentVariantRows()
}

function getVariantValue(templateId, value) {
    return ensureVariantValueEntry(templateId, value) || { price: 0, cost: 0, is_active: true }
}

function buildVariantPayload() {
    return Object.entries(variantMatrix).flatMap(([templateId, values]) => {
        const template = variantTemplates.value.find((t) => t.id === templateId)
        return Object.entries(values).map(([value, entry], index) => ({
            template_id: templateId,
            template_name: template?.name || null,
            template_value: value,
            name: template?.name ? `${template.name} · ${value}` : value,
            price_impact: Number(entry.price) || 0,
            cost_price: Number(entry.cost) || 0,
            sku: entry.sku || null,
            barcode: entry.barcode || null,
            is_active: entry.is_active !== false,
            sort_order: index,
        }))
    })
}

function clearPrimaryPhoto() {
    form.photos = [{
        photo_url: '',
        is_primary: true,
        sort_order: 0,
    }]
}

function handleImageUpload(event) {
    const file = event.target.files?.[0]
    if (!file) return

    if (file.size > 5 * 1024 * 1024) {
        alert('Fichier trop volumineux. Taille maximale: 5MB')
        return
    }

    if (!file.type.startsWith('image/')) {
        alert('Veuillez sélectionner une image valide')
        return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
        form.photos = [{
            photo_url: e.target?.result || '',
            is_primary: true,
            sort_order: 0,
        }]
        event.target.value = ''
    }
    reader.readAsDataURL(file)
}

async function fetchCategories() {
    try {
        const response = await categoriesApi.list()
        categories.value = response.data
    } catch (error) {
        console.error('Failed to fetch categories:', error)
        if (error.response?.status === 401) {
            alert('Votre session a expiré. Veuillez vous reconnecter.')
            router.push('/login')
        } else {
            alert('Erreur lors du chargement des catégories')
        }
    }
}

async function fetchOptions() {
    try {
        const response = await optionsApi.list({ active: true })
        options.value = response.data
    } catch (error) {
        console.error('Failed to fetch options:', error)
        if (error.response?.status === 401) {
            alert('Votre session a expiré. Veuillez vous reconnecter.')
            router.push('/login')
        } else {
            alert('Erreur lors du chargement des options')
        }
    }
}

async function fetchArticle() {
    if (!isEdit.value) {
        if (form.photos.length === 0) {
            form.photos = [{ photo_url: '', is_primary: true, sort_order: 0 }]
        }
        return
    }

    try {
        const response = await articlesApi.get(route.params.id)
        const article = response.data
        Object.assign(form, article)

        if (!form.price_type) {
            form.price_type = 'fixed'
        }

        if (article.options) {
            selectedOptions.value = article.options.map((o) => o.id)
        }

        if (article.variants) {
            form.has_variants = article.variants.length > 0
            populateVariantMatrixFromArticle(article.variants)
        } else {
            resetVariantMatrix()
        }

        if (article.photos && article.photos.length > 0) {
            form.photos = article.photos.map((p) => ({
                photo_url: p.photo_url,
                is_primary: p.is_primary,
                sort_order: p.sort_order,
            }))
        } else if (article.photo) {
            form.photos = [{ photo_url: article.photo, is_primary: true, sort_order: 0 }]
        } else {
            form.photos = [{ photo_url: '', is_primary: true, sort_order: 0 }]
        }
    } catch (error) {
        console.error('Failed to fetch article:', error)
        router.push('/articles')
    }
}

function focusNextField() {
    const nextField = document.querySelector('input[placeholder*="Catégorie"]') ||
        document.querySelector('input[placeholder*="category"]')
    if (nextField instanceof HTMLElement) {
        nextField.focus()
    }
}

async function handleSubmit() {
    saving.value = true

    try {
        const token = localStorage.getItem('auth_token')
        if (!token) {
            alert('Vous êtes déconnecté. Veuillez vous reconnecter.')
            router.push('/login')
            return
        }

        const data = { ...form }

        if (form.has_options) {
            data.options = selectedOptions.value
        } else {
            data.options = []
        }

        if (form.has_variants) {
            data.variants = buildVariantPayload()
        } else {
            data.variants = []
        }

        if (data.photos && data.photos.length > 0) {
            data.photos = data.photos
                .filter((p) => p.photo_url && p.photo_url.trim())
                .map((p, i) => ({
                    ...p,
                    sort_order: i,
                    is_primary: i === 0 ? true : p.is_primary,
                }))

            if (data.photos.length > 0 && !data.photos.some((p) => p.is_primary)) {
                data.photos[0].is_primary = true
            }

            const weightedPhoto = data.photos.find((p) => p.is_primary)
            if (weightedPhoto) {
                data.photo = weightedPhoto.photo_url
            }
        }

        if (isEdit.value) {
            await articlesApi.update(route.params.id, data)
        } else {
            await articlesApi.create(data)
        }
        router.push('/articles')
    } catch (error) {
        console.error('Failed to save article:', error)

        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('auth_user')
            alert('Votre session a expiré. Veuillez vous reconnecter.')
            router.push('/login')
        } else if (error.response?.status === 422) {
            const errors = error.response.data.errors
            const errorMessages = Object.values(errors).flat().join('\n')
            alert('Erreur de validation:\n' + errorMessages)
        } else if (error.response?.data?.message) {
            alert('Erreur: ' + error.response.data.message)
        } else {
            alert('Erreur lors de l\'enregistrement: ' + (error.message || 'Erreur inconnue'))
        }
    } finally {
        saving.value = false
    }
}

async function createNewOption() {
    if (!newOption.optionName.trim() || !newOption.variantName.trim() || newOption.variantPrice === null) {
        alert('Veuillez remplir le nom, la première valeur et le prix')
        return
    }

    creatingOption.value = true
    try {
        const response = await optionsApi.create({
            name: newOption.optionName.trim(),
            type: 'fixed',
            values: [newOption.variantName.trim()],
            extra_price: Number(newOption.variantPrice) || 0,
            is_active: true,
            is_required: false,
        })

        const created = response.data
        created.variants = [
            {
                id: Date.now(),
                name: newOption.variantName.trim(),
                price_impact: Number(newOption.variantPrice) || 0,
            },
        ]

        options.value.push(created)
        selectedOptions.value.push(created.id)

        newOption.optionName = ''
        newOption.variantName = ''
        newOption.variantPrice = null
        showOptionModal.value = false

        alert('Option créée avec succès!')
    } catch (error) {
        console.error('Failed to create option:', error)
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('auth_user')
            alert('Votre session a expiré. Veuillez vous reconnecter.')
            router.push('/login')
        } else {
            alert('Erreur lors de la création de l\'option: ' + (error.response?.data?.message || error.message))
        }
    } finally {
        creatingOption.value = false
    }
}

watch(
    variantTemplates,
    (templates) => {
        const sanitizedActive = activeVariantTemplateIds.value.filter((id) => templates.some((template) => template.id === id))
        activeVariantTemplateIds.value = sanitizedActive
        ensureCurrentVariantRows()
    },
    { immediate: true }
)

watch(
    activeVariantTemplateIds,
    () => {
        ensureCurrentVariantRows()
    },
    { deep: true }
)

onMounted(async () => {
    variantTemplatesStore.loadTemplates()
    await fetchCategories()
    await fetchOptions()
    await fetchArticle()
})
</script>


<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s, transform 0.3s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
