<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Loss extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'loss_date',
        'responsible_employee_id',
        'responsible_name',
        'store_id',
        'notes',
        'created_by',
        'total_quantity',
        'total_cost',
        'validated_at',
    ];

    protected $casts = [
        'loss_date' => 'date',
        'total_cost' => 'decimal:2',
        'validated_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(LossItem::class);
    }

    public function responsibleEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'responsible_employee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateReference(): string
    {
        $prefix = 'PER-' . now()->format('Ymd');

        if (!Schema::hasTable((new self())->getTable())) {
            return $prefix . '-0001';
        }

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
