<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $selectedRole = $request->get('role', 'super_admin');
        
        // Define all available permissions
        $allPermissions = [
            'brand_settings' => 'Brand Settings',
            'payment_settings' => 'Payment Settings',
            'users' => 'Staff Users',
            'permissions' => 'Permissions',
            'roles' => 'Roles & Wages',
            'menu' => 'Menu Items',
            'categories' => 'Menu Categories',
            'promos' => 'Promos',
            'tables' => 'Tables & QR',
            'orders' => 'Payments',
            'current_orders' => 'Current Orders',
            'pickup_station' => 'Pickup Station',
            'inventory' => 'Inventory',
            'payroll' => 'Payroll',
            'expenses' => 'Expenses',
            'reports' => 'Reports',
            'activity_logs' => 'Activity Logs',
            'database_management' => 'Database Management',
        ];
        
        // Get existing permissions for the selected role
        $permissions = Permission::where('role', $selectedRole)
            ->get()
            ->keyBy('permission');
        
        // Get all roles from database
        $dbRoles = Role::all()->pluck('name', 'slug')->toArray();
        
        return view('super-admin.permissions.index', [
            'selectedRole' => $selectedRole,
            'roles' => $dbRoles,
            'allPermissions' => $allPermissions,
            'permissions' => $permissions,
        ]);
    }
    
    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $role = $request->input('role');
        
        // Don't allow changing Super Admin permissions
        if ($role === 'super_admin') {
            return back()->with('error', 'Cannot change Super Admin permissions.');
        }
        
        $permissions = $request->input('permissions', []);
        $allPermissions = [
            'brand_settings' => 'Brand Settings',
            'payment_settings' => 'Payment Settings',
            'users' => 'Staff Users',
            'permissions' => 'Permissions',
            'roles' => 'Roles & Wages',
            'menu' => 'Menu Items',
            'categories' => 'Menu Categories',
            'promos' => 'Promos',
            'tables' => 'Tables & QR',
            'orders' => 'Payments',
            'current_orders' => 'Current Orders',
            'pickup_station' => 'Pickup Station',
            'inventory' => 'Inventory',
            'payroll' => 'Payroll',
            'expenses' => 'Expenses',
            'reports' => 'Reports',
            'activity_logs' => 'Activity Logs',
            'database_management' => 'Database Management',
        ];
        
        // Update all permissions
        foreach ($allPermissions as $permissionName => $label) {
            $canView = isset($permissions[$permissionName]['can_view']) && $permissions[$permissionName]['can_view'] == '1';
            $canEdit = isset($permissions[$permissionName]['can_edit']) && $permissions[$permissionName]['can_edit'] == '1';
            
            Permission::updateOrCreate(
                ['role' => $role, 'permission' => $permissionName],
                [
                    'can_view' => $canView,
                    'can_edit' => $canEdit,
                ]
            );
        }
        
        return back()->with('success', 'Permissions updated successfully.');
    }
}
