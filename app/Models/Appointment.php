<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'time',
        'duration',
        'subject',
        'customer_id',
        'phone',
        'whatsapp',
        'responsible_id',
        'location',
        'location_type',
        'status',
        'notes',
        'reminder_enabled',
        'reminder_channel',
        'reminder_timing',
        'reminder_custom_value',
        'reminder_custom_unit',
        'reminder_message',
        'reminder_sent_at',
    ];

    protected $casts = [
        'date' => 'date',
        'reminder_enabled' => 'boolean',
        'reminder_sent_at' => 'datetime',
    ];

    /**
     * Get the customer associated with the appointment.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the responsible employee for the appointment.
     */
    public function responsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }

    /**
     * Get the user who created the appointment.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get upcoming appointments.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
                     ->whereIn('status', ['en_cours', 'confirme'])
                     ->orderBy('date')
                     ->orderBy('time');
    }

    /**
     * Scope to get appointments by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get appointments by date range.
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    /**
     * Get the full datetime of the appointment.
     */
    public function getFullDateTimeAttribute()
    {
        return $this->date->format('Y-m-d') . ' ' . $this->time;
    }

    /**
     * Check if appointment needs reminder.
     */
    public function needsReminder()
    {
        if (!$this->reminder_enabled || $this->reminder_sent_at || $this->status === 'annule') {
            return false;
        }

        $appointmentDateTime = \Carbon\Carbon::parse($this->full_date_time);
        $now = now();

        // Calculate reminder time based on settings
        $reminderTime = $this->getReminderDateTime();

        return $now >= $reminderTime && $now < $appointmentDateTime;
    }

    /**
     * Get the datetime when reminder should be sent.
     */
    public function getReminderDateTime()
    {
        $appointmentDateTime = \Carbon\Carbon::parse($this->full_date_time);

        switch ($this->reminder_timing) {
            case '24h':
                return $appointmentDateTime->copy()->subHours(24);
            case '2h':
                return $appointmentDateTime->copy()->subHours(2);
            case '30min':
                return $appointmentDateTime->copy()->subMinutes(30);
            case 'custom':
                $unit = $this->reminder_custom_unit ?? 'hours';
                $value = $this->reminder_custom_value ?? 1;
                return $appointmentDateTime->copy()->sub($value, $unit);
            default:
                return $appointmentDateTime->copy()->subHours(2);
        }
    }
}
