<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $fillable = [
        'employee_id',
        'base_salary',
        'overtime_rate',
        'allowance',
        'bonus',
        'deductions',
        'tax',
        'net_salary',
        'period_start',
        'period_end',
        'status'
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'allowance' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deductions' => 'decimal:2',
        'tax' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payments()
    {
        return $this->hasMany(SalaryPayment::class);
    }
}
