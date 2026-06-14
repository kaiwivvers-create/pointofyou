<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'hours_worked',
        'overtime_hours',
        'status',
        'notes',
        'face_verified'
    ];

    protected $casts = [
        'date' => 'date',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Employee::class,
            'id', // Foreign key on Employee table
            'id', // Foreign key on User table
            'employee_id', // Local key on Attendance table
            'user_id' // Local key on Employee table
        );
    }
}
