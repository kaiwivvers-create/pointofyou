<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->orderBy('name')->get();

        return view('super-admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('super-admin.users.create', [
            'roles' => [UserRole::SuperAdmin, UserRole::Admin, UserRole::Cashier],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => UserRole::from($validated['role']),
        ]);

        return redirect()->route('super-admin.users.index')->with('success', 'Staff user created.');
    }

    public function edit(User $user): View
    {
        return view('super-admin.users.edit', [
            'user' => $user,
            'roles' => [UserRole::SuperAdmin, UserRole::Admin, UserRole::Cashier],
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validateUser($request, $user);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = UserRole::from($validated['role']);

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
            : ['required', 'string', 'min:8'];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => $passwordRule,
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
        ]);
    }
}
