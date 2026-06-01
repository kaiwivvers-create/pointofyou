<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Role;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role_id', $request->role);
        }

        $users = $query->orderBy('name')->paginate(15);

        return view('super-admin.users.index', [
            'users' => $users,
            'roles' => Role::all(),
        ]);
    }

    public function create(): View
    {
        return view('super-admin.users.create', [
            'roles' => Role::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        $role = Role::find($validated['role']);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role_id' => $validated['role'],
            'role' => $role ? \App\Enums\UserRole::tryFrom($role->slug) ?? \App\Enums\UserRole::Cashier : \App\Enums\UserRole::Cashier,
        ]);

        // Create employee record for all users with a role_id
        if ($role) {
            $employee = Employee::create([
                'employee_id' => 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'full_name' => $validated['name'],
                'email' => $validated['email'],
                'position' => $role->name,
                'base_salary' => $role->base_salary ?? 0,
                'hire_date' => now(),
                'status' => 'active',
            ]);

            $user->employee_id = $employee->id;
            $user->save();
        }

        return redirect()->route('super-admin.users.index')->with('success', 'Staff user created.');
    }

    public function edit(User $user): View
    {
        return view('super-admin.users.edit', [
            'user' => $user,
            'roles' => Role::all(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validateUser($request, $user);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role'];
        
        $role = Role::find($validated['role']);
        // Try to map to enum, if not found, use the role_id for custom roles
        $enumRole = \App\Enums\UserRole::tryFrom($role->slug ?? '');
        if ($enumRole) {
            $user->role = $enumRole;
        } else {
            // For custom roles, we'll use the role_id primarily
            // Keep the existing role enum or set to a default
            $user->role = $user->role ?? \App\Enums\UserRole::Cashier;
        }

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()->route('super-admin.users.index')->with('success', 'Staff user updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('super-admin.users.index')->with('success', 'Staff user removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateUser(Request $request, ?User $user = null): array
    {
        $passwordRule = $user
            ? ['nullable', 'string', 'min:8']
            : ['required', 'string', 'min:8', 'confirmed'];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => $passwordRule,
            'role' => ['required', 'exists:roles,id'],
        ]);
    }
}
