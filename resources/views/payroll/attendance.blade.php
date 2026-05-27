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
                            <td class="text-slate-900">{{ $record->check_in ? $record->check_in->format('H:i') : '-' }}</td>
                            <td class="text-slate-900">{{ $record->check_out ? $record->check_out->format('H:i') : '-' }}</td>
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
                                    <span class="staff-badge-blue">Half Day</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-500">No attendance records yet.</td>
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
@endsection
