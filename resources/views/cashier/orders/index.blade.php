@extends('layouts.staff')

@section('title', 'Cashier')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Cashier</h1>
            <p class="staff-page-subtitle">Mark orders as paid when customers pay at the counter.</p>
        </div>
    </div>

    <x-flash />

    <h2 class="font-display text-xl font-semibold text-amber-950 mb-4">Waiting for payment</h2>

    @forelse ($pendingOrders as $order)
        <div class="staff-order-card">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                <div>
                    <p class="font-display text-lg font-semibold text-amber-950">Order #{{ $order->id }}</p>
                    <p class="text-sm font-semibold text-amber-800 mt-1">{{ $order->cafeTable->name }}</p>
                    <p class="text-xs text-stone-500 mt-1">{{ $order->created_at->diffForHumans() }}</p>
                </div>
                <p class="font-display text-2xl font-semibold text-amber-950">${{ number_format($order->total, 2) }}</p>
            </div>
            <ul class="text-sm text-stone-600 space-y-1.5 mb-5 border-t border-amber-100 pt-4">
                @foreach ($order->items as $line)
                    <li class="flex justify-between gap-4">
                        <span>{{ $line->quantity }}× {{ $line->item_name }}</span>
                        <span class="font-medium text-stone-800">${{ number_format($line->line_total, 2) }}</span>
                    </li>
                @endforeach
            </ul>
            <form method="POST" action="{{ route('cashier.orders.pay', $order) }}">
                @csrf
                <button type="submit" class="staff-btn-success w-full sm:w-auto">Mark as paid</button>
            </form>
        </div>
    @empty
        <div class="staff-card p-12 text-center mb-10">
            <p class="text-4xl mb-3">✨</p>
            <p class="text-stone-600 font-medium">No orders waiting — you're all caught up!</p>
        </div>
    @endforelse

    @if ($recentPaid->isNotEmpty())
        <h2 class="font-display text-xl font-semibold text-amber-950 mb-4 mt-10">Recently paid</h2>
        <div class="staff-table-wrap">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Table</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentPaid as $order)
                        <tr>
                            <td class="font-medium text-amber-950">#{{ $order->id }}</td>
                            <td>{{ $order->cafeTable->name }}</td>
                            <td class="text-right font-semibold text-emerald-700">${{ number_format($order->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
