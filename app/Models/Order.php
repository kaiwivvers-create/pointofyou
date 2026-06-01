<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use LogsActivity;

    protected $fillable = [
        'cafe_table_id',
        'status',
        'order_type',
        'total',
        'notes',
        'is_closed',
        'closed_by',
        'closed_at',
        'paid_by',
        'paid_at',
        'payment_method',
        'amount_paid',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total' => 'decimal:2',
            'is_closed' => 'boolean',
            'closed_at' => 'datetime',
            'paid_at' => 'datetime',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function cafeTable(): BelongsTo
    {
        return $this->belongsTo(CafeTable::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(OrderAdjustment::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::Pending;
    }

    public function isClosed(): bool
    {
        return $this->is_closed;
    }

    public function isFullyReady(): bool
    {
        // If there are no items, it's not ready. Otherwise, it's ready if ALL items are ready.
        if ($this->items->isEmpty()) {
            return false;
        }
        return $this->items->every(fn($item) => $item->is_ready);
    }
}
