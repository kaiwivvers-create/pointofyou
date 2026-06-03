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
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col gap-3">
                        @if(!$isClosed)
                            <div class="flex gap-2">
                                @if($isFullyReady)
                                <form method="POST" action="{{ route('admin.pickup-station.close', $order) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors shadow-sm flex justify-center items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Pick Up
                                    </button>
                                </form>
                                @endif
                                @if(!$isPaid && $order->order_type === 'dine_in')
                                <button onclick="openPaymentModal({{ $order->id }}, {{ $order->total }})" class="flex-1 font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors shadow-sm flex justify-center items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white">
                                    Pay Now
                                </button>
                                @endif
                            </div>
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
</div>

<!-- Payment Modal -->
<div id="payment-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-slate-900">Select Payment Method</h3>
            <button onclick="closePaymentModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div class="space-y-3 mb-6">
            <button onclick="processPayment('cash')" class="w-full p-4 rounded-xl border-2 border-slate-200 hover:border-emerald-500 hover:bg-emerald-50 transition-colors flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                    <svg class="size-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div class="text-left">
                    <p class="font-bold text-slate-900">Cash</p>
                    <p class="text-sm text-slate-500">Pay with cash</p>
                </div>
            </button>
            
            <button onclick="processPayment('card')" class="w-full p-4 rounded-xl border-2 border-slate-200 hover:border-blue-500 hover:bg-blue-50 transition-colors flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="size-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <div class="text-left">
                    <p class="font-bold text-slate-900">Card</p>
                    <p class="text-sm text-slate-500">Pay with card</p>
                </div>
            </button>
        </div>
        
        <div class="flex justify-between items-center p-4 bg-slate-50 rounded-xl">
            <span class="font-semibold text-slate-900">Amount:</span>
            <span id="modal-amount" class="text-2xl font-bold text-emerald-600">$0.00</span>
        </div>
    </div>
</div>

<script>
    let currentOrderId = null;
    let currentAmount = 0;

    function openPaymentModal(orderId, amount) {
        currentOrderId = orderId;
        currentAmount = amount;
        document.getElementById('modal-amount').textContent = '$' + amount.toFixed(2);
        document.getElementById('payment-modal').classList.remove('hidden');
        document.getElementById('payment-modal').classList.add('flex');
    }

    function closePaymentModal() {
        document.getElementById('payment-modal').classList.add('hidden');
        document.getElementById('payment-modal').classList.remove('flex');
        currentOrderId = null;
        currentAmount = 0;
    }

    function processPayment(paymentMethod) {
        if (!currentOrderId) return;

        fetch('{{ route('admin.current-orders.pay', ':id') }}'.replace(':id', currentOrderId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                payment_method: paymentMethod,
                amount_paid: currentAmount
            })
        })
        .then(response => response.json())
        .then(data => {
            closePaymentModal();
            alert('Payment successful!');
            setTimeout(() => location.reload(), 1000);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Payment failed. Please try again.');
        });
    }
</script>
@endsection
