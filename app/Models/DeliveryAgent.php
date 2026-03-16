<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'phone',
        'commission_type',
        'commission_value',
        'status',
        'platform_name',
        'active',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)->where('status', 'active');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'platform' && $this->platform_name) {
            return $this->platform_name;
        }

        return $this->name;
    }
}
