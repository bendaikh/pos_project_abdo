<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Utilisateurs</h1>
                <p class="text-gray-500">Gérez les accès et les agents placeurs</p>
            </div>
            <button @click="openUserForm()" class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center">
                <PlusIcon class="w-5 h-5 mr-2" />
                Nouvel Utilisateur
            </button>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8">
                <button 
                    @click="activeTab = 'access'"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                    :class="activeTab === 'access' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                >
                    <UserIcon class="w-5 h-5 inline mr-2" />
                    Accès Utilisateurs
                </button>
                <button 
                    @click="activeTab = 'agents'"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                    :class="activeTab === 'agents' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                >
                    <UserPlusIcon class="w-5 h-5 inline mr-2" />
                    Agents Placeurs
                </button>
            </nav>
        </div>

        <!-- ACCESS TAB -->
        <div v-show="activeTab === 'access'" class="space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Total Utilisateurs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ users.length }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Comptes Actifs</p>
                    <p class="text-2xl font-bold text-green-600">{{ activeUsersCount }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Gestionnaires</p>
                    <p class="text-2xl font-bold text-blue-600">{{ managersCount }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Vendeurs</p>
                    <p class="text-2xl font-bold text-purple-600">{{ vendorsCount }}</p>
                </div>
            </div>

            <!-- Search & Filter -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
                <input 
                    v-model="searchUser"
                    type="text"
                    placeholder="Rechercher par nom..."
                    class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                <select v-model="filterRole" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous les rôles</option>
                    <option value="gestionnaire">Gestionnaire</option>
                    <option value="vendeur">Vendeur</option>
                    <option value="caissier">Caissier</option>
                    <option value="admin">Administrateur</option>
                </select>
                <select v-model="filterAccountStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous les états</option>
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                    <option value="suspendu">Suspendu</option>
                </select>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">État du Compte</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dernière Connexion</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                        <span class="text-gray-900 font-medium">{{ getInitials(user.name) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ user.name }}</p>
                                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="getRoleBadgeClass(user.role)" class="px-2 py-1 text-xs font-medium rounded-full">
                                    {{ getRoleLabel(user.role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="getStatusBadgeClass(user.account_status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                    {{ getStatusLabel(user.account_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ user.last_login ? formatDate(user.last_login) : 'Jamais' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button 
                                        v-if="user.account_status !== 'actif'"
                                        @click="toggleUserStatus(user, 'actif')" 
                                        class="p-2 text-green-400 hover:text-green-600 rounded-lg hover:bg-green-50" 
                                        title="Activer"
                                    >
                                        <CheckCircleIcon class="w-5 h-5" />
                                    </button>
                                    <button 
                                        v-if="user.account_status === 'actif'"
                                        @click="toggleUserStatus(user, 'suspendu')" 
                                        class="p-2 text-orange-400 hover:text-orange-600 rounded-lg hover:bg-orange-50" 
                                        title="Suspendre"
                                    >
                                        <NoSymbolIcon class="w-5 h-5" />
                                    </button>
                                    <button @click="openUserForm(user)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                        <PencilIcon class="w-5 h-5" />
                                    </button>
                                    <button @click="confirmDeleteUser(user)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredUsers.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <UserIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                                Aucun utilisateur trouvé
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- AGENTS PLACEURS TAB -->
        <div v-show="activeTab === 'agents'" class="space-y-6">
            <!-- Add Agent Button -->
            <div class="flex justify-end">
                <button @click="openAgentForm()" class="px-4 py-2 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 flex items-center">
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Nouvel Agent Placeur
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Total Agents</p>
                    <p class="text-2xl font-bold text-gray-900">{{ agents.length }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Commission Moyenne (%)</p>
                    <p class="text-2xl font-bold text-blue-600">{{ averageCommissionPercent.toFixed(1) }}%</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Commission Moyenne (Fixe)</p>
                    <p class="text-2xl font-bold text-green-600">{{ formatCurrency(averageCommissionFixed) }}</p>
                </div>
            </div>

            <!-- Agents Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commission (%)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commission (Montant par acte)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Commissions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="agent in agents" :key="agent.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <UserPlusIcon class="w-5 h-5 text-blue-600" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ agent.name }}</p>
                                        <p class="text-sm text-gray-500">{{ agent.phone || '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg font-medium">
                                    {{ agent.commission_percent }}%
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-green-50 text-green-700 rounded-lg font-medium">
                                    {{ formatCurrency(agent.commission_fixed) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ formatCurrency(agent.total_commissions || 0) }}</p>
                                <p class="text-sm text-gray-500">{{ agent.transactions_count || 0 }} transactions</p>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="agent.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" class="px-2 py-1 text-xs font-medium rounded-full">
                                    {{ agent.active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button @click="viewAgentHistory(agent)" class="p-2 text-blue-400 hover:text-blue-600 rounded-lg hover:bg-blue-50" title="Historique">
                                        <ClockIcon class="w-5 h-5" />
                                    </button>
                                    <button @click="openAgentForm(agent)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                        <PencilIcon class="w-5 h-5" />
                                    </button>
                                    <button @click="confirmDeleteAgent(agent)" class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50" title="Supprimer">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="agents.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <UserPlusIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                                Aucun agent placeur trouvé
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Form Modal -->
        <div v-if="showUserForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showUserForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingUser ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}
                </h3>
                
                <form @submit.prevent="saveUser" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input v-model="userForm.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom complet">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="userForm.email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="email@exemple.com">
                    </div>

                    <div v-if="!editingUser">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe *</label>
                        <input v-model="userForm.password" type="password" :required="!editingUser" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="••••••••">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rôle *</label>
                        <select v-model="userForm.role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="gestionnaire">Gestionnaire</option>
                            <option value="vendeur">Vendeur</option>
                            <option value="caissier">Caissier</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">État du Compte</label>
                        <select v-model="userForm.account_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                            <option value="suspendu">Suspendu</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="button" @click="showUserForm = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                        <button type="submit" :disabled="saving" class="flex-1 px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-50">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Agent Form Modal -->
        <div v-if="showAgentForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showAgentForm = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ editingAgent ? 'Modifier l\'agent placeur' : 'Nouvel agent placeur' }}
                </h3>
                
                <form @submit.prevent="saveAgent" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input v-model="agentForm.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Nom de l'agent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input v-model="agentForm.phone" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="+212 600 000 000">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Commission (%) *</label>
                        <div class="relative">
                            <input v-model.number="agentForm.commission_percent" type="number" step="0.1" min="0" max="100" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 pr-10" placeholder="5">
                            <span class="absolute right-3 top-2.5 text-gray-500">%</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Pourcentage de commission sur chaque vente</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Commission (Montant par acte) *</label>
                        <div class="relative">
                            <input v-model.number="agentForm.commission_fixed" type="number" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 pr-16" placeholder="50">
                            <span class="absolute right-3 top-2.5 text-gray-500">DH</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Montant fixe par transaction</p>
                    </div>

                    <div class="flex items-center">
                        <input v-model="agentForm.active" type="checkbox" id="agent-active" class="w-4 h-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500">
                        <label for="agent-active" class="ml-2 text-sm text-gray-700">Agent actif</label>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="button" @click="showAgentForm = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                        <button type="submit" :disabled="saving" class="flex-1 px-4 py-2 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 disabled:opacity-50">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Agent History Modal -->
        <div v-if="showAgentHistory" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showAgentHistory = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-3xl w-full mx-4 shadow-xl z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Historique Commissions - {{ selectedAgent?.name }}
                    </h3>
                    <button @click="showAgentHistory = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Summary -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Total Transactions</p>
                        <p class="text-xl font-bold text-gray-900">{{ selectedAgent?.transactions_count || 0 }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Total Commissions</p>
                        <p class="text-xl font-bold text-green-600">{{ formatCurrency(selectedAgent?.total_commissions || 0) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-500">Taux Actuel</p>
                        <p class="text-xl font-bold text-blue-600">{{ selectedAgent?.commission_percent }}% + {{ formatCurrency(selectedAgent?.commission_fixed) }}</p>
                    </div>
                </div>

                <!-- History Table -->
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Transaction</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Montant Vente</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Commission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in agentHistory" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(item.date) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ item.transaction_id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ formatCurrency(item.sale_amount) }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-green-600 text-right">{{ formatCurrency(item.commission) }}</td>
                        </tr>
                        <tr v-if="agentHistory.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                Aucune transaction enregistrée
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Delete User Confirmation -->
        <div v-if="showDeleteUserModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteUserModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer l'utilisateur</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer "{{ userToDelete?.name }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteUserModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteUser" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>

        <!-- Delete Agent Confirmation -->
        <div v-if="showDeleteAgentModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteAgentModal = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Supprimer l'agent placeur</h3>
                <p class="text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer "{{ agentToDelete?.name }}" ?</p>
                <div class="flex space-x-3">
                    <button @click="showDeleteAgentModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="deleteAgent" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useSettingsStore } from '../../stores/settings'
import { 
    PlusIcon, 
    PencilIcon, 
    TrashIcon, 
    UserIcon,
    UserPlusIcon,
    ClockIcon,
    XMarkIcon,
    CheckCircleIcon,
    NoSymbolIcon
} from '@heroicons/vue/24/outline'

const settingsStore = useSettingsStore()
const formatCurrency = (amount) => settingsStore.formatCurrency(amount)

// Tab state
const activeTab = ref('access')

// Users state
const users = ref([])
const searchUser = ref('')
const filterRole = ref('')
const filterAccountStatus = ref('')
const showUserForm = ref(false)
const showDeleteUserModal = ref(false)
const editingUser = ref(null)
const userToDelete = ref(null)

const userForm = reactive({
    name: '',
    email: '',
    password: '',
    role: 'vendeur',
    account_status: 'actif'
})

// Agents state
const agents = ref([])
const showAgentForm = ref(false)
const showAgentHistory = ref(false)
const showDeleteAgentModal = ref(false)
const editingAgent = ref(null)
const agentToDelete = ref(null)
const selectedAgent = ref(null)
const agentHistory = ref([])

const agentForm = reactive({
    name: '',
    phone: '',
    commission_percent: 5,
    commission_fixed: 50,
    active: true
})

const saving = ref(false)

// Computed
const filteredUsers = computed(() => {
    let result = users.value
    
    if (searchUser.value) {
        const query = searchUser.value.toLowerCase()
        result = result.filter(u => 
            u.name?.toLowerCase().includes(query) ||
            u.email?.toLowerCase().includes(query)
        )
    }
    
    if (filterRole.value) {
        result = result.filter(u => u.role === filterRole.value)
    }
    
    if (filterAccountStatus.value) {
        result = result.filter(u => u.account_status === filterAccountStatus.value)
    }
    
    return result
})

const activeUsersCount = computed(() => users.value.filter(u => u.account_status === 'actif').length)
const managersCount = computed(() => users.value.filter(u => u.role === 'gestionnaire').length)
const vendorsCount = computed(() => users.value.filter(u => u.role === 'vendeur').length)

const averageCommissionPercent = computed(() => {
    if (agents.value.length === 0) return 0
    return agents.value.reduce((sum, a) => sum + (a.commission_percent || 0), 0) / agents.value.length
})

const averageCommissionFixed = computed(() => {
    if (agents.value.length === 0) return 0
    return agents.value.reduce((sum, a) => sum + (a.commission_fixed || 0), 0) / agents.value.length
})

// Helper functions
function getInitials(name) {
    if (!name) return 'U'
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

function getRoleLabel(role) {
    const labels = {
        admin: 'Administrateur',
        gestionnaire: 'Gestionnaire',
        vendeur: 'Vendeur',
        caissier: 'Caissier'
    }
    return labels[role] || role
}

function getRoleBadgeClass(role) {
    const classes = {
        admin: 'bg-purple-100 text-purple-800',
        gestionnaire: 'bg-blue-100 text-blue-800',
        vendeur: 'bg-green-100 text-green-800',
        caissier: 'bg-orange-100 text-orange-800'
    }
    return classes[role] || 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status) {
    const labels = {
        actif: 'Actif',
        inactif: 'Inactif',
        suspendu: 'Suspendu'
    }
    return labels[status] || status
}

function getStatusBadgeClass(status) {
    const classes = {
        actif: 'bg-green-100 text-green-800',
        inactif: 'bg-gray-100 text-gray-600',
        suspendu: 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-600'
}

// User functions
function openUserForm(user = null) {
    editingUser.value = user
    if (user) {
        userForm.name = user.name || ''
        userForm.email = user.email || ''
        userForm.password = ''
        userForm.role = user.role || 'vendeur'
        userForm.account_status = user.account_status || 'actif'
    } else {
        userForm.name = ''
        userForm.email = ''
        userForm.password = ''
        userForm.role = 'vendeur'
        userForm.account_status = 'actif'
    }
    showUserForm.value = true
}

function toggleUserStatus(user, newStatus) {
    const index = users.value.findIndex(u => u.id === user.id)
    if (index > -1) {
        users.value[index].account_status = newStatus
    }
}

function confirmDeleteUser(user) {
    userToDelete.value = user
    showDeleteUserModal.value = true
}

function saveUser() {
    saving.value = true
    
    if (editingUser.value) {
        const index = users.value.findIndex(u => u.id === editingUser.value.id)
        if (index > -1) {
            users.value[index] = {
                ...users.value[index],
                name: userForm.name,
                email: userForm.email,
                role: userForm.role,
                account_status: userForm.account_status
            }
        }
    } else {
        users.value.unshift({
            id: Date.now(),
            name: userForm.name,
            email: userForm.email,
            role: userForm.role,
            account_status: userForm.account_status,
            last_login: null
        })
    }
    
    showUserForm.value = false
    saving.value = false
}

function deleteUser() {
    users.value = users.value.filter(u => u.id !== userToDelete.value.id)
    showDeleteUserModal.value = false
}

// Agent functions
function openAgentForm(agent = null) {
    editingAgent.value = agent
    if (agent) {
        agentForm.name = agent.name || ''
        agentForm.phone = agent.phone || ''
        agentForm.commission_percent = agent.commission_percent || 5
        agentForm.commission_fixed = agent.commission_fixed || 50
        agentForm.active = agent.active !== false
    } else {
        agentForm.name = ''
        agentForm.phone = ''
        agentForm.commission_percent = 5
        agentForm.commission_fixed = 50
        agentForm.active = true
    }
    showAgentForm.value = true
}

function viewAgentHistory(agent) {
    selectedAgent.value = agent
    // Demo history
    agentHistory.value = [
        { id: 1, date: '2026-02-08T14:30:00', transaction_id: 'TRX-20260208-001', sale_amount: 2500, commission: 175 },
        { id: 2, date: '2026-02-07T10:15:00', transaction_id: 'TRX-20260207-003', sale_amount: 1800, commission: 140 },
        { id: 3, date: '2026-02-06T16:45:00', transaction_id: 'TRX-20260206-007', sale_amount: 3200, commission: 210 },
        { id: 4, date: '2026-02-05T11:20:00', transaction_id: 'TRX-20260205-002', sale_amount: 950, commission: 97.5 },
    ]
    showAgentHistory.value = true
}

function confirmDeleteAgent(agent) {
    agentToDelete.value = agent
    showDeleteAgentModal.value = true
}

function saveAgent() {
    saving.value = true
    
    if (editingAgent.value) {
        const index = agents.value.findIndex(a => a.id === editingAgent.value.id)
        if (index > -1) {
            agents.value[index] = {
                ...agents.value[index],
                name: agentForm.name,
                phone: agentForm.phone,
                commission_percent: agentForm.commission_percent,
                commission_fixed: agentForm.commission_fixed,
                active: agentForm.active
            }
        }
    } else {
        agents.value.unshift({
            id: Date.now(),
            name: agentForm.name,
            phone: agentForm.phone,
            commission_percent: agentForm.commission_percent,
            commission_fixed: agentForm.commission_fixed,
            active: agentForm.active,
            total_commissions: 0,
            transactions_count: 0
        })
    }
    
    showAgentForm.value = false
    saving.value = false
}

function deleteAgent() {
    agents.value = agents.value.filter(a => a.id !== agentToDelete.value.id)
    showDeleteAgentModal.value = false
}

// Load data
onMounted(() => {
    // Demo users
    users.value = [
        { id: 1, name: 'Ahmed Benali', email: 'ahmed@pos.com', role: 'admin', account_status: 'actif', last_login: '2026-02-08T09:30:00' },
        { id: 2, name: 'Sara Mansouri', email: 'sara@pos.com', role: 'gestionnaire', account_status: 'actif', last_login: '2026-02-08T08:15:00' },
        { id: 3, name: 'Mohamed Tazi', email: 'mohamed@pos.com', role: 'vendeur', account_status: 'actif', last_login: '2026-02-07T17:45:00' },
        { id: 4, name: 'Fatima El Amrani', email: 'fatima@pos.com', role: 'caissier', account_status: 'actif', last_login: '2026-02-08T10:00:00' },
        { id: 5, name: 'Youssef Chraibi', email: 'youssef@pos.com', role: 'vendeur', account_status: 'suspendu', last_login: '2026-01-15T14:30:00' },
    ]
    
    // Demo agents
    agents.value = [
        { id: 1, name: 'Karim Alaoui', phone: '0612345678', commission_percent: 5, commission_fixed: 50, active: true, total_commissions: 12500, transactions_count: 85 },
        { id: 2, name: 'Nadia Benjelloun', phone: '0698765432', commission_percent: 7.5, commission_fixed: 75, active: true, total_commissions: 18200, transactions_count: 62 },
        { id: 3, name: 'Hassan Idrissi', phone: '0655443322', commission_percent: 4, commission_fixed: 40, active: false, total_commissions: 5800, transactions_count: 28 },
    ]
})
</script>
