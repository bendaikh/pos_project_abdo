<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentTypeAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_type_id',
        'employee_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function incidentType(): BelongsTo
    {
        return $this->belongsTo(CustomListItem::class, 'incident_type_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public static function getResponsibleForType(int $incidentTypeId): ?Employee
    {
        $assignment = self::where('incident_type_id', $incidentTypeId)
            ->where('is_active', true)
            ->first();

        return $assignment?->employee;
    }
}
