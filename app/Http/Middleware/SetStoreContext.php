<?php

namespace App\Http\Middleware;

use App\Support\StoreContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetStoreContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $requestedStoreId = $request->header('X-Store-Id')
            ?? $request->query('store_id')
            ?? $request->input('store_id');

        $store = StoreContext::resolveForUser(
            $user,
            $requestedStoreId ? (int) $requestedStoreId : null
        );

        if ($store) {
            StoreContext::set($store->id);
            $request->attributes->set('current_store', $store);
        } else {
            StoreContext::clear();
        }

        $response = $next($request);

        StoreContext::clear();

        return $response;
    }
}
