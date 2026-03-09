<template>
    <div>
        <div v-show="!showForm" class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestion des Clients</h1>
                    <p class="text-gray-500">Gérez vos clients et leur historique d'achats</p>
                </div>
                <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Nouveau Client
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Total Clients</p>
                    <p class="text-2xl font-bold text-gray-900">{{ customers.length }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Clients Fidèles</p>
                    <p class="text-2xl font-bold text-primary-600">{{ loyalCustomersCount }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Total Achats</p>
                    <p class="text-2xl font-bold text-green-600">{{ formatCurrency(totalSpent) }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Moyenne/Client</p>
                    <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(averageSpent) }}</p>
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
                <select v-model="filterLoyalty" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous les clients</option>
                    <option value="loyal">Clients fidèles</option>
                    <option value="new">Nouveaux clients</option>
                </select>
            </div>

            <!-- Customers Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ICE</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fidélité</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Achats</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="customer in filteredCustomers" :key="customer.id" class="hover:bg-gray-50 cursor-pointer" @click="$router.push(`/customers/${customer.client_id || customer.id}`)">
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ customer.client_id || `CLI-${String(customer.id).padStart(4, '0')}` }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full overflow-hidden bg-primary-100 border border-primary-100">
                                        <img v-if="customer.photo_url" :src="resolveCustomerPhotoUrl(customer.photo_url)" alt="Photo client" class="w-full h-full object-cover" />
                                        <span v-else class="flex items-center justify-center w-full h-full text-xs font-semibold text-primary-600">{{ getInitials(customer.name || `${customer.nom || ''} ${customer.prenom || ''}`.trim()) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ customer.name || `${customer.nom || ''} ${customer.prenom || ''}`.trim() }}</p>
                                        <p class="text-sm text-gray-500">{{ customer.city || 'Pas de ville' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ customer.ice || '-' }}</td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ customer.phone || '-' }}</p>
                                <p class="text-sm text-gray-500">{{ customer.email || '-' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <span v-if="customer.loyalty_discount > 0" class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        -{{ customer.loyalty_discount }}%
                                    </span>
                                    <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                                        Standard
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ formatCurrency(calculateCustomerTotal(customer)) }}</p>
                                <p class="text-sm text-gray-500">{{ calculateCustomerSalesCount(customer) }} achat(s)</p>
                            </td>
                            <td class="px-6 py-4 text-right" @click.stop>
                                <div class="flex items-center justify-end space-x-2">
                                    <button @click="viewHistory(customer)" class="p-2 text-purple-400 hover:text-purple-600 rounded-lg hover:bg-purple-50" title="Historique">
                                        <ClockIcon class="w-5 h-5" />
                                    </button>
                                    <button @click="openForm(customer)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                        <PencilIcon class="w-5 h-5" />
                                    </button>
                                    <button @click="confirmDelete(customer)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredCustomers.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <UserGroupIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                                Aucun client trouvé
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Customer Form Page -->
        <div v-show="showForm" class="bg-slate-50 min-h-screen px-4 py-6">
            <form @submit.prevent="saveCustomer" class="w-full max-w-5xl mx-auto bg-white rounded-3xl shadow-[0_25px_50px_rgba(15,23,42,0.25)] overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-5 border-b flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.5em] text-gray-400">Client</p>
                        <h3 class="text-2xl font-bold text-gray-900">{{ editingCustomer ? 'Modifier le client' : 'Nouvel client' }}</h3>
                    </div>
                    <button type="button" @click="closeCustomerForm" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>
                <div class="px-6 py-4 border-b flex flex-wrap gap-2">
                    <button type="button" @click="activeCustomerTab = 'informations'" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeCustomerTab === 'informations' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                        <ClipboardDocumentListIcon class="w-4 h-4 inline-block mr-1" />
                        Informations
                    </button>
                    <button type="button" @click="activeCustomerTab = 'historique'" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeCustomerTab === 'historique' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                        <ClockIcon class="w-4 h-4 inline-block mr-1" />
                        Historique
                    </button>
                    <button type="button" @click="activeCustomerTab = 'fidelite'" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeCustomerTab === 'fidelite' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                        <GiftIcon class="w-4 h-4 inline-block mr-1" />
                        Fidélité
                    </button>
                    <button type="button" @click="activeCustomerTab = 'documents'" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeCustomerTab === 'documents' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                        <DocumentIcon class="w-4 h-4 inline-block mr-1" />
                        Documents
                    </button>
                </div>
                <div class="px-6 py-6 overflow-y-auto space-y-6 flex-1">
                    <div v-show="activeCustomerTab === 'informations'" class="space-y-6">
                        <div class="grid gap-6 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                            <section class="bg-gray-50 border border-gray-200 rounded-2xl p-5 shadow-sm space-y-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-primary-100">
                                        <img v-if="customerPhotoPreview" :src="resolveCustomerPhotoUrl(customerPhotoPreview)" alt="Photo client" class="w-full h-full object-cover" />
                                        <PhotoIcon v-else class="w-full h-full p-3 text-primary-500" />
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.3em] text-gray-400">ID Client</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ editingCustomer?.client_id || (editingCustomer ? `CLI-${String(editingCustomer.id).padStart(4, '0')}` : 'Auto-généré') }}</p>
                                        <p class="text-xs text-gray-500">Type : {{ form.type_client === 'entreprise' ? 'Entreprise' : 'Particulier' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 border border-gray-200 rounded-xl cursor-pointer text-sm font-medium text-primary-600 hover:bg-primary-50">
                                        <PhotoIcon class="w-4 h-4" />
                                        Changer la photo
                                        <input type="file" accept="image/*" class="sr-only" @change="handleCustomerPhotoUpload">
                                    </label>
                                    <button type="button" @click="customerPhotoPreview = ''" class="px-3 py-2 border border-gray-200 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50">Supprimer</button>
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
                                        <label class="block text-sm font-medium text-gray-600">Type client</label>
                                        <select v-model="form.type_client" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                            <option value="particulier">Particulier</option>
                                            <option value="entreprise">Entreprise</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Raison sociale</label>
                                        <input v-model="form.raison_sociale" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom de l'entreprise">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Activité</label>
                                    <input v-model="form.activite" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Commerce, Service, ...">
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
                                        <input v-model="form.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="email@client.com">
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
                            </section>
                        </div>
                    </div>
                    <div v-show="activeCustomerTab === 'historique'" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700">Historique des achats</h4>
                                <div v-if="editingCustomer && customerHistory && customerHistory.length" class="space-y-3">
                                    <div v-for="purchase in customerHistory.slice(0, 4)" :key="purchase.id" class="flex items-center justify-between text-xs text-gray-600">
                                        <div>
                                            <p class="text-sm text-gray-900">{{ purchase.transaction_id || `TX-${purchase.id}` }}</p>
                                            <p>{{ formatDate(purchase.date) }}</p>
                                        </div>
                                        <p class="font-semibold text-gray-900">{{ formatCurrency(purchase.total) }}</p>
                                    </div>
                                </div>
                                <p v-else class="text-xs text-gray-500">Aucun achat enregistré</p>
                            </article>
                            <article v-if="editingCustomer && customerHistory && customerHistory.length" class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700">Résumé récent</h4>
                                <div class="space-y-3">
                                    <div v-for="purchase in customerHistory.slice(0, 2)" :key="purchase.id" class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs text-gray-500">{{ formatDate(purchase.date) }}</p>
                                            <p class="text-sm font-semibold text-gray-900">Vente</p>
                                        </div>
                                        <p class="text-sm font-semibold text-primary-600">{{ formatCurrency(purchase.total) }}</p>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                    <div v-show="activeCustomerTab === 'fidelite'" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700">Fidélité & avantages</h4>
                                <div v-if="editingCustomer" class="grid gap-3">
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-3">
                                        <p class="text-xs text-gray-500">Points accumulés</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ form.loyalty_points || 0 }}</p>
                                        <p class="text-xs text-gray-400">Points fidélité</p>
                                    </div>
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-3">
                                        <p class="text-xs text-gray-500">Remise</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ form.loyalty_discount || 0 }}%</p>
                                        <p class="text-xs text-gray-400">Réduction appliquée</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500">Points fidélité</label>
                                        <input v-model.number="form.loyalty_points" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500">Remise (%)</label>
                                        <input v-model.number="form.loyalty_discount" type="number" min="0" max="100" step="0.5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                </div>
                            </article>
                            <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700">Notes internes</h4>
                                <textarea v-model="form.note_interne" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ajoutez un commentaire, avantage spécial ou condition de traitement..."></textarea>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Avantages client</label>
                                    <input v-model="form.avantages" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ex: livraison prioritaire">
                                </div>
                            </article>
                        </div>
                    </div>
                    <div v-show="activeCustomerTab === 'documents'" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <!-- CIN Document -->
                            <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700">Carte d'Identité (CIN)</h4>
                                <div v-if="form.documents?.cin?.name || customerDocuments.cin" class="space-y-2">
                                    <div class="bg-green-50 rounded border border-green-200 p-3 flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <DocumentIcon class="w-5 h-5 text-green-600" />
                                            <span class="text-sm text-gray-700 font-medium truncate">{{ form.documents?.cin?.name || 'Document chargé' }}</span>
                                        </div>
                                        <button type="button" v-if="customerDocuments.cin" @click="downloadFile(customerDocuments.cin)" class="text-green-600 hover:text-green-700 text-sm font-medium flex items-center">
                                            <ArrowDownTrayIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <button type="button" @click="() => { form.documents.cin = null; customerDocuments.cin = null }" class="text-xs text-red-600 hover:text-red-700 font-medium">
                                        Supprimer le document
                                    </button>
                                </div>
                                <label class="block cursor-pointer">
                                    <span class="text-xs text-gray-600 font-medium">{{ form.documents?.cin?.name ? 'Remplacer le document' : 'Charger un document' }}</span>
                                    <input type="file" @change="(e) => handleDocumentUpload(e, 'cin')" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full mt-2 px-3 py-2 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                                </label>
                            </article>

                            <!-- Registre de Commerce -->
                            <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700">Registre de Commerce</h4>
                                <div v-if="form.documents?.registre_commerce?.name || customerDocuments.registre_commerce" class="space-y-2">
                                    <div class="bg-green-50 rounded border border-green-200 p-3 flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <DocumentIcon class="w-5 h-5 text-green-600" />
                                            <span class="text-sm text-gray-700 font-medium truncate">{{ form.documents?.registre_commerce?.name || 'Document chargé' }}</span>
                                        </div>
                                        <button type="button" v-if="customerDocuments.registre_commerce" @click="downloadFile(customerDocuments.registre_commerce)" class="text-green-600 hover:text-green-700 text-sm font-medium flex items-center">
                                            <ArrowDownTrayIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <button type="button" @click="() => { form.documents.registre_commerce = null; customerDocuments.registre_commerce = null }" class="text-xs text-red-600 hover:text-red-700 font-medium">
                                        Supprimer le document
                                    </button>
                                </div>
                                <label class="block cursor-pointer">
                                    <span class="text-xs text-gray-600 font-medium">{{ form.documents?.registre_commerce?.name ? 'Remplacer le document' : 'Charger un document' }}</span>
                                    <input type="file" @change="(e) => handleDocumentUpload(e, 'registre_commerce')" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full mt-2 px-3 py-2 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                                </label>
                            </article>

                            <!-- Attestation TVA -->
                            <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700">Attestation TVA</h4>
                                <div v-if="form.documents?.attestation_tva?.name || customerDocuments.attestation_tva" class="space-y-2">
                                    <div class="bg-green-50 rounded border border-green-200 p-3 flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <DocumentIcon class="w-5 h-5 text-green-600" />
                                            <span class="text-sm text-gray-700 font-medium truncate">{{ form.documents?.attestation_tva?.name || 'Document chargé' }}</span>
                                        </div>
                                        <button type="button" v-if="customerDocuments.attestation_tva" @click="downloadFile(customerDocuments.attestation_tva)" class="text-green-600 hover:text-green-700 text-sm font-medium flex items-center">
                                            <ArrowDownTrayIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <button type="button" @click="() => { form.documents.attestation_tva = null; customerDocuments.attestation_tva = null }" class="text-xs text-red-600 hover:text-red-700 font-medium">
                                        Supprimer le document
                                    </button>
                                </div>
                                <label class="block cursor-pointer">
                                    <span class="text-xs text-gray-600 font-medium">{{ form.documents?.attestation_tva?.name ? 'Remplacer le document' : 'Charger un document' }}</span>
                                    <input type="file" @change="(e) => handleDocumentUpload(e, 'attestation_tva')" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full mt-2 px-3 py-2 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                                </label>
                            </article>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
                    <button type="button" @click="closeCustomerForm" class="px-5 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-100">Annuler</button>
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
                        Historique des Achats - {{ selectedCustomer?.name }}
                    </h3>
                    <button @click="showHistoryModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Customer Summary -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Total Achats</p>
                        <p class="text-xl font-bold text-gray-900">{{ selectedCustomer?.completed_sales_count || 0 }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Montant Total</p>
                        <p class="text-xl font-bold text-green-600">{{ formatCurrency(selectedCustomer?.total_spent || 0) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Points Fidélité</p>
                        <p class="text-xl font-bold text-primary-600">{{ selectedCustomer?.loyalty_points || 0 }}</p>
                    </div>
                </div>

                <!-- Purchase History Table -->
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">N° Transaction</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="purchase in customerHistory" :key="purchase.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(purchase.date) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ purchase.transaction_id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ purchase.items_count }} article(s)</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatCurrency(purchase.total) }}</td>
                        </tr>
                        <tr v-if="customerHistory.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                Aucun achat enregistré
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
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer le client</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer "{{ customerToDelete?.name }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteCustomer" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { customersApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { 
    PlusIcon, 
    PencilIcon, 
    TrashIcon, 
    ClockIcon, 
    UserGroupIcon,
    GiftIcon,
    XMarkIcon,
    PhotoIcon,
    ClipboardDocumentListIcon,
    CalendarDaysIcon,
    DocumentIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)
const MAX_CUSTOMER_PHOTO_FILE_SIZE = 4 * 1024 * 1024
const MAX_CUSTOMER_PHOTO_DATA_URL_LENGTH = 8_000_000

const customers = ref([])
const search = ref('')
const filterLoyalty = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const showHistoryModal = ref(false)
const editingCustomer = ref(null)
const customerToDelete = ref(null)
const selectedCustomer = ref(null)
const customerHistory = ref([])
const customerDocuments = ref({})
const saving = ref(false)

function calculateCustomerTotal(customer) {
    return Number(customer?.total_spent || 0)
}

function calculateCustomerSalesCount(customer) {
    return Number(customer?.completed_sales_count || 0)
}

const defaultCustomerForm = () => ({
    nom: '',
    prenom: '',
    raison_sociale: '',
    activite: '',
    type_client: 'particulier',
    phone: '',
    email: '',
    address: '',
    city: '',
    country: '',
    ice: '',
    if: '',
    note_interne: '',
    loyalty_points: 0,
    loyalty_discount: 0,
    avantages: '',
    photo: null,
    documents: {
        cin: null,
        registre_commerce: null,
        attestation_tva: null
    }
})

const form = reactive(defaultCustomerForm())
const activeCustomerTab = ref('informations')
const customerPhotoPreview = ref('')

const filteredCustomers = computed(() => {
    let result = customers.value
    
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(c => {
            const name = (c.name || `${c.nom || ''} ${c.prenom || ''}`).trim().toLowerCase()
            return (
                (name && name.includes(query)) ||
                c.email?.toLowerCase().includes(query) ||
                c.phone?.includes(query) ||
                c.ice?.includes(query) ||
                c.client_id?.toLowerCase().includes(query)
            )
        })
    }
    
    if (filterLoyalty.value === 'loyal') {
        result = result.filter(c => c.loyalty_discount > 0 || c.is_vip)
    } else if (filterLoyalty.value === 'new') {
        result = result.filter(c => (c.completed_sales_count || 0) <= 1)
    }
    
    return result
})

