<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoRule extends Model
{
    protected $fillable = [
        'promo_id',
        'buy_item_id',
        'get_item_id',
        'buy_quantity',
        'get_quantity',
    ];

    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    public function buyItem()
    {
        return $this->belongsTo(MenuItem::class, 'buy_item_id');
    }

    public function getItem()
    {
        return $this->belongsTo(MenuItem::class, 'get_item_id');
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class, 'gift_id');
    }
}
