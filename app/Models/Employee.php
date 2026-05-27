<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'full_name',
        'email',
        'phone',
        'position',
        'hire_date',
        'base_salary',
        'bank_name',
        'bank_account',
        'address',
        'status'
    ];

    protected $casts = [
        'hire_date' => 'date',
        'base_salary' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
}
