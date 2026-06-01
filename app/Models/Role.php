<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_paid',
        'base_salary',
        'payment_frequency',
        'can_manage_inventory',
        'can_manage_payroll',
        'can_manage_expenses',
        'can_view_reports',
        'is_admin',
        'dashboard_route',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'base_salary' => 'decimal:2',
        'can_manage_inventory' => 'boolean',
        'can_manage_payroll' => 'boolean',
        'can_manage_expenses' => 'boolean',
        'can_view_reports' => 'boolean',
        'is_admin' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
