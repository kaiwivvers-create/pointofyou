@extends('layouts.staff')

@section('title', 'Salaries')

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
            <h1 class="staff-page-title">Salaries</h1>
            <p class="staff-page-subtitle">Manage salary records and payments.</p>
        </div>
        @if ($can('payroll', 'edit'))
            <button onclick="openAddSalaryModal()" class="staff-btn-primary">Add Salary Record</button>
        @endif
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
                        <th>Base Salary</th>
                        <th>Allowance</th>
                        <th>Bonus</th>
                        <th>Deductions</th>
                        <th>Tax</th>
                        <th>Net Salary</th>
                        <th>Period</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salaries as $salary)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $salary->employee ? $salary->employee->full_name : '-' }}</td>
                            <td class="text-slate-900">${{ number_format($salary->base_salary, 2) }}</td>
                            <td class="text-slate-900">${{ number_format($salary->allowance, 2) }}</td>
                            <td class="text-slate-900">${{ number_format($salary->bonus, 2) }}</td>
                            <td class="text-slate-900">${{ number_format($salary->deductions, 2) }}</td>
                            <td class="text-slate-900">${{ number_format($salary->tax, 2) }}</td>
                            <td class="font-semibold text-slate-900">${{ number_format($salary->net_salary, 2) }}</td>
                            <td class="text-slate-600">{{ $salary->period_start->format('M d') }} - {{ $salary->period_end->format('M d, Y') }}</td>
                            <td>
                                @if ($salary->status === 'paid')
                                    <span class="staff-badge-green">Paid</span>
                                @elseif ($salary->status === 'approved')
                                    <span class="staff-badge-blue">Approved</span>
                                @else
                                    <span class="staff-badge-yellow">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-16 text-center text-slate-500">No salary records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($salaries->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $salaries->links() }}
        </div>
    @endif

    <!-- Add Salary Modal -->
    <div id="addSalaryModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="addSalaryModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add Salary Record</h2>
                <p class="text-sm text-slate-500 mt-1">Create a new salary record for an employee.</p>
            </div>
            <form method="POST" action="{{ route('payroll.salaries.store') }}" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Employee</label>
                        <select name="employee_id" required class="staff-input">
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Period Start</label>
                            <input type="date" name="period_start" required class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Period End</label>
                            <input type="date" name="period_end" required class="staff-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Base Salary</label>
                            <input type="number" step="0.01" name="base_salary" required class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Allowance</label>
                            <input type="number" step="0.01" name="allowance" value="0" class="staff-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Bonus</label>
                            <input type="number" step="0.01" name="bonus" value="0" class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Deductions</label>
                            <input type="number" step="0.01" name="deductions" value="0" class="staff-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tax</label>
                        <input type="number" step="0.01" name="tax" value="0" class="staff-input">
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeAddSalaryModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Save Salary</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddSalaryModal() {
            const modal = document.getElementById('addSalaryModal');
            const content = document.getElementById('addSalaryModalContent');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeAddSalaryModal() {
            const modal = document.getElementById('addSalaryModal');
            const content = document.getElementById('addSalaryModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        document.getElementById('addSalaryModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddSalaryModal();
        });
    </script>
@endsection
