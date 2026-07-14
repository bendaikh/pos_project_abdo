<?php

namespace App\Models\Concerns;

use App\Support\StoreContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToStore
{
    public static function bootBelongsToStore(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->store_id) && StoreContext::id()) {
                $model->store_id = StoreContext::id();
            }
        });

        static::addGlobalScope('store', function (Builder $builder) {
            $storeId = StoreContext::id();
            if ($storeId) {
                $builder->where($builder->getModel()->getTable().'.store_id', $storeId);
            }
        });
    }

    public function scopeForStore(Builder $query, ?int $storeId): Builder
    {
        if ($storeId) {
            return $query->where($this->getTable().'.store_id', $storeId);
        }

        return $query;
    }
}
