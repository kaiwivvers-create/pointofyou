@extends('layouts.staff')

@section('title', 'Stock Movements')

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
            <h1 class="staff-page-title">Stock Movements</h1>
            <p class="staff-page-subtitle">Track inventory changes and adjustments.</p>
        </div>
        <a href="{{ route('inventory.stock-movements.export.csv', request()->all()) }}" class="staff-btn-secondary">Export CSV</a>
    </div>

    <x-flash />

    <div class="staff-tabs mb-6">
        <button onclick="window.location.href='{{ route('inventory.index') }}'" class="staff-tab {{ request()->routeIs('inventory.index') ? 'staff-tab-active' : '' }}">Products</button>
        <button onclick="window.location.href='{{ route('inventory.supplies') }}'" class="staff-tab {{ request()->routeIs('inventory.supplies') ? 'staff-tab-active' : '' }}">Supplies</button>
        <button onclick="window.location.href='{{ route('inventory.categories') }}'" class="staff-tab {{ request()->routeIs('inventory.categories') ? 'staff-tab-active' : '' }}">Categories</button>
        <button onclick="window.location.href='{{ route('inventory.stock-movements') }}'" class="staff-tab {{ request()->routeIs('inventory.stock-movements') ? 'staff-tab-active' : '' }}">Stock Movements</button>
        <button onclick="window.location.href='{{ route('inventory.bulk-purchases.history') }}'" class="staff-tab {{ request()->routeIs('inventory.bulk-purchases.history') ? 'staff-tab-active' : '' }}">Bulk History</button>
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
            <select name="product_type" class="staff-input">
                <option value="">All</option>
                <option value="inventory" @selected(request('product_type') === 'inventory')>Inventory</option>
                <option value="supply" @selected(request('product_type') === 'supply')>Supply</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Sort By</label>
            <select name="sort_by" class="staff-input">
                <option value="date" @selected(request('sort_by', 'date') === 'date')>Date</option>
                <option value="quantity" @selected(request('sort_by') === 'quantity')>Quantity</option>
                <option value="amount" @selected(request('sort_by') === 'amount')>Amount</option>
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
                        <th>Date</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                        <th>Total</th>
                        <th>Source</th>
                        <th>Reference</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="text-slate-600">{{ $movement->created_at->format('M d, Y H:i') }}</td>
                            <td class="font-semibold text-slate-900">{{ $movement->product ? $movement->product->name : '-' }}</td>
                            <td>
                                @if ($movement->type === 'in')
                                    <span class="staff-badge-green">Stock In</span>
                                @elseif ($movement->type === 'out')
                                    <span class="staff-badge-muted">Stock Out</span>
                                @else
                                    <span class="staff-badge-yellow">Adjustment</span>
                                @endif
                            </td>
                            <td class="font-semibold text-slate-900">{{ $movement->quantity }}</td>
                            <td class="text-slate-900">${{ number_format($movement->unit_cost, 2) }}</td>
                            <td class="text-slate-900">${{ number_format($movement->quantity * $movement->unit_cost, 2) }}</td>
                            <td>
                                @if($movement->source === 'bulk_purchase')
                                    <span class="staff-badge-green">Bulk Purchase</span>
                                @elseif($movement->source === 'auto_supply')
                                    <span class="staff-badge-yellow">Auto Supply</span>
                                @else
                                    <span class="staff-badge-muted">Manual</span>
                                @endif
                            </td>
                            <td class="text-slate-600">{{ $movement->reference ?? '-' }}</td>
                            <td class="text-slate-600">{{ $movement->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-16 text-center text-slate-500">No stock movements recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($movements->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $movements->links() }}
        </div>
    @endif
@endsection
