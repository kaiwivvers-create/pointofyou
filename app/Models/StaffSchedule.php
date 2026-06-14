<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class StaffSchedule extends Model
{
    use LogsActivity;

    protected $fillable = [
        'role_id',
        'day_of_week',
        'is_day_off',
        'expected_start_time',
        'expected_end_time',
        'notes',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_day_off' => 'boolean',
        'expected_start_time' => 'datetime',
        'expected_end_time' => 'datetime',
    ];


    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
