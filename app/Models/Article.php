<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ProductionEntryItem;
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
        'has_options',
        'has_variants',
        'is_on_sale',
        'color',
        'price_type',
        'is_composite',
        'has_tax',
    ];

    protected $casts = [
        'sell_price' => 'decimal:2',
        'buy_price' => 'decimal:2',
        'manage_stock' => 'boolean',
        'is_favorite' => 'boolean',
        'is_active' => 'boolean',
        'has_options' => 'boolean',
        'has_variants' => 'boolean',
        'is_on_sale' => 'boolean',
        'is_composite' => 'boolean',
        'has_tax' => 'boolean',
    ];

    protected $appends = [
        'cost_basis',
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

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class)->orderBy('sort_order');
    }

    public function productionItems(): HasMany
    {
        return $this->hasMany(ProductionEntryItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ArticlePhoto::class)->orderBy('sort_order');
    }

    public function bomItems(): HasMany
    {
        return $this->hasMany(ArticleBomItem::class)->orderBy('sort_order');
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

    public function getCostBasisAttribute(): float
    {
        return round($this->calculateCostBasis(), 2);
    }

    public function calculateCostBasis(): float
    {
        $buyPrice = $this->buy_price;
        if (!is_null($buyPrice) && (float) $buyPrice > 0) {
            return (float) $buyPrice;
        }

        $compositeCost = $this->calculateCompositeCost();
        if ($compositeCost > 0) {
            return $compositeCost;
        }

        $productionCost = $this->productionItems()
            ->whereHas('productionEntry', fn ($query) => $query->where('status', 'validated'))
            ->latest('created_at')
            ->value('unit_cost');

        if (!is_null($productionCost)) {
            return (float) $productionCost;
        }

        return (float) ($this->sell_price ?? 0);
    }

    protected function calculateCompositeCost(): float
    {
        if (!$this->is_composite) {
            return 0.0;
        }

        $this->loadMissing('bomItems.component');

        $total = 0.0;
        foreach ($this->bomItems as $bomItem) {
            $componentCost = $bomItem->unit_cost;

            if ($componentCost === null && $bomItem->component) {
                $componentCost = $bomItem->component->buy_price ?? $bomItem->component->sell_price ?? 0;
            }

            $total += (float) ($componentCost ?? 0) * (float) ($bomItem->quantity ?? 0);
        }

        return $total;
    }
}
