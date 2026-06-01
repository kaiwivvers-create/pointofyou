@extends('layouts.staff')

@section('title', 'Bulk Purchase History')

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

    $canEditInventory = $user && ($user->isSuperAdmin() || $user->isOwner() || $user->isAdmin());
@endphp

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Bulk Purchase History</h1>
            <p class="staff-page-subtitle">Review stock bought in bulk for inventory and takeout supplies.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('inventory.bulk-purchases.export.csv', request()->all()) }}" class="staff-btn-secondary">Export CSV</a>
            @if ($canEditInventory)
                <button onclick="openBulkPurchaseModal()" class="staff-btn-primary">New Bulk Purchase</button>
            @endif
        </div>
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
                        <th>Reference</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                        <th>Total</th>
                        <th>Type</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="text-slate-600">{{ $movement->created_at->format('M d, Y H:i') }}</td>
                            <td class="font-semibold text-slate-900">{{ $movement->reference ?? 'Bulk Purchase' }}</td>
                            <td class="text-slate-900">{{ $movement->product?->name ?? '-' }}</td>
                            <td class="font-semibold text-slate-900">{{ $movement->quantity }}</td>
                            <td class="text-slate-900">${{ number_format($movement->unit_cost ?? 0, 2) }}</td>
                            <td class="text-slate-900">${{ number_format(($movement->unit_cost ?? 0) * $movement->quantity, 2) }}</td>
                            <td class="text-slate-600">{{ $movement->product?->consume_on_takeout ? 'Supply' : 'Inventory' }}</td>
                            <td class="text-slate-600">{{ $movement->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-500">No bulk purchases recorded yet.</td>
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

    @if ($canEditInventory)
        @include('inventory.partials.bulk-purchase-modal', [
            'bulkProducts' => \App\Models\Product::query()->orderBy('name')->get(['id', 'name', 'stock_quantity', 'purchase_price']),
            'bulkPurchaseTitle' => 'Bulk Purchase Inventory',
            'bulkPurchaseDescription' => 'Record a new bulk stock purchase.',
        ])
    @endif
@endsection
