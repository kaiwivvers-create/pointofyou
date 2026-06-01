<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use LogsActivity;

    protected $fillable = ['name', 'description', 'color'];

    public function expenses()
    {
        return $this->hasMany(OperationalExpense::class);
    }
}
