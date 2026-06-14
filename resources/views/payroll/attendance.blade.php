@extends('layouts.staff')

@section('title', 'Attendance')

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
            <h1 class="staff-page-title">Attendance</h1>
            <p class="staff-page-subtitle">Track employee attendance and working hours.</p>
        </div>
    </div>

    <x-flash />

    <div class="staff-tabs mb-6">
        <button onclick="window.location.href='{{ route('payroll.index') }}'" class="staff-tab {{ request()->routeIs('payroll.index') ? 'staff-tab-active' : '' }}">Employees</button>
        <button onclick="window.location.href='{{ route('payroll.salaries') }}'" class="staff-tab {{ request()->routeIs('payroll.salaries') ? 'staff-tab-active' : '' }}">Salaries</button>
        <button onclick="window.location.href='{{ route('payroll.attendance') }}'" class="staff-tab {{ request()->routeIs('payroll.attendance') ? 'staff-tab-active' : '' }}">Attendance</button>
    </div>

    <!-- Filters -->
    <div class="staff-card p-6 mb-8">
        <form method="GET" action="{{ route('payroll.attendance') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="employee_id" class="staff-label">Employee</label>
                <select id="employee_id" name="employee_id" class="staff-input">
                    <option value="">All Employees</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" {{ $employeeId == $employee->id ? 'selected' : '' }}>
                            {{ $employee->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label for="start_date" class="staff-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="staff-input">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label for="end_date" class="staff-label">End Date</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="staff-input">
            </div>
            <div>
                <button type="submit" class="staff-btn-primary">Filter</button>
            </div>
        </form>
    </div>

    <!-- Attendance History Table -->
    <div class="staff-card p-6 mb-8">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Attendance History</h2>
        <div class="staff-table-wrap">
            <div class="overflow-x-auto">
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Hours Worked</th>
                            <th>Overtime</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendance as $record)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $record->employee ? $record->employee->full_name : '-' }}</td>
                                <td class="text-slate-600">{{ $record->date->format('M d, Y') }}</td>
                                <td class="text-slate-900">{{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : '-' }}</td>
                                <td class="text-slate-900">{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : '-' }}</td>
                                <td class="text-slate-900">{{ $record->hours_worked ?? '-' }}</td>
                                <td class="text-slate-900">{{ $record->overtime_hours ?? '-' }}</td>
                                <td>
                                    @if ($record->status === 'present')
                                        <span class="staff-badge-green">Present</span>
                                    @elseif ($record->status === 'absent')
                                        <span class="staff-badge-red">Absent</span>
                                    @elseif ($record->status === 'late')
                                        <span class="staff-badge-yellow">Late</span>
                                    @else
                                        <span class="staff-badge-blue">Leave</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-16 text-center text-slate-500">No attendance records found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if ($attendance->hasPages())
            <div class="mt-6 flex justify-center">
                {{ $attendance->links() }}
            </div>
        @endif
    </div>
@endsection
