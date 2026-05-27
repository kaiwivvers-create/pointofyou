@extends('layouts.staff')

@section('title', 'Payroll')

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
            <h1 class="staff-page-title">Payroll</h1>
            <p class="staff-page-subtitle">Manage employees, salaries, and attendance.</p>
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
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Base Salary</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td class="text-slate-600">{{ $employee->employee_id }}</td>
                            <td class="font-semibold text-slate-900">{{ $employee->full_name }}</td>
                            <td class="text-slate-600">{{ $employee->email }}</td>
                            <td class="text-slate-900">{{ $employee->position }}</td>
                            <td class="text-slate-900">${{ number_format($employee->base_salary, 2) }}</td>
                            <td>
                                @if ($employee->status === 'active')
                                    <span class="staff-badge-green">Active</span>
                                @else
                                    <span class="staff-badge-muted">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right space-x-4">
                                @if ($can('payroll', 'edit'))
                                    <button onclick="openAttendanceModal({{ $employee->toJson() }})" class="staff-link">Attendance</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-500">No employees yet. Add your first employee!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($employees->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $employees->links() }}
        </div>
    @endif

    <!-- Attendance Modal -->
    <div id="attendanceModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="attendanceModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Record Attendance</h2>
                <p class="text-sm text-slate-500 mt-1">Record check-in/check-out for employee.</p>
            </div>
            <form method="POST" action="{{ route('payroll.attendance.store') }}" class="p-6">
                @csrf
                <input type="hidden" name="employee_id" id="attendanceEmployeeId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Employee</label>
                        <input type="text" id="attendanceEmployeeName" readonly class="staff-input bg-slate-50">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Date</label>
                            <input type="date" name="date" required class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <select name="status" required class="staff-input">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="half_day">Half Day</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Check In</label>
                            <input type="time" name="check_in" class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Check Out</label>
                            <input type="time" name="check_out" class="staff-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="staff-input"></textarea>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeAttendanceModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Record Attendance</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAttendanceModal(employee) {
            const modal = document.getElementById('attendanceModal');
            const content = document.getElementById('attendanceModalContent');
            document.getElementById('attendanceEmployeeId').value = employee.id;
            document.getElementById('attendanceEmployeeName').value = employee.full_name;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeAttendanceModal() {
            const modal = document.getElementById('attendanceModal');
            const content = document.getElementById('attendanceModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        document.getElementById('addEmployeeModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddEmployeeModal();
        });

        document.getElementById('attendanceModal').addEventListener('click', function(e) {
            if (e.target === this) closeAttendanceModal();
        });
    </script>
@endsection
