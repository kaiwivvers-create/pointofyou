<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CafeTable extends Model
{
    protected $fillable = [
        'name',
        'token',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CafeTable $table) {
            if (empty($table->token)) {
                $table->token = Str::lower(Str::random(12));
            }
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scanUrl(): string
    {
        return route('table.scan', $this->token);
    }
}
