<template>
    <div>
        <div v-show="!showForm" class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestion des Fournisseurs</h1>
                    <p class="text-gray-500">Gérez vos fournisseurs et leur historique de ventes</p>
                </div>
                <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Nouveau Fournisseur
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Total Fournisseurs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ fournisseurs.length }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Fournisseurs Actifs</p>
                    <p class="text-2xl font-bold text-green-600">{{ activeFournisseurs }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Total Commandes</p>
                    <p class="text-2xl font-bold text-blue-600">{{ totalOrders }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Total Achats</p>
                    <p class="text-2xl font-bold text-primary-600">{{ formatCurrency(totalPurchases) }}</p>
                </div>
            </div>

            <!-- Search -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
                <input 
                    v-model="search"
                    type="text"
                    placeholder="Rechercher par ID, nom, ICE, email ou téléphone..."
                    class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous les fournisseurs</option>
                    <option value="active">Actifs</option>
                    <option value="inactive">Inactifs</option>
                </select>
            </div>

            <!-- Fournisseurs Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Fournisseur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ICE</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ville</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Ventes</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="fournisseur in filteredFournisseurs" :key="fournisseur.id" class="hover:bg-primary-50 cursor-pointer transition" @click="viewFournisseurDetails(fournisseur)">
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ fournisseur.fournisseur_id || `FRN-${String(fournisseur.id).padStart(4, '0')}` }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-14 h-14 rounded-full overflow-hidden bg-secondary-100 border border-secondary-100 shadow-sm">
                                        <img v-if="fournisseur.photo_url" :src="getSupplierPhotoUrl(fournisseur)" alt="Photo fournisseur" class="w-full h-full object-cover" />
                                        <span v-else class="flex items-center justify-center w-full h-full text-sm font-semibold text-primary-600">{{ getInitials(fournisseur.name || `${fournisseur.nom || ''} ${fournisseur.prenom || ''}`.trim()) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ fournisseur.name || `${fournisseur.nom || ''} ${fournisseur.prenom || ''}`.trim() }}</p>
                                        <p class="text-sm text-gray-500">{{ fournisseur.email || '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ fournisseur.ice || '-' }}</td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ fournisseur.phone || '-' }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ fournisseur.city || '-' }}</td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ formatCurrency(calculateSupplierTotal(fournisseur)) }}</p>
                                <p class="text-sm text-gray-500">{{ calculateSupplierOrdersCount(fournisseur) }} réception(s)</p>
                            </td>
                            <td class="px-6 py-4 text-right" @click.stop>
                                <div class="flex items-center justify-end space-x-2">
                                    <button @click="viewHistory(fournisseur)" class="p-2 text-purple-400 hover:text-purple-600 rounded-lg hover:bg-purple-50" title="Historique">
                                        <ClockIcon class="w-5 h-5" />
                                    </button>
                                    <button @click="openForm(fournisseur)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                        <PencilIcon class="w-5 h-5" />
                                    </button>
                                    <button @click="confirmDelete(fournisseur)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredFournisseurs.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <BuildingOfficeIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                                Aucun fournisseur trouvé
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Fournisseur Form Page -->
        <div v-show="showForm" class="bg-slate-50 min-h-screen px-4 py-6">
            <form @submit.prevent="saveFournisseur" class="w-full max-w-5xl mx-auto bg-white rounded-3xl shadow-[0_25px_50px_rgba(15,23,42,0.25)] overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-5 border-b flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.5em] text-gray-400">Fournisseur</p>
                        <h3 class="text-2xl font-bold text-gray-900">{{ editingFournisseur ? 'Modifier le fournisseur' : 'Nouveau fournisseur' }}</h3>
                    </div>
                    <button type="button" @click="closeFournisseurForm" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>
                <div class="px-6 py-4 border-b flex flex-wrap gap-2">
                    <button type="button" @click="activeSupplierTab = 'informations'" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeSupplierTab === 'informations' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                        <ClipboardDocumentListIcon class="w-4 h-4 inline-block mr-1" />
                        Informations
                    </button>
                    <button type="button" @click="activeSupplierTab = 'historique'" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeSupplierTab === 'historique' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                        <ClockIcon class="w-4 h-4 inline-block mr-1" />
                        Historique
                    </button>
                    <button type="button" @click="activeSupplierTab = 'documents'" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeSupplierTab === 'documents' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                        <CalendarDaysIcon class="w-4 h-4 inline-block mr-1" />
                        Documents
                    </button>
                </div>
                <div class="px-6 py-6 overflow-y-auto space-y-6 flex-1">
                    <div v-show="activeSupplierTab === 'informations'" class="space-y-6">
                        <div class="grid gap-6 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                            <section class="bg-gray-50 border border-gray-200 rounded-2xl p-5 shadow-sm space-y-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-primary-100">
                                        <img v-if="supplierPhotoPreview" :src="supplierPhotoPreview" alt="Photo fournisseur" class="w-full h-full object-cover" />
                                        <PhotoIcon v-else class="w-full h-full p-3 text-primary-500" />
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.3em] text-gray-400">ID Fournisseur</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ editingFournisseur?.fournisseur_id || (editingFournisseur ? `FRN-${String(editingFournisseur.id).padStart(4, '0')}` : 'Auto-généré') }}</p>
                                        <p class="text-xs text-gray-500">Type : {{ form.type === 'entreprise' ? 'Entreprise' : 'Particulier' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 border border-gray-200 rounded-xl cursor-pointer text-sm font-medium text-primary-600 hover:bg-primary-50">
                                        <PhotoIcon class="w-4 h-4" />
                                        Changer la photo
                                        <input type="file" accept="image/*" class="sr-only" @change="handleFournisseurPhotoUpload">
                                    </label>
                                    <button type="button" @click="removeSupplierPhoto" class="px-3 py-2 border border-gray-200 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50">Supprimer</button>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Nom *</label>
                                        <input v-model="form.nom" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom de famille">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Prénom</label>
                                        <input v-model="form.prenom" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Prénom">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Type</label>
                                        <select v-model="form.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                            <option value="particulier">Particulier</option>
                                            <option value="entreprise">Entreprise</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Raison sociale</label>
                                        <input v-model="form.raison_sociale" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom de l'entreprise">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Statut</label>
                                        <select v-model="form.statut" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                            <option value="Actif">Actif</option>
                                            <option value="Inactif">Inactif</option>
                                            <option value="Suspendu">Suspendu</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input v-model="form.prefere" type="checkbox" id="prefere" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                        <label for="prefere" class="text-sm font-medium text-gray-700">Fournisseur préféré</label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Activité</label>
                                    <input v-model="form.activite" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Distribution, Import, ...">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Banque</label>
                                        <input v-model="form.banque" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Banque partenaire">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">IBAN</label>
                                        <input v-model="form.iban" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="IBAN">
                                    </div>
                                </div>
                            </section>
                            <section class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm space-y-5">
                                <h4 class="text-md font-semibold text-gray-800">Coordonnées</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Téléphone</label>
                                        <input v-model="form.phone" type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="+212 600 000 000">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Email</label>
                                        <input v-model="form.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="email@fournisseur.com">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Ville</label>
                                        <input v-model="form.city" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ville">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Pays</label>
                                        <input v-model="form.country" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Pays">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Contact principal</label>
                                        <input v-model="form.contact_principal" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom du contact">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Site web</label>
                                        <input v-model="form.site_web" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="www.example.com">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Adresse</label>
                                    <textarea v-model="form.address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Adresse complète"></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">ICE</label>
                                        <input v-model="form.ice" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="000000000000000">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">IF</label>
                                        <input v-model="form.if" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="000000000000000">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Reg. Commercial</label>
                                        <input v-model="form.reg_commercial" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="RC-123456">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">TVA</label>
                                        <input v-model="form.tva" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="TVA-123456">
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input v-model="form.is_active" type="checkbox" id="is_active" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <label for="is_active" class="text-sm font-medium text-gray-700">Fournisseur actif</label>
                                </div>
                            </section>
                        </div>
                    </div>
                    <div v-show="activeSupplierTab === 'historique'" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700">Historique des commandes</h4>
                                <div v-if="fournisseurHistory.length" class="space-y-3">
                                    <div v-for="order in fournisseurHistory.slice(0, 4)" :key="order.id" class="flex items-center justify-between text-xs text-gray-600">
                                        <div>
                                            <p class="text-sm text-gray-900">{{ order.order_id }}</p>
                                            <p>{{ formatDate(order.date) }}</p>
                                        </div>
                                        <p class="font-semibold text-gray-900">{{ formatCurrency(order.total) }}</p>
                                    </div>
                                </div>
                                <p v-else class="text-xs text-gray-500">Aucune commande enregistrée</p>
                            </article>
                            <article v-if="supplierHighlights.length > 0" class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700">Résumé fournisseur</h4>
                                <div class="grid gap-3">
                                    <div v-for="insight in supplierHighlights" :key="insight.id" class="bg-gray-50 border border-gray-200 rounded-2xl p-3">
                                        <p class="text-xs text-gray-500">{{ insight.label }}</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ insight.value }}</p>
                                        <p class="text-xs text-gray-400">{{ insight.detail }}</p>
                                    </div>
                                </div>
                            </article>
                            <article v-else class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3 flex items-center justify-center min-h-32">
                                <p class="text-sm text-gray-500">Aucune donnée de commande disponible</p>
                            </article>
                        </div>
                    </div>
                    <div v-show="activeSupplierTab === 'documents'" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold text-gray-700">Contrat</p>
                                    <input type="file" class="text-xs text-primary-500" @change="handleContractUpload">
                                </div>
                                <p class="text-xs text-gray-500">Upload du contrat signé (PDF, DOC)</p>
                                <div v-if="form.contract_file" class="mt-2 flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700">
                                    <span>{{ form.contract_file.name }}</span>
                                    <button type="button" @click="form.contract_file = null" class="text-red-500 hover:text-red-700">Supprimer</button>
                                </div>
                            </article>
                            <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold text-gray-700">RIB</p>
                                    <input type="file" class="text-xs text-primary-500" @change="handleRibUpload">
                                </div>
                                <p class="text-xs text-gray-500">Relevé bancaire officiel</p>
                                <div v-if="form.rib_file" class="mt-2 flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700">
                                    <span>{{ form.rib_file.name }}</span>
                                    <button type="button" @click="form.rib_file = null" class="text-red-500 hover:text-red-700">Supprimer</button>
                                </div>
                            </article>
                        </div>
                        <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                            <h4 class="text-sm font-semibold text-gray-700">Notes internes</h4>
                            <textarea v-model="form.note" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Demande spécifique, conditions de paiement..."></textarea>
                        </article>
                    </div>
                </div>
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
                    <button type="button" @click="closeFournisseurForm" class="px-5 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-100">Annuler</button>
                    <button type="submit" :disabled="saving" class="px-5 py-2 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 disabled:opacity-60">Enregistrer</button>
                </div>
            </form>
        </div>

        <!-- History Modal -->
        <div v-if="showHistoryModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showHistoryModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-3xl w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Historique des Ventes - {{ selectedFournisseur?.name }}
                    </h3>
                    <button @click="showHistoryModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Fournisseur Summary -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Total Commandes</p>
                        <p class="text-xl font-bold text-gray-900">{{ selectedFournisseur?.orders_count || 0 }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Montant Total</p>
                        <p class="text-xl font-bold text-green-600">{{ formatCurrency(selectedFournisseur?.total_purchases || 0) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Dernière Commande</p>
                        <p class="text-xl font-bold text-primary-600">{{ formatDate(selectedFournisseur?.last_order_date) }}</p>
                    </div>
                </div>

                <!-- Purchase History Table -->
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">N° Commande</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="order in fournisseurHistory" :key="order.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(order.date) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ order.order_id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ order.items_count }} article(s)</td>
                            <td class="px-4 py-3">
                                <span :class="getStatusClass(order.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                    {{ getStatusLabel(order.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatCurrency(order.total) }}</td>
                        </tr>
                        <tr v-if="fournisseurHistory.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                Aucune commande enregistrée
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer le fournisseur</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer "{{ fournisseurToDelete?.name }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteFournisseur" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSettingsStore } from '../../stores/settings'
import { fournisseursApi } from '../../api'
import { 
    PlusIcon, 
    PencilIcon, 
    TrashIcon, 
    ClockIcon, 
    BuildingOfficeIcon,
    XMarkIcon,
    EyeIcon,
    PhotoIcon,
    ClipboardDocumentListIcon,
    CalendarDaysIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const router = useRouter()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const fournisseurs = ref([])
const search = ref('')
const filterStatus = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const showHistoryModal = ref(false)
const editingFournisseur = ref(null)
const fournisseurToDelete = ref(null)
const selectedFournisseur = ref(null)
const fournisseurHistory = ref([])
const saving = ref(false)
const supplierPhotoTouched = ref(false)

// Load fournisseurs from localStorage or API
const loadFournisseurs = async () => {
    try {
        // Try API first
        const response = await fournisseursApi.list()
        fournisseurs.value = Array.isArray(response.data) ? response.data : response.data.data || []
        // Save to localStorage as backup
        localStorage.setItem('pos_fournisseurs', JSON.stringify(fournisseurs.value))
    } catch (error) {
        console.error('Error loading fournisseurs from API:', error)
        // Fallback to localStorage
        const saved = localStorage.getItem('pos_fournisseurs')
        if (saved) {
            fournisseurs.value = JSON.parse(saved)
        }
    }
}

// Save fournisseurs to localStorage
const saveFournisseursLocally = () => {
    localStorage.setItem('pos_fournisseurs', JSON.stringify(fournisseurs.value))
}

const defaultFournisseurForm = () => ({
    nom: '',
    prenom: '',
    raison_sociale: '',
    activite: '',
    type: 'particulier',
    statut: 'Actif',
    prefere: false,
    phone: '',
    email: '',
    address: '',
    city: '',
    country: '',
    contact_principal: '',
    site_web: '',
    ice: '',
    if: '',
    reg_commercial: '',
    tva: '',
    note: '',
    is_active: true,
    photo: null,
    contract_file: null,
    rib_file: null,
    banque: '',
    iban: ''
})

const form = reactive(defaultFournisseurForm())
const activeSupplierTab = ref('informations')
const supplierPhotoPreview = ref('')

const supplierHighlights = computed(() => {
    // Only show highlights if there is actual history data
    if (fournisseurHistory.value.length === 0) {
        return []
    }
    
    const highlights = []
    
    // Show count of commandes from actual history
    if (fournisseurHistory.value.length > 0) {
        highlights.push({
            id: 1,
            label: 'Commandes enregistrées',
            value: String(fournisseurHistory.value.length),
            detail: 'Nombre total'
        })
    }
    
    // Calculate average delivery time if available
    const deliveryTimes = fournisseurHistory.value
        .map(h => {
            if (h.delivery_days !== undefined) return h.delivery_days
            return null
        })
        .filter(d => d !== null)
    
    if (deliveryTimes.length > 0) {
        const avgDelay = Math.round(deliveryTimes.reduce((a, b) => a + b, 0) / deliveryTimes.length)
        highlights.push({
            id: 2,
            label: 'Délai moyen',
            value: `${avgDelay} jours`,
            detail: 'Livraison moyenne'
        })
    }
    

    return highlights
})

const filteredFournisseurs = computed(() => {
    let result = fournisseurs.value
    
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(f => {
            const name = (f.name || `${f.nom || ''} ${f.prenom || ''}`).trim().toLowerCase()
            return (
                (name && name.includes(query)) ||
                f.email?.toLowerCase().includes(query) ||
                f.phone?.includes(query) ||
                f.ice?.includes(query) ||
                f.fournisseur_id?.toLowerCase().includes(query)
            )
        })
    }
    
    if (filterStatus.value === 'active') {
        result = result.filter(f => f.is_active !== false)
    } else if (filterStatus.value === 'inactive') {
        result = result.filter(f => f.is_active === false)
    }
    
    return result
})

const activeFournisseurs = computed(() => fournisseurs.value.filter(f => f.is_active !== false).length)
const totalOrders = computed(() => fournisseurs.value.reduce((sum, f) => sum + (f.orders_count || 0), 0))
const totalPurchases = computed(() => {
    try {
        const stored = localStorage.getItem('pos_receptions')
        if (stored) {
            const receptions = JSON.parse(stored)
            return receptions.reduce((sum, r) => sum + (r.montant_total || 0), 0)
        }
    } catch (error) {
        console.error('Error calculating total purchases:', error)
    }
    return 0
})

function getInitials(name = '') {
    const fragments = (name || '').trim().split(' ').filter(Boolean)
    if (!fragments.length) return ''
    return fragments.map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function isDataUrl(url) {
    return typeof url === 'string' && (url.startsWith('data:') || url.startsWith('blob:'))
}

function getSupplierPhotoUrl(fournisseur) {
    const url = fournisseur?.photo_url || fournisseur?.logo
    if (!url) {
        return ''
    }
    if (isDataUrl(url)) {
        return url
    }
    const cacheKey = fournisseur.photo_cache_key || 0
    const separator = url.includes('?') ? '&' : '?'
    return `${url}${separator}t=${cacheKey}`
}

function calculateSupplierTotal(fournisseur) {
    // Calculate total from receptions
    try {
        const stored = localStorage.getItem('pos_receptions')
        if (stored) {
            const receptions = JSON.parse(stored)
            const supplierReceptions = receptions.filter(r => r.fournisseur_id === fournisseur.id)
            return supplierReceptions.reduce((sum, r) => sum + (r.montant_total || 0), 0)
        }
    } catch (error) {
        console.error('Error calculating supplier total:', error)
    }
    return 0
}

function calculateSupplierOrdersCount(fournisseur) {
    // Calculate count from receptions (goods received)
    try {
        const stored = localStorage.getItem('pos_receptions')
        if (stored) {
            const receptions = JSON.parse(stored)
            return receptions.filter(r => r.fournisseur_id === fournisseur.id).length
        }
    } catch (error) {
        console.error('Error calculating supplier orders count:', error)
    }
    return 0
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function resetFournisseurForm() {
    Object.assign(form, defaultFournisseurForm())
    supplierPhotoPreview.value = ''
    supplierPhotoTouched.value = false
    activeSupplierTab.value = 'informations'
}

function closeFournisseurForm() {
    showForm.value = false
    editingFournisseur.value = null
    resetFournisseurForm()
}

function handleFournisseurPhotoUpload(event) {
    const file = event.target.files?.[0]
    if (!file) return
    form.photo = file
    supplierPhotoTouched.value = true
    const reader = new FileReader()
    reader.onload = (e) => {
        supplierPhotoPreview.value = e.target?.result || ''
    }
    reader.readAsDataURL(file)
}

function removeSupplierPhoto() {
    supplierPhotoPreview.value = ''
    supplierPhotoTouched.value = true
}

function handleContractUpload(event) {
    const file = event.target.files?.[0]
    if (!file) return
    const reader = new FileReader()
    reader.onload = (e) => {
        form.contract_file = {
            name: file.name,
            url: e.target?.result || ''
        }
    }
    reader.readAsDataURL(file)
}

function handleRibUpload(event) {
    const file = event.target.files?.[0]
    if (!file) return
    const reader = new FileReader()
    reader.onload = (e) => {
        form.rib_file = {
            name: file.name,
            url: e.target?.result || ''
        }
    }
    reader.readAsDataURL(file)
}

function getStatusClass(status) {
    const classes = {
        'reçue': 'bg-green-100 text-green-800',
        'en_cours': 'bg-yellow-100 text-yellow-800',
        'envoyée': 'bg-blue-100 text-blue-800',
        'annulée': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
    const labels = {
        'reçue': 'Reçue',
        'en_cours': 'En cours',
        'envoyée': 'Envoyée',
        'annulée': 'Annulée'
    }
    return labels[status] || status
}

function openForm(fournisseur = null) {
    editingFournisseur.value = fournisseur
    resetFournisseurForm()
    
    // Reset history when opening form
    fournisseurHistory.value = []
    
    if (fournisseur) {
        form.nom = fournisseur.nom || fournisseur.name || ''
        form.prenom = fournisseur.prenom || ''
        form.raison_sociale = fournisseur.raison_sociale || ''
        form.activite = fournisseur.activite || ''
        form.type = fournisseur.type || 'particulier'
        form.statut = fournisseur.statut || 'Actif'
        form.prefere = fournisseur.prefere || false
        form.phone = fournisseur.phone || fournisseur.telephone || ''
        form.email = fournisseur.email || ''
        form.address = fournisseur.address || fournisseur.adresse || ''
        form.city = fournisseur.city || fournisseur.ville || ''
        form.country = fournisseur.country || ''
        form.ice = fournisseur.ice || ''
        form.if = fournisseur.if || ''
        form.reg_commercial = fournisseur.reg_commercial || ''
        form.tva = fournisseur.tva || ''
        form.note = fournisseur.note || fournisseur.observations || ''
        form.is_active = fournisseur.is_active !== false
        form.contact_principal = fournisseur.contact_principal || ''
        form.site_web = fournisseur.site_web || ''
        form.banque = fournisseur.banque || ''
        form.iban = fournisseur.iban || ''
        form.contract_file = fournisseur.contract_file || null
        form.rib_file = fournisseur.rib_file || null
        supplierPhotoPreview.value = fournisseur.photo_url || fournisseur.logo || ''
        supplierPhotoTouched.value = false
        
        // Load commandes history from BonCommandeList localStorage
        try {
            const stored = localStorage.getItem('pos_bon_commandes')
            if (stored) {
                const commandes = JSON.parse(stored)
                // Filter commandes for this supplier
                fournisseurHistory.value = commandes
                    .filter(c => c.fournisseur_id === fournisseur.id)
                    .map(c => ({
                        id: c.id,
                        date: c.date,
                        order_id: c.numero,
                        items_count: c.nb_articles,
                        status: c.statut,
                        total: c.montant_total
                    }))
            }
        } catch (error) {
            console.error('Error loading commandes history:', error)
        }
    }
    showForm.value = true
}

function viewHistory(fournisseur) {
    selectedFournisseur.value = fournisseur
    // Demo history data
    fournisseurHistory.value = [
        { id: 1, date: '2026-02-08', order_id: 'BC-2026-015', items_count: 25, status: 'reçue', total: 12500 },
        { id: 2, date: '2026-02-01', order_id: 'BC-2026-012', items_count: 15, status: 'reçue', total: 8500 },
        { id: 3, date: '2026-01-20', order_id: 'BC-2026-008', items_count: 40, status: 'reçue', total: 22000 },
        { id: 4, date: '2026-01-10', order_id: 'BC-2026-003', items_count: 10, status: 'reçue', total: 5500 },
    ]
    showHistoryModal.value = true
}

function confirmDelete(fournisseur) {
    fournisseurToDelete.value = fournisseur
    showDeleteModal.value = true
}

async function saveFournisseur() {
    saving.value = true
    try {
        const fullName = `${form.nom} ${form.prenom}`.trim() || form.raison_sociale || 'Fournisseur'
        const payload = {
            name: fullName,
            contact_name: form.contact_principal || null,
            email: form.email || null,
            phone: form.phone || null,
            address: form.address || null,
            city: form.city || null,
            country: form.country || null,
            ice: form.ice || null,
            notes: form.note || null,
            is_active: form.is_active !== false,
        }

        let saved
        if (editingFournisseur.value?.id && Number(editingFournisseur.value.id) < 1e12) {
            const { data } = await fournisseursApi.update(editingFournisseur.value.id, payload)
            saved = data
        } else {
            const { data } = await fournisseursApi.create(payload)
            saved = data
        }

        await loadFournisseurs()
        showForm.value = false
        resetFournisseurForm()
        return saved
    } catch (error) {
        alert(error.response?.data?.message || error.message || 'Erreur lors de l\'enregistrement')
    } finally {
        saving.value = false
    }
}

async function deleteFournisseur() {
    try {
        if (fournisseurToDelete.value?.id) {
            await fournisseursApi.delete(fournisseurToDelete.value.id)
        }
        await loadFournisseurs()
    } catch (error) {
        // Fallback local if API unavailable
        fournisseurs.value = fournisseurs.value.filter(f => f.id !== fournisseurToDelete.value.id)
        saveFournisseursLocally()
    }
    showDeleteModal.value = false
}

onMounted(() => {
    loadFournisseurs()
})

function viewFournisseurDetails(fournisseur) {
    router.push(`/fournisseurs/${fournisseur.id}`)
}
</script>
