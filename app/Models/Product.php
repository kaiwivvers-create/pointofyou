<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'inventory_category_id',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'min_stock_level',
        'unit',
        'description',
        'consume_on_takeout',
        'consume_per_item',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'consume_on_takeout' => 'boolean',
        'consume_per_item' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function menuItems(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_product')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
