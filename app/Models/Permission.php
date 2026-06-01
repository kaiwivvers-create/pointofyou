<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use LogsActivity;

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
