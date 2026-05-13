<template>
    <div>
        <div v-show="!showForm" class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Employés</h1>
                <p class="text-gray-500">Gérez votre équipe et leurs accès</p>
            </div>
            <button @click="openForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouvel Employé
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Employés</p>
                <p class="text-2xl font-bold text-gray-900">{{ employees.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Actifs</p>
                <p class="text-2xl font-bold text-green-600">{{ activeCount }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total Ventes</p>
                <p class="text-2xl font-bold text-primary-600">{{ formatCurrency(totalSales) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Moyenne/Employé</p>
                <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(averageSales) }}</p>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
                <input
                    v-model="search"
                    type="text"
                placeholder="Rechercher par ID, nom, prénom, email ou téléphone..."
                class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="active">Actifs</option>
                <option value="inactive">Inactifs</option>
                <option value="suspended">Suspendus</option>
            </select>
            <select v-model="filterRole" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les rôles</option>
                <option value="admin">Administrateur</option>
                <option value="manager">Manager</option>
                <option value="cashier">Caissier</option>
            </select>
        </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Employé</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employé</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ville</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ventes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="employee in filteredEmployees" :key="employee.id" class="hover:bg-primary-50 cursor-pointer" @click="openEmployeeDetails(employee)">
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ employee.employee_id || `EMP-${String(employee.id).padStart(4, '0')}` }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-100 bg-primary-100">
                                        <img v-if="employee.photo_url" :key="employee.id" :src="getEmployeePhotoUrl(employee)" alt="Photo employé" class="w-full h-full object-cover" />
                                        <span v-else class="flex items-center justify-center w-full h-full text-xs font-semibold text-primary-600">{{ getInitials(employee.nom, employee.prenom) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ employee.nom }} {{ employee.prenom }}</p>
                                        <p class="text-sm text-gray-500">{{ employee.email || '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ employee.phone || '-' }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ employee.city || '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ getRoleLabel(employee.role) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ formatCurrency(employee.total_sales || 0) }}</p>
                                <p class="text-sm text-gray-500">{{ employee.sales_count || 0 }} ventes</p>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(employee.status)]">{{ getStatusLabel(employee.status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button @click.stop="viewHistory(employee)" class="p-2 text-purple-400 hover:text-purple-600 rounded-lg hover:bg-purple-50" title="Historique des ventes">
                                        <ClockIcon class="w-5 h-5" />
                                    </button>
                                    <button @click.stop="openForm(employee)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                        <PencilIcon class="w-5 h-5" />
                                    </button>
                                    <button @click.stop="confirmDelete(employee)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredEmployees.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <UsersIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                                Aucun employé trouvé
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Employee Form Page -->
        <div v-show="showForm" class="bg-slate-50 min-h-screen px-4 py-6">
            <form @submit.prevent="saveEmployee" class="w-full max-w-5xl mx-auto bg-white rounded-3xl shadow-[0_25px_50px_rgba(15,23,42,0.25)] overflow-hidden max-h-[90vh] flex flex-col">
                        <div class="px-6 py-5 border-b flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.5em] text-gray-400">Employé</p>
                                <h3 class="text-2xl font-bold text-gray-900">{{ editingEmployee ? 'Modifier l\'employé' : 'Nouvel employé' }}</h3>
                            </div>
                            <button type="button" @click="closeEmployeeForm" class="text-gray-400 hover:text-gray-600 transition">
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="px-6 py-4 border-b flex flex-wrap gap-2">
                            <button type="button" @click="switchEmployeeTab('informations')" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeEmployeeTab === 'informations' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                                <ClipboardDocumentListIcon class="w-4 h-4 inline-block mr-1" />
                                Informations
                            </button>
                            <button type="button" @click="switchEmployeeTab('dossier')" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeEmployeeTab === 'dossier' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                                <DocumentArrowUpIcon class="w-4 h-4 inline-block mr-1" />
                                Dossier employé
                            </button>
                            <button type="button" @click="switchEmployeeTab('presence')" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeEmployeeTab === 'presence' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                                <CalendarDaysIcon class="w-4 h-4 inline-block mr-1" />
                                Présence
                            </button>
                            <button type="button" @click="switchEmployeeTab('historique')" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeEmployeeTab === 'historique' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                                <ClockIcon class="w-4 h-4 inline-block mr-1" />
                                Historique
                            </button>
                            <button type="button" @click="switchEmployeeTab('paie')" :class="['px-4 py-2 rounded-full text-sm font-semibold transition', activeEmployeeTab === 'paie' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600']">
                                <CreditCardIcon class="w-4 h-4 inline-block mr-1" />
                                Paie
                            </button>
                        </div>
                        <div class="px-6 py-6 overflow-y-auto space-y-6 flex-1">
                            <div v-show="activeEmployeeTab === 'informations'" class="space-y-6">
                                <div class="grid gap-6 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                                    <section class="bg-gray-50 border border-gray-200 rounded-2xl p-5 shadow-sm space-y-5">
                                        <div class="flex items-center gap-4">
                                            <div class="w-16 h-16 rounded-xl overflow-hidden bg-primary-100">
                                                <img v-if="profilePhotoPreview" :src="profilePhotoPreview" alt="Photo employé" class="w-full h-full object-cover" />
                                                <PhotoIcon v-else class="w-full h-full p-3 text-primary-500" />
                                            </div>
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.3em] text-gray-400">ID Employé</p>
                                                <p class="text-lg font-semibold text-gray-900">{{ editingEmployee?.employee_id || (editingEmployee ? `EMP-${String(editingEmployee.id).padStart(4, '0')}` : 'Auto-généré') }}</p>
                                                <p class="text-xs text-gray-500">{{ form.poste || 'Poste non défini' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <label class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 border border-gray-200 rounded-xl cursor-pointer text-sm font-medium text-primary-600 hover:bg-primary-50">
                                                <PhotoIcon class="w-4 h-4" />
                                                <span>Changer la photo</span>
                                                <input type="file" accept="image/*" class="sr-only" @change="handlePhotoUpload">
                                            </label>
                                            <button type="button" @click="removePhoto" class="px-3 py-2 border border-red-200 text-red-600 rounded-xl text-sm font-medium hover:bg-red-50">Supprimer</button>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600">Nom *</label>
                                                <input v-model="form.nom" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600">Prénom *</label>
                                                <input v-model="form.prenom" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Prénom">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600">Poste</label>
                                                <input v-model="form.poste" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ex: Chef de caisse">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600">Rôle</label>
                                                <select v-model="form.role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                                    <option value="admin">Administrateur</option>
                                                    <option value="manager">Manager</option>
                                                    <option value="cashier">Caissier</option>
                                                    <option value="vendor">Vendeur</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">Tâches / responsabilités</label>
                                            <div class="flex gap-2 mt-2">
                                                <input v-model="newTask" type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ex: Gestion caisse">
                                                <button type="button" @click="addTaskTag" class="px-4 py-2 bg-primary-500 text-white rounded-lg text-sm font-semibold hover:bg-primary-600">Ajouter</button>
                                            </div>
                                            <div class="flex flex-wrap gap-2 mt-3">
                                                <span v-for="(task, index) in form.tasks" :key="task" class="flex items-center gap-1 px-3 py-1 bg-white border border-gray-200 rounded-full text-xs font-medium text-gray-700">
                                                    <TagIcon class="w-3 h-3 text-primary-500" />
                                                    {{ task }}
                                                    <button type="button" @click="removeTask(index)" class="ml-1 text-gray-400 hover:text-gray-600">×</button>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600">Date d'entrée</label>
                                                <input v-model="form.date_entree" type="datetime-local" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600">Date de sortie</label>
                                                <input v-model="form.date_sortie" type="datetime-local" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">Observations</label>
                                            <textarea v-model="form.observations" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Notes internes..."></textarea>
                                        </div>
                                    </section>
                                    <section class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm space-y-5">
                                        <h4 class="text-md font-semibold text-gray-800">Coordonnées & statut</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600">Téléphone</label>
                                                <input v-model="form.phone" type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="+212 600 000 000">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600">Email</label>
                                                <input v-model="form.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="email@exemple.com">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600">Ville</label>
                                                <input v-model="form.city" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ville">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600">Pays</label>
                                                <input v-model="form.pays" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Pays">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">Adresse</label>
                                            <textarea v-model="form.address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Adresse complète"></textarea>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600">Statut</label>
                                                <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                                    <option value="active">Actif</option>
                                                    <option value="inactive">Inactif</option>
                                                    <option value="suspended">Suspendu</option>
                                                </select>
                                            </div>
                                            <div class="flex items-end">
                                                <p class="text-sm text-gray-500">{{ editingEmployee?.department || 'Aucune affectation' }}</p>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                            <div v-show="activeEmployeeTab === 'dossier'" class="space-y-6">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-gray-700">CIN</p>
                                            <input type="file" class="text-xs text-primary-500" @change="handleDossierUpload('cin', $event)">
                                        </div>
                                        <p class="text-xs text-gray-500">{{ dossierUploads.cin || 'Aucun document téléchargé' }}</p>
                                        <p class="text-xs text-gray-400">Format accepté : PDF, JPG</p>
                                    </article>
                                    <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-gray-700">Diplômes</p>
                                            <input type="file" class="text-xs text-primary-500" @change="handleDossierUpload('diplomes', $event)">
                                        </div>
                                        <p class="text-xs text-gray-500">{{ dossierUploads.diplomes || 'Aucun document' }}</p>
                                        <p class="text-xs text-gray-400">Accepter les fichiers lourds</p>
                                    </article>
                                </div>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-gray-700">Contrats</p>
                                            <input type="file" class="text-xs text-primary-500" @change="handleDossierUpload('contrats', $event)">
                                        </div>
                                        <p class="text-xs text-gray-500">{{ dossierUploads.contrats || 'Aucun dossier contractuel' }}</p>
                                    </article>
                                    <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-gray-700">Autres documents</p>
                                            <button type="button" class="text-xs text-primary-500" @click="addAutreDocument">Enregistrer</button>
                                        </div>
                                        <div class="flex gap-2">
                                            <input v-model="autreDocumentLabel" type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom du document">
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="(doc, index) in dossierUploads.autres" :key="doc + index" class="flex items-center gap-2 px-3 py-1 bg-gray-100 text-xs rounded-full">
                                                <ClipboardDocumentListIcon class="w-4 h-4 text-primary-500" />
                                                {{ doc }}
                                                <button type="button" class="text-red-500" @click="removeAutreDocument(index)">×</button>
                                            </span>
                                        </div>
                                    </article>
                                </div>
                            </div>
                            <div v-show="activeEmployeeTab === 'presence'" class="space-y-4">
                                <div class="grid gap-4 md:grid-cols-3">
                                    <article v-for="record in presenceRecords" :key="record.date" class="bg-white border border-gray-200 rounded-2xl p-4 space-y-2">
                                        <p class="text-xs text-gray-400">{{ record.date }}</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ record.status }}</p>
                                        <p class="text-sm text-gray-600">Entrée: {{ record.entree || '—' }}</p>
                                        <p class="text-sm text-gray-600">Sortie: {{ record.sortie || '—' }}</p>
                                        <p class="text-xs text-gray-500">{{ record.note }}</p>
                                    </article>
                                </div>
                                <div v-if="presenceRecords.length === 0" class="bg-gray-50 border border-gray-200 rounded-2xl p-6 text-center">
                                    <CalendarDaysIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                                    <p class="text-gray-500">Aucune donnée de présence disponible</p>
                                </div>
                                <div v-else class="bg-white border border-gray-200 rounded-2xl p-4 flex items-center gap-4">
                                    <CalendarDaysIcon class="w-6 h-6 text-primary-500" />
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700">Résumé présence</p>
                                        <p class="text-xs text-gray-500">Aucune donnée de présence ce mois-ci</p>
                                    </div>
                                </div>
                            </div>
                            <div v-show="activeEmployeeTab === 'paie'" class="grid gap-4 md:grid-cols-2">
                                <section class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm space-y-4">
                                    <h4 class="text-md font-semibold text-gray-800">Rémunération</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">Salaire de base</label>
                                            <input v-model="form.salaire_base" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="0.00" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">Prime / bonus</label>
                                            <input v-model="form.prime" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="0.00" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">Mode de paiement</label>
                                            <select v-model="form.mode_paiement" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                                <option value="virement">Virement</option>
                                                <option value="especes">Espèces</option>
                                                <option value="cheque">Chèque</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">Date de paiement</label>
                                            <input v-model="form.date_paiement" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" />
                                        </div>
                                    </div>
                                </section>
                                <section class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm space-y-4">
                                    <h4 class="text-md font-semibold text-gray-800">Coordonnées bancaires</h4>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">Banque</label>
                                            <input v-model="form.banque" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom de la banque" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600">IBAN / RIB</label>
                                            <input v-model="form.iban" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="MA00 0000 0000 0000 0000 0000" />
                                        </div>
                                        <p class="text-xs text-gray-500">Ces informations restent internes et sécurisées.</p>
                                    </div>
                                </section>
                            </div>
                            <div v-show="activeEmployeeTab === 'historique'" class="space-y-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                                        <h4 class="text-sm font-semibold text-gray-700">Actions système</h4>
                                        <div v-for="action in historiqueActions" :key="action.id" class="space-y-1">
                                            <p class="text-xs text-gray-400">{{ action.date }} · {{ action.author }}</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ action.label }}</p>
                                            <p class="text-xs text-gray-500">{{ action.detail }}</p>
                                        </div>
                                    </article>
                                    <article class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3">
                                        <h4 class="text-sm font-semibold text-gray-700">Historique des ventes</h4>
                                        <div v-if="employeeHistory.length" class="space-y-3">
                                            <div v-for="sale in employeeHistory.slice(0, 3)" :key="sale.id" class="flex items-center justify-between text-xs text-gray-600">
                                                <div>
                                                    <p class="text-sm text-gray-900">{{ sale.transaction_id }}</p>
                                                    <p>{{ formatDate(sale.date) }}</p>
                                                </div>
                                                <p class="font-semibold text-gray-900">{{ formatCurrency(sale.total) }}</p>
                                            </div>
                                        </div>
                                        <p v-else class="text-xs text-gray-500">Aucun historique récent</p>
                                    </article>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
                            <button type="button" @click="closeEmployeeForm" class="px-5 py-2 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-100">Annuler</button>
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
                        Historique des Ventes - {{ selectedEmployee?.nom }} {{ selectedEmployee?.prenom }}
                    </h3>
                    <button @click="showHistoryModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Employee Summary -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Total Ventes</p>
                        <p class="text-xl font-bold text-gray-900">{{ selectedEmployee?.sales_count || 0 }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Montant Total</p>
                        <p class="text-xl font-bold text-green-600">{{ formatCurrency(selectedEmployee?.total_sales || 0) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Moyenne/Vente</p>
                        <p class="text-xl font-bold text-primary-600">{{ formatCurrency(selectedEmployee?.sales_count > 0 ? selectedEmployee?.total_sales / selectedEmployee?.sales_count : 0) }}</p>
                    </div>
                </div>

                <!-- Sales History Table -->
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">N° Transaction</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="sale in employeeHistory" :key="sale.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(sale.date) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ sale.transaction_id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ sale.customer_name || 'Client comptoir' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ sale.items_count }} article(s)</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatCurrency(sale.total) }}</td>
                        </tr>
                        <tr v-if="employeeHistory.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                Aucune vente enregistrée
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
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer l'employé</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer "{{ employeeToDelete?.nom }} {{ employeeToDelete?.prenom }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteEmployee" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { employeesApi } from '../../api'
import { useSettingsStore } from '../../stores/settings'
import { 
    PlusIcon, 
    PencilIcon, 
    TrashIcon, 
    UsersIcon, 
    ClockIcon,
    XMarkIcon,
    EyeIcon,
    PhotoIcon,
    DocumentArrowUpIcon,
    ClipboardDocumentListIcon,
    CalendarDaysIcon,
    TagIcon,
    CreditCardIcon
} from '@heroicons/vue/24/outline'

