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
                            <td class="font-semibold text-amber-950">{{ $user->name }}</td>
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
                                    <span class="text-xs text-stone-400">(you)</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
