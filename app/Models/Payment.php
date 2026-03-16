<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'customer_id',
        'payment_type',
        'transfer_mode',
        'amount',
        'received_amount',
        'change_amount',
        'reference',
        'transaction_number',
        'piece_number',
        'issue_date',
        'bank_name',
        'due_date',
        'payment_status',
        'paid_at',
        'confirmed_at',
        'is_deferred',
        'collection_status',
        'collected_at',
        'collected_by',
        'collection_notes',
        'created_by',
        'validated_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'collected_at' => 'datetime',
        'is_deferred' => 'boolean',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(PaymentCollection::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(PaymentReminder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('payment_type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeDeferred($query)
    {
        return $query->where('is_deferred', true);
    }

    public function scopePending($query)
    {
        return $query->where('collection_status', 'pending');
    }

    public function scopeCollected($query)
    {
        return $query->where('collection_status', 'collected');
    }

    public function isDeferred(): bool
    {
        if (in_array($this->payment_type, ['check', 'cheque', 'credit'], true)) {
            return true;
        }

        return $this->payment_type === 'virement' && $this->transfer_mode !== 'instant';
    }

    public function markAsCollected(?string $collectedBy = null, ?string $notes = null): void
    {
        $this->update(self::persistable([
            'collection_status' => 'collected',
            'payment_status' => 'completed',
            'confirmed_at' => now(),
            'collected_at' => now(),
            'collected_by' => $collectedBy ?? auth()->user()?->name,
            'collection_notes' => $notes,
            'validated_by' => auth()->id(),
        ]));

        // Record the collection action
        PaymentCollection::create([
            'payment_id' => $this->id,
            'user_id' => auth()->id(),
            'action' => 'collected',
            'amount' => $this->amount,
            'notes' => $notes,
        ]);
    }

    public function scheduleCollection(string $scheduledDate, ?string $notes = null): void
    {
        $this->update(self::persistable([
            'collection_status' => 'pending',
            'payment_status' => 'pending',
            'confirmed_at' => null,
            'validated_by' => null,
        ]));

        PaymentCollection::create([
            'payment_id' => $this->id,
            'user_id' => auth()->id(),
            'action' => 'scheduled',
            'amount' => $this->amount,
            'scheduled_date' => $scheduledDate,
            'notes' => $notes,
        ]);
    }

    public function rescheduleCollection(string $newDate, ?string $notes = null): void
    {
        PaymentCollection::create([
            'payment_id' => $this->id,
            'user_id' => auth()->id(),
            'action' => 'rescheduled',
            'amount' => $this->amount,
            'scheduled_date' => $newDate,
            'notes' => $notes ?? 'Rescheduled collection',
        ]);
    }

    public static function supportsColumn(string $column): bool
    {
        static $cache = [];
        $table = (new static())->getTable();
        $key = $table . ':' . $column;

        if (!array_key_exists($key, $cache)) {
            $cache[$key] = Schema::hasColumn($table, $column);
        }

        return $cache[$key];
    }

    public static function persistable(array $attributes): array
    {
        return array_filter(
            $attributes,
            fn ($value, $key) => static::supportsColumn((string) $key),
            ARRAY_FILTER_USE_BOTH
        );
    }
}