const STORAGE_KEY = 'pos_employees'
const SALES_STORAGE_KEY = 'pos_sales'

const settingsStore = useSettingsStore()
const router = useRouter()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

const employees = ref([])
const search = ref('')
const filterStatus = ref('')
const filterRole = ref('')
const showForm = ref(false)
const showDeleteModal = ref(false)
const showHistoryModal = ref(false)
const editingEmployee = ref(null)
const employeeToDelete = ref(null)
const selectedEmployee = ref(null)
const employeeHistory = ref([])
const saving = ref(false)

const defaultEmployeeForm = () => ({
    nom: '',
    prenom: '',
    poste: '',
    tasks: [],
    email: '',
    phone: '',
    city: '',
    pays: '',
    address: '',
    role: 'cashier',
    status: 'active',
    date_entree: '',
    date_sortie: '',
    observations: '',
    photo: null,
    salaire_base: '',
    prime: '',
    mode_paiement: 'virement',
    iban: '',
    banque: '',
    date_paiement: ''
})

const form = reactive(defaultEmployeeForm())
const activeEmployeeTab = ref('informations')
const newTask = ref('')
const profilePhotoPreview = ref('')
const photoTouched = ref(false)
const presenceRecords = ref([])
const historiqueActions = ref([])
const dossierUploads = reactive({
    cin: '',
    diplomes: '',
    contrats: '',
    autres: []
})
const autreDocumentLabel = ref('')

