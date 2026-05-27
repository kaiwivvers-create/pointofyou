<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
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
            'dashboard' => 'Dashboard',
            'brand_settings' => 'Brand Settings',
            'users' => 'Staff Users',
            'permissions' => 'Permissions',
            'roles' => 'Roles & Wages',
            'menu' => 'Menu Items',
            'categories' => 'Menu Categories',
            'promos' => 'Promos',
            'tables' => 'Tables & QR',
            'orders' => 'Payments',
            'kitchen' => 'Kitchen Orders',
            'inventory' => 'Inventory',
            'payroll' => 'Payroll',
            'expenses' => 'Expenses',
            'reports' => 'Reports',
        ];
        
        // Get existing permissions for the selected role
        $permissions = Permission::where('role', $selectedRole)
            ->get()
            ->keyBy('permission');
        
        return view('super-admin.permissions.index', [
            'selectedRole' => $selectedRole,
            'roles' => [
                'super_admin' => 'Super Admin',
                'owner' => 'Owner',
                'manager' => 'Manager',
                'admin' => 'Admin',
                'cashier' => 'Cashier',
            ],
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
        
        foreach ($permissions as $permissionName => $data) {
            Permission::updateOrCreate(
                ['role' => $role, 'permission' => $permissionName],
                [
                    'can_view' => $data['can_view'] ?? false,
                    'can_edit' => $data['can_edit'] ?? false,
                ]
            );
        }
        
        return back()->with('success', 'Permissions updated successfully.');
    }
}