const loyalCustomersCount = computed(() => customers.value.filter(c => c.loyalty_discount > 0 || c.is_vip).length)
const totalSpent = computed(() => {
    return customers.value.reduce((sum, customer) => sum + Number(customer?.total_spent || 0), 0)
})
const averageSpent = computed(() => customers.value.length > 0 ? totalSpent.value / customers.value.length : 0)

function buildCustomerPayload() {
    const fullName = `${form.nom || ''} ${form.prenom || ''}`.trim()
    return {
        name: fullName || form.raison_sociale || 'Client',
        email: form.email || null,
        phone: form.phone || null,
        activity: form.activite || null,
        address: form.address || null,
        city: form.city || null,
        country: form.country || null,
        photo_url: customerPhotoPreview.value || null,
        notes: form.note_interne || null,
    }
}

function mapSalesHistory(responseData) {
    const rows = Array.isArray(responseData?.data) ? responseData.data : (Array.isArray(responseData) ? responseData : [])
    return rows.map((sale) => ({
        id: sale.id,
        date: sale.created_at || sale.date || null,
        transaction_id: sale.reference || sale.order_number || `TX-${sale.id}`,
        items_count: Array.isArray(sale.items) ? sale.items.length : Number(sale.items_count || 0),
        total: Number(sale.total || 0),
    }))
}