const readStoredEmployees = () => {
    try {
        const stored = localStorage.getItem(STORAGE_KEY)
        return stored ? JSON.parse(stored) : []
    } catch (error) {
        console.error('Unable to parse stored employees:', error)
        return []
    }
}

const saveEmployeesToStorage = () => {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(employees.value))
    } catch (error) {
        console.error('Unable to save employees to storage:', error)
    }
}

const calculateEmployeeSalesFromPOS = (employeeId) => {
    try {
        const storedSales = localStorage.getItem(SALES_STORAGE_KEY)
        if (!storedSales) return { total_sales: 0, sales_count: 0 }
        const sales = JSON.parse(storedSales)
        const employeeSales = sales.filter(s => s.employee_id === employeeId)
        return {
            total_sales: employeeSales.reduce((sum, s) => sum + (s.total || 0), 0),
            sales_count: employeeSales.length
        }
    } catch (error) {
        console.error('Unable to calculate sales:', error)
        return { total_sales: 0, sales_count: 0 }
    }
}

const filteredEmployees = computed(() => {
    let result = employees.value
    
    if (search.value) {
        const query = search.value.toLowerCase()
        result = result.filter(e => 
            e.nom?.toLowerCase().includes(query) ||
            e.prenom?.toLowerCase().includes(query) ||
            e.email?.toLowerCase().includes(query) ||
            e.phone?.includes(query) ||
            e.employee_id?.toLowerCase().includes(query)
        )
    }
    
    if (filterStatus.value) {
        result = result.filter(e => e.status === filterStatus.value)
    }
    
    if (filterRole.value) {
        result = result.filter(e => e.role === filterRole.value)
    }
    
    return result
})

