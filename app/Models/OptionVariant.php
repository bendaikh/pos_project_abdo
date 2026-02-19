<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'option_id',
        'name',
        'price_impact',
        'color',
        'image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_impact' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
