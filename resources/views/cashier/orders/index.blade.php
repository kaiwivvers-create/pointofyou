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
    @php
        $paymentSettings = \App\Models\PaymentSettings::getSettings();
        \Log::info('Payment settings loaded', [
            'qr_code_image' => $paymentSettings->qr_code_image ?? 'null',
            'qr_code_instructions' => $paymentSettings->qr_code_instructions ?? 'null',
            'card_instructions' => $paymentSettings->card_instructions ?? 'null',
            'bank_name' => $paymentSettings->bank_name ?? 'null',
            'account_number' => $paymentSettings->account_number ?? 'null',
        ]);
        $promos = \App\Models\Promo::where('is_active', true)->with(['buyItem', 'getItem'])->get();
        $moneyMadeToday = \App\Models\Order::whereDate('created_at', today())
            ->where('status', \App\Enums\OrderStatus::Paid)
            ->sum('total');
    @endphp
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Cashier Dashboard</h1>
            <p class="staff-page-subtitle">Manage payments and order handoffs</p>
        </div>
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-2">
            <p class="text-xs text-emerald-600 font-medium uppercase tracking-wider">Money Made Today</p>
            <p class="text-2xl font-bold text-emerald-700">${{ number_format($moneyMadeToday, 2) }}</p>
        </div>
    </div>

    <x-flash />

    <h2 class="font-sans text-xl font-semibold text-slate-900 mb-4">Active Orders</h2>
    <p class="text-sm text-slate-500 mb-6">Orders disappear when they are both <strong>Paid</strong> and <strong>Picked Up / Closed</strong>.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4">
        @forelse ($activeOrders as $order)
            @php
                $isPaid = $order->status->value === 'paid';
                $isClosed = $order->isClosed();
                $isFullyReady = $order->isFullyReady();
            @endphp
            
            <div class="bg-white rounded-xl shadow-sm border {{ $isPaid ? 'border-emerald-200' : 'border-amber-200' }} overflow-hidden flex flex-col relative group p-2 sm:p-5">
                <div class="p-2 sm:p-5 border-b border-slate-100 flex justify-between items-start {{ $isPaid ? 'bg-emerald-50/50' : 'bg-amber-50/50' }}">
                    <div>
                        <h3 class="font-display text-sm sm:text-xl font-bold text-slate-900">
                            @if($order->order_type === 'dine_in' && $order->cafeTable)
                                Table {{ $order->cafeTable->name }}
                            @else
                                Takeout
                            @endif
                        </h3>
                        <p class="text-[10px] sm:text-xs font-medium text-slate-500 mt-1">
                            Order #{{ $order->id }} &middot; {{ $order->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-1 sm:gap-2">
                        <p class="font-sans text-sm sm:text-xl font-bold text-slate-900">${{ number_format($order->total, 2) }}</p>
                        <div class="flex gap-1 sm:gap-2 flex-wrap justify-end">
                            <!-- Payment Status Badge -->
                            @if($isPaid)
                                <span class="bg-emerald-100 text-emerald-800 text-[8px] sm:text-[10px] font-bold px-1 sm:px-2 py-0.5 rounded border border-emerald-200 uppercase tracking-wider">Paid</span>
                            @else
                                <span class="bg-amber-100 text-amber-800 text-[8px] sm:text-[10px] font-bold px-1 sm:px-2 py-0.5 rounded border border-amber-200 uppercase tracking-wider">Unpaid</span>
                            @endif

                            <!-- Kitchen Status Badge -->
                            @if($isClosed)
                                <span class="bg-stone-100 text-stone-600 text-[8px] sm:text-[10px] font-bold px-1 sm:px-2 py-0.5 rounded border border-stone-200 uppercase tracking-wider">Picked Up</span>
                            @elseif($isFullyReady)
                                <span class="bg-indigo-100 text-indigo-700 text-[8px] sm:text-[10px] font-bold px-1 sm:px-2 py-0.5 rounded border border-indigo-200 uppercase tracking-wider">Ready</span>
                            @else
                                <span class="bg-blue-100 text-blue-700 text-[8px] sm:text-[10px] font-bold px-1 sm:px-2 py-0.5 rounded border border-blue-200 uppercase tracking-wider">Preparing</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-2 sm:p-5 flex-1 space-y-1 sm:space-y-3">
                    <button onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')" class="sm:hidden text-[10px] font-medium text-slate-600 hover:text-slate-900 flex items-center gap-1 w-full">
                        <svg class="size-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        Show Items
                    </button>
                    <div class="space-y-1 sm:space-y-3 hidden sm:block">
                        @foreach ($order->items as $line)
                            <div class="flex justify-between gap-2 sm:gap-4 text-[10px] sm:text-sm {{ $line->is_ready ? 'text-slate-400 line-through' : 'text-slate-700 font-medium' }}">
                                <span>{{ $line->quantity }}× {{ $line->item_name }}</span>
                                <span>${{ number_format($line->line_total, 2) }}</span>
                            </div>
                    @endforeach
                </div>
                
                    @if($order->adjustments->isNotEmpty())
                        <div class="pt-2 border-t border-slate-100 space-y-2">
                            <button onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')" class="sm:hidden text-[10px] font-medium text-slate-600 hover:text-slate-900 flex items-center gap-1 w-full">
                                <svg class="size-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                                Show Adjustments
                            </button>
                            <div class="space-y-2 hidden sm:block">
                                @foreach ($order->adjustments as $adjustment)
                                    <div class="flex justify-between gap-2 sm:gap-4 text-[10px] sm:text-sm {{ $adjustment->type === 'discount' ? 'text-emerald-700' : 'text-slate-700' }}">
                                        <span class="flex items-center gap-2">
                                            <span>{{ $adjustment->label }}</span>
                                            @if($adjustment->source === 'auto_supply')
                                                <span class="text-[8px] sm:text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-700 px-1 sm:px-2 py-0.5 rounded">Supply</span>
                                            @endif
                                        </span>
                                        <span class="flex items-center gap-2">
                                            <span>{{ $adjustment->type === 'discount' ? '-' : '+' }}${{ number_format($adjustment->amount, 2) }}</span>
                                            @if($can('orders', 'edit') && $adjustment->source === 'manual')
                                                <form method="POST" action="{{ route('cashier.orders.adjustments.destroy', [$order, $adjustment]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-[10px] sm:text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 px-1 sm:px-2 py-0.5 sm:py-1 rounded border border-red-200 hover:border-red-300 transition-colors">Remove</button>
                                                </form>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                @if ($can('orders', 'edit'))
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row gap-3">
                        @if(!$isPaid)
                            <button type="button" onclick="openPaymentModal({{ $order->id }}, {{ $order->total }})" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors shadow-sm flex justify-center items-center gap-2">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Pay
                            </button>
                        @endif

                        @if(!$isClosed)
                            <form method="POST" action="{{ route('cashier.orders.close', $order) }}" class="flex-1" onsubmit="return validateOrderReady({{ $isFullyReady }})">
                                @csrf
                                <button type="submit" class="w-full font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors shadow-sm flex justify-center items-center gap-2 {{ $isFullyReady ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-white border border-slate-300 hover:bg-slate-50 opacity-50 cursor-not-allowed' }} text-slate-900" {{ !$isFullyReady ? 'disabled' : '' }}>
                                    @if($isFullyReady)
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                    Pick Up / Close
                                </button>
                            </form>
                        @endif

                        <button type="button" onclick="openAdjustmentModal({{ $order->id }})" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors shadow-sm flex justify-center items-center gap-2 whitespace-nowrap">
                            Add Bill Item
                        </button>
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

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 transition-opacity duration-200">
        <div id="paymentModalContent" class="bg-white rounded-3xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0">
            <div class="p-6 border-b border-slate-100">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-display font-bold text-slate-900">Process Payment</h3>
                    <button onclick="closePaymentModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div class="bg-slate-50 rounded-2xl p-4 mb-6">
                    <p class="text-sm text-slate-500 mb-1">Total Amount</p>
                    <p class="text-3xl font-bold text-slate-900" id="modalTotal">$0.00</p>
                </div>

                <!-- Payment Method Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Select Payment Method</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="selectPaymentMethod('cash')" id="btn-cash" class="payment-method-btn p-4 rounded-xl border-2 border-slate-200 hover:border-emerald-500 hover:bg-emerald-50 transition-all text-center">
                            <svg class="size-8 mx-auto mb-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="font-medium text-slate-700">Cash</span>
                        </button>
                        <button type="button" onclick="selectPaymentMethod('qr')" id="btn-qr" class="payment-method-btn p-4 rounded-xl border-2 border-slate-200 hover:border-emerald-500 hover:bg-emerald-50 transition-all text-center">
                            <svg class="size-8 mx-auto mb-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            <span class="font-medium text-slate-700">QR Code</span>
                        </button>
                        <button type="button" onclick="selectPaymentMethod('card')" id="btn-card" class="payment-method-btn p-4 rounded-xl border-2 border-slate-200 hover:border-emerald-500 hover:bg-emerald-50 transition-all text-center">
                            <svg class="size-8 mx-auto mb-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <span class="font-medium text-slate-700">Card</span>
                        </button>
                        <button type="button" onclick="selectPaymentMethod('transfer')" id="btn-transfer" class="payment-method-btn p-4 rounded-xl border-2 border-slate-200 hover:border-emerald-500 hover:bg-emerald-50 transition-all text-center">
                            <svg class="size-8 mx-auto mb-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            <span class="font-medium text-slate-700">Transfer</span>
                        </button>
                    </div>
                </div>

                <!-- Cash Payment Form -->
                <div id="cash-form" class="payment-form hidden">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Amount Received</label>
                            <input type="number" id="amountReceived" step="0.01" min="0" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all text-lg" placeholder="0.00" oninput="calculateChange()">
                        </div>
                        <div id="change-display" class="bg-emerald-50 rounded-xl p-4 hidden">
                            <p class="text-sm text-emerald-600 mb-1">Change Due</p>
                            <p class="text-2xl font-bold text-emerald-700" id="changeAmount">$0.00</p>
                        </div>
                    </div>
                </div>

                <!-- QR Payment Form -->
                <div id="qr-form" class="payment-form hidden">
                    <div class="text-center py-6">
                        <div class="bg-white border-2 border-slate-200 rounded-xl p-4 inline-block mb-4">
                            <div class="w-48 h-48 bg-slate-100 flex items-center justify-center overflow-hidden">
                                @if($paymentSettings->qr_code_image)
                                    <img src="{{ asset('storage/' . $paymentSettings->qr_code_image) }}" alt="QR Code" class="w-full h-full object-contain">
                                @else
                                    <svg class="size-16 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                @endif
                            </div>
                        </div>
                        <p class="text-sm text-slate-500">{{ $paymentSettings->qr_code_instructions ?? 'Scan QR code to pay' }}</p>
                    </div>
                </div>

                <!-- Card Payment Form -->
                <div id="card-form" class="payment-form hidden">
                    <div class="text-center py-6">
                        <div class="bg-slate-50 rounded-xl p-6 mb-4">
                            <svg class="size-16 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <p class="text-sm text-slate-500">{{ $paymentSettings->card_instructions ?? 'Insert or tap card to complete payment' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Transfer Payment Form -->
                <div id="transfer-form" class="payment-form hidden">
                    <div class="text-center py-6">
                        <div class="bg-slate-50 rounded-xl p-6 mb-4">
                            <svg class="size-16 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            <p class="text-sm text-slate-500 mb-4">{{ $paymentSettings->transfer_instructions ?? 'Transfer to the following bank account:' }}</p>
                            <div class="text-left space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Bank:</span>
                                    <span class="font-medium text-slate-900">{{ $paymentSettings->bank_name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Account Number:</span>
                                    <span class="font-medium text-slate-900">{{ $paymentSettings->account_number ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Account Name:</span>
                                    <span class="font-medium text-slate-900">{{ $paymentSettings->account_name ?? 'N/A' }}</span>
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
                </div>

                <form id="paymentForm" method="POST" action="{{ route('cashier.orders.pay', ':orderId') }}" class="mt-6">
                    @csrf
                    <input type="hidden" id="paymentMethod" name="payment_method" value="">
                    <input type="hidden" id="amountPaid" name="amount_paid" value="">
                    <button type="submit" id="submitPayment" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-4 rounded-xl transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Complete Payment
                    </button>
                </form>

                <!-- Receipt Section -->
                <div id="receiptSection" class="mt-6 hidden">
                    <div class="bg-white border-2 border-slate-200 rounded-xl p-6">
                        <div class="text-center mb-4">
                            <h4 class="text-lg font-bold text-slate-900">Payment Receipt</h4>
                            <p class="text-sm text-slate-500">Order #<span id="receiptOrderId"></span></p>
                        </div>
                        <div class="space-y-2 text-sm border-b border-slate-200 pb-4 mb-4">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Date:</span>
                                <span class="text-slate-900 font-medium" id="receiptDate"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Payment Method:</span>
                                <span class="text-slate-900 font-medium capitalize" id="receiptMethod"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Total:</span>
                                <span class="text-slate-900 font-medium" id="receiptTotal"></span>
                            </div>
                            <div class="flex justify-between" id="receiptAmountPaidRow">
                                <span class="text-slate-600">Amount Paid:</span>
                                <span class="text-slate-900 font-medium" id="receiptAmountPaid"></span>
                            </div>
                            <div class="flex justify-between" id="receiptChangeRow">
                                <span class="text-slate-600">Change:</span>
                                <span class="text-emerald-600 font-bold" id="receiptChange"></span>
                            </div>
                        </div>
                        <div class="text-center">
                            <button onclick="openReceiptModal()" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                                <svg class="size-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Print Receipt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div id="receipt-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto transform scale-95 transition-transform duration-300" id="receipt-modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-slate-900">Receipt</h2>
                    <button onclick="closeReceiptModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="receipt-content">
                    <!-- Receipt content will be loaded here -->
                </div>
                <div class="mt-4 flex gap-2">
                    <button onclick="printModalReceipt()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg flex-1">Print Receipt</button>
                    <button onclick="closeReceiptModal()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium py-2 px-4 rounded-lg flex-1">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Adjustment Modal -->
    <div id="adjustmentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 transition-opacity duration-200">
        <div id="adjustmentModalContent" class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0">
            <div class="p-6 border-b border-slate-100 flex justify-between items-start gap-4">
                <div class="min-w-0">
                    <h3 class="text-xl font-display font-bold text-slate-900">Add Bill Item</h3>
                    <p class="text-sm text-slate-500 mt-1">Add a charge, discount, or stock-backed bill line.</p>
                </div>
                <button type="button" onclick="closeAdjustmentModal()" class="text-slate-400 hover:text-slate-600 transition-colors shrink-0">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="adjustmentForm" method="POST" action="" class="p-6">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <div class="lg:col-span-3 space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Label</label>
                            <input type="text" name="label" id="adjustmentLabel" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition-all bg-white" placeholder="Takeout box, spoon, discount, etc.">
                            <p class="mt-2 text-xs text-slate-500">This is what shows on the bill.</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700">Inventory Item</label>
                                    <p class="text-xs text-slate-500">Use this for stock-backed items like boxes, spoons, or extra packaging.</p>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 bg-slate-100 px-2 py-1 rounded-full">Optional</span>
                            </div>
                            <select name="product_id" id="adjustmentProduct" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition-all bg-white">
                                <option value="">No inventory link</option>
                                @foreach ($inventoryProducts as $product)
                                    <option value="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->selling_price }}" data-stock="{{ $product->stock_quantity }}" data-type="inventory">
                                        {{ $product->name }} — {{ $product->stock_quantity }} in stock
                                    </option>
                                @endforeach
                                @foreach ($promos as $promo)
                                    @if ($promo->rules && $promo->rules->count() > 0)
                                        @foreach ($promo->rules as $rule)
                                            <option value="promo-{{ $promo->id }}-{{ $rule->id }}" data-name="{{ $promo->title }}" data-price="{{ $promo->discount_value ?? 0 }}" data-type="promo" data-promo-type="{{ $promo->discount_type }}" data-promo-discount="{{ $promo->discount_value }}" data-promo-buy-item-id="{{ $rule->buy_item_id }}">
                                                {{ $promo->title }} — Buy {{ $rule->buy_quantity }} {{ $rule->buyItem?->name ?? 'item' }}, Get {{ $rule->get_quantity }} {{ $rule->getItem?->name ?? 'item' }}
                                            </option>
                                        @endforeach
                                    @endif
                                @endforeach
                            </select>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <button type="button" class="quick-bill-chip rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-all" data-label="Takeout box">Takeout box</button>
                                <button type="button" class="quick-bill-chip rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-all" data-label="Spoon">Spoon</button>
                                <button type="button" class="quick-bill-chip rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-all" data-label="Extra sauce">Extra sauce</button>
                                <button type="button" class="quick-bill-chip rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-all" data-label="Discount">Discount</button>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Type</label>
                            <select name="type" id="adjustmentType" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition-all bg-white">
                                <option value="charge">Charge</option>
                                <option value="discount">Discount</option>
                            </select>
                            <p class="mt-2 text-xs text-slate-500">Inventory-linked items are forced to charge.</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Quantity</label>
                            <input type="number" min="1" name="quantity" id="adjustmentQuantity" value="1" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition-all bg-white">
                            <p class="mt-2 text-xs text-slate-500">Use this for multiple boxes or spoons at once.</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Amount</label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="adjustmentAmount" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition-all bg-white" placeholder="0.00">
                            <p class="mt-2 text-xs text-slate-500">Auto-fills from the selected inventory item. Amount is locked when inventory is linked.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-end">
                    <button type="button" onclick="closeAdjustmentModal()" class="sm:w-auto w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 px-4 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="sm:w-auto w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-4 rounded-xl transition-colors">Save Bill Item</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentOrderId = null;
        let currentTotal = 0;
        let selectedMethod = null;
        let currentAdjustmentOrderId = null;

        function openPaymentModal(orderId, total) {
            currentOrderId = orderId;
            currentTotal = total;
            selectedMethod = null;

            document.getElementById('modalTotal').textContent = '$' + total.toFixed(2);
            document.getElementById('paymentForm').action = '{{ route('cashier.orders.pay', ':orderId') }}'.replace(':orderId', orderId);
            
            // Reset form
            document.getElementById('paymentMethod').value = '';
            document.getElementById('amountPaid').value = '';
            document.getElementById('amountReceived').value = '';
            document.getElementById('change-display').classList.add('hidden');
            document.getElementById('submitPayment').disabled = true;
            document.getElementById('receiptSection').classList.add('hidden');

            // Reset payment method buttons
            document.querySelectorAll('.payment-method-btn').forEach(btn => {
                btn.classList.remove('border-emerald-500', 'bg-emerald-50');
                btn.classList.add('border-slate-200');
            });

            // Hide all payment forms
            document.querySelectorAll('.payment-form').forEach(form => {
                form.classList.add('hidden');
            });

            // Show modal with animation
            const modal = document.getElementById('paymentModal');
            const modalContent = document.getElementById('paymentModalContent');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
            }, 10);
        }

        function openAdjustmentModal(orderId) {
            currentAdjustmentOrderId = orderId;
            const modal = document.getElementById('adjustmentModal');
            const modalContent = document.getElementById('adjustmentModalContent');
            const form = document.getElementById('adjustmentForm');
            form.action = '{{ route('cashier.orders.adjustments.store', ':orderId') }}'.replace(':orderId', orderId);
            form.reset();
            document.getElementById('adjustmentQuantity').value = 1;
            document.getElementById('adjustmentAmount').value = '';
            
            // Filter promos based on cart contents
            filterPromosByCart(orderId);
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
            }, 10);
        }

        async function filterPromosByCart(orderId) {
            try {
                const response = await fetch('{{ route('cashier.orders.cart-items', ':id') }}'.replace(':id', orderId));
                const cartItems = await response.json();
                
                const productSelect = document.getElementById('adjustmentProduct');
                const cartItemIds = cartItems.map(item => item.menu_item_id);
                
                Array.from(productSelect.options).forEach(option => {
                    const optionType = option.dataset.type;
                    if (optionType === 'promo') {
                        const promoBuyItemId = option.dataset.promoBuyItemId;
                        // Show promo if no buy requirement or if buy item is in cart
                        if (!promoBuyItemId || cartItemIds.includes(parseInt(promoBuyItemId))) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    }
                });
            } catch (error) {
                console.error('Error filtering promos:', error);
            }
        }

        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            const modalContent = document.getElementById('paymentModalContent');
            
            modalContent.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function closeAdjustmentModal() {
            const modal = document.getElementById('adjustmentModal');
            const modalContent = document.getElementById('adjustmentModalContent');

            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        document.getElementById('adjustmentProduct').addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const amountInput = document.getElementById('adjustmentAmount');
            const quantityInput = document.getElementById('adjustmentQuantity');
            const labelInput = document.getElementById('adjustmentLabel');

            if (!this.value) {
                // No inventory selected - enable amount editing
                amountInput.readOnly = false;
                amountInput.classList.remove('bg-slate-100', 'cursor-not-allowed');
                return;
            }

            // Inventory selected - disable amount editing
            amountInput.readOnly = true;
            amountInput.classList.add('bg-slate-100', 'cursor-not-allowed');

            // Auto-fill label with product name
            if (!labelInput.value) {
                labelInput.value = selected.dataset.name || '';
            }

            // Auto-fill amount with product price * quantity
            updateAmountFromInventory();
        });

        // Update amount when quantity changes (if inventory is selected)
        document.getElementById('adjustmentQuantity').addEventListener('input', function() {
            const productSelect = document.getElementById('adjustmentProduct');
            if (productSelect.value) {
                updateAmountFromInventory();
            }
        });

        function updateAmountFromInventory() {
            const productSelect = document.getElementById('adjustmentProduct');
            const selected = productSelect.options[productSelect.selectedIndex];
            const amountInput = document.getElementById('adjustmentAmount');
            const quantityInput = document.getElementById('adjustmentQuantity');

            const price = Number(selected.dataset.price || 0);
            const quantity = Number(quantityInput.value || 1);
            const total = price * quantity;

            amountInput.value = total.toFixed(2);
        }

        // Quick bill chip functionality
        document.querySelectorAll('.quick-bill-chip').forEach(chip => {
            chip.addEventListener('click', function() {
                const label = this.dataset.label;
                const labelInput = document.getElementById('adjustmentLabel');
                const productSelect = document.getElementById('adjustmentProduct');
                const amountInput = document.getElementById('adjustmentAmount');
                const typeSelect = document.getElementById('adjustmentType');

                // Remove selected state from all chips
                document.querySelectorAll('.quick-bill-chip').forEach(c => {
                    c.classList.remove('border-amber-500', 'bg-amber-50', 'text-amber-800');
                    c.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-700');
                });

                // Add selected state to clicked chip
                this.classList.remove('border-slate-200', 'bg-slate-50', 'text-slate-700');
                this.classList.add('border-amber-500', 'bg-amber-50', 'text-amber-800');

                // Set the label
                labelInput.value = label;

                // If discount chip, auto-change type to discount
                if (label.toLowerCase() === 'discount') {
                    typeSelect.value = 'discount';
                    // Trigger type change to filter dropdown
                    typeSelect.dispatchEvent(new Event('change'));
                } else {
                    typeSelect.value = 'charge';
                    // Trigger type change to filter dropdown
                    typeSelect.dispatchEvent(new Event('change'));
                }

                // Try to find a matching inventory item (only for charge type)
                if (label.toLowerCase() !== 'discount') {
                    let foundMatch = false;
                    for (let i = 0; i < productSelect.options.length; i++) {
                        const option = productSelect.options[i];
                        const productName = option.dataset.name || '';
                        const optionType = option.dataset.type || '';
                        
                        // Only match inventory items, not promos
                        if (optionType === 'inventory' && (productName.toLowerCase().includes(label.toLowerCase()) || label.toLowerCase().includes(productName.toLowerCase()))) {
                            productSelect.value = option.value;
                            // Trigger change event to auto-fill amount
                            productSelect.dispatchEvent(new Event('change'));
                            foundMatch = true;
                            break;
                        }
                    }

                    // If no match found, clear the product selection
                    if (!foundMatch) {
                        productSelect.value = '';
                        // Enable amount editing if no inventory linked
                        amountInput.readOnly = false;
                        amountInput.classList.remove('bg-slate-100', 'cursor-not-allowed');
                    }
                }
            });
        });

        // Remove chip glow when inventory dropdown changes
        document.getElementById('adjustmentProduct').addEventListener('change', function() {
            const selectedValue = this.value;
            const labelInput = document.getElementById('adjustmentLabel');

            // Check if any chip matches the selected product
            let chipMatches = false;
            document.querySelectorAll('.quick-bill-chip').forEach(chip => {
                const chipLabel = chip.dataset.label;
                const selected = this.options[this.selectedIndex];
                const productName = selected.dataset.name || '';

                if (productName.toLowerCase().includes(chipLabel.toLowerCase()) || chipLabel.toLowerCase().includes(productName.toLowerCase())) {
                    if (selectedValue) {
                        chipMatches = true;
                        // Keep this chip highlighted
                        chip.classList.remove('border-slate-200', 'bg-slate-50', 'text-slate-700');
                        chip.classList.add('border-amber-500', 'bg-amber-50', 'text-amber-800');
                    }
                } else {
                    // Remove highlight from non-matching chips
                    chip.classList.remove('border-amber-500', 'bg-amber-50', 'text-amber-800');
                    chip.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-700');
                }
            });

            // If no chip matches, remove all highlights
            if (!chipMatches) {
                document.querySelectorAll('.quick-bill-chip').forEach(chip => {
                    chip.classList.remove('border-amber-500', 'bg-amber-50', 'text-amber-800');
                    chip.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-700');
                });
            }
        });

        // Filter dropdown options based on type
        document.getElementById('adjustmentType').addEventListener('change', function() {
            const type = this.value;
            const productSelect = document.getElementById('adjustmentProduct');
            const currentValue = productSelect.value;

            // Show/hide options based on type
            Array.from(productSelect.options).forEach(option => {
                const optionType = option.dataset.type;
                if (type === 'discount') {
                    // Show promos, hide inventory
                    if (optionType === 'promo' || option.value === '') {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                } else {
                    // Show inventory, hide promos
                    if (optionType === 'inventory' || option.value === '') {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                }
            });

            // Reset selection if current value is hidden
            if (currentValue) {
                const currentOption = productSelect.querySelector(`option[value="${currentValue}"]`);
                if (currentOption && currentOption.style.display === 'none') {
                    productSelect.value = '';
                    // Enable amount editing
                    const amountInput = document.getElementById('adjustmentAmount');
                    amountInput.readOnly = false;
                    amountInput.classList.remove('bg-slate-100', 'cursor-not-allowed');
                }
            }
        });

        function selectPaymentMethod(method) {
            selectedMethod = method;
            document.getElementById('paymentMethod').value = method;

            // Update button styles
            document.querySelectorAll('.payment-method-btn').forEach(btn => {
                btn.classList.remove('border-emerald-500', 'bg-emerald-50');
                btn.classList.add('border-slate-200');
            });
            document.getElementById('btn-' + method).classList.add('border-emerald-500', 'bg-emerald-50');
            document.getElementById('btn-' + method).classList.remove('border-slate-200');

            // Show appropriate form
            document.querySelectorAll('.payment-form').forEach(form => {
                form.classList.add('hidden');
            });
            document.getElementById(method + '-form').classList.remove('hidden');

            // Enable submit button for non-cash methods
            if (method !== 'cash') {
                document.getElementById('submitPayment').disabled = false;
                document.getElementById('amountPaid').value = currentTotal;
            } else {
                document.getElementById('submitPayment').disabled = true;
            }
        }

        function calculateChange() {
            const amountReceived = parseFloat(document.getElementById('amountReceived').value) || 0;
            const change = amountReceived - currentTotal;

            if (amountReceived >= currentTotal) {
                document.getElementById('changeAmount').textContent = '$' + change.toFixed(2);
                document.getElementById('change-display').classList.remove('hidden');
                document.getElementById('submitPayment').disabled = false;
                document.getElementById('amountPaid').value = amountReceived;
            } else {
                document.getElementById('change-display').classList.add('hidden');
                document.getElementById('submitPayment').disabled = true;
            }
        }

        function showReceipt() {
            const amountPaid = parseFloat(document.getElementById('amountPaid').value) || currentTotal;
            const change = amountPaid - currentTotal;

            document.getElementById('receiptOrderId').textContent = currentOrderId;
            document.getElementById('receiptDate').textContent = new Date().toLocaleString();
            document.getElementById('receiptMethod').textContent = selectedMethod;
            document.getElementById('receiptTotal').textContent = '$' + currentTotal.toFixed(2);
            document.getElementById('receiptAmountPaid').textContent = '$' + amountPaid.toFixed(2);

            if (selectedMethod === 'cash' && change > 0) {
                document.getElementById('receiptChangeRow').classList.remove('hidden');
                document.getElementById('receiptChange').textContent = '$' + change.toFixed(2);
            } else {
                document.getElementById('receiptChangeRow').classList.add('hidden');
            }

            document.getElementById('receiptSection').classList.remove('hidden');
            document.getElementById('paymentForm').classList.add('hidden');
        }

        function openReceiptModal() {
            console.log('openReceiptModal called');
            // Make sure receipt section is visible
            const receiptSection = document.getElementById('receiptSection');
            if (!receiptSection) {
                console.error('receiptSection not found');
                return;
            }
            receiptSection.classList.remove('hidden');
            
            const receiptContent = receiptSection.innerHTML;
            const modal = document.getElementById('receipt-modal');
            const modalContent = document.getElementById('receipt-modal-content');
            const contentDiv = document.getElementById('receipt-content');
            
            if (!modal || !modalContent || !contentDiv) {
                console.error('Modal elements not found', { modal, modalContent, contentDiv });
                return;
            }
            
            console.log('Loading receipt content into modal');
            // Load content
            contentDiv.innerHTML = receiptContent;
            
            console.log('Showing modal');
            // Show modal with animation
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }, 10);
        }

        function printReceipt() {
            openReceiptModal();
        }

        function closeReceiptModal() {
            const modal = document.getElementById('receipt-modal');
            const modalContent = document.getElementById('receipt-modal-content');
            
            // Animate out
            modal.classList.add('opacity-0');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        function printModalReceipt() {
            const content = document.getElementById('receipt-content').innerHTML;
            const printWindow = window.open('', '', 'width=400,height=600');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Receipt - Order #${currentOrderId}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .text-center { text-align: center; }
                        .font-bold { font-weight: bold; }
                        .text-lg { font-size: 1.125rem; }
                        .text-sm { font-size: 0.875rem; }
                        .space-y-2 > * + * { margin-top: 0.5rem; }
                        .flex { display: flex; justify-content: space-between; }
                        .border-b { border-bottom: 1px solid #e2e8f0; }
                        .pb-4 { padding-bottom: 1rem; }
                        .mb-4 { margin-bottom: 1rem; }
                        .text-emerald-600 { color: #059669; }
                    </style>
                </head>
                <body>${content}</body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeReceiptModal();
            }
        });

        // Close modal on backdrop click
        document.getElementById('receipt-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReceiptModal();
            }
        });

        function validateOrderReady(isFullyReady) {
            if (!isFullyReady) {
                alert('All items must be marked as ready by the kitchen before this order can be picked up.');
                return false;
            }
            return true;
        }

        // Handle form submission
        document.getElementById('paymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    showReceipt();
                    // Refresh the page after a delay to show updated order status
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else {
                    const data = await response.json();
                    alert(data.message || 'Payment failed. Please try again.');
                }
            } catch (error) {
                console.error('Payment error:', error);
                alert('Payment failed. Please try again.');
            }
        });

        // Close modal on outside click
        document.getElementById('paymentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePaymentModal();
            }
        });
    </script>
@endsection
