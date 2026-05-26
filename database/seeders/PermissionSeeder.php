<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['owner', 'manager', 'admin', 'cashier'];
        $permissions = ['dashboard', 'menu', 'tables', 'users', 'orders', 'reports'];
        
        foreach ($roles as $role) {
            foreach ($permissions as $permission) {
                Permission::updateOrCreate(
                    ['role' => $role, 'permission' => $permission],
                    [
                        'can_view' => $this->getDefaultViewPermission($role, $permission),
                        'can_edit' => $this->getDefaultEditPermission($role, $permission),
                    ]
                );
            }
        }
    }
    
    private function getDefaultViewPermission(string $role, string $permission): bool
    {
        // Owner can see everything
        if ($role === 'owner') {
            return true;
        }
        
        // Manager can see dashboard, menu, tables, orders
        if ($role === 'manager') {
            return in_array($permission, ['dashboard', 'menu', 'tables', 'orders']);
        }
        
        // Admin can see dashboard, menu, tables
        if ($role === 'admin') {
            return in_array($permission, ['dashboard', 'menu', 'tables']);
        }
        
        // Cashier can only see orders
        if ($role === 'cashier') {
            return $permission === 'orders';
        }
        
        return false;
    }
    
    private function getDefaultEditPermission(string $role, string $permission): bool
    {
        // Owner can edit everything
        if ($role === 'owner') {
            return true;
        }
        
        // Manager can edit menu, tables, orders
        if ($role === 'manager') {
            return in_array($permission, ['menu', 'tables', 'orders']);
        }
        
        // Admin can edit menu, tables
        if ($role === 'admin') {
            return in_array($permission, ['menu', 'tables']);
        }
        
        // Cashier can edit orders
        if ($role === 'cashier') {
            return $permission === 'orders';
        }
        
        return false;
    }
}
