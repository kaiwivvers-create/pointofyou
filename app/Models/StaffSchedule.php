<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class StaffSchedule extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'role_id',
        'date',
        'type',
        'expected_start_time',
        'expected_end_time',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'expected_start_time' => 'datetime',
        'expected_end_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
