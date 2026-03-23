<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'label',
        'value',
        'metadata',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(CustomList::class, 'list_id');
    }
}