const activeCount = computed(() => employees.value.filter(e => e.status === 'active').length)
const totalSales = computed(() => employees.value.reduce((sum, e) => sum + (e.total_sales || 0), 0))
const averageSales = computed(() => employees.value.length > 0 ? totalSales.value / employees.value.length : 0)

function getInitials(nom, prenom) {
    const n = nom ? nom[0] : ''
    const p = prenom ? prenom[0] : ''
    return (n + p).toUpperCase() || 'EM'
}

function isDataUrl(url) {
    return typeof url === 'string' && (url.startsWith('data:') || url.startsWith('blob:'))
}

function getEmployeePhotoUrl(employee) {
    const url = employee?.photo_url
    if (!url) {
        return ''
    }
    if (isDataUrl(url)) {
        return url
    }
    const cacheKey = employee.photo_cache_key || 0
    const separator = url.includes('?') ? '&' : '?'
    return `${url}${separator}t=${cacheKey}`
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR')
}

function getRoleLabel(role) {
    const labels = { admin: 'Administrateur', manager: 'Manager', cashier: 'Caissier', vendor: 'Vendeur' }
    return labels[role] || role
}

function getStatusClass(status) {
    const classes = { 
        active: 'bg-green-100 text-green-800', 
        inactive: 'bg-gray-100 text-gray-700', 
        suspended: 'bg-red-100 text-red-700' 
    }
    return classes[status] || 'bg-gray-100 text-gray-700'
}

