<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'is_active',
        'default_store_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'role_label',
        'can_create_store',
        'has_store',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function ownedStores(): HasMany
    {
        return $this->hasMany(Store::class, 'owner_id');
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_user')
            ->withPivot(['role_in_store', 'is_active'])
            ->withTimestamps();
    }

    public function defaultStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'default_store_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner' || $this->ownedStores()->exists();
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['superadmin', 'owner', 'admin'], true);
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['superadmin', 'owner', 'admin', 'manager'], true);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'superadmin' => 'Super Admin',
            'owner' => 'Propriétaire PDV',
            'admin' => 'Administrateur',
            'manager' => 'Manager',
            'cashier' => 'Caissier',
            default => (string) ($this->role ?: 'Utilisateur'),
        };
    }

    public function getCanCreateStoreAttribute(): bool
    {
        return $this->isSuperAdmin() || $this->role === 'owner';
    }

    public function getHasStoreAttribute(): bool
    {
        if ($this->isSuperAdmin()) {
            return Store::query()->exists();
        }

        return $this->ownedStores()->exists() || $this->stores()->where('store_user.is_active', true)->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
