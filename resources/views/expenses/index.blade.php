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
            <p class="staff-page-subtitle">Automatic expense history from stock purchases.</p>
        </div>
        <a href="{{ route('expenses.export.csv', request()->all()) }}" class="staff-btn-secondary">Export CSV</a>
    </div>

    <x-flash />

    <div class="staff-tabs mb-6">
        <button onclick="window.location.href='{{ route('expenses.index') }}'" class="staff-tab {{ request()->routeIs('expenses.index') ? 'staff-tab-active' : '' }}">Expenses</button>
        <button onclick="window.location.href='{{ route('expenses.categories') }}'" class="staff-tab {{ request()->routeIs('expenses.categories') ? 'staff-tab-active' : '' }}">Categories</button>
    </div>

    <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-3 bg-white p-4 rounded-2xl border border-slate-200">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="staff-input">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="staff-input">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Item Type</label>
            <select name="item_type" class="staff-input">
                <option value="">All</option>
                <option value="inventory" @selected(request('item_type') === 'inventory')>Inventory</option>
                <option value="supply" @selected(request('item_type') === 'supply')>Supply</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Sort By</label>
            <select name="sort_by" class="staff-input">
                <option value="date" @selected(request('sort_by', 'date') === 'date')>Date</option>
                <option value="amount" @selected(request('sort_by') === 'amount')>Amount</option>
                <option value="title" @selected(request('sort_by') === 'title')>Title</option>
            </select>
        </div>
        <div class="flex items-end gap-3">
            <select name="sort_direction" class="staff-input flex-1">
                <option value="desc" @selected(request('sort_direction', 'desc') === 'desc')>High to Low</option>
                <option value="asc" @selected(request('sort_direction') === 'asc')>Low to High</option>
            </select>
            <button type="submit" class="staff-btn-primary">Filter</button>
        </div>
    </form>

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Item Type</th>
                        <th>Qty</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $expense->title }}</td>
                            <td>{{ $expense->category ? $expense->category->name : '-' }}</td>
                            <td class="text-slate-600">{{ $expense->item_type ? ucfirst($expense->item_type) : '-' }}</td>
                            <td class="font-semibold text-slate-900">{{ $expense->quantity }}</td>
                            <td class="font-semibold text-slate-900">${{ number_format($expense->amount, 2) }}</td>
                            <td class="text-slate-600">{{ $expense->expense_date->format('M d, Y') }}</td>
                            <td class="text-slate-600">{{ $expense->reference ?? '-' }}</td>
                            <td>
                                <span class="staff-badge-green">Automatic</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-500">No expenses recorded yet.</td>
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

@endsection
