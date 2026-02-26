<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialConsumption extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_entry_id',
        'produced_article_id',
        'article_id',
        'reason',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'stock_before',
        'stock_after',
        'consumed_at',
        'user_id',
        'store_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'consumed_at' => 'datetime',
    ];

    public function productionEntry(): BelongsTo
    {
        return $this->belongsTo(ProductionEntry::class);
    }

    public function producedArticle(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'produced_article_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
