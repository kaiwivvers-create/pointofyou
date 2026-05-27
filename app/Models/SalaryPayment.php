<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    protected $fillable = [
        'salary_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function salary()
    {
        return $this->belongsTo(Salary::class);
    }
}
