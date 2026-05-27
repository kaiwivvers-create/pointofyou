<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'inventory_category_id',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'min_stock_level',
        'unit',
        'description'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
