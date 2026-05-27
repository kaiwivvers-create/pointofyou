@extends('layouts.staff')

@section('title', 'Cashier')

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
            <h1 class="staff-page-title">Cashier Dashboard</h1>
            <p class="staff-page-subtitle">Manage payments and order handoffs</p>
        </div>
    </div>

    <x-flash />

    <h2 class="font-sans text-xl font-semibold text-slate-900 mb-4">Active Orders</h2>
    <p class="text-sm text-slate-500 mb-6">Orders disappear when they are both <strong>Paid</strong> and <strong>Picked Up / Closed</strong>.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                        <p class="font-sans text-xl font-bold text-slate-900">${{ number_format($order->total, 2) }}</p>
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
                            <span>${{ number_format($line->line_total, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                
                @if ($can('orders', 'edit'))
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row gap-3">
                        @if(!$isPaid)
                            <form method="POST" action="{{ route('cashier.orders.pay', $order) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors shadow-sm flex justify-center items-center gap-2">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Mark Paid
                                </button>
                            </form>
                        @endif

                        @if(!$isClosed)
                            <form method="POST" action="{{ route('cashier.orders.close', $order) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors shadow-sm flex justify-center items-center gap-2 {{ $isFullyReady ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50' }}">
                                    @if($isFullyReady)
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Pick Up / Close
                                    @else
                                        Pick Up / Close
                                    @endif
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
                <p class="text-slate-500 mt-1 max-w-sm">All orders are fully paid and picked up!</p>
            </div>
        @endforelse
    </div>
@endsection

