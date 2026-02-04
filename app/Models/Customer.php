<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function getTotalPurchasesAttribute(): float
    {
        return $this->sales()->where('status', 'completed')->sum('total');
    }

    public function getSalesCountAttribute(): int
    {
        return $this->sales()->where('status', 'completed')->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
