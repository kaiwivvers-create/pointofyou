<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use LogsActivity;

    protected $fillable = [
        'image',
        'title',
        'description',
        'is_active',
        'order',
        'discount_type',
        'discount_value',
        'buy_item_id',
        'get_item_id',
        'gift_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
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

    public function rules()
    {
        return $this->hasMany(PromoRule::class);
    }
}
