@extends('layouts.staff')

@section('title', 'Staff users')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Staff users</h1>
            <p class="staff-page-subtitle">Admin, Super Admin, and Cashier accounts.</p>
        </div>
        <a href="{{ route('super-admin.users.create') }}" class="staff-btn-primary">Add user</a>
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
                                <a href="{{ route('super-admin.users.edit', $user) }}" class="staff-link">Edit</a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('super-admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Remove {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="staff-link-danger">Delete</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">(you)</span>
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
@endsection
