<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'role',
        'permission',
        'can_view',
        'can_edit',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_edit' => 'boolean',
    ];
}
