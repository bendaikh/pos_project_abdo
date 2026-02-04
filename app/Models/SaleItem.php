<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'article_id',
        'article_name',
        'quantity',
        'unit_price',
        'discount_amount',
        'selected_options',
        'options_price',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'selected_options' => 'array',
        'options_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->calculateTotal();
        });

        static::updating(function ($item) {
            $item->calculateTotal();
        });
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function calculateTotal(): void
    {
        $baseTotal = ($this->unit_price + $this->options_price) * $this->quantity;
        $this->total = $baseTotal - $this->discount_amount;
    }
}
