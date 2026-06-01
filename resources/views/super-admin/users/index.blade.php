@extends('layouts.staff')

@section('title', 'Staff users')

@php
    $user = auth()->user();
    $userPermissions = [];
    if ($user) {
        $userPermissions = \App\Models\Permission::where('role', $user->role->value)
            ->get()
            ->keyBy('permission');
    }
    
    $can = function($permission, $action = 'view') use ($user, $userPermissions) {
        if (!$user) return false;
        if ($user->isSuperAdmin()) return true;
        $perm = $userPermissions->get($permission);
        if (!$perm) return false;
        return $action === 'edit' ? $perm->can_edit : $perm->can_view;
    };
@endphp

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Staff users</h1>
            <p class="staff-page-subtitle">Admin, Super Admin, and Cashier accounts.</p>
        </div>
        @if ($can('users', 'edit'))
            <button onclick="openCreateModal()" class="staff-btn-primary">Add user</button>
        @endif
    </div>

    <x-flash />

    <!-- Search and Filter -->
    <form method="GET" action="{{ route('super-admin.users.index') }}" class="staff-card p-4 mb-6 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" maxlength="255" placeholder="Search users by name or email..." class="staff-input">
        </div>
        <div class="sm:w-48">
            <select name="role" class="staff-input">
                <option value="">All Roles</option>
                <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                <option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>Owner</option>
                <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="cashier" {{ request('role') === 'cashier' ? 'selected' : '' }}>Cashier</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="staff-btn-primary">Filter</button>
            @if (request('search') || request('role'))
                <a href="{{ route('super-admin.users.index') }}" class="staff-btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="staff-badge-amber">{{ $user->dbRole->name ?? $user->role->label() }}</span></td>
                            <td class="text-right space-x-4">
                                @if ($can('users', 'edit'))
                                    <button onclick="openEditModal({{ $user->toJson() }})" class="staff-link">Edit</button>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('super-admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Remove {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="staff-link-danger">Delete</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">(you)</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($users->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $users->appends(request()->only(['search', 'role']))->links() }}
        </div>
    @endif

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div id="createModalContent" class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-hidden transform transition-all duration-200 scale-95 opacity-0">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add staff user</h2>
                <p class="text-sm text-slate-500 mt-1">Create a new staff account.</p>
            </div>
            <form method="POST" action="{{ route('super-admin.users.store') }}" class="p-6">
                @csrf
                @include('super-admin.users._form', ['roles' => $roles, 'user' => null])
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeCreateModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Create user</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div id="editModalContent" class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-hidden transform transition-all duration-200 scale-95 opacity-0">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Edit staff user</h2>
            </div>
            <form id="editForm" method="POST" action="{{ route('super-admin.users.update', ':id') }}" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <div>
                        <label for="edit_name" class="staff-label">Name</label>
                        <input id="edit_name" name="name" type="text" required maxlength="255" class="staff-input">
                    </div>
                    <div>
                        <label for="edit_email" class="staff-label">Email</label>
                        <input id="edit_email" name="email" type="email" required maxlength="255" class="staff-input">
                    </div>
                    <div>
                        <label for="edit_password" class="staff-label">
                            New password <span class="font-normal text-slate-500">(leave blank to keep current)</span>
                        </label>
                        <input id="edit_password" name="password" type="password" maxlength="255" class="staff-input" autocomplete="new-password">
                    </div>
                    <div>
                        <label for="edit_role" class="staff-label">Role</label>
                        <select id="edit_role" name="role" required class="staff-input">
                            <option value="">Select a role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="resetPassword()" class="staff-btn-secondary">Reset Password</button>
                    <button type="button" onclick="closeEditModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Update user</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openCreateModal() {
            const modal = document.getElementById('createModal');
            const content = document.getElementById('createModalContent');
            const form = modal.querySelector('form');
            
            if (!modal || !content || !form) {
                console.error('Modal elements not found');
                return;
            }
            
            // Reset form and clear all values
            form.reset();
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const passwordConfirmInput = document.getElementById('password_confirmation');
            const roleInput = document.getElementById('role');
            
            if (nameInput) nameInput.value = '';
            if (emailInput) emailInput.value = '';
            if (passwordInput) passwordInput.value = '';
            if (passwordConfirmInput) passwordConfirmInput.value = '';
            if (roleInput) roleInput.value = '';
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeCreateModal() {
            const modal = document.getElementById('createModal');
            const content = document.getElementById('createModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function openEditModal(user) {
            const modal = document.getElementById('editModal');
            const content = document.getElementById('editModalContent');
            const form = document.getElementById('editForm');
            form.action = form.action.replace(':id', user.id);
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_role').value = user.role_id;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            const content = document.getElementById('editModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function resetPassword() {
            const newPassword = prompt('Enter new password:');
            if (newPassword && newPassword.length >= 8) {
                const confirmPassword = prompt('Confirm new password:');
                if (newPassword === confirmPassword) {
                    const form = document.getElementById('editForm');
                    form.querySelector('[name="password"]').value = newPassword;
                    alert('Password will be updated when you click Update user');
                } else {
                    alert('Passwords do not match.');
                }
            } else if (newPassword) {
                alert('Password must be at least 8 characters.');
            }
        }

        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) closeCreateModal();
        });

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    </script>
@endpush
