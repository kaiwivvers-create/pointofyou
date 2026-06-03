@extends('layouts.staff')

@section('title', 'Current Orders')

@section('content')
@php
    $paymentSettings = \App\Models\PaymentSettings::getSettings();
@endphp
<div class="space-y-6">
    <div class="flex justify-between items-end">
        <div>
            <h1 class="font-display text-3xl font-bold text-slate-900 tracking-tight">Current Orders</h1>
            <p class="text-slate-500 mt-1">Live feed of active orders being prepared.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
            <span class="text-sm font-semibold text-slate-600">Auto-refreshing</span>
        </div>
    </div>

    <x-flash />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($activeOrders as $order)
            <div class="bg-white rounded-3xl shadow-sm border {{ $order->is_closed ? 'border-slate-200 opacity-60' : ($order->status->value === 'paid' ? 'border-emerald-200' : 'border-amber-200') }} overflow-hidden flex flex-col relative group {{ $order->is_closed ? 'grayscale' : '' }}">
                
                @if($order->status->value === 'paid')
                    <div class="absolute top-0 right-0 bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-bl-xl border-b border-l border-emerald-200 uppercase tracking-wider">
                        Paid
                    </div>
                @endif
                
                <div class="p-5 border-b border-slate-100 {{ $order->is_closed ? 'bg-slate-100' : ($order->status->value === 'paid' ? 'bg-emerald-50/50' : 'bg-amber-50/50') }}">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-display text-xl font-bold {{ $order->is_closed ? 'text-slate-400' : 'text-slate-900' }}">
                                @if($order->order_type === 'dine_in' && $order->cafeTable)
                                    Table {{ $order->cafeTable->name }}
                                @else
                                    Takeout
                                @endif
                            </h3>
                            <p class="text-xs font-medium {{ $order->is_closed ? 'text-slate-400' : 'text-slate-500' }} mt-1">
                                Order #{{ $order->id }} &middot; {{ $order->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            @if($order->isFullyReady())
                                <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded border border-indigo-200 uppercase tracking-wider">Ready</span>
                            @else
                                <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded border border-blue-200 uppercase tracking-wider">Preparing</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-5 flex-1 space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex items-start gap-4 p-3 rounded-2xl border {{ $item->is_ready ? 'bg-slate-50 border-slate-100 opacity-60' : 'bg-white border-slate-200 shadow-sm' }} transition-all">
                            <form action="{{ route('admin.current-orders.toggle-ready', $item->id) }}" method="POST" class="shrink-0 mt-0.5">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-6 h-6 rounded-md flex items-center justify-center border-2 transition-colors {{ $item->is_ready ? 'bg-emerald-500 border-emerald-500 text-white' : 'bg-white border-slate-300 text-transparent hover:border-emerald-400' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </form>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start gap-2">
                                    <h4 class="font-bold text-slate-900 text-sm {{ $item->is_ready ? 'line-through text-slate-500' : '' }}"><span class="text-amber-600 mr-1">{{ $item->quantity }}x</span> {{ $item->item_name }}</h4>
                                </div>
                                
                                @if($item->flavor)
                                    <div class="mt-1">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                            Flavor: {{ $item->flavor['name'] ?? '' }}
                                        </span>
                                    </div>
                                @endif
                                
                                @if(!empty($item->modifications))
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($item->modifications as $mod)
                                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                + {{ is_array($mod) ? $mod['name'] : $mod }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if($item->notes)
                                    <div class="mt-2 p-2 rounded-lg bg-amber-50 border border-amber-100 text-xs font-semibold text-amber-900 flex gap-1.5 items-start">
                                        <svg class="size-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                        <span class="break-words">{{ $item->notes }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 border-t border-slate-100 bg-slate-50 space-y-3">
                    @if(!$order->is_closed)

                        {{-- ── Lifecycle progress bar ────────────────── --}}
                        @php
                            $step1 = $order->isFullyReady();
                            $step2 = $order->is_picked_up;
                            $step3 = !$order->isPending(); // paid
                        @endphp
                        <div class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide mb-1">
                            <div class="flex-1 text-center {{ $step1 ? 'text-emerald-600' : 'text-slate-400' }}">
                                <div class="w-6 h-6 rounded-full border-2 {{ $step1 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 text-slate-400' }} flex items-center justify-center mx-auto mb-0.5 text-xs">
                                    @if($step1) ✓ @else 1 @endif
                                </div>
                                Chef
                            </div>
                            <div class="h-px flex-1 {{ $step1 ? 'bg-emerald-400' : 'bg-slate-200' }}"></div>
                            <div class="flex-1 text-center {{ $step2 ? 'text-emerald-600' : 'text-slate-400' }}">
                                <div class="w-6 h-6 rounded-full border-2 {{ $step2 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 text-slate-400' }} flex items-center justify-center mx-auto mb-0.5 text-xs">
                                    @if($step2) ✓ @else 2 @endif
                                </div>
                                Picked Up
                            </div>
                            <div class="h-px flex-1 {{ $step2 ? 'bg-emerald-400' : 'bg-slate-200' }}"></div>
                            <div class="flex-1 text-center {{ $step3 ? 'text-emerald-600' : 'text-slate-400' }}">
                                <div class="w-6 h-6 rounded-full border-2 {{ $step3 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 text-slate-400' }} flex items-center justify-center mx-auto mb-0.5 text-xs">
                                    @if($step3) ✓ @else 3 @endif
                                </div>
                                Paid
                            </div>
                            <div class="h-px flex-1 {{ $order->canClose() ? 'bg-emerald-400' : 'bg-slate-200' }}"></div>
                            <div class="flex-1 text-center {{ $order->canClose() ? 'text-emerald-600' : 'text-slate-400' }}">
                                <div class="w-6 h-6 rounded-full border-2 {{ $order->canClose() ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 text-slate-400' }} flex items-center justify-center mx-auto mb-0.5 text-xs">4</div>
                                Done
                            </div>
                        </div>

                        {{-- ── Action buttons ────────────────────────── --}}
                        <div class="flex gap-2">

                            {{-- Pick Up: only enabled when chef is done --}}
                            @if(!$order->is_picked_up)
                                <button
                                    @if($step1) onclick="markAsPickedUp({{ $order->id }})" @else disabled @endif
                                    class="flex-1 py-2.5 px-1 rounded-xl font-bold text-sm transition-colors
                                        {{ $step1 ? 'bg-emerald-600 text-white hover:bg-emerald-700 cursor-pointer' : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}">
                                    Pick Up
                                </button>
                            @else
                                <div class="flex-1 py-2.5 px-1 rounded-xl font-bold text-sm text-center bg-emerald-100 text-emerald-700">
                                    ✓ Picked Up
                                </div>
                            @endif

                            {{-- Pay: always available (can pay anytime) --}}
                            @if($order->isPending())
                                <button onclick="openPaymentModal({{ $order->id }}, {{ $order->total }})"
                                    class="flex-1 py-2.5 px-1 rounded-xl font-bold bg-amber-600 text-white hover:bg-amber-700 transition-colors text-sm cursor-pointer">
                                    Pay
                                </button>
                            @else
                                <div class="flex-1 py-2.5 px-1 rounded-xl font-bold text-sm text-center bg-amber-100 text-amber-700">
                                    ✓ Paid
                                </div>
                            @endif

                            {{-- Add Items (dine-in only) --}}
                            <button onclick="openAddItemModal({{ $order->id }})"
                                class="flex-1 py-2.5 px-1 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors text-sm cursor-pointer">
                                + Items
                            </button>

                        </div>

                        {{-- Close button: only when all 3 steps done --}}
                        @if($order->canClose())
                            <button onclick="markAsClosed({{ $order->id }})"
                                class="w-full py-2.5 rounded-xl font-bold bg-slate-800 text-white hover:bg-slate-900 transition-colors text-sm cursor-pointer">
                                Close Order
                            </button>
                        @else
                            <button disabled
                                class="w-full py-2.5 rounded-xl font-bold bg-slate-100 text-slate-400 cursor-not-allowed text-sm"
                                title="Complete all steps first">
                                Close Order
                            </button>
                        @endif

                    @else
                        <div class="text-center py-2">
                            <span class="text-sm font-semibold text-slate-400">✓ Order Closed</span>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 flex flex-col items-center justify-center text-center bg-white rounded-3xl border border-dashed border-slate-300">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="size-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <h3 class="text-xl font-display font-bold text-slate-900">No active orders</h3>
                <p class="text-slate-500 mt-1 max-w-sm">The kitchen is clear. New orders will appear here automatically.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Payment Modal -->
<div id="payment-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all scale-95 opacity-0" id="payment-modal-content">
        <div class="p-4 sm:p-6 lg:p-8 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 shrink-0">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-700">Payment</p>
                <h1 class="font-display text-2xl sm:text-3xl font-semibold text-amber-950">Complete payment</h1>
                <p class="text-sm text-stone-500 mt-1">Order #<span id="modal-order-id"></span></p>
            </div>
            <button onclick="closePaymentModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="p-4 sm:p-6 lg:p-8 bg-[#fffaf3] border-b lg:border-b-0 lg:border-r border-amber-100">
                <div class="bg-white rounded-2xl border border-amber-100 p-4 sm:p-5 shadow-sm mb-4 sm:mb-5">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-400 mb-2">Order Total</p>
                    <p class="font-display text-4xl sm:text-5xl font-semibold text-amber-950" id="modal-total">$0.00</p>
                </div>
            </div>

            <div class="p-4 sm:p-6 lg:p-8 bg-white">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-400 mb-3 sm:mb-4">Payment method</p>

                <div class="grid grid-cols-2 gap-2 mb-4 sm:mb-5">
                    <button type="button" onclick="selectPaymentMethod('cash')" id="btn-cash" class="payment-method-btn p-3 rounded-2xl border-2 border-slate-200 hover:border-amber-500 hover:bg-amber-50 transition-all flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                            <svg class="size-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div class="text-left min-w-0">
                            <span class="block font-semibold text-slate-700 text-sm leading-tight">Cash</span>
                            <span class="text-[10px] text-stone-500 leading-tight block">Cash payment</span>
                        </div>
                    </button>
                    <button type="button" onclick="selectPaymentMethod('qr')" id="btn-qr" class="payment-method-btn p-3 rounded-2xl border-2 border-slate-200 hover:border-amber-500 hover:bg-amber-50 transition-all flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center shrink-0">
                            <svg class="size-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        </div>
                        <div class="text-left min-w-0">
                            <span class="block font-semibold text-slate-700 text-sm leading-tight">QR Code</span>
                            <span class="text-[10px] text-stone-500 leading-tight block">Scan to pay</span>
                        </div>
                    </button>
                    <button type="button" onclick="selectPaymentMethod('card')" id="btn-card" class="payment-method-btn p-3 rounded-2xl border-2 border-slate-200 hover:border-amber-500 hover:bg-amber-50 transition-all flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                            <svg class="size-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                        <div class="text-left min-w-0">
                            <span class="block font-semibold text-slate-700 text-sm leading-tight">Card</span>
                            <span class="text-[10px] text-stone-500 leading-tight block">Tap or insert</span>
                        </div>
                    </button>
                    <button type="button" onclick="selectPaymentMethod('transfer')" id="btn-transfer" class="payment-method-btn p-3 rounded-2xl border-2 border-slate-200 hover:border-amber-500 hover:bg-amber-50 transition-all flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                            <svg class="size-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                        </div>
                        <div class="text-left min-w-0">
                            <span class="block font-semibold text-slate-700 text-sm leading-tight">Transfer</span>
                            <span class="text-[10px] text-stone-500 leading-tight block">Bank transfer</span>
                        </div>
                    </button>
                </div>

                <div id="cash-form" class="payment-form hidden">
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 sm:p-5">
                        <label class="block text-sm font-bold text-amber-900 mb-2">Amount Received</label>
                        <div class="relative mb-4">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-amber-700">$</span>
                            <input type="number" id="cashReceived" oninput="calculateChange()" step="0.01" min="0"
                                class="w-full pl-8 pr-4 py-3 bg-white border-2 border-amber-200/70 rounded-xl focus:ring-0 focus:border-amber-500 font-bold text-lg text-amber-950 transition-colors placeholder:font-normal"
                                autocomplete="off" placeholder="0.00">
                        </div>
                        <div class="flex justify-between items-center text-amber-900">
                            <span class="font-bold">Change:</span>
                            <span class="text-xl font-display font-bold" id="cashChange">$0.00</span>
                        </div>
                    </div>
                </div>

                <div id="qr-form" class="payment-form hidden">
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 sm:p-5 text-center">
                        <div class="w-36 h-36 sm:w-44 sm:h-44 mx-auto bg-white border-2 border-slate-200 rounded-2xl mb-3 sm:mb-4 overflow-auto flex items-center justify-center">
                            @if($paymentSettings->qr_code_image)
                                <img src="{{ asset('storage/' . $paymentSettings->qr_code_image) }}" alt="QR Code" class="object-contain">
                            @else
                                <svg class="size-10 sm:size-14 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h4v4H4V4zm8 0h4v4h-4V4zM4 12h4v4H4v-4zm8 8v-4h4v4h-4zm4-8h4v4h-4v-4zm0-8h4v4h-4V4zM8 8h8v8H8V8z" />
                                </svg>
                            @endif
                        </div>
                        <p class="text-sm text-stone-600">{{ $paymentSettings->qr_code_instructions ?? 'Scan the QR code to complete payment.' }}</p>
                    </div>
                </div>

                <div id="card-form" class="payment-form hidden">
                    <div class="bg-slate-50 rounded-2xl p-4 sm:p-5 text-center">
                        <p class="text-sm text-slate-600">{{ $paymentSettings->card_instructions ?? 'Insert or tap your card.' }}</p>
                    </div>
                </div>

                <div id="transfer-form" class="payment-form hidden">
                    <div class="bg-slate-50 rounded-2xl p-4 sm:p-5">
                        <p class="text-sm text-slate-600 mb-4">{{ $paymentSettings->transfer_instructions ?? 'Transfer to the account details below' }}</p>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Bank:</span>
                                <span class="font-medium text-slate-900">{{ $paymentSettings->bank_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Account Number:</span>
                                <span class="font-medium text-slate-900">{{ $paymentSettings->account_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Account Name:</span>
                                <span class="font-medium text-slate-900">{{ $paymentSettings->account_name }}</span>
                            </div>
                            @if($paymentSettings->bank_address)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Bank Address:</span>
                                <span class="font-medium text-slate-900">{{ $paymentSettings->bank_address }}</span>
                            </div>
                            @endif
                            @if($paymentSettings->swift_code)
                            <div class="flex justify-between">
                                <span class="text-slate-500">SWIFT Code:</span>
                                <span class="font-medium text-slate-900">{{ $paymentSettings->swift_code }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <form id="adminPaymentForm" method="POST" action="{{ route('admin.current-orders.pay', ['order' => ':id']) }}" class="mt-6">
                    @csrf
                    <input type="hidden" id="paymentMethod" name="payment_method" value="">
                    <input type="hidden" id="orderId" name="order_id" value="">
                    <button type="button" onclick="submitAdminPayment()" class="w-full py-4 sm:py-5 rounded-2xl font-bold bg-amber-800 hover:bg-amber-900 text-amber-50 text-base sm:text-lg shadow-lg shadow-amber-900/20 transition-transform active:scale-95">
                        Pay now
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div id="receipt-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full mx-4 p-6 transform transition-all scale-95 opacity-0" id="receipt-modal-content">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="size-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-slate-900">Payment Successful!</h3>
            <p class="text-slate-500 mt-1">Order #<span id="receipt-order-id"></span></p>
        </div>
        
        <div class="border-t border-b border-slate-200 py-4 mb-6">
            <div class="flex justify-between mb-2">
                <span class="text-slate-600">Payment Method:</span>
                <span id="receipt-payment-method" class="font-semibold text-slate-900"></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">Amount Paid:</span>
                <span id="receipt-amount" class="font-bold text-emerald-600"></span>
            </div>
        </div>
        
        <button onclick="closeReceiptModal()" class="w-full py-3 rounded-xl font-bold bg-slate-900 text-white hover:bg-slate-800 transition-colors">
            Close
        </button>
    </div>
</div>

<!-- Add Item Modal -->
<div id="add-item-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-xl max-w-lg w-full overflow-hidden transform transition-all scale-95 opacity-0 max-h-[90vh] flex flex-col" id="add-item-modal-content">
        
        {{-- Header --}}
        <div class="p-6 pb-4 flex justify-between items-center border-b border-slate-100 shrink-0">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-blue-500">Order #<span id="add-item-order-label"></span></p>
                <h3 class="text-xl font-bold text-slate-800 mt-0.5">Add Item</h3>
            </div>
            <button type="button" onclick="closeAddItemModal()" class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition-colors">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-slate-100 shrink-0">
            <button onclick="switchTab('search')" id="tab-search" class="add-item-tab flex-1 py-3 text-xs font-semibold border-b-2 border-blue-500 text-blue-600 transition-colors flex flex-col items-center gap-1">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"></path></svg>
                Search
            </button>
            <button onclick="switchTab('scanner')" id="tab-scanner" class="add-item-tab flex-1 py-3 text-xs font-semibold border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-colors flex flex-col items-center gap-1">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm13-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V4a1 1 0 011-1zM4 17a1 1 0 01-1-1v-3a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4zm13-1h3a1 1 0 011-1v-3a1 1 0 01-1-1h-3a1 1 0 01-1 1v3a1 1 0 011 1z"></path></svg>
                Scan Barcode
            </button>
            <button onclick="switchTab('manual')" id="tab-manual" class="add-item-tab flex-1 py-3 text-xs font-semibold border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-colors flex flex-col items-center gap-1">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Enter Code
            </button>
        </div>

        {{-- Scrollable content --}}
        <div class="overflow-y-auto flex-1 p-6">

            {{-- Search Tab --}}
            <div id="panel-search">
                <div class="relative mb-3">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"></path></svg>
                    <input type="text" id="item-search-input" oninput="filterItems()" placeholder="Search menu items..." class="w-full pl-9 pr-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400">
                </div>
                <div id="item-search-results" class="space-y-2 max-h-64 overflow-y-auto">
                    {{-- Rendered by JS --}}
                </div>
            </div>

            {{-- Scanner Tab --}}
            <div id="panel-scanner" class="hidden">
                <div class="bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl overflow-hidden relative flex flex-col items-center justify-center min-h-[200px] mb-4">
                    <div id="qr-reader" class="w-full"></div>
                    <div id="scanner-placeholder" class="text-center p-6">
                        <svg class="size-14 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm13-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V4a1 1 0 011-1zM4 17a1 1 0 01-1-1v-3a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4zm13-1h3a1 1 0 011-1v-3a1 1 0 01-1-1h-3a1 1 0 01-1 1v3a1 1 0 011 1z"></path></svg>
                        <p class="text-slate-500 font-medium text-sm">Camera is off</p>
                        <p class="text-slate-400 text-xs mt-1">Click the button below to start</p>
                    </div>
                </div>
                <div id="scan-result-bar" class="hidden mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm">
                    <span class="text-green-700 font-semibold">Scanned:</span>
                    <span id="scanned-code-display" class="text-green-800 ml-1"></span>
                </div>
                <div class="flex gap-2">
                    <button type="button" id="start-scanner-btn" onclick="startScanner()" class="flex-1 py-3 px-4 rounded-xl font-bold bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors text-sm">
                        📷 Live Scanner
                    </button>
                    <button type="button" id="stop-scanner-btn" onclick="stopScanner()" class="hidden flex-1 py-3 px-4 rounded-xl font-bold bg-red-100 text-red-600 hover:bg-red-200 transition-colors text-sm">
                        ⏹ Stop Camera
                    </button>
                    
                    <input type="file" id="barcode-image-file" accept="image/*" capture="environment" class="hidden" onchange="scanFromFile(event)">
                    <button type="button" onclick="document.getElementById('barcode-image-file').click()" class="flex-1 py-3 px-4 rounded-xl font-bold bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors text-sm">
                        📸 Take Photo
                    </button>
                </div>
                </div>
                {{-- Scanner quantity + submit --}}
                <div id="scanner-submit-form" class="hidden mt-4 space-y-3">
                    <input type="hidden" id="scanned-barcode-value">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Quantity</label>
                        <input type="number" id="scanner-quantity" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-300" value="1" min="1">
                    </div>
                    <button type="button" onclick="submitScannedItem()" class="w-full py-3 rounded-2xl font-bold bg-blue-600 hover:bg-blue-700 text-white text-sm shadow-md transition-transform active:scale-95">
                        Add to Order
                    </button>
                </div>
            </div>

            {{-- Manual Entry Tab --}}
            <div id="panel-manual" class="hidden">
                <p class="text-sm text-slate-500 mb-4">Type in a barcode number or any custom code directly. Useful when a scanner isn't available or the barcode is damaged.</p>
                <form id="addItemForm" onsubmit="submitAddItem(event)" class="space-y-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Barcode / Code</label>
                        <input type="text" id="manual-barcode" name="barcode" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-300 focus:border-blue-400" placeholder="e.g. 1234567890">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Quantity</label>
                        <input type="number" id="add-item-quantity" name="quantity" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-300 focus:border-blue-400" value="1" min="1" required>
                    </div>
                    <button type="submit" class="w-full py-3 rounded-2xl font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-md transition-transform active:scale-95">
                        Add to Order
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    const allMenuItems = @json($searchableItemsJson);
</script>



<script>
    let currentOrderId = null;
    let currentAmount = 0;
    let selectedPaymentMethod = null;

    function openPaymentModal(orderId, amount) {
        currentOrderId = orderId;
        currentAmount = amount;
        selectedPaymentMethod = null;
        document.getElementById('modal-order-id').textContent = orderId;
        document.getElementById('modal-total').textContent = '$' + amount.toFixed(2);
        document.getElementById('orderId').value = orderId;
        document.getElementById('adminPaymentForm').action = '{{ route('admin.current-orders.pay', ['order' => ':id']) }}'.replace(':id', orderId);
        document.getElementById('payment-modal').classList.remove('hidden');
        document.getElementById('payment-modal').classList.add('flex');
        
        setTimeout(() => {
            document.getElementById('payment-modal-content').classList.remove('scale-95', 'opacity-0');
            document.getElementById('payment-modal-content').classList.add('scale-100', 'opacity-100');
        }, 10);
        
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('border-amber-500', 'bg-amber-50');
            btn.classList.add('border-slate-200');
        });
        
        document.getElementById('cashReceived').value = '';
        calculateChange();
        
        selectPaymentMethod('cash');
    }

    function closePaymentModal() {
        document.getElementById('payment-modal-content').classList.remove('scale-100', 'opacity-100');
        document.getElementById('payment-modal-content').classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            document.getElementById('payment-modal').classList.add('hidden');
            document.getElementById('payment-modal').classList.remove('flex');
            currentOrderId = null;
            currentAmount = 0;
            selectedPaymentMethod = null;
        }, 200);
    }

    function selectPaymentMethod(method) {
        selectedPaymentMethod = method;
        document.getElementById('paymentMethod').value = method;

        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('border-amber-500', 'bg-amber-50');
            btn.classList.add('border-slate-200');
        });

        document.getElementById('btn-' + method).classList.add('border-amber-500', 'bg-amber-50');
        document.getElementById('btn-' + method).classList.remove('border-slate-200');

        document.querySelectorAll('.payment-form').forEach(form => form.classList.add('hidden'));
        document.getElementById(method + '-form').classList.remove('hidden');
    }

    async function submitAdminPayment() {
        const form = document.getElementById('adminPaymentForm');
        const formData = new FormData(form);

        if (!selectedPaymentMethod) {
            alert('Select a payment method first.');
            return;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    payment_method: selectedPaymentMethod,
                    amount_paid: currentAmount
                })
            });

            const contentType = response.headers.get('content-type');
            
            if (!response.ok) {
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    alert(data.message || 'Payment failed. Please try again.');
                } else {
                    alert('Payment failed. Please try again.');
                }
                return;
            }

            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                closePaymentModal();
                alert('Payment successful!');
                setTimeout(() => location.reload(), 1000);
            } else {
                closePaymentModal();
                alert('Payment successful!');
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Payment failed. Please try again.');
        }
    }

    function calculateChange() {
        const received = parseFloat(document.getElementById('cashReceived').value) || 0;
        const change = Math.max(0, received - currentAmount);
        document.getElementById('cashChange').textContent = '$' + change.toFixed(2);
    }

    // ─── Pick Up ──────────────────────────────────────────────────
    async function markAsPickedUp(orderId) {
        if (!confirm('Mark this order as picked up?')) return;
        try {
            const res = await fetch('{{ route('admin.current-orders.pickup', ':id') }}'.replace(':id', orderId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (!res.ok) { alert(data.message || 'Failed.'); return; }
            location.reload();
        } catch(e) {
            alert('Failed to mark as picked up.');
        }
    }

    // ─── Close Order ──────────────────────────────────────────────
    function markAsClosed(orderId) {
        if (!confirm('Close this order? This will hide it from the current orders list.')) return;
        fetch('{{ route('admin.current-orders.close', ':id') }}'.replace(':id', orderId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.message && data.message.includes('Cannot')) {
                alert(data.message);
            } else {
                location.reload();
            }
        })
        .catch(() => alert('Failed to close order.'));
    }

    // Auto-refresh every 10 seconds
    let refreshTimer = setTimeout(function() {
        if (document.getElementById('add-item-modal').classList.contains('hidden') && document.getElementById('payment-modal').classList.contains('hidden')) {
            window.location.reload();
        } else {
            // If modal is open, try again in 10s without reloading now
            refreshTimer = setTimeout(arguments.callee, 10000);
        }
    }, 10000);

    // Reset timer on visibility change to prevent spamming server in background
    document.addEventListener("visibilitychange", function() {
        if (document.visibilityState === 'visible') {
            clearTimeout(refreshTimer);
            refreshTimer = setTimeout(function() {
                if (document.getElementById('add-item-modal').classList.contains('hidden') && document.getElementById('payment-modal').classList.contains('hidden')) {
                    window.location.reload();
                } else {
                    refreshTimer = setTimeout(arguments.callee, 10000);
                }
            }, 10000);
        } else {
            clearTimeout(refreshTimer);
        }
    });
</script>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrcodeScanner = null;
    let addModalOrderId = null;
    const addItemUrl = '{{ route('admin.current-orders.add-item', ':id') }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ─── Tab switching ────────────────────────────────────────────
    function switchTab(tab) {
        ['search', 'scanner', 'manual'].forEach(t => {
            document.getElementById('panel-' + t).classList.add('hidden');
            const btn = document.getElementById('tab-' + t);
            btn.classList.remove('border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-slate-400');
        });
        document.getElementById('panel-' + tab).classList.remove('hidden');
        const active = document.getElementById('tab-' + tab);
        active.classList.add('border-blue-500', 'text-blue-600');
        active.classList.remove('border-transparent', 'text-slate-400');

        // Stop scanner if leaving scanner tab
        if (tab !== 'scanner') stopScanner();
    }

    // ─── Open / Close modal ───────────────────────────────────────
    function openAddItemModal(orderId) {
        addModalOrderId = orderId;
        document.getElementById('add-item-order-label').textContent = orderId;
        document.getElementById('manual-barcode').value = '';
        document.getElementById('add-item-quantity').value = '1';
        document.getElementById('item-search-input').value = '';
        renderItems('');

        switchTab('search');

        const modal = document.getElementById('add-item-modal');
        const content = document.getElementById('add-item-modal-content');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeAddItemModal() {
        stopScanner();
        const modal = document.getElementById('add-item-modal');
        const content = document.getElementById('add-item-modal-content');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }

    // ─── Item Search ──────────────────────────────────────────────
    function renderItems(query) {
        const container = document.getElementById('item-search-results');
        const filtered = query.trim() === ''
            ? allMenuItems
            : allMenuItems.filter(i =>
                i.name.toLowerCase().includes(query.toLowerCase()) ||
                (i.category && i.category.toLowerCase().includes(query.toLowerCase()))
            );

        if (filtered.length === 0) {
            container.innerHTML = '<p class="text-center text-slate-400 text-sm py-6">No items found</p>';
            return;
        }

        const placeholderSvg = `<div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
            <svg class="size-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>`;

        container.innerHTML = filtered.map(item => `
            <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50 transition-colors">
                ${item.image
                    ? `<img src="${item.image}" alt="${item.name}" class="w-10 h-10 rounded-lg object-cover shrink-0 bg-slate-100">`
                    : placeholderSvg
                }
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-800 text-sm truncate">${item.name}</p>
                    <p class="text-xs text-slate-400">${item.category || 'Menu Item'} &middot; $${item.price.toFixed(2)}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <input type="number" value="1" min="1" id="qty-${item.id}"
                        class="w-14 text-center border border-slate-200 rounded-lg py-1.5 text-sm focus:ring-2 focus:ring-blue-300">
                    <button onclick="submitItemById(${item.id}, 'qty-${item.id}')"
                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors">
                        Add
                    </button>
                </div>
            </div>
        `).join('');
    }

    function filterItems() {
        renderItems(document.getElementById('item-search-input').value);
    }

    async function submitItemById(itemId, qtyInputId) {
        const qty = parseInt(document.getElementById(qtyInputId).value) || 1;
        // Find item in the unified list to get its type
        const item = allMenuItems.find(i => i.id === itemId);
        const type = item ? item.type : 'menu_item';

        const payload = { quantity: qty, item_type: type };
        if (type === 'menu_item') payload.menu_item_id = itemId;
        else if (type === 'gift')      payload.gift_id = itemId;
        else if (type === 'product')   payload.product_id = itemId;

        await doAddItem(payload);
    }

    // ─── Barcode Scanner ──────────────────────────────────────────
    function startScanner() {
        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5Qrcode("qr-reader");
        }

        const onSuccess = (decodedText) => {
            document.getElementById('scanned-barcode-value').value = decodedText;
            document.getElementById('scanned-code-display').textContent = decodedText;
            document.getElementById('scan-result-bar').classList.remove('hidden');
            document.getElementById('scanner-submit-form').classList.remove('hidden');
            stopScanner();
        };

        document.getElementById('scanner-placeholder').classList.add('hidden');
        document.getElementById('start-scanner-btn').classList.add('hidden');
        document.getElementById('stop-scanner-btn').classList.remove('hidden');

        html5QrcodeScanner.start(
            { facingMode: "environment" }, 
            { 
                fps: 20, 
                qrbox: { width: 300, height: 150 }, // Rectangular box works better for 1D barcodes
                disableFlip: false // Helps with some mirrored camera feeds
            }, 
            onSuccess
        ).catch(err => {
            alert('Could not start camera: ' + err);
            stopScanner();
        });
    }

    function stopScanner() {
        if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
            html5QrcodeScanner.stop().then(() => html5QrcodeScanner.clear()).catch(() => {});
        }
        const ph = document.getElementById('scanner-placeholder');
        if (ph) ph.classList.remove('hidden');
        const startBtn = document.getElementById('start-scanner-btn');
        if (startBtn) startBtn.classList.remove('hidden');
        const stopBtn = document.getElementById('stop-scanner-btn');
        if (stopBtn) stopBtn.classList.add('hidden');
    }

    async function submitScannedItem() {
        const barcode = document.getElementById('scanned-barcode-value').value;
        const qty = parseInt(document.getElementById('scanner-quantity').value) || 1;
        if (!barcode) { alert('No barcode scanned.'); return; }
        await doAddItem({ barcode, quantity: qty });
    }

    function scanFromFile(event) {
        const file = event.target.files[0];
        if (!file) return;

        stopScanner();
        document.getElementById('scanner-placeholder').innerHTML = '<p class="text-blue-500 font-bold py-6">Analyzing photo...</p>';

        const tempScanner = new Html5Qrcode("qr-reader");
        tempScanner.scanFile(file, true)
            .then(decodedText => {
                document.getElementById('scanned-barcode-value').value = decodedText;
                document.getElementById('scanned-code-display').textContent = decodedText;
                document.getElementById('scan-result-bar').classList.remove('hidden');
                document.getElementById('scanner-submit-form').classList.remove('hidden');
                
                document.getElementById('scanner-placeholder').innerHTML = '<p class="text-green-500 font-bold py-6">Found Barcode!</p>';
                document.getElementById('scanner-placeholder').classList.remove('hidden');
                tempScanner.clear();
            })
            .catch(err => {
                alert('Could not detect a barcode in that photo. Try making sure the barcode is in focus and well lit.');
                document.getElementById('scanner-placeholder').innerHTML = '<p class="text-red-500 font-bold py-6">No barcode found</p>';
                document.getElementById('scanner-placeholder').classList.remove('hidden');
                tempScanner.clear();
            });
            
        // Reset file input so same file can be selected again if needed
        event.target.value = '';
    }

    // ─── Manual entry ─────────────────────────────────────────────
    async function submitAddItem(e) {
        e.preventDefault();
        const barcode = document.getElementById('manual-barcode').value.trim();
        const quantity = parseInt(document.getElementById('add-item-quantity').value) || 1;
        if (!barcode) { alert('Please enter a code.'); return; }
        await doAddItem({ barcode, quantity });
    }

    // ─── Shared API call ──────────────────────────────────────────
    async function doAddItem(payload) {
        try {
            const url = addItemUrl.replace(':id', addModalOrderId);
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (!response.ok) {
                alert(data.message || 'Failed to add item.');
                return;
            }
            alert(data.message || 'Item added!');
            closeAddItemModal();
            location.reload();
        } catch (err) {
            console.error(err);
            alert('Failed to add item. Please try again.');
        }
    }
</script>
@endsection