async function fetchCustomers() {
    const response = await customersApi.list({ with_stats: true, paginate: false, active: true })
    customers.value = Array.isArray(response.data) ? response.data : (response.data?.data || [])
}

function getInitials(name = '') {
    const fragments = (name || '').trim().split(' ').filter(Boolean)
    if (!fragments.length) return ''
    return fragments.map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function resolveCustomerPhotoUrl(value) {
    const url = String(value || '').trim()
    if (!url) return ''
    if (
        url.startsWith('data:image/')
        || url.startsWith('blob:')
        || url.startsWith('http://')
        || url.startsWith('https://')
        || url.startsWith('//')
    ) {
        return url
    }
    if (url.startsWith('/')) {
        return `${window.location.origin}${url}`
    }
    if (url.startsWith('storage/')) {
        return `${window.location.origin}/${url}`
    }
    return `${window.location.origin}/storage/${url}`
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function resetCustomerForm() {
    Object.assign(form, defaultCustomerForm())
    customerPhotoPreview.value = ''
    activeCustomerTab.value = 'informations'
}

function closeCustomerForm() {
    showForm.value = false
    editingCustomer.value = null
    resetCustomerForm()
}

function readFileAsDataUrl(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader()
        reader.onload = (e) => resolve(String(e.target?.result || ''))
        reader.onerror = () => reject(new Error('Impossible de lire l\'image'))
        reader.readAsDataURL(file)
    })
}

