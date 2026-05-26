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
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users by name or email..." class="staff-input">
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
                            <td><span class="staff-badge-amber">{{ $user->role->label() }}</span></td>
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
    <div id="createModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="createModalContent" class="bg-white rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] overflow-hidden scale-95 opacity-0 transition-all duration-200">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add staff user</h2>
                <p class="text-sm text-slate-500 mt-1">Create a new staff account.</p>
            </div>
            <form method="POST" action="{{ route('super-admin.users.store') }}" class="p-6">
                @csrf
                @include('super-admin.users._form', ['roles' => $roles])
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeCreateModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Create user</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="editModalContent" class="bg-white rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] overflow-hidden scale-95 opacity-0 transition-all duration-200">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Edit staff user</h2>
            </div>
            <form id="editForm" method="POST" class="p-6">
                @csrf
                @method('PUT')
                @include('super-admin.users._form', ['roles' => $roles, 'user' => null])
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeEditModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Update user</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            const modal = document.getElementById('createModal');
            const content = document.getElementById('createModalContent');
            const form = modal.querySelector('form');
            
            // Clear form fields
            form.querySelector('[name="name"]').value = '';
            form.querySelector('[name="email"]').value = '';
            form.querySelector('[name="password"]').value = '';
            form.querySelector('[name="role"]').value = '';
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-1');
            }, 10);
        }

        function closeCreateModal() {
            const modal = document.getElementById('createModal');
            const content = document.getElementById('createModalContent');
            content.classList.remove('scale-100', 'opacity-1');
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
            form.action = '/super-admin/users/' + user.id;
            form.querySelector('[name="name"]').value = user.name;
            form.querySelector('[name="email"]').value = user.email;
            form.querySelector('[name="password"]').value = '';
            form.querySelector('[name="role"]').value = user.role.value;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-1');
            }, 10);
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            const content = document.getElementById('editModalContent');
            content.classList.remove('scale-100', 'opacity-1');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }
    </script>
@endsection
