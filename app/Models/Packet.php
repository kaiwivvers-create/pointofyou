<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Packet extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'image',
        'fixed_price',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fixed_price' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    public function items()
    {
        return $this->belongsToMany(MenuItem::class, 'packet_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function formattedPrice()
    {
        return '$' . number_format($this->fixed_price, 2);
    }
}