function loadImageElement(dataUrl) {
    return new Promise((resolve, reject) => {
        const image = new Image()
        image.onload = () => resolve(image)
        image.onerror = () => reject(new Error('Image invalide'))
        image.src = dataUrl
    })
}

async function optimizeImageForUpload(file) {
    const sourceDataUrl = await readFileAsDataUrl(file)
    const image = await loadImageElement(sourceDataUrl)

    const maxDimension = 1024
    const sourceWidth = image.naturalWidth || image.width
    const sourceHeight = image.naturalHeight || image.height
    const scale = Math.min(1, maxDimension / Math.max(sourceWidth, sourceHeight))
    const width = Math.max(1, Math.round(sourceWidth * scale))
    const height = Math.max(1, Math.round(sourceHeight * scale))

    const canvas = document.createElement('canvas')
    canvas.width = width
    canvas.height = height

    const context = canvas.getContext('2d')
    if (!context) return sourceDataUrl

    context.drawImage(image, 0, 0, width, height)

    const targetType = file.type === 'image/png' ? 'image/png' : 'image/jpeg'
    const optimizedDataUrl = canvas.toDataURL(targetType, targetType === 'image/jpeg' ? 0.85 : undefined)

    return optimizedDataUrl.length > sourceDataUrl.length ? sourceDataUrl : optimizedDataUrl
}

