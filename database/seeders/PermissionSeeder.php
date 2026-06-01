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
        $roles = ['owner', 'manager', 'admin', 'cashier', 'chef'];
        $permissions = ['menu', 'tables', 'users', 'orders', 'reports', 'inventory', 'payroll', 'expenses', 'brand_settings', 'payment_settings', 'current_orders', 'pickup_station', 'categories', 'promos', 'permissions', 'roles', 'activity_logs', 'database_management', 'database_export', 'database_import', 'backup_download', 'backup_delete', 'cache_clear', 'application_optimize', 'database_migrate', 'database_seed', 'permits', 'staff_schedules'];
        
        foreach ($roles as $role) {
            foreach ($permissions as $permission) {
                // Only create if it doesn't exist, don't overwrite existing permissions
                $existing = Permission::where('role', $role)
                    ->where('permission', $permission)
                    ->first();
                
                if (!$existing) {
                    Permission::create([
                        'role' => $role,
                        'permission' => $permission,
                        'can_view' => $this->getDefaultViewPermission($role, $permission),
                        'can_edit' => $this->getDefaultEditPermission($role, $permission),
                    ]);
                }
            }
        }
    }
    
    private function getDefaultViewPermission(string $role, string $permission): bool
    {
        // Owner can see everything
        if ($role === 'owner') {
            return true;
        }

        // Brand settings, payment settings, activity logs, and database management are super-admin only
        if (in_array($permission, ['brand_settings', 'payment_settings', 'activity_logs', 'database_management', 'database_export', 'database_import', 'backup_download', 'backup_delete', 'cache_clear', 'application_optimize', 'database_migrate', 'database_seed'])) {
            return false;
        }
        
        // Manager can see menu, tables, orders, inventory, payroll, expenses, categories, promos, current_orders, pickup_station, permits, staff_schedules
        if ($role === 'manager') {
            return in_array($permission, ['menu', 'tables', 'orders', 'inventory', 'payroll', 'expenses', 'categories', 'promos', 'current_orders', 'pickup_station', 'permits', 'staff_schedules']);
        }
        
        // Admin can see menu, tables, inventory
        if ($role === 'admin') {
            return in_array($permission, ['menu', 'tables', 'inventory']);
        }
        
        // Cashier can only see orders
        if ($role === 'cashier') {
            return $permission === 'orders';
        }
        
        // Chef can only see current_orders, pickup_station
        if ($role === 'chef') {
            return in_array($permission, ['current_orders', 'pickup_station']);
        }
        
        return false;
    }
    
    private function getDefaultEditPermission(string $role, string $permission): bool
    {
        // Owner can edit everything
        if ($role === 'owner') {
            return true;
        }

        // Brand settings, payment settings, activity logs, and database management are super-admin only
        if (in_array($permission, ['brand_settings', 'payment_settings', 'activity_logs', 'database_management', 'database_export', 'database_import', 'backup_download', 'backup_delete', 'cache_clear', 'application_optimize', 'database_migrate', 'database_seed'])) {
            return false;
        }
        
        // Manager can edit menu, tables, orders, inventory, payroll, expenses, categories, promos, current_orders, pickup_station, permits, staff_schedules
        if ($role === 'manager') {
            return in_array($permission, ['menu', 'tables', 'orders', 'inventory', 'payroll', 'expenses', 'categories', 'promos', 'current_orders', 'pickup_station', 'permits', 'staff_schedules']);
        }
        
        // Admin can edit menu, tables, inventory
        if ($role === 'admin') {
            return in_array($permission, ['menu', 'tables', 'inventory']);
        }
        
        // Cashier can edit orders
        if ($role === 'cashier') {
            return $permission === 'orders';
        }
        
        // Chef can edit current_orders, pickup_station
        if ($role === 'chef') {
            return in_array($permission, ['current_orders', 'pickup_station']);
        }
        
        return false;
    }
}
