<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'produced_at',
        'status',
        'total_cost',
        'user_id',
        'store_id',
        'notes',
        'validated_at',
    ];

    protected $casts = [
        'produced_at' => 'date',
        'total_cost' => 'decimal:2',
        'validated_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ProductionEntryItem::class);
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(MaterialConsumption::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateReference(): string
    {
        $prefix = 'PRD-' . now()->format('Ymd');
        $latest = self::where('reference', 'like', $prefix . '-%')
            ->orderByDesc('reference')
            ->first();

        if (!$latest) {
            return $prefix . '-0001';
        }

        $lastNumber = (int) substr($latest->reference, -4);
        $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);

        return $prefix . '-' . $nextNumber;
    }
}
