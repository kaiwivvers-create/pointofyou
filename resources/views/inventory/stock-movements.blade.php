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
    </div>

    <x-flash />

    <div class="staff-tabs mb-6">
        <button onclick="window.location.href='{{ route('inventory.index') }}'" class="staff-tab {{ request()->routeIs('inventory.index') ? 'staff-tab-active' : '' }}">Products</button>
        <button onclick="window.location.href='{{ route('inventory.categories') }}'" class="staff-tab {{ request()->routeIs('inventory.categories') ? 'staff-tab-active' : '' }}">Categories</button>
        <button onclick="window.location.href='{{ route('inventory.stock-movements') }}'" class="staff-tab {{ request()->routeIs('inventory.stock-movements') ? 'staff-tab-active' : '' }}">Stock Movements</button>
    </div>

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
                            <td class="text-slate-600">{{ $movement->reference ?? '-' }}</td>
                            <td class="text-slate-600">{{ $movement->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-500">No stock movements recorded yet.</td>
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
