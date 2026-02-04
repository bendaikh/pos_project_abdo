<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rate',
        'fixed_amount',
        'description',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function calculateCommission(float $saleTotal): float
    {
        $percentageCommission = $saleTotal * ($this->rate / 100);
        return $percentageCommission + $this->fixed_amount;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
