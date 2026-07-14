<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'activity',
        'code',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'owner_id',
        'owner_name',
        'payment_amount',
        'payment_method',
        'echeance',
        'due_date',
        'opening_date',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'payment_amount' => 'decimal:2',
        'due_date' => 'date',
        'opening_date' => 'date',
    ];

    protected $appends = [
        'display_owner_name',
    ];

    public function getDisplayOwnerNameAttribute(): string
    {
        return $this->owner_name ?: ($this->owner?->name ?? '—');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_user')
            ->withPivot(['role_in_store', 'is_active'])
            ->withTimestamps();
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Seed per-store payment modes, charges and service modes.
     */
    public function seedDefaultLists(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('custom_lists')) {
            return;
        }

        $defaults = [
            'mode_de_paiement' => [
                ['label' => 'Espèces', 'value' => 'cash', 'metadata' => ['payment_type' => 'cash', 'is_default' => true]],
                ['label' => 'Carte bancaire', 'value' => 'card', 'metadata' => ['payment_type' => 'card']],
                ['label' => 'Virement', 'value' => 'virement', 'metadata' => ['payment_type' => 'virement']],
                ['label' => 'Mobile Money', 'value' => 'mobile', 'metadata' => ['payment_type' => 'mobile']],
            ],
            'categories_depenses' => [
                ['label' => 'Loyer', 'value' => 'loyer'],
                ['label' => 'Salaires', 'value' => 'salaires'],
                ['label' => 'Charges fixes', 'value' => 'charges_fixes'],
                ['label' => 'Achats', 'value' => 'achats'],
            ],
            'depenses' => [
                ['label' => 'Loyer mensuel', 'value' => 'loyer', 'metadata' => ['expense_type' => 'fixed', 'expense_is_recurring' => true, 'expense_frequency' => 'monthly']],
                ['label' => 'Électricité', 'value' => 'electricite', 'metadata' => ['expense_type' => 'variable']],
            ],
            'mode_de_service' => [
                ['label' => 'Sur place', 'value' => 'dine_in'],
                ['label' => 'Emporté', 'value' => 'pickup'],
                ['label' => 'Livraison', 'value' => 'delivery'],
            ],
        ];

        foreach ($defaults as $listName => $items) {
            $exists = DB::table('custom_lists')
                ->where('name', $listName)
                ->where('store_id', $this->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $listId = DB::table('custom_lists')->insertGetId([
                'store_id' => $this->id,
                'name' => $listName,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($items as $index => $item) {
                $row = [
                    'list_id' => $listId,
                    'label' => $item['label'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (\Illuminate\Support\Facades\Schema::hasColumn('custom_list_items', 'value')) {
                    $row['value'] = $item['value'] ?? null;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('custom_list_items', 'metadata')) {
                    $row['metadata'] = isset($item['metadata']) ? json_encode($item['metadata']) : null;
                }

                DB::table('custom_list_items')->insert($row);
            }
        }
    }
}
