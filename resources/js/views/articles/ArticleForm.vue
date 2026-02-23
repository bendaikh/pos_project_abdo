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
            <!-- Image & Color Section (TOP) -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">🖼️ Image & Couleur</h2>
                
                <div class="space-y-6">
                    <!-- Main Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Image de l'article</label>
                        
                        <!-- Hidden file input -->
                        <input 
                            ref="imageInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleImageUpload"
                        >
                        
                        <!-- No Image - Upload Area -->
                        <div v-if="form.photos.length === 0" 
                             @click="$refs.imageInput.click()"
                             class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center bg-gray-50 hover:bg-blue-50 hover:border-primary-400 transition-colors cursor-pointer">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="text-gray-700 font-medium">Cliquez pour ajouter une image</p>
                            <p class="text-sm text-gray-500 mt-1">ou glissez une image ici</p>
                        </div>
                        
                        <!-- Image Display -->
                        <div v-else-if="form.photos.length > 0 && form.photos.find(p => p.is_primary)" class="space-y-3">
                            <div class="flex gap-4 items-start">
                                <div class="w-32 h-32 bg-gray-100 rounded-lg overflow-hidden border-2 border-primary-500 flex-shrink-0">
                                    <img 
                                        :src="form.photos.find(p => p.is_primary).photo_url" 
                                        class="w-full h-full object-cover cursor-pointer hover:opacity-80"
                                        @click="$refs.imageInput.click()"
                                        @error="$event.target.src='https://via.placeholder.com/128'"
                                    >
                                </div>
                                <div class="flex-1 space-y-2">
                                    <button 
                                        type="button"
                                        @click="$refs.imageInput.click()"
                                        class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        📁 Modifier l'image
                                    </button>
                                    <button 
                                        type="button"
                                        @click="removePhoto(form.photos.findIndex(p => p.is_primary))"
                                        class="w-full px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                                    >
                                        🗑️ Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Color Selector -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Couleur de l'article</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input 
                                    v-model="form.color"
                                    type="text"
                                    placeholder="Ex: Rouge, Bleu, #FF5733"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                            </div>
                            <button 
                                type="button"
                                @click="showColorPicker = !showColorPicker"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center"
                                :style="form.color && isHexColor(form.color) ? { backgroundColor: form.color, borderColor: form.color } : {}"
                            >
                                🎨
                            </button>
                        </div>
                        
                        <!-- Color Picker Dropdown -->
                        <div v-if="showColorPicker" class="mt-3 p-4 border border-gray-300 rounded-lg bg-white">
                            <!-- Standard Colors -->
                            <div class="mb-4">
                                <p class="text-sm font-medium text-gray-700 mb-2">Couleurs standard</p>
                                <div class="grid grid-cols-6 gap-2">
                                    <button 
                                        v-for="color in standardColors"
                                        :key="color"
                                        type="button"
                                        @click="form.color = color; showColorPicker = false"
                                        class="w-10 h-10 rounded-lg border-2 hover:border-gray-900 transition-all"
                                        :style="{ backgroundColor: color, borderColor: form.color === color ? '#000' : '#ddd' }"
                                        :title="color"
                                    />
                                </div>
                            </div>
                            
                            <!-- Custom Color Picker -->
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-2">Couleur personnalisée</p>
                                <input 
                                    v-model="form.color"
                                    type="color"
                                    class="w-full h-10 rounded-lg cursor-pointer border border-gray-300"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barcode / Identification Section (SECOND) -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">📱 Code Barre / Identification</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ID Article *</label>
                        <input 
                            v-model="form.sku"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                            placeholder="Ex: ART-001"
                        >
                        <p class="text-xs text-gray-500 mt-1">Identifiant unique de l'article</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code Barre / QR Code</label>
                        <input 
                            ref="barcodeScanner"
                            v-model="form.barcode"
                            type="text"
                            placeholder="Scannez le code barre ici"
                            @keyup.enter="focusNextField"
                            class="w-full px-4 py-2 border-2 border-primary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-primary-50 font-mono"
                        >
                        <p class="text-xs text-gray-500 mt-1">💡 Lecteur USB</p>
                    </div>
                </div>
            </div>

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

            <!-- Product Variants Section (Single-Choice, Article-Specific) -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">📏 Variantes Produit</h2>
                        <p class="text-sm text-gray-500 mt-1">Sélection unique (taille, couleur, format, etc.)</p>
                    </div>
                    <button 
                        type="button"
                        @click="showVariantModal = true"
                        class="text-sm text-primary-600 hover:text-primary-700 font-medium hover:underline"
                    >
                        + Ajouter une variante
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <label for="has_variants" class="text-sm font-medium text-gray-700">
                                Activer les variantes
                            </label>
                            <p class="text-xs text-gray-500 mt-1">L'utilisateur doit sélectionner exactement une variante</p>
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
                        <div v-if="form.has_variants" class="space-y-3">
                            <div v-if="articleVariants.length === 0" class="text-sm text-gray-500 italic p-4 bg-gray-50 rounded-lg border border-gray-200">
                                Aucune variante définie. 
                                <button 
                                    type="button"
                                    @click="showVariantModal = true"
                                    class="text-primary-600 hover:underline font-medium"
                                >
                                    Ajoutez-en une
                                </button>
                                pour commencer.
                            </div>
                            
                            <div v-else class="space-y-2 max-h-80 overflow-y-auto p-2">
                                <div 
                                    v-for="(variant, index) in articleVariants" 
                                    :key="index"
                                    class="flex items-start justify-between p-4 border rounded-lg hover:bg-gray-50 transition-all bg-gray-50 border-gray-200"
                                >
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">
                                            {{ variant.template_name ? `${variant.template_name} · ${variant.template_value}` : variant.name }}
                                        </p>
                                        <div class="flex flex-wrap items-center gap-3 mt-1 text-xs">
                                            <span v-if="variant.price_impact > 0" class="text-xs text-green-600 font-medium">
                                                +{{ formatCurrency(variant.price_impact) }}
                                            </span>
                                            <span v-if="variant.cost_price > 0" class="text-xs text-orange-600 font-medium">
                                                Coût: {{ formatCurrency(variant.cost_price) }}
                                            </span>
                                            <span v-if="variant.sku" class="text-xs text-gray-600 font-medium">
                                                SKU: {{ variant.sku }}
                                            </span>
                                            <span v-if="variant.barcode" class="text-xs text-gray-600 font-medium">
                                                Code-barres: {{ variant.barcode }}
                                            </span>
                                            <span v-if="variant.is_active" class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                                Actif
                                            </span>
                                            <span v-else class="px-2 py-0.5 text-xs font-medium bg-gray-200 text-gray-700 rounded-full">
                                                Inactif
                                            </span>
                                        </div>
                                    </div>
                                    <button 
                                        type="button"
                                        @click="editArticleVariant(index)"
                                        class="text-primary-600 hover:text-primary-700 text-sm font-medium px-3 py-1 border border-primary-200 rounded-lg hover:bg-primary-50"
                                    >
                                        Éditer
                                    </button>
                                </div>
                            </div>
                            
                            <div v-if="articleVariants.length > 0" class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-sm text-blue-800 font-medium">
                                    {{ articleVariants.length }} variante(s) définie(s)
                                </p>
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

        <!-- Create Variant Modal -->
        <transition name="fade">
            <div v-if="showVariantModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ editingVariantIndex !== null ? 'Éditer la variante' : 'Ajouter une variante' }}
                        </h3>
                        <button 
                            type="button"
                            @click="showVariantModal = false; editingVariantIndex = null; resetVariantForm()"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de la variante *</label>
                                <select
                                    v-model="newVariant.templateId"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white"
                                >
                                    <option value="" disabled>Choisir une variante enregistrée</option>
                                    <option v-for="template in variantTemplates" :key="template.id" :value="template.id">
                                        {{ template.name }}
                                    </option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    Ces variantes sont définies depuis <strong>/options</strong>.
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Valeur *</label>
                                <input
                                    v-model="newVariant.value"
                                    type="text"
                                    placeholder="Ex: Petit, Moyen, Grand..."
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-medium"
                                >
                                <p v-if="selectedTemplateValues.length" class="text-xs text-gray-500 mt-1">
                                    Valeurs existantes :
                                    <span v-for="value in selectedTemplateValues" :key="value" class="inline-flex items-center py-0.5 px-2 rounded-full bg-gray-100 text-xs text-gray-600 mr-1">
                                        {{ value }}
                                    </span>
                                </p>
                                <p v-else-if="variantTemplates.length === 0" class="text-xs text-red-600 mt-1">
                                    Définissez vos variantes d'abord depuis la page options.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Impact sur le prix ({{ settingsStore.currencyCode }})</label>
                                <input
                                    v-model.number="newVariant.price_impact"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-bold text-lg"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Coût ({{ settingsStore.currencyCode }})</label>
                                <input
                                    v-model.number="newVariant.cost_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-bold text-lg"
                                >
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">ID variante</label>
                                <input
                                    v-model="newVariant.sku"
                                    type="text"
                                    placeholder="Ex: VAR-XL"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Code-barres</label>
                                <input
                                    v-model="newVariant.barcode"
                                    type="text"
                                    placeholder="1234567890123"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 font-mono"
                                >
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Variante active</label>
                                <p class="text-xs text-gray-500 mt-1">Disponible à la sélection</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    v-model="newVariant.is_active"
                                    type="checkbox"
                                    class="sr-only peer"
                                >
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end space-x-3">
                        <button 
                            type="button"
                            @click="showVariantModal = false; editingVariantIndex = null; resetVariantForm()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100"
                        >
                            Annuler
                        </button>
                        <button 
                            v-if="editingVariantIndex !== null"
                            type="button"
                            @click="deleteArticleVariant(editingVariantIndex); showVariantModal = false; editingVariantIndex = null; resetVariantForm()"
                            class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50"
                        >
                            Supprimer
                        </button>
                        <button 
                            type="button"
                            @click="editingVariantIndex !== null ? updateArticleVariant() : addArticleVariant()"
                            :disabled="!newVariant.templateId || !newVariant.value.trim()"
                            class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ editingVariantIndex !== null ? 'Mettre à jour' : 'Ajouter' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>

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
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { articlesApi, categoriesApi, optionsApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { XMarkIcon, PlusIcon } from '@heroicons/vue/24/outline'
import { useVariantTemplatesStore } from '../../stores/variantTemplates'

const route = useRoute()
const router = useRouter()
const settingsStore = useSettingsStore()

const isEdit = computed(() => !!route.params.id)
const categories = ref([])
const options = ref([])
const variantTemplatesStore = useVariantTemplatesStore()
const variantTemplates = computed(() => variantTemplatesStore.templates)
const selectedTemplateValues = computed(() => {
    const template = variantTemplates.value.find(t => t.id === newVariant.templateId)
    return template?.values || []
})
const selectedOptions = ref([])
const articleVariants = ref([])
const saving = ref(false)
const showOptionModal = ref(false)
const showVariantModal = ref(false)
const showColorPicker = ref(false)
const creatingOption = ref(false)
const editingVariantIndex = ref(null)
const standardColors = ['#FF0000', '#00A854', '#0050B3', '#FAAD14', '#F5222D', '#722ED1']

const newOption = reactive({
    optionName: '',
    variantName: '',
    variantPrice: null,
})

const newVariant = reactive({
    templateId: '',
    value: '',
    price_impact: 0,
    cost_price: 0,
    sku: '',
    barcode: '',
    is_active: true,
})

const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

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
    price_type: 'fixed', // 'fixed' or 'variable'
    photos: [],
})

