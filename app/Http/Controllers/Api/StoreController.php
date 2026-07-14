<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Store::with('owner:id,name,email')
            ->orderByDesc('opening_date')
            ->orderByDesc('id');

        if (! $request->boolean('include_inactive')) {
            $query->where('is_active', true);
        }

        if (! $user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('members', fn ($m) => $m->where('users.id', $user->id));
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('activity', 'like', "%{$search}%");
            });
        }

        return response()->json($query->get());
    }

    public function nextCode(): JsonResponse
    {
        return response()->json([
            'code' => $this->nextPdvCode(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->can_create_store && ! $user->isSuperAdmin()) {
            return response()->json(['message' => 'Vous n\'avez pas le droit de créer un point de vente.'], 403);
        }

        if ($user->role === 'owner' && $user->ownedStores()->exists() && ! $user->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'name' => 'Vous avez déjà un point de vente. Contactez le superadmin pour en ajouter.',
            ]);
        }

        $validated = $this->validatedPayload($request);

        // Empêche les doublons (double-clic / requêtes répétées)
        $duplicate = Store::query()
            ->where('is_active', true)
            ->where('phone', $validated['phone'])
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($validated['name'])])
            ->where('created_at', '>=', now()->subMinute())
            ->first();

        if ($duplicate) {
            return response()->json($duplicate->load('owner:id,name,email'), 200);
        }

        $ownerId = $user->isSuperAdmin() && ! empty($validated['owner_id'])
            ? (int) $validated['owner_id']
            : $user->id;

        $owner = User::findOrFail($ownerId);

        $store = DB::transaction(function () use ($validated, $owner, $user) {
            // Référence toujours attribuée côté serveur (enchaînement pdv-0001, pdv-0002...)
            $code = $this->nextPdvCode();

            $store = Store::create([
                'name' => $validated['name'],
                'activity' => $validated['activity'] ?? null,
                'code' => $code,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'],
                'country' => $validated['country'] ?? null,
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'owner_id' => $owner->id,
                'owner_name' => $validated['owner_name'] ?? $owner->name,
                'payment_amount' => $validated['payment_amount'],
                'payment_method' => $validated['payment_method'],
                'echeance' => $validated['echeance'],
                'due_date' => $validated['due_date'] ?? null,
                'opening_date' => $validated['opening_date'] ?? now()->toDateString(),
                'is_active' => true,
            ]);

            $store->members()->syncWithoutDetaching([
                $owner->id => [
                    'role_in_store' => 'owner',
                    'is_active' => true,
                ],
            ]);

            if (! $owner->default_store_id) {
                $owner->update(['default_store_id' => $store->id]);
            }

            if ($user->isSuperAdmin() && $owner->id !== $user->id && in_array($owner->role, ['cashier', 'manager', 'admin'], true)) {
                $owner->update(['role' => 'owner']);
            }

            $store->seedDefaultLists();

            return $store;
        });

        return response()->json($store->load('owner:id,name,email'), 201);
    }

    public function show(Request $request, Store $store): JsonResponse
    {
        $this->authorizeStoreAccess($request->user(), $store);

        return response()->json($store->load(['owner:id,name,email', 'members:id,name,email,role']));
    }

    public function update(Request $request, Store $store): JsonResponse
    {
        $user = $request->user();
        $this->authorizeStoreAccess($user, $store, true);

        $validated = $this->validatedPayload($request, $store->id, false);

        if (! empty($validated['owner_id']) && $user->isSuperAdmin()) {
            $store->owner_id = (int) $validated['owner_id'];
        }

        $store->fill(collect($validated)->except(['owner_id'])->all());
        $store->save();

        return response()->json($store->fresh()->load('owner:id,name,email'));
    }

    public function destroy(Request $request, Store $store): JsonResponse
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && $store->owner_id !== $user->id) {
            return response()->json(['message' => 'Seul le propriétaire ou le superadmin peut supprimer ce PDV.'], 403);
        }

        $store->update(['is_active' => false]);

        return response()->json(['message' => 'Point de vente désactivé.']);
    }

    public function current(Request $request): JsonResponse
    {
        $store = $request->attributes->get('current_store');

        if (! $store) {
            $store = StoreContext::resolveForUser($request->user());
        }

        if (! $store) {
            return response()->json([
                'store' => null,
                'message' => 'Aucun point de vente associé.',
            ]);
        }

        return response()->json([
            'store' => $store->load('owner:id,name,email'),
        ]);
    }

    public function select(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
        ]);

        $store = Store::findOrFail($validated['store_id']);
        $this->authorizeStoreAccess($request->user(), $store);

        $request->user()->update(['default_store_id' => $store->id]);

        return response()->json([
            'message' => 'Point de vente sélectionné.',
            'store' => $store,
        ]);
    }

    private function validatedPayload(Request $request, ?int $storeId = null, bool $creating = true): array
    {
        $codeRule = $storeId
            ? ['nullable', 'string', 'max:20', 'regex:/^pdv-\d{4}$/i', 'unique:stores,code,'.$storeId]
            : ['nullable', 'string', 'max:20', 'regex:/^pdv-\d{4}$/i', 'unique:stores,code'];

        return $request->validate([
            'name' => ($creating ? 'required' : 'sometimes|required').'|string|max:255',
            'activity' => 'nullable|string|max:255',
            'code' => $codeRule,
            'owner_name' => 'nullable|string|max:255',
            'owner_id' => 'nullable|exists:users,id',
            'phone' => ($creating ? 'required' : 'sometimes|required').'|digits:10',
            'city' => ($creating ? 'required' : 'sometimes|required').'|string|max:100',
            'address' => 'nullable|string|max:500',
            'country' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'payment_amount' => [
                $creating ? 'required' : 'sometimes|required',
                'numeric',
                'min:0',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! preg_match('/^\d+\.00$/', number_format((float) $value, 2, '.', ''))) {
                        $fail('Le montant paiement doit se terminer par .00.');
                    }
                },
            ],
            'payment_method' => ($creating ? 'required' : 'sometimes|required').'|in:Esp,Chq,Eff,Vir,Vers',
            'echeance' => ($creating ? 'required' : 'sometimes|required').'|in:Mensuel,Annuel',
            'due_date' => 'nullable|date',
            'opening_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);
    }

    private function nextPdvCode(): string
    {
        $max = 0;

        foreach (Store::query()->pluck('code') as $code) {
            // Série officielle pdv-0001, pdv-0002, ...
            if (preg_match('/^pdv-(\d{4})$/i', (string) $code, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        do {
            $max++;
            $candidate = 'pdv-'.str_pad((string) $max, 4, '0', STR_PAD_LEFT);
        } while (
            Store::query()
                ->whereRaw('LOWER(code) = ?', [strtolower($candidate)])
                ->exists()
        );

        return $candidate;
    }

    private function normalizePdvCode(string $code): string
    {
        $code = strtolower(trim($code));

        if (! preg_match('/^pdv-\d{4}$/', $code)) {
            throw ValidationException::withMessages([
                'code' => 'La référence PDV doit être au format pdv-0001.',
            ]);
        }

        return $code;
    }

    private function authorizeStoreAccess(User $user, Store $store, bool $ownerOnly = false): void
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        $isOwner = $store->owner_id === $user->id;
        $isMember = $store->members()->where('users.id', $user->id)->where('store_user.is_active', true)->exists();

        if ($ownerOnly && ! $isOwner) {
            abort(403, 'Seul le propriétaire peut modifier ce point de vente.');
        }

        if (! $isOwner && ! $isMember) {
            abort(403, 'Accès au point de vente refusé.');
        }
    }
}
