<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MenuCategory extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'label',
        'sort_order',
        'is_visible',
        'icon_url',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope: only visible categories, ordered by sort_order.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('sort_order');
    }

    /**
     * All categories ordered by sort_order.
     */
    public static function getOrdered()
    {
        return static::orderBy('sort_order')->get();
    }

    /**
     * Get icon URL for this category.
     * Uses database icon_url if available, otherwise falls back to default.
     */
    public function getIconUrlAttribute(): string
    {
        $iconUrl = $this->attributes['icon_url'] ?? null;
        if ($iconUrl) {
            return $iconUrl;
        }

        return $this->defaultIcon($this->name);
    }

    /**
     * Default icon URLs keyed by category name.
     */
    public static function defaultIcon(string $name): string
    {
        return match ($name) {
            'promo'  => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=120&auto=format&fit=crop&q=80',
            'food'   => 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=120&auto=format&fit=crop&q=80',
            'drinks' => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=120&auto=format&fit=crop&q=80',
            'pastry' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=120&auto=format&fit=crop&q=80',
            default  => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=120&auto=format&fit=crop&q=80',
        };
    }
}
