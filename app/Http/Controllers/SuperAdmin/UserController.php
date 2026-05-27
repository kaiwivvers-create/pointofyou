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

        // Create employee record for staff roles with is_paid true or staff roles
        if ($role && ($role->is_paid || in_array($role->slug, ['cashier', 'admin', 'manager']))) {
            $nameParts = explode(' ', $validated['name'], 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $employee = Employee::create([
                'employee_id' => 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $validated['email'],
                'position' => $role->name,
                'base_salary' => $role->base_salary ?? 0,
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
        $user->role = $role ? \App\Enums\UserRole::tryFrom($role->slug) ?? \App\Enums\UserRole::Cashier : \App\Enums\UserRole::Cashier;

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
