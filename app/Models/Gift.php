<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    use LogsActivity;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'image',
        'cost',
        'purchase_price',
        'stock_quantity',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'cost' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }
}
