<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Variant extends Model
{
    protected $fillable = [
        'article_id',
        'name',
        'price_impact',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price_impact' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the article that owns this variant.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
