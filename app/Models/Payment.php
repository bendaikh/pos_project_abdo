<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'payment_type',
        'amount',
        'received_amount',
        'change_amount',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('payment_type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