function resetDossierUploads() {
    dossierUploads.cin = ''
    dossierUploads.diplomes = ''
    dossierUploads.contrats = ''
    dossierUploads.autres = []
}

function resetEmployeeForm() {
    Object.assign(form, defaultEmployeeForm())
    profilePhotoPreview.value = ''
    newTask.value = ''
    photoTouched.value = false
    activeEmployeeTab.value = 'informations'
    resetDossierUploads()
}

function switchEmployeeTab(tab) {
    activeEmployeeTab.value = tab
}

function handlePhotoUpload(event) {
    const file = event.target.files?.[0]
    if (!file) return
    form.photo = file
    photoTouched.value = true
    
    // Convert image to base64 for persistent storage
    const reader = new FileReader()
    reader.onload = (e) => {
        profilePhotoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
}

function openEmployeeDetails(employee) {
    router.push(`/employees/${employee.id}`)
}

function removePhoto() {
    form.photo = null
    profilePhotoPreview.value = ''
    photoTouched.value = true
}

function addTaskTag() {
    const value = newTask.value.trim()
    if (!value) return
    if (!form.tasks.includes(value)) {
        form.tasks.push(value)
    }
    newTask.value = ''
}

function removeTask(index) {
    form.tasks.splice(index, 1)
}

function handleDossierUpload(type, event) {
    const file = event.target.files?.[0]
    if (!file) return
    dossierUploads[type] = file.name
}

function addAutreDocument() {
    const label = autreDocumentLabel.value.trim()
    if (!label) return
    dossierUploads.autres.push(label)
    autreDocumentLabel.value = ''
}

function removeAutreDocument(index) {
    dossierUploads.autres.splice(index, 1)
}

function getStatusLabel(status) {
    const labels = { active: 'Actif', inactive: 'Inactif', suspended: 'Suspendu' }
    return labels[status] || status
}

function closeEmployeeForm() {
    showForm.value = false
    editingEmployee.value = null
    resetEmployeeForm()
}

function openForm(employee = null) {
    editingEmployee.value = employee
    resetEmployeeForm()
    if (employee) {
        form.nom = employee.nom || ''
        form.prenom = employee.prenom || ''
        form.poste = employee.poste || ''
        form.tasks = Array.isArray(employee.tasks) ? [...employee.tasks] : []
        form.email = employee.email || ''
        form.phone = employee.phone || employee.telephone || ''
        form.city = employee.city || employee.ville || ''
        form.pays = employee.pays || ''
        form.address = employee.address || employee.adresse || ''
        form.role = employee.role || 'cashier'
        form.status = employee.status || 'active'
        form.date_entree = employee.date_entree || ''
        form.date_sortie = employee.date_sortie || ''
        form.observations = employee.observations || ''
        profilePhotoPreview.value = employee.photo_url || employee.avatar || ''
        photoTouched.value = false
        dossierUploads.cin = employee.documents?.cin || ''
        dossierUploads.diplomes = employee.documents?.diplomes || ''
        dossierUploads.contrats = employee.documents?.contrats || ''
        dossierUploads.autres = Array.isArray(employee.documents?.autres) ? [...employee.documents.autres] : []
    }
    showForm.value = true
}

function viewHistory(employee) {
    selectedEmployee.value = employee
    // Load real sales history from localStorage
    try {
        const storedSales = localStorage.getItem(SALES_STORAGE_KEY)
        if (storedSales) {
            const allSales = JSON.parse(storedSales)
            employeeHistory.value = allSales
                .filter(s => s.employee_id === employee.id)
                .sort((a, b) => new Date(b.date) - new Date(a.date))
        } else {
            employeeHistory.value = []
        }
    } catch (error) {
        console.error('Error loading sales history:', error)
        employeeHistory.value = []
    }
    showHistoryModal.value = true
}

function confirmDelete(employee) {
    employeeToDelete.value = employee
    showDeleteModal.value = true
}

async function saveEmployee() {
    saving.value = true
    try {
        // Prepare data for API (matching backend validation)
        const apiData = {
            name: `${form.nom} ${form.prenom}`.trim(),
            email: form.email || null,
            phone: form.phone || null,
            role: form.role,
            status: form.status,
            hire_date: form.date_entree || null,
        }

        console.log('Saving employee with data:', apiData)

        // Call the API to save to database
        let response
        if (editingEmployee.value) {
            console.log('Updating employee ID:', editingEmployee.value.id)
            response = await employeesApi.update(editingEmployee.value.id, apiData)
        } else {
            console.log('Creating new employee...')
            response = await employeesApi.create(apiData)
        }

        console.log('API response:', response.data)

        // Get the saved employee from API response
        const savedEmployee = response.data

        // Prepare full employee data for localStorage with additional frontend fields
        const employeeData = {
            ...savedEmployee,
            nom: form.nom,
            prenom: form.prenom,
            poste: form.poste,
            tasks: [...form.tasks],
            city: form.city,
            pays: form.pays,
            address: form.address,
            date_entree: form.date_entree,
            date_sortie: form.date_sortie,
            observations: form.observations,
            photo_url: profilePhotoPreview.value || null,
            photo_cache_key: photoTouched.value ? Date.now() : (editingEmployee.value?.photo_cache_key || 0),
            documents: {
                cin: dossierUploads.cin,
                diplomes: dossierUploads.diplomes,
                contrats: dossierUploads.contrats,
                autres: [...dossierUploads.autres]
            }
        }

        // Calculate sales from POS data
        const salesData = calculateEmployeeSalesFromPOS(employeeData.id)
        employeeData.total_sales = salesData.total_sales
        employeeData.sales_count = salesData.sales_count

        // Update local state
        if (editingEmployee.value) {
            const index = employees.value.findIndex(e => e.id === editingEmployee.value.id)
            if (index > -1) employees.value[index] = employeeData
        } else {
            employees.value.unshift(employeeData)
        }
        
        // Also save to localStorage for offline access
        saveEmployeesToStorage()
        
        console.log('Employee saved successfully!')
        closeEmployeeForm()
    } catch (error) {
        console.error('Error saving employee:', error)
        console.error('Error response:', error.response?.data)
        console.error('Error status:', error.response?.status)
        
        let errorMessage = 'Impossible de sauvegarder l\'employé'
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message
        } else if (error.response?.data?.errors) {
            const errors = Object.values(error.response.data.errors).flat()
            errorMessage = errors.join(', ')
        } else if (error.message) {
            errorMessage = error.message
        }
        
        alert('Erreur: ' + errorMessage)
    } finally {
        saving.value = false
    }
}

