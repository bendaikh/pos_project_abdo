<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (! in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'Accès refusé. Permissions insuffisantes.',
            ], 403);
        }

        return $next($request);
    }
}
