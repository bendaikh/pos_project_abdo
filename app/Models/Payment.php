<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'payment_type',
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
        'is_deferred',
        'collection_status',
        'collected_at',
        'collected_by',
        'collection_notes',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'collected_at' => 'datetime',
        'is_deferred' => 'boolean',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(PaymentCollection::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(PaymentReminder::class);
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
        return in_array($this->payment_type, ['check', 'cheque', 'virement', 'credit']);
    }

    public function markAsCollected(?string $collectedBy = null, ?string $notes = null): void
    {
        $this->update([
            'collection_status' => 'collected',
            'collected_at' => now(),
            'collected_by' => $collectedBy ?? auth()->user()?->name,
            'collection_notes' => $notes,
        ]);

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
        $this->update([
            'collection_status' => 'pending',
        ]);

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
}
