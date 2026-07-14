<?php

namespace App\Support;

use App\Models\Store;
use App\Models\User;

class StoreContext
{
    protected static ?int $storeId = null;

    public static function set(?int $storeId): void
    {
        static::$storeId = $storeId;
    }

    public static function id(): ?int
    {
        return static::$storeId;
    }

    public static function clear(): void
    {
        static::$storeId = null;
    }

    public static function resolveForUser(User $user, ?int $requestedStoreId = null): ?Store
    {
        if ($user->isSuperAdmin()) {
            if ($requestedStoreId) {
                return Store::find($requestedStoreId);
            }

            return $user->defaultStore
                ?? $user->ownedStores()->first()
                ?? Store::query()->first();
        }

        $stores = Store::query()
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('members', fn ($m) => $m->where('users.id', $user->id)->where('store_user.is_active', true));
            })
            ->where('is_active', true)
            ->get();

        if ($requestedStoreId) {
            $store = $stores->firstWhere('id', $requestedStoreId);
            if ($store) {
                return $store;
            }
        }

        if ($user->default_store_id) {
            $default = $stores->firstWhere('id', $user->default_store_id);
            if ($default) {
                return $default;
            }
        }

        return $stores->first();
    }
}
