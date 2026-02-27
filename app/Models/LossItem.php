<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LossItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'loss_id',
        'article_id',
        'loss_type',
        'quantity',
        'unit_cost',
        'total_cost',
        'stock_before',
        'stock_after',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function loss(): BelongsTo
    {
        return $this->belongsTo(Loss::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
