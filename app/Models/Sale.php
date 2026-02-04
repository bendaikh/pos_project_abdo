<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'user_id',
        'customer_id',
        'employee_id',
        'subtotal',
        'discount_amount',
        'discount_percent',
        'tax_rate',
        'tax_amount',
        'total',
        'status',
        'payment_status',
        'delivery_mode',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->reference)) {
                $sale->reference = self::generateReference();
            }
        });
    }

    public static function generateReference(): string
    {
        $lastSale = self::latest('id')->first();
        $nextId = $lastSale ? $lastSale->id + 1 : 1;
        return 'TRX-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total');
        
        // Apply discount
        $discountAmount = $this->discount_amount;
        if ($this->discount_percent > 0) {
            $discountAmount += $this->subtotal * ($this->discount_percent / 100);
        }
        
        $afterDiscount = $this->subtotal - $discountAmount;
        
        // Apply tax
        $this->tax_amount = $afterDiscount * ($this->tax_rate / 100);
        $this->total = $afterDiscount + $this->tax_amount;
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->payments->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->total - $this->total_paid;
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
}
