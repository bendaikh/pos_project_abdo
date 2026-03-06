<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'reminder_date',
        'status',
        'days_before',
        'notes',
    ];

    protected $casts = [
        'reminder_date' => 'date',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('reminder_date', today());
    }

    public function scopeDuesoon($query, int $days = 7)
    {
        return $query->whereBetween('reminder_date', [today(), today()->addDays($days)]);
    }
}
