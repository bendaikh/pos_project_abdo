<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'incident_type_id',
        'title',
        'description',
        'priority_id',
        'responsible_id',
        'reported_by_id',
        'status',
        'resolved_at',
        'resolution_notes',
        'created_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public const STATUS_EN_ATTENTE = 'en_attente';
    public const STATUS_EN_COURS = 'en_cours';
    public const STATUS_RESOLU = 'resolu';
    public const STATUS_ABANDONNE = 'abandonne';

    public const STATUSES = [
        self::STATUS_EN_ATTENTE => 'En attente',
        self::STATUS_EN_COURS => 'En cours',
        self::STATUS_RESOLU => 'Résolu',
        self::STATUS_ABANDONNE => 'Abandonné',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = self::generateTicketNumber();
            }
        });
    }

    public static function generateTicketNumber(): string
    {
        $lastTicket = self::withTrashed()->orderBy('id', 'desc')->first();
        $lastNumber = $lastTicket ? intval(substr($lastTicket->ticket_number, -4)) : 1000;
        return (string) ($lastNumber + 1);
    }

    public function incidentType(): BelongsTo
    {
        return $this->belongsTo(CustomListItem::class, 'incident_type_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(CustomListItem::class, 'priority_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reported_by_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_EN_ATTENTE, self::STATUS_EN_COURS]);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLU);
    }

    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function markAsInProgress(): void
    {
        $this->update(['status' => self::STATUS_EN_COURS]);
    }

    public function markAsResolved(string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_RESOLU,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    public function markAsAbandoned(): void
    {
        $this->update(['status' => self::STATUS_ABANDONNE]);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_EN_ATTENTE => 'orange',
            self::STATUS_EN_COURS => 'blue',
            self::STATUS_RESOLU => 'green',
            self::STATUS_ABANDONNE => 'gray',
            default => 'gray',
        };
    }
}