async function handleCustomerPhotoUpload(event) {
    const file = event.target.files?.[0]
    if (!file) return

    if (!file.type.startsWith('image/')) {
        alert('Veuillez sélectionner une image valide (PNG, JPG, WebP...).')
        event.target.value = ''
        return
    }

    if (file.size > MAX_CUSTOMER_PHOTO_FILE_SIZE) {
        alert('Image trop volumineuse. Taille maximale autorisée: 4 Mo.')
        event.target.value = ''
        return
    }

    form.photo = file

    try {
        const optimizedDataUrl = await optimizeImageForUpload(file)
        if (optimizedDataUrl.length > MAX_CUSTOMER_PHOTO_DATA_URL_LENGTH) {
            alert('Image trop lourde pour le serveur. Réduisez la taille de l\'image.')
            return
        }
        customerPhotoPreview.value = optimizedDataUrl
    } catch (error) {
        console.error('Customer photo processing error:', error)
        alert('Impossible de traiter cette image.')
    } finally {
        event.target.value = ''
    }
}

function extractFileName(path) {
    if (!path) return 'document'
    if (typeof path === 'object') {
        return path.name || 'document'
    }
    return path.split('/').pop().replace(/\.pdf|\.doc|\.docx|\.jpg|\.jpeg|\.png/i, '')
}

