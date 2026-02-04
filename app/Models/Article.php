<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'category_id',
        'subcategory_id',
        'sell_price',
        'buy_price',
        'unit',
        'manage_stock',
        'stock_quantity',
        'stock_alert_threshold',
        'photo',
        'is_favorite',
        'is_active',
    ];

    protected $casts = [
        'sell_price' => 'decimal:2',
        'buy_price' => 'decimal:2',
        'manage_stock' => 'boolean',
        'is_favorite' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'article_options');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    public function scopeLowStock($query)
    {
        return $query->where('manage_stock', true)
            ->whereColumn('stock_quantity', '<=', 'stock_alert_threshold');
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('manage_stock', false)
                ->orWhere('stock_quantity', '>', 0);
        });
    }

    public function isLowStock(): bool
    {
        return $this->manage_stock && $this->stock_quantity <= $this->stock_alert_threshold;
    }

    public function decrementStock(int $quantity): void
    {
        if ($this->manage_stock) {
            $this->decrement('stock_quantity', $quantity);
        }
    }

    public function incrementStock(int $quantity): void
    {
        if ($this->manage_stock) {
            $this->increment('stock_quantity', $quantity);
        }
    }
}
