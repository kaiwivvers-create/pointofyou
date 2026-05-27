<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super_admin',
                'description' => 'Full system access',
                'is_paid' => true,
                'base_salary' => 5000.00,
                'payment_frequency' => 'monthly',
                'can_manage_inventory' => true,
                'can_manage_payroll' => true,
                'can_manage_expenses' => true,
                'can_view_reports' => true,
                'is_admin' => true,
            ],
            [
                'name' => 'Owner',
                'slug' => 'owner',
                'description' => 'Business owner with full access',
                'is_paid' => true,
                'base_salary' => 4000.00,
                'payment_frequency' => 'monthly',
                'can_manage_inventory' => true,
                'can_manage_payroll' => true,
                'can_manage_expenses' => true,
                'can_view_reports' => true,
                'is_admin' => true,
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Manages daily operations',
                'is_paid' => true,
                'base_salary' => 3000.00,
                'payment_frequency' => 'monthly',
                'can_manage_inventory' => true,
                'can_manage_payroll' => true,
                'can_manage_expenses' => true,
                'can_view_reports' => true,
                'is_admin' => true,
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrative staff',
                'is_paid' => true,
                'base_salary' => 2500.00,
                'payment_frequency' => 'monthly',
                'can_manage_inventory' => true,
                'can_manage_payroll' => false,
                'can_manage_expenses' => false,
                'can_view_reports' => true,
                'is_admin' => true,
            ],
            [
                'name' => 'Cashier',
                'slug' => 'cashier',
                'description' => 'Handles orders and payments',
                'is_paid' => true,
                'base_salary' => 2000.00,
                'payment_frequency' => 'monthly',
                'can_manage_inventory' => false,
                'can_manage_payroll' => false,
                'can_manage_expenses' => false,
                'can_view_reports' => false,
                'is_admin' => false,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
