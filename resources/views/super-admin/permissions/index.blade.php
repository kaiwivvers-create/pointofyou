@extends('layouts.staff')

@section('title', 'Permissions')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Permissions</h1>
            <p class="staff-page-subtitle">Manage role-based access control.</p>
        </div>
    </div>

    <x-flash />

    <div class="staff-card p-6 mb-6">
        <form method="GET" action="{{ route('super-admin.permissions.index') }}" class="flex items-center gap-4">
            <label class="text-sm font-medium text-slate-700">Select Role:</label>
            <select name="role" class="staff-input w-48" onchange="this.form.submit()">
                @foreach ($roles as $key => $label)
                    <option value="{{ $key }}" {{ $selectedRole === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @if ($selectedRole === 'super_admin')
                <span class="text-sm text-amber-600 font-medium">Super Admin has all permissions (cannot be changed)</span>
            @endif
        </form>
    </div>

    <form method="POST" action="{{ route('super-admin.permissions.update') }}">
        @csrf
        <input type="hidden" name="role" value="{{ $selectedRole }}">
        
        <div class="staff-table-wrap">
            <div class="overflow-x-auto">
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>Page/Feature</th>
                            <th class="text-center">Can View</th>
                            <th class="text-center">Can Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allPermissions as $key => $label)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $label }}</td>
                                <td class="text-center">
                                    <input type="checkbox" 
                                           name="permissions[{{ $key }}][can_view]" 
                                           value="1"
                                           {{ $selectedRole === 'super_admin' ? 'checked disabled' : ($permissions->get($key)?->can_view ? 'checked' : '') }}
                                           class="size-5 rounded border-slate-300 text-slate-900 focus:ring-slate-300 {{ $selectedRole === 'super_admin' ? 'opacity-50 cursor-not-allowed' : '' }}">
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" 
                                           name="permissions[{{ $key }}][can_edit]" 
                                           value="1"
                                           {{ $selectedRole === 'super_admin' ? 'checked disabled' : ($permissions->get($key)?->can_edit ? 'checked' : '') }}
                                           class="size-5 rounded border-slate-300 text-slate-900 focus:ring-slate-300 {{ $selectedRole === 'super_admin' ? 'opacity-50 cursor-not-allowed' : '' }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($selectedRole !== 'super_admin')
            <div class="mt-6 flex justify-end">
                <button type="submit" class="staff-btn-primary">Save Permissions</button>
            </div>
        @endif
    </form>
@endsection
