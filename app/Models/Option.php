<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'values',
        'extra_price',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        'values' => 'array',
        'extra_price' => 'decimal:2',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_options');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFixed($query)
    {
        return $query->where('type', 'fixed');
    }

    public function scopeMultiple($query)
    {
        return $query->where('type', 'multiple');
    }
}
