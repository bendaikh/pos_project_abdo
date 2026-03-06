<?php

namespace App\Models;

use App\Services\TaskAutomationService;
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

        $movement = self::create([
            'article_id' => $article->id,
            'user_id' => $userId ?? auth()->id(),
            'sale_id' => $saleId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $article->fresh()->stock_quantity,
            'reason' => $reason,
        ]);

        // Try to sync low stock tasks, but don't fail if tables don't exist
        try {
            app(TaskAutomationService::class)->syncLowStockTasks();
        } catch (\Exception $e) {
            \Log::warning('Could not sync low stock tasks: ' . $e->getMessage());
        }

        return $movement;
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
