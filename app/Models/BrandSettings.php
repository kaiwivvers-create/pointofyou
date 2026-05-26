<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandSettings extends Model
{
    protected $fillable = [
        'app_name',
        'logo_fallback',
        'logo',
        'landing_kicker',
        'fan_favourite_ids',
        'address',
        'hours',
        'instagram',
        'facebook',
        'phone',
        'primary_color',
        'secondary_color',
        'accent_color',
    ];
    
    protected $casts = [
        'fan_favourite_ids' => 'array',
    ];
    
    public static function getSettings()
    {
        return self::first() ?? self::createDefault();
    }
    
    public static function createDefault()
    {
        return self::create([
            'app_name' => 'Golden Crumb',
            'logo_fallback' => 'GC',
            'landing_kicker' => 'Freshly baked goodness, every day.',
            'address' => '123 Baker Street',
            'hours' => 'Mon – Fri: 6am – 3pm\nSat – Sun: 7am – 4pm',
            'primary_color' => '#f59e0b',
            'secondary_color' => '#faf6f0',
            'accent_color' => '#10b981',
        ]);
    }
}
