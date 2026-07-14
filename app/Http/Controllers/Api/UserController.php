<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $auth = $request->user();
        $query = User::query()->with('defaultStore:id,name')->orderBy('name');

        if ($auth->isSuperAdmin()) {
            // Superadmin sees everyone
        } elseif ($auth->isOwner() || $auth->role === 'owner') {
            $storeIds = Store::query()
                ->where('owner_id', $auth->id)
                ->pluck('id');

            $query->where(function ($q) use ($auth, $storeIds) {
                $q->where('id', $auth->id)
                    ->orWhereIn('default_store_id', $storeIds)
                    ->orWhereHas('stores', fn ($s) => $s->whereIn('stores.id', $storeIds));
            })->where('role', '!=', 'superadmin');
        } else {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate($request->get('per_page', 50)));
    }

    public function store(Request $request): JsonResponse
    {
        $auth = $request->user();

        $allowedRoles = $auth->isSuperAdmin()
            ? ['owner', 'admin', 'manager', 'cashier']
            : ['admin', 'manager', 'cashier'];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['required', Rule::in($allowedRoles)],
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'store_id' => 'nullable|exists:stores,id',
            'create_store' => 'boolean',
            'store_name' => 'nullable|string|max:255',
        ]);

        // Only superadmin can create PDV owners
        if ($validated['role'] === 'owner' && ! $auth->isSuperAdmin()) {
            return response()->json(['message' => 'Seul le superadmin peut créer un propriétaire de PDV.'], 403);
        }

        $user = DB::transaction(function () use ($validated, $auth) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
                'phone' => $validated['phone'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Superadmin creates an owner: optionally create their PDV immediately
            if ($auth->isSuperAdmin() && $validated['role'] === 'owner' && ($validated['create_store'] ?? false)) {
                $store = Store::create([
                    'name' => $validated['store_name'] ?: ('PDV '.$user->name),
                    'code' => 'PDV-'.$user->id.'-'.now()->format('His'),
                    'owner_id' => $user->id,
                    'is_active' => true,
                ]);

                $store->members()->attach($user->id, [
                    'role_in_store' => 'owner',
                    'is_active' => true,
                ]);

                $store->seedDefaultLists();
                $user->update(['default_store_id' => $store->id]);
            }

            // PDV owner / admin attaching staff to their store
            if (in_array($validated['role'], ['admin', 'manager', 'cashier'], true)) {
                $storeId = $validated['store_id']
                    ?? $auth->default_store_id
                    ?? Store::where('owner_id', $auth->id)->value('id');

                if ($storeId) {
                    $store = Store::find($storeId);
                    if ($store && ($auth->isSuperAdmin() || $store->owner_id === $auth->id)) {
                        $store->members()->syncWithoutDetaching([
                            $user->id => [
                                'role_in_store' => $validated['role'],
                                'is_active' => true,
                            ],
                        ]);
                        $user->update(['default_store_id' => $store->id]);
                    }
                }
            }

            return $user;
        });

        return response()->json($user->load('defaultStore:id,name'), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->load(['defaultStore:id,name', 'ownedStores:id,name,owner_id', 'stores:id,name']));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $auth = $request->user();

        if (! $auth->isSuperAdmin() && $user->role === 'superadmin') {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $allowedRoles = $auth->isSuperAdmin()
            ? ['owner', 'admin', 'manager', 'cashier', 'superadmin']
            : ['admin', 'manager', 'cashier'];

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', Password::defaults()],
            'role' => ['sometimes', Rule::in($allowedRoles)],
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'default_store_id' => 'nullable|exists:stores,id',
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }
        unset($validated['password']);

        $user->fill($validated);
        $user->save();

        return response()->json($user->fresh()->load('defaultStore:id,name'));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 422);
        }

        if ($user->isSuperAdmin()) {
            return response()->json(['message' => 'Impossible de supprimer un superadmin.'], 422);
        }

        if (! $request->user()->isSuperAdmin() && $user->role === 'owner') {
            return response()->json(['message' => 'Seul le superadmin peut supprimer un propriétaire.'], 403);
        }

        $user->update(['is_active' => false]);

        return response()->json(['message' => 'Utilisateur désactivé.']);
    }
}
