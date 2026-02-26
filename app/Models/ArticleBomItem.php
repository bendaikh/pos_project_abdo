<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleBomItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'component_id',
        'quantity',
        'unit',
        'unit_cost',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'component_id');
    }
}
