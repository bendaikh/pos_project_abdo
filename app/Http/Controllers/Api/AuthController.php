<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Ce compte a été désactivé.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        $user->load(['defaultStore:id,name,code', 'ownedStores:id,name,code,owner_id', 'stores:id,name,code']);

        $currentStore = \App\Support\StoreContext::resolveForUser($user);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'current_store' => $currentStore,
            'needs_store_setup' => $user->role === 'owner' && ! $user->ownedStores()->exists(),
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'defaultStore:id,name,code',
            'ownedStores:id,name,code,owner_id',
            'stores:id,name,code',
        ]);

        $currentStore = $request->attributes->get('current_store')
            ?? \App\Support\StoreContext::resolveForUser($user);

        return response()->json([
            'user' => $user,
            'current_store' => $currentStore,
            'needs_store_setup' => $user->role === 'owner' && ! $user->ownedStores()->exists(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie']);
    }
}
