@extends('layouts.staff')

@section('title', 'Expenses')

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
            <h1 class="staff-page-title">Expenses</h1>
            <p class="staff-page-subtitle">Track and manage operational expenses.</p>
        </div>
        @if ($can('expenses', 'edit'))
            <button onclick="openAddExpenseModal()" class="staff-btn-primary">Add Expense</button>
        @endif
    </div>

    <x-flash />

    <div class="staff-tabs mb-6">
        <button onclick="window.location.href='{{ route('expenses.index') }}'" class="staff-tab {{ request()->routeIs('expenses.index') ? 'staff-tab-active' : '' }}">Expenses</button>
        <button onclick="window.location.href='{{ route('expenses.categories') }}'" class="staff-tab {{ request()->routeIs('expenses.categories') ? 'staff-tab-active' : '' }}">Categories</button>
    </div>

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $expense->title }}</td>
                            <td>{{ $expense->category ? $expense->category->name : '-' }}</td>
                            <td class="font-semibold text-slate-900">${{ number_format($expense->amount, 2) }}</td>
                            <td class="text-slate-600">{{ $expense->expense_date->format('M d, Y') }}</td>
                            <td class="text-slate-600">{{ $expense->reference ?? '-' }}</td>
                            <td>
                                @if ($expense->status === 'approved')
                                    <span class="staff-badge-green">Approved</span>
                                @elseif ($expense->status === 'rejected')
                                    <span class="staff-badge-red">Rejected</span>
                                @else
                                    <span class="staff-badge-yellow">Pending</span>
                                @endif
                            </td>
                            <td class="text-right space-x-4">
                                @if ($can('expenses', 'edit') && $expense->status === 'pending')
                                    <form method="POST" action="{{ route('expenses.approve', $expense) }}" class="inline" onsubmit="return confirm('Approve this expense?')">
                                        @csrf
                                        <button type="submit" class="staff-link">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('expenses.reject', $expense) }}" class="inline" onsubmit="return confirm('Reject this expense?')">
                                        @csrf
                                        <button type="submit" class="staff-link-danger">Reject</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-500">No expenses recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($expenses->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $expenses->links() }}
        </div>
    @endif

    <!-- Add Expense Modal -->
    <div id="addExpenseModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="addExpenseModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add Expense</h2>
                <p class="text-sm text-slate-500 mt-1">Record a new operational expense.</p>
            </div>
            <form method="POST" action="{{ route('expenses.store') }}" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                        <input type="text" name="title" required class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                        <select name="expense_category_id" required class="staff-input">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Amount</label>
                            <input type="number" step="0.01" name="amount" required class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Date</label>
                            <input type="date" name="expense_date" required class="staff-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Reference</label>
                        <input type="text" name="reference" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" rows="3" class="staff-input"></textarea>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeAddExpenseModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Save Expense</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddExpenseModal() {
            const modal = document.getElementById('addExpenseModal');
            const content = document.getElementById('addExpenseModalContent');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeAddExpenseModal() {
            const modal = document.getElementById('addExpenseModal');
            const content = document.getElementById('addExpenseModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        document.getElementById('addExpenseModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddExpenseModal();
        });
    </script>
@endsection
