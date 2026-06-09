@extends('layouts.staff')

@section('title', 'Current Orders')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-end">
        <div>
            <h1 class="font-display text-3xl font-bold text-slate-900 tracking-tight">Current Orders</h1>
            <p class="text-slate-500 mt-1">Manage and pay for active orders.</p>
        </div>
    </div>

    <x-flash />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($activeOrders as $order)
            <div class="bg-white rounded-3xl shadow-sm border border-amber-200 overflow-hidden flex flex-col relative group">
                <div class="p-5 border-b border-slate-100 bg-amber-50/50">
                    <div class="flex justify-between items-start">
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
                            <button onclick="readOrder({{ $order->id }})" class="px-3 py-2 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-700 transition-colors flex items-center gap-2 text-sm font-semibold" title="Read Order">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.536a5 5 0 001.414 1.414m2.828-9.9a9 9 0 012.828-2.828"></path></svg>
                                <span>Read</span>
                            </button>
                            <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded border border-blue-200 uppercase tracking-wider">Pending</span>
                        </div>
                    </div>
                </div>

                <div class="p-5 flex-1 space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex items-start gap-4 p-3 rounded-2xl border bg-white border-slate-200 shadow-sm">
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start gap-2">
                                    <h4 class="font-bold text-slate-900 text-sm"><span class="text-amber-600 mr-1">{{ $item->quantity }}x</span> {{ $item->item_name }}</h4>
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

                <div class="p-5 border-t border-slate-100 bg-slate-50">
                    <div class="flex justify-between items-center mb-4">
                        <span class="font-semibold text-slate-900">Total:</span>
                        <span class="text-2xl font-bold text-emerald-600">${{ number_format($order->total, 2) }}</span>
                    </div>
                    <div class="flex gap-2">
                        @if($order->order_type === 'dine_in')
                        <button onclick="openPaymentModal({{ $order->id }}, {{ $order->total }})" class="flex-1 py-3 rounded-xl font-bold bg-amber-600 text-white hover:bg-amber-700 transition-colors">
                            Pay Now
                        </button>
                        <button class="flex-1 py-3 rounded-xl font-bold bg-slate-600 text-white hover:bg-slate-700 transition-colors">
                            Add Item
                        </button>
                        @endif
                        <button onclick="markAsPickedUp({{ $order->id }})" class="flex-1 py-3 rounded-xl font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                            Pick Up
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 flex flex-col items-center justify-center text-center bg-white rounded-3xl border border-dashed border-slate-300">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="size-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <h3 class="text-xl font-display font-bold text-slate-900">No pending orders</h3>
                <p class="text-slate-500 mt-1 max-w-sm">There are no orders waiting to be paid.</p>
            </div>
        @endforelse
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
            
            <button onclick="processPayment('qr')" class="w-full p-4 rounded-xl border-2 border-slate-200 hover:border-purple-500 hover:bg-purple-50 transition-colors flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="size-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V8a1 1 0 00-1-1H5a1 1 0 00-1 1v.01M4 12h2a1 1 0 001-1V12a1 1 0 00-1-1H4a1 1 0 00-1 1v.01M4 16h2a1 1 0 001-1V16a1 1 0 00-1-1H4a1 1 0 00-1 1v.01M5 20h2a1 1 0 001-1V20a1 1 0 00-1-1H5a1 1 0 00-1 1v.01"></path></svg>
                </div>
                <div class="text-left">
                    <p class="font-bold text-slate-900">QR Code</p>
                    <p class="text-sm text-slate-500">Scan QR to pay</p>
                </div>
            </button>
        </div>
        
        <div class="flex justify-between items-center p-4 bg-slate-50 rounded-xl">
            <span class="font-semibold text-slate-900">Amount:</span>
            <span id="modal-amount" class="text-2xl font-bold text-emerald-600">$0.00</span>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div id="receipt-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full mx-4 p-6">
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

