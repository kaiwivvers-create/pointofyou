<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationalExpense extends Model
{
    protected $fillable = [
        'expense_category_id',
        'product_id',
        'source',
        'item_type',
        'quantity',
        'title',
        'description',
        'amount',
        'expense_date',
        'receipt',
        'reference',
        'status',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'quantity' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
