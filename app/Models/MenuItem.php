<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'category',
        'price',
        'emoji',
        'is_available',
        'is_promo',
        'promo_type',
        'promo_discount_percentage',
        'promo_discount_amount',
        'promo_buy_item_id',
        'promo_get_item_id',
        'promo_min_quantity',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'is_promo' => 'boolean',
            'promo_discount_percentage' => 'decimal:2',
            'promo_discount_amount' => 'decimal:2',
            'promo_min_quantity' => 'integer',
        ];
    }

    public function formattedPrice(): string
    {
        return '$'.number_format((float) $this->price, 2);
    }

    public function modifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MenuItemModification::class);
    }

    public function flavors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Flavor::class);
    }

    public function ingredients(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'menu_item_product')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