<script>
    let currentOrderId = null;
    let currentAmount = 0;

    // Load voices for text-to-speech
    if ('speechSynthesis' in window) {
        window.speechSynthesis.getVoices();
        // Some browsers need this event to load voices
        window.speechSynthesis.onvoiceschanged = () => {
            window.speechSynthesis.getVoices();
        };
    }

    // Store order data for text-to-speech
    const ordersData = {
        @foreach($activeOrders as $order)
        {{ $order->id }}: {
            id: {{ $order->id }},
            table: "{{ $order->order_type === 'dine_in' && $order->cafeTable ? 'Table ' . $order->cafeTable->name : 'Takeout' }}",
            items: [
                @foreach($order->items as $item)
                {
                    name: "{{ $item->item_name }}",
                    quantity: {{ $item->quantity }},
                    flavor: @isset($item->flavor) "{{ $item->flavor['name'] ?? '' }}" @else "" @endif,
                    modifications: @if(!empty($item->modifications)) {!! json_encode($item->modifications) !!} @else [] @endif,
                    notes: @if($item->notes) "{{ $item->notes }}" @else "" @endif
                },
                @endforeach
            ],
            total: {{ $order->total }}
        },
        @endforeach
    };

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

        fetch('{{ route('cashier.orders.pay', ':id') }}'.replace(':id', currentOrderId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                payment_method: paymentMethod,
                amount_paid: currentAmount
            })
        })
        .then(response => response.json())
        .then(data => {
            closePaymentModal();
            showReceipt(paymentMethod, currentAmount, currentOrderId);
            setTimeout(() => location.reload(), 2000);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Payment failed. Please try again.');
        });
    }

    function showReceipt(paymentMethod, amount, orderId) {
        document.getElementById('receipt-order-id').textContent = orderId;
        document.getElementById('receipt-payment-method').textContent = paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1);
        document.getElementById('receipt-amount').textContent = '$' + amount.toFixed(2);
        document.getElementById('receipt-modal').classList.remove('hidden');
        document.getElementById('receipt-modal').classList.add('flex');
    }

    function readOrder(orderId) {
        const order = ordersData[orderId];
        if (!order) return;

        // Build natural language text for the order
        let text = `Order ${order.id} for ${order.table}. `;

        if (order.items.length === 1) {
            text += 'You have one item: ';
        } else {
            text += `You have ${order.items.length} items: `;
        }

        order.items.forEach((item, index) => {
            // Add item with quantity
            text += `${item.quantity} ${item.name}`;

            // Add flavor if present
            if (item.flavor) {
                text += `, with ${item.flavor} flavor`;
            }

            // Add modifications if present
            if (item.modifications && item.modifications.length > 0) {
                const modNames = item.modifications.map(mod => {
                    if (typeof mod === 'string') return mod;
                    return mod.name || mod;
                }).join(', ');
                text += `, with ${modNames}`;
            }

            // Add notes if present
            if (item.notes) {
                text += `. Note: ${item.notes}`;
            }

            // Add separator between items
            if (index < order.items.length - 1) {
                text += '. Next, ';
            }
        });

        text += `. Total price is ${order.total.toFixed(2)} dollars.`;

        // Use Web Speech API to read the text
        if ('speechSynthesis' in window) {
            // Cancel any current speech
            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 0.9; // Slightly slower for clarity
            utterance.pitch = 1;
            utterance.volume = 1;

            // Try to get a good voice
            const voices = window.speechSynthesis.getVoices();
            const preferredVoice = voices.find(voice =>
                voice.lang.includes('en') && voice.name.includes('Google')
            ) || voices.find(voice => voice.lang.includes('en'));

            if (preferredVoice) {
                utterance.voice = preferredVoice;
            }

            window.speechSynthesis.speak(utterance);
        } else {
            alert('Text-to-speech is not supported in your browser.');
        }
    }

    function closeReceiptModal() {
        document.getElementById('receipt-modal').classList.add('hidden');
        document.getElementById('receipt-modal').classList.remove('flex');
    }
</script>
@endsection