async function deleteEmployee() {
    try {
        // Delete from database via API
        await employeesApi.delete(employeeToDelete.value.id)
        
        // Remove from local state
        employees.value = employees.value.filter(e => e.id !== employeeToDelete.value.id)
        saveEmployeesToStorage()
        showDeleteModal.value = false
    } catch (error) {
        console.error('Error deleting employee:', error)
        alert('Erreur lors de la suppression: ' + (error.response?.data?.message || error.message || 'Impossible de supprimer l\'employé'))
    }
}

onMounted(async () => {
    // Load from localStorage first
    const storedEmployees = readStoredEmployees()
    if (storedEmployees.length > 0) {
        employees.value = storedEmployees.map(emp => ({
            ...emp,
            ...calculateEmployeeSalesFromPOS(emp.id)
        }))
        return
    }
    
    // Fallback to API if localStorage is empty
    try {
        const response = await employeesApi.list()
        employees.value = Array.isArray(response.data) ? response.data : response.data.data || []
        saveEmployeesToStorage()
    } catch (error) {
        console.error('Error loading employees:', error)
        // Demo data
        employees.value = [
            { id: 1, employee_id: 'EMP-0001', nom: 'Benali', prenom: 'Ahmed', phone: '0612345678', email: 'ahmed.benali@pos.com', city: 'Casablanca', address: '123 Rue Mohammed V', role: 'admin', status: 'active', total_sales: 125000, sales_count: 85 },
            { id: 2, employee_id: 'EMP-0002', nom: 'Mansouri', prenom: 'Sara', phone: '0698765432', email: 'sara.mansouri@pos.com', city: 'Rabat', address: '45 Avenue Hassan II', role: 'manager', status: 'active', total_sales: 98000, sales_count: 62 },
            { id: 3, employee_id: 'EMP-0003', nom: 'Tazi', prenom: 'Mohamed', phone: '0655443322', email: 'mohamed.tazi@pos.com', city: 'Marrakech', address: '78 Boulevard Zerktouni', role: 'cashier', status: 'active', total_sales: 75000, sales_count: 120 },
            { id: 4, employee_id: 'EMP-0004', nom: 'El Amrani', prenom: 'Fatima', phone: '0677889900', email: 'fatima.elamrani@pos.com', city: 'Fès', address: '22 Rue Allal Ben Abdellah', role: 'vendor', status: 'inactive', total_sales: 45000, sales_count: 38 },
        ]
        saveEmployeesToStorage()
    }
})
</script>