// Computed property for margin percentage
const marginPercentage = computed(() => {
    if (!form.buy_price || form.buy_price <= 0 || !form.sell_price || form.sell_price <= 0) {
        return '-'
    }
    const margin = ((form.sell_price - form.buy_price) / form.buy_price) * 100
    return margin.toFixed(2)
})

// Validate hex color format
function isHexColor(color) {
    return /^#[0-9A-F]{6}$/i.test(color)
}

// Calculate margin when prices change
function calculateMargin() {
    // Margin is calculated automatically via computed property
}

// Get margin class for styling
function getMarginClass() {
    if (marginPercentage.value === '-') return 'bg-gray-100 text-gray-700'
    const margin = parseFloat(marginPercentage.value)
    if (margin < 10) return 'bg-red-100 text-red-700'
    if (margin < 30) return 'bg-orange-100 text-orange-700'
    return 'bg-green-100 text-green-700'
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
        // Initialize with one empty photo for new articles
        if (form.photos.length === 0) {
            form.photos = [{ photo_url: '', is_primary: true, sort_order: 0 }]
        }
        return
    }
    
    try {
        const response = await articlesApi.get(route.params.id)
        const article = response.data
        Object.assign(form, article)
        
        // Set default price type if not set
        if (!form.price_type) {
            form.price_type = 'fixed'
        }
        
        // Set selected options
        if (article.options) {
            selectedOptions.value = article.options.map(o => o.id)
        }
        
        // Set variants
        if (article.variants) {
            articleVariants.value = article.variants.map((v, idx) => {
                const templateMatch = variantTemplates.value.find(
                    (template) => template.name === v.template_name
                )
                return {
                    id: v.id,
                    name: v.name,
                    template_id: templateMatch?.id || '',
                    template_name: v.template_name || null,
                    template_value: v.template_value || null,
                    price_impact: Number(v.price_impact) || 0,
                    cost_price: Number(v.cost_price) || 0,
                    sku: v.sku || '',
                    barcode: v.barcode || '',
                    is_active: v.is_active !== false,
                    sort_order: v.sort_order ?? idx,
                }
            })
        }
        
        // Set photos
        if (article.photos && article.photos.length > 0) {
            form.photos = article.photos.map(p => ({
                photo_url: p.photo_url,
                is_primary: p.is_primary,
                sort_order: p.sort_order
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

function addPhoto() {
    const newIndex = form.photos.length
    form.photos.push({ 
        photo_url: '', 
        is_primary: newIndex === 0, 
        sort_order: newIndex 
    })
}

function removePhoto(index) {
    // Allow removing any photo, even if it's the last one
    const wasMain = form.photos[index].is_primary
    form.photos.splice(index, 1)
    
    // If we removed the main photo and there are still photos, make the first one main
    if (wasMain && form.photos.length > 0) {
        form.photos[0].is_primary = true
    }
    
    // Update sort order
    form.photos.forEach((p, i) => p.sort_order = i)
}

function setPrimaryPhoto(index) {
    form.photos.forEach((p, i) => {
        p.is_primary = i === index
    })
}

// File upload handler - converts file to data URL
// Image upload handler - converts file to data URL
function handleImageUpload(event) {
    const file = event.target.files?.[0]
    if (!file) return

    // Check file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('Fichier trop volumineux. Taille maximale: 5MB')
        return
    }

    // Check file type
    if (!file.type.startsWith('image/')) {
        alert('Veuillez sélectionner une image valide')
        return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
        // Clear old photos and add new one as primary
        form.photos = [{
            photo_url: e.target?.result || '',
            is_primary: true,
            sort_order: 0
        }]
        // Reset file input
        event.target.value = ''
    }
    reader.readAsDataURL(file)
}

function handleFileUpload(index, event) {
    const file = event.target.files?.[0]
    if (!file) return

    // Check file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('Fichier trop volumineux. Taille maximale: 5MB')
        return
    }

    // Check file type
    if (!file.type.startsWith('image/')) {
        alert('Veuillez sélectionner une image valide')
        return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
        // Store as base64 data URL
        form.photos[index].photo_url = e.target?.result || ''
        // Reset file input
        event.target.value = ''
    }
    reader.readAsDataURL(file)
}

// Barcode scanner helper - auto-focus after entry
function focusNextField() {
    // Auto-move to next field after barcode scan
    const nextField = document.querySelector('input[placeholder*="Catégorie"]') || 
                     document.querySelector('input[placeholder*="category"]')
    if (nextField instanceof HTMLElement) {
        nextField.focus()
    }
}

async function handleSubmit() {
    saving.value = true
    
    try {
        // Check if user is authenticated
        const token = localStorage.getItem('auth_token')
        if (!token) {
            alert('Vous êtes déconnecté. Veuillez vous reconnecter.')
            router.push('/login')
            return
        }

        const data = { ...form }
        
        // Add selected options
        if (form.has_options) {
            data.options = selectedOptions.value
        } else {
            data.options = []
        }
        
        // Add variants
        if (form.has_variants) {
            data.variants = articleVariants.value.map(variant => ({
                name: variant.name,
                price_impact: variant.price_impact || 0,
                cost_price: variant.cost_price || 0,
                sku: variant.sku || null,
                barcode: variant.barcode || null,
                template_name: variant.template_name || null,
                template_value: variant.template_value || null,
                is_active: variant.is_active,
                sort_order: variant.sort_order || 0,
            }))
        } else {
            data.variants = []
        }
        
        // Filter out empty photo URLs
        if (data.photos && data.photos.length > 0) {
            data.photos = data.photos
                .filter(p => p.photo_url && p.photo_url.trim())
                .map((p, i) => ({
                    ...p,
                    sort_order: i,
                    is_primary: i === 0 ? true : p.is_primary
                }))
            
            // Ensure at least one photo is marked as primary
            if (data.photos.length > 0 && !data.photos.some(p => p.is_primary)) {
                data.photos[0].is_primary = true
            }
            
            // Set primary photo as the main photo field (for backwards compatibility)
            const primaryPhoto = data.photos.find(p => p.is_primary)
            if (primaryPhoto) {
                data.photo = primaryPhoto.photo_url
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
        
        // Handle specific errors
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('auth_user')
            alert('Votre session a expiré. Veuillez vous reconnecter.')
            router.push('/login')
        } else if (error.response?.status === 422) {
            // Validation errors
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
            is_required: false
        })

        const created = response.data
        created.variants = [
            {
                id: Date.now(),
                name: newOption.variantName.trim(),
                price_impact: Number(newOption.variantPrice) || 0
            }
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

function resetVariantForm() {
    newVariant.templateId = ''
    newVariant.value = ''
    newVariant.price_impact = 0
    newVariant.cost_price = 0
    newVariant.sku = ''
    newVariant.barcode = ''
    newVariant.is_active = true
}

function addArticleVariant() {
    if (!newVariant.templateId) {
        alert('Sélectionnez une variante existante')
        return
    }
    if (!newVariant.value.trim()) {
        alert('Veuillez entrer une valeur pour la variante')
        return
    }

    const template = variantTemplates.value.find(t => t.id === newVariant.templateId)
    const templateName = template?.name || ''
    const templateValue = newVariant.value.trim()
    const label = templateName ? `${templateName} · ${templateValue}` : templateValue

    articleVariants.value.push({
        name: label,
        template_id: template?.id || '',
        template_name: templateName || null,
        template_value: templateValue,
        price_impact: Number(newVariant.price_impact) || 0,
        cost_price: Number(newVariant.cost_price) || 0,
        sku: newVariant.sku.trim() || null,
        barcode: newVariant.barcode.trim() || null,
        is_active: newVariant.is_active,
        sort_order: articleVariants.value.length,
    })
    
    // Reset form
    resetVariantForm()
    showVariantModal.value = false
}

function editArticleVariant(index) {
    const variant = articleVariants.value[index]
    const templateMatch = variantTemplates.value.find(t => t.name === variant.template_name)
    newVariant.templateId = templateMatch?.id || ''
    newVariant.value = variant.template_value || variant.name || ''
    newVariant.price_impact = variant.price_impact || 0
    newVariant.cost_price = variant.cost_price || 0
    newVariant.sku = variant.sku || ''
    newVariant.barcode = variant.barcode || ''
    newVariant.is_active = variant.is_active
    editingVariantIndex.value = index
    showVariantModal.value = true
}

function updateArticleVariant() {
    if (editingVariantIndex.value === null) {
        addArticleVariant()
        return
    }
    
    if (!newVariant.templateId) {
        alert('Sélectionnez une variante existante')
        return
    }
    if (!newVariant.value.trim()) {
        alert('Veuillez entrer une valeur pour la variante')
        return
    }

    const template = variantTemplates.value.find(t => t.id === newVariant.templateId)
    const templateName = template?.name || ''
    const templateValue = newVariant.value.trim()
    const label = templateName ? `${templateName} · ${templateValue}` : templateValue

    articleVariants.value[editingVariantIndex.value] = {
        name: label,
        template_id: template?.id || '',
        template_name: templateName || null,
        template_value: templateValue,
        price_impact: Number(newVariant.price_impact) || 0,
        cost_price: Number(newVariant.cost_price) || 0,
        sku: newVariant.sku.trim() || null,
        barcode: newVariant.barcode.trim() || null,
        is_active: newVariant.is_active,
        sort_order: editingVariantIndex.value,
    }
    
    // Reset form
    resetVariantForm()
    editingVariantIndex.value = null
    showVariantModal.value = false
}

function deleteArticleVariant(index) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette variante?')) {
        articleVariants.value.splice(index, 1)
    }
}

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
