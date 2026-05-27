<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::orderBy('name')->get();
        return view('super-admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('super-admin.roles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug',
            'description' => 'nullable|string',
            'is_paid' => 'boolean',
            'base_salary' => 'nullable|numeric|min:0',
            'payment_frequency' => 'required|in:monthly,bi-weekly,weekly',
            'can_manage_inventory' => 'boolean',
            'can_manage_payroll' => 'boolean',
            'can_manage_expenses' => 'boolean',
            'can_view_reports' => 'boolean',
            'is_admin' => 'boolean',
        ]);

        Role::create($validated);

        return redirect()->route('super-admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        return view('super-admin.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string',
            'is_paid' => 'boolean',
            'base_salary' => 'nullable|numeric|min:0',
            'payment_frequency' => 'required|in:monthly,bi-weekly,weekly',
            'can_manage_inventory' => 'boolean',
            'can_manage_payroll' => 'boolean',
            'can_manage_expenses' => 'boolean',
            'can_view_reports' => 'boolean',
            'is_admin' => 'boolean',
        ]);

        $role->update($validated);

        return redirect()->route('super-admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        // Set role_id to null for all users with this role
        $role->users()->update(['role_id' => null]);

        $role->delete();

        return redirect()->route('super-admin.roles.index')->with('success', 'Role deleted successfully. Users with this role have been unassigned.');
    }
}
