<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'user_id',
        'sale_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reason',
        'notes',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public static function record(
        Article $article,
        string $type,
        int $quantity,
        ?string $reason = null,
        ?int $userId = null,
        ?int $saleId = null
    ): self {
        $stockBefore = $article->stock_quantity;

        // Update article stock
        if ($type === 'in' || $type === 'return') {
            $article->incrementStock($quantity);
        } else {
            $article->decrementStock($quantity);
        }

        return self::create([
            'article_id' => $article->id,
            'user_id' => $userId ?? auth()->id(),
            'sale_id' => $saleId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $article->fresh()->stock_quantity,
            'reason' => $reason,
        ]);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