function downloadFile(url) {
    const source = typeof url === 'string' ? url : url?.url
    const filename = typeof url === 'object' ? url?.name || extractFileName(source) : extractFileName(source)
    if (source) {
        const link = document.createElement('a')
        link.href = source
        link.download = filename
        link.click()
    }
}

function handleDocumentUpload(event, docType) {
    const file = event.target.files?.[0]
    if (!file) return
    
    const reader = new FileReader()
    reader.onload = (e) => {
        if (!form.documents) {
            form.documents = {}
        }
        // Store only file name and type, not the base64 content (too large for localStorage)
        form.documents[docType] = {
            name: file.name,
            type: file.type,
            size: file.size
        }
        // Store the base64 in memory only (in customerDocuments) for download
        customerDocuments.value[docType] = {
            name: file.name,
            url: e.target.result,
            type: file.type
        }
    }
    reader.readAsDataURL(file)
}

async function openForm(customer = null) {
    editingCustomer.value = customer
    resetCustomerForm()
    customerDocuments.value = {} // Reset memory-only document storage
    if (customer) {
        // Load customer data into form
        form.nom = customer.nom || customer.name || ''
        form.prenom = customer.prenom || ''
        form.raison_sociale = customer.raison_sociale || ''
        form.activite = customer.activite || customer.activity || ''
        form.type_client = customer.type_client || 'particulier'
        form.phone = customer.phone || ''
        form.email = customer.email || ''
        form.address = customer.address || ''
        form.city = customer.city || ''
        form.country = customer.country || ''
        form.ice = customer.ice || ''
        form.if = customer.if || ''
        form.note_interne = customer.note_interne || ''
        form.loyalty_discount = customer.loyalty_discount || 0
        form.loyalty_points = customer.loyalty_points || 0
        form.avantages = customer.avantages || ''
        form.documents = customer.documents || { cin: null, registre_commerce: null, attestation_tva: null }
        customerPhotoPreview.value = customer.photo_url || ''
        // Note: customerDocuments stays empty for loaded customers (files don't persist across sessions)
        // Users can upload files again in the current session
        
        try {
            const historyResponse = await customersApi.history(customer.id)
            customerHistory.value = mapSalesHistory(historyResponse.data)
        } catch (error) {
            customerHistory.value = []
            console.error('Error loading customer history:', error)
        }
    }
    showForm.value = true
}

async function viewHistory(customer) {
    selectedCustomer.value = customer
    try {
        const historyResponse = await customersApi.history(customer.id)
        customerHistory.value = mapSalesHistory(historyResponse.data)
    } catch (error) {
        customerHistory.value = []
        console.error('Error loading customer history:', error)
    }
    showHistoryModal.value = true
}

function confirmDelete(customer) {
    customerToDelete.value = customer
    showDeleteModal.value = true
}

async function saveCustomer() {
    saving.value = true
    try {
        const payload = buildCustomerPayload()
        if (editingCustomer.value) {
            await customersApi.update(editingCustomer.value.id, payload)
        } else {
            await customersApi.create(payload)
        }
        await fetchCustomers()
        closeCustomerForm()
    } catch (error) {
        alert(error.response?.data?.message || 'Erreur lors de l\'enregistrement du client')
    } finally {
        saving.value = false
    }
}

async function deleteCustomer() {
    try {
        await customersApi.delete(customerToDelete.value.id)
        await fetchCustomers()
        showDeleteModal.value = false
    } catch (error) {
        alert('Erreur lors de la suppression')
    }
}

onMounted(async () => {
    try {
        await fetchCustomers()
    } catch (error) {
        console.error('Error loading customers:', error)
    }
})
</script>
