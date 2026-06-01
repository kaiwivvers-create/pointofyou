@extends('layouts.staff')

@section('title', 'Pickup Station')

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
            <h1 class="staff-page-title">Pickup Station</h1>
            <p class="staff-page-subtitle">Manage order handoffs and track completed orders</p>
        </div>
    </div>

    <x-flash />

    <h2 class="font-sans text-xl font-semibold text-slate-900 mb-4">Active Orders</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        @forelse ($activeOrders as $order)
            @php
                $isPaid = $order->status->value === 'paid';
                $isClosed = $order->isClosed();
                $isFullyReady = $order->isFullyReady();
            @endphp
            
            <div class="bg-white rounded-3xl shadow-sm border {{ $isPaid ? 'border-emerald-200' : 'border-amber-200' }} overflow-hidden flex flex-col relative group">
                <div class="p-5 border-b border-slate-100 flex justify-between items-start {{ $isPaid ? 'bg-emerald-50/50' : 'bg-amber-50/50' }}">
                    <div>
                        <h3 class="font-display text-xl font-bold text-slate-900">
                            @if($order->order_type === 'dine_in' && $order->cafeTable)
                                Table {{ $order->cafeTable->name }}
                            @else
                                Takeout
                            @endif
                        </h3>
                        <p class="text-xs font-medium text-slate-500 mt-1">
                            Order #{{ $order->id }} &middot; {{ $order->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <div class="flex gap-2">
                            <!-- Payment Status Badge -->
                            @if($isPaid)
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-200 uppercase tracking-wider">Paid</span>
                            @else
                                <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded border border-amber-200 uppercase tracking-wider">Unpaid</span>
                            @endif

                            <!-- Kitchen Status Badge -->
                            @if($isClosed)
                                <span class="bg-stone-100 text-stone-600 text-[10px] font-bold px-2 py-0.5 rounded border border-stone-200 uppercase tracking-wider">Picked Up</span>
                            @elseif($isFullyReady)
                                <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded border border-indigo-200 uppercase tracking-wider">Ready for Pickup</span>
                            @else
                                <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded border border-blue-200 uppercase tracking-wider">Preparing</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-5 flex-1 space-y-3">
                    @foreach ($order->items as $line)
                        <div class="flex justify-between gap-4 text-sm {{ $line->is_ready ? 'text-slate-400 line-through' : 'text-slate-700 font-medium' }}">
                            <span>{{ $line->quantity }}× {{ $line->item_name }}</span>
                        </div>
                    @endforeach
                </div>
                
                @if ($can('kitchen', 'edit'))
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row gap-3">
                        @if(!$isClosed)
                            <form method="POST" action="{{ route('admin.pickup-station.close', $order) }}" class="flex-1">
                                @csrf
                                <button type="submit" @disabled(! $isFullyReady) class="w-full font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors shadow-sm flex justify-center items-center gap-2 {{ $isFullyReady ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-slate-100 border border-slate-200 text-slate-400 cursor-not-allowed' }}">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    {{ $isFullyReady ? 'Pick Up / Close' : 'Waiting for Kitchen' }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full py-16 flex flex-col items-center justify-center text-center bg-white rounded-3xl border border-dashed border-slate-300">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="size-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-xl font-display font-bold text-slate-900">No active orders</h3>
                <p class="text-slate-500 mt-1 max-w-sm">All orders are fully picked up!</p>
            </div>
        @endforelse
    </div>

    <h2 class="font-sans text-xl font-semibold text-slate-900 mb-4">Recently Completed</h2>
    <div class="staff-table-wrap">
        <table class="staff-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Table</th>
                    <th>Payment</th>
                    <th>Closed By</th>
                    <th>Closed At</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentlyClosed as $order)
                    <tr>
                        <td class="font-medium text-slate-900">#{{ $order->id }}</td>
                        <td>{{ $order->cafeTable->name ?? 'Takeout' }}</td>
                        <td>
                            @if($order->status->value === 'paid')
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-200 uppercase tracking-wider">Paid</span>
                            @else
                                <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded border border-amber-200 uppercase tracking-wider">Unpaid</span>
                            @endif
                        </td>
                        <td>
                            @if($order->closedBy)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                        {{ substr($order->closedBy->name, 0, 1) }}
                                    </div>
                                    <span class="text-sm font-medium text-slate-700">{{ $order->closedBy->name }}</span>
                                </div>
                            @else
                                <span class="text-slate-400 italic">Unknown</span>
                            @endif
                        </td>
                        <td class="text-sm text-slate-600">
                            {{ $order->closed_at ? $order->closed_at->format('M j, g:i A') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-slate-500">No recently completed orders.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
