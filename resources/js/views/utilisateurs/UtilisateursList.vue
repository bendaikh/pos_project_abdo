<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Utilisateurs</h1>
                <p class="text-gray-500">
                    <span v-if="authStore.isSuperAdmin">Créez des propriétaires de PDV et gérez les accès globaux.</span>
                    <span v-else>Gérez l'équipe de votre point de vente (admin, manager, caissier).</span>
                </p>
            </div>
            <button
                v-if="authStore.canManageUsers"
                @click="openUserForm()"
                class="px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 flex items-center"
            >
                <PlusIcon class="w-5 h-5 mr-2" />
                {{ authStore.isSuperAdmin ? 'Nouveau propriétaire / utilisateur' : 'Nouvel utilisateur' }}
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Total</p>
                <p class="text-2xl font-bold text-gray-900">{{ users.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Actifs</p>
                <p class="text-2xl font-bold text-green-600">{{ users.filter(u => u.is_active !== false).length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Propriétaires PDV</p>
                <p class="text-2xl font-bold text-blue-600">{{ users.filter(u => u.role === 'owner').length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500">Équipe caisse</p>
                <p class="text-2xl font-bold text-purple-600">{{ users.filter(u => ['cashier','manager','admin'].includes(u.role)).length }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-wrap gap-4">
            <input v-model="search" type="text" placeholder="Rechercher nom ou email..." class="flex-1 min-w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            <select v-model="filterRole" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les rôles</option>
                <option v-if="authStore.isSuperAdmin" value="owner">Propriétaire PDV</option>
                <option value="admin">Administrateur</option>
                <option value="manager">Manager</option>
                <option value="cashier">Caissier</option>
            </select>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PDV</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="loading">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Chargement...</td>
                    </tr>
                    <tr v-else-if="filteredUsers.length === 0">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Aucun utilisateur</td>
                    </tr>
                    <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center text-sm font-semibold text-gray-900">
                                    {{ initials(user.name) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ user.name }}</p>
                                    <p class="text-sm text-gray-500">{{ user.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full" :class="roleBadge(user.role)">
                                {{ user.role_label || roleLabel(user.role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ user.default_store?.name || '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <span :class="user.is_active !== false ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ user.is_active !== false ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="openUserForm(user)" class="p-2 text-gray-400 hover:text-gray-700 rounded-lg hover:bg-gray-100" title="Modifier">
                                <PencilIcon class="w-5 h-5" />
                            </button>
                            <button
                                v-if="user.id !== authStore.user?.id"
                                @click="confirmDelete(user)"
                                class="p-2 text-red-400 hover:text-red-600 rounded-lg hover:bg-red-50"
                                title="Désactiver"
                            >
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Form modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500/70" @click="showForm = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6 z-10">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ editing ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}
                    </h3>
                    <button @click="showForm = false" class="p-2 text-gray-400 hover:text-gray-600"><XMarkIcon class="w-5 h-5" /></button>
                </div>

                <form class="space-y-4" @submit.prevent="saveUser">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input v-model="form.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input v-model="form.email" type="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Mot de passe {{ editing ? '(laisser vide pour ne pas changer)' : '*' }}
                        </label>
                        <input v-model="form.password" type="password" :required="!editing" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rôle *</label>
                        <select v-model="form.role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option v-for="role in availableRoles" :key="role.value" :value="role.value">{{ role.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input v-model="form.phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500" />
                    </div>

                    <div v-if="authStore.isSuperAdmin && form.role === 'owner' && !editing" class="rounded-lg border border-primary-100 bg-primary-50 p-4 space-y-3">
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-800">
                            <input v-model="form.create_store" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                            Créer immédiatement son point de vente
                        </label>
                        <input
                            v-if="form.create_store"
                            v-model="form.store_name"
                            type="text"
                            placeholder="Nom du PDV"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                        />
                        <p v-else class="text-xs text-gray-600">
                            Sinon, le propriétaire créera son PDV à la première connexion.
                        </p>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                        Compte actif
                    </label>

                    <p v-if="formError" class="text-sm text-red-600">{{ formError }}</p>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showForm = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Annuler</button>
                        <button type="submit" :disabled="saving" class="flex-1 px-4 py-2 bg-primary-500 text-gray-900 font-medium rounded-lg hover:bg-primary-600 disabled:opacity-60">
                            {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showDelete" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-500/70" @click="showDelete = false"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Désactiver l'utilisateur</h3>
                <p class="text-gray-500 mb-4">Désactiver le compte de « {{ userToDelete?.name }} » ?</p>
                <div class="flex gap-3">
                    <button @click="showDelete = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">Annuler</button>
                    <button @click="deleteUser" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Désactiver</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { PlusIcon, PencilIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { usersApi } from '../../api'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const users = ref([])
const loading = ref(false)
const saving = ref(false)
const search = ref('')
const filterRole = ref('')
const showForm = ref(false)
const showDelete = ref(false)
const editing = ref(null)
const userToDelete = ref(null)
const formError = ref('')

const form = reactive({
    name: '',
    email: '',
    password: '',
    role: 'cashier',
    phone: '',
    is_active: true,
    create_store: true,
    store_name: '',
})

const availableRoles = computed(() => {
    if (authStore.isSuperAdmin) {
        return [
            { value: 'owner', label: 'Propriétaire PDV' },
            { value: 'admin', label: 'Administrateur' },
            { value: 'manager', label: 'Manager' },
            { value: 'cashier', label: 'Caissier' },
        ]
    }
    return [
        { value: 'admin', label: 'Administrateur' },
        { value: 'manager', label: 'Manager' },
        { value: 'cashier', label: 'Caissier' },
    ]
})

const filteredUsers = computed(() => {
    let list = users.value
    if (search.value) {
        const q = search.value.toLowerCase()
        list = list.filter(u => u.name?.toLowerCase().includes(q) || u.email?.toLowerCase().includes(q))
    }
    if (filterRole.value) {
        list = list.filter(u => u.role === filterRole.value)
    }
    return list
})

function initials(name) {
    return (name || 'U').split(' ').map(p => p[0]).join('').toUpperCase().slice(0, 2)
}

function roleLabel(role) {
    return ({
        superadmin: 'Super Admin',
        owner: 'Propriétaire PDV',
        admin: 'Administrateur',
        manager: 'Manager',
        cashier: 'Caissier',
    })[role] || role
}

function roleBadge(role) {
    return ({
        superadmin: 'bg-slate-900 text-white',
        owner: 'bg-blue-100 text-blue-800',
        admin: 'bg-purple-100 text-purple-800',
        manager: 'bg-amber-100 text-amber-800',
        cashier: 'bg-green-100 text-green-800',
    })[role] || 'bg-gray-100 text-gray-800'
}

async function loadUsers() {
    loading.value = true
    try {
        const { data } = await usersApi.list({ per_page: 100 })
        users.value = data.data || data || []
    } catch (e) {
        console.error(e)
        users.value = []
    } finally {
        loading.value = false
    }
}

function openUserForm(user = null) {
    editing.value = user
    formError.value = ''
    form.name = user?.name || ''
    form.email = user?.email || ''
    form.password = ''
    form.role = user?.role || (authStore.isSuperAdmin ? 'owner' : 'cashier')
    form.phone = user?.phone || ''
    form.is_active = user?.is_active !== false
    form.create_store = true
    form.store_name = user ? '' : ''
    showForm.value = true
}

function confirmDelete(user) {
    userToDelete.value = user
    showDelete.value = true
}

async function saveUser() {
    saving.value = true
    formError.value = ''
    try {
        const payload = {
            name: form.name,
            email: form.email,
            role: form.role,
            phone: form.phone || null,
            is_active: form.is_active,
        }
        if (form.password) payload.password = form.password

        if (!editing.value) {
            if (authStore.isSuperAdmin && form.role === 'owner') {
                payload.create_store = !!form.create_store
                if (form.create_store && form.store_name) {
                    payload.store_name = form.store_name
                }
            }
            await usersApi.create(payload)
        } else {
            await usersApi.update(editing.value.id, payload)
        }

        showForm.value = false
        await loadUsers()
    } catch (e) {
        formError.value = e.response?.data?.message
            || Object.values(e.response?.data?.errors || {})?.[0]?.[0]
            || 'Erreur lors de l\'enregistrement'
    } finally {
        saving.value = false
    }
}

async function deleteUser() {
    try {
        await usersApi.delete(userToDelete.value.id)
        showDelete.value = false
        await loadUsers()
    } catch (e) {
        alert(e.response?.data?.message || 'Suppression impossible')
    }
}

onMounted(loadUsers)
</script>
