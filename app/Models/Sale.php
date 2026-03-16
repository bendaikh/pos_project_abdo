<?php

namespace App\Models;

use App\Services\SalePaymentWorkflowService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'order_number',
        'user_id',
        'customer_id',
        'employee_id',
        'delivery_agent_id',
        'delivery_agent_name_snapshot',
        'delivery_platform_name_snapshot',
        'subtotal',
        'discount_amount',
        'discount_percent',
        'tax_rate',
        'tax_amount',
        'total',
        'delivery_commission_type',
        'delivery_commission_value_snapshot',
        'delivery_commission_amount',
        'delivery_commission_calculated_at',
        'status',
        'order_status',
        'payment_status',
        'payment_status_code',
        'delivery_mode',
        'origin',
        'customer_activity',
        'pickup_date',
        'delivery_address',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'delivery_commission_value_snapshot' => 'decimal:2',
        'delivery_commission_amount' => 'decimal:2',
        'delivery_commission_calculated_at' => 'datetime',
        'pickup_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->reference)) {
                $sale->reference = self::generateReference();
            }
        });

        static::created(function ($sale) {
            if (empty($sale->order_number)) {
                $sale->order_number = self::generateOrderNumber($sale->id);
                $sale->saveQuietly();
            }
        });
    }

    public static function generateReference(): string
    {
        $lastSale = self::latest('id')->first();
        $nextId = $lastSale ? $lastSale->id + 1 : 1;
        return 'TRX-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    public static function generateOrderNumber(?int $id = null): string
    {
        $nextId = $id;
        if (!$nextId) {
            $lastSale = self::latest('id')->first();
            $nextId = $lastSale ? $lastSale->id + 1 : 1;
        }
        return 'CMD-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
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

    public function deliveryAgent(): BelongsTo
    {
        return $this->belongsTo(DeliveryAgent::class);
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

    public function logs(): HasMany
    {
        return $this->hasMany(SaleLog::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleItemReturn::class);
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
        return app(SalePaymentWorkflowService::class)
            ->computeSaleSummary($this)['paid_confirmed_amount'];
    }

    public function getRemainingAmountAttribute(): float
    {
        return app(SalePaymentWorkflowService::class)
            ->computeSaleSummary($this)['remaining_amount'];
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

    public static function supportsColumn(string $column): bool
    {
        static $cache = [];
        $table = (new static())->getTable();
        $key = $table . ':' . $column;

        if (!array_key_exists($key, $cache)) {
            $cache[$key] = Schema::hasColumn($table, $column);
        }

        return $cache[$key];
    }

    public static function persistable(array $attributes): array
    {
        return array_filter(
            $attributes,
            fn ($value, $key) => static::supportsColumn((string) $key),
            ARRAY_FILTER_USE_BOTH
        );
    }
}
