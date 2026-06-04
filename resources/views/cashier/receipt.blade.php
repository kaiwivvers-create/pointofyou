@php
    $brandSettings = \App\Models\BrandSettings::getSettings();
@endphp

<div class="receipt-header">
    <h2 class="text-lg font-bold">{{ $brandSettings->app_name ?? 'Point of You' }}</h2>
    <p class="text-sm text-slate-600">{{ $brandSettings->landing_kicker ?? 'Cafe & Bakery' }}</p>
    <p class="text-xs text-slate-500 mt-2">Order #{{ $order->order_number }}</p>
    <p class="text-xs text-slate-500">{{ $order->paid_at ? $order->paid_at->format('M d, Y H:i') : now()->format('M d, Y H:i') }}</p>
    @if($order->cafeTable)
        <p class="text-xs text-slate-500">Table: {{ $order->cafeTable->name }}</p>
    @endif
</div>

<div class="mt-4">
    <h3 class="text-sm font-semibold border-b border-slate-300 pb-2 mb-2">Items</h3>
@foreach($order->items as $item)
        <div class="receipt-item">
            <span>{{ $item->item_name }} x{{ $item->quantity }}</span>
            <span>${{ number_format($item->subtotal, 2) }}</span>
        </div>
    @endforeach
</div>

@if($order->adjustments->isNotEmpty())
    <div class="mt-4">
        <h3 class="text-sm font-semibold border-b border-slate-300 pb-2 mb-2">Adjustments</h3>
        @foreach($order->adjustments as $adjustment)
            <div class="receipt-item">
                <span>{{ $adjustment->label }} ({{ ucfirst($adjustment->type) }})</span>
                <span class="{{ $adjustment->type === 'discount' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $adjustment->type === 'discount' ? '-' : '+' }}${{ number_format($adjustment->amount, 2) }}
                </span>
            </div>
        @endforeach
    </div>
@endif

<div class="receipt-total">
    <div class="receipt-item">
        <span>Subtotal</span>
        <span>${{ number_format($order->total, 2) }}</span>
    </div>
    <div class="receipt-item">
        <span>Payment Method</span>
        <span>{{ $order->payment_method ? ucfirst($order->payment_method) : 'Pending' }}</span>
    </div>
    <div class="receipt-item">
        <span>Paid By</span>
        <span>{{ $order->cashier?->name ?? ($order->payment_method ? 'N/A' : 'Pending') }}</span>
    </div>
    <div class="receipt-item text-lg">
        <span>Total</span>
        <span>${{ number_format($order->total, 2) }}</span>
    </div>
</div>

<div class="mt-4 text-center text-xs text-slate-500">
    <p>Thank you for your order!</p>
    <p class="mt-2">Powered by {{ $brandSettings->app_name ?? 'Point of You' }}</p>
</div>
