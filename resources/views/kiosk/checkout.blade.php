@extends('kiosk.layout')

@section('content')
    @php
        $cart = session('kiosk_cart', []);
        $cartTotal = collect($cart)->sum('line_total');
        $isTakeout = $orderType === 'takeout';
    @endphp

    <div class="fixed inset-0 bg-[#faf6f0]">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-950 via-amber-900 to-stone-900 opacity-95"></div>
        <div class="relative z-10 h-full w-full flex items-center justify-center p-4 sm:p-6 overflow-auto">
            <div class="w-full max-w-3xl bg-white rounded-[2rem] shadow-2xl overflow-hidden border border-amber-200/60 my-4 sm:my-6">
                <div class="p-4 sm:p-6 lg:p-8 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-700">Checkout</p>
                        <h1 class="font-display text-2xl sm:text-3xl font-semibold text-amber-950">Complete payment</h1>
                        <p class="text-sm text-stone-500 mt-1">{{ $isTakeout ? 'Takeout order' : 'Dine-in order' }}</p>
                    </div>
                    <div class="px-3 py-1.5 sm:px-4 sm:py-2 rounded-full bg-amber-50 text-amber-800 font-bold border border-amber-200 text-sm sm:text-base">
                        {{ $isTakeout ? 'Takeout' : 'Table' }}
                    </div>
                </div>

                <div class="px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6">
                    <x-flash />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] lg:max-h-[80vh] lg:overflow-y-auto">
                    <div class="p-4 sm:p-6 lg:p-8 bg-[#fffaf3] border-b lg:border-b-0 lg:border-r border-amber-100">
                        <div class="bg-white rounded-2xl border border-amber-100 p-4 sm:p-5 shadow-sm mb-4 sm:mb-5">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-400 mb-2">Order Total</p>
                            <p class="font-display text-4xl sm:text-5xl font-semibold text-amber-950">${{ number_format($cartTotal, 2) }}</p>
                        </div>

                        @if(! $isTakeout)
                            <div class="mb-6">
                                <label for="table_number" class="block text-sm font-bold text-stone-700 mb-2">Table number</label>
                                <input type="text" id="table_number" name="table_number" form="kioskPaymentForm" required placeholder="e.g. 12"
                                    class="w-full px-4 py-4 bg-white border-2 border-amber-200/70 rounded-2xl focus:ring-0 focus:border-amber-500 font-medium text-stone-800 transition-colors placeholder:font-normal"
                                    autocomplete="off">
                            </div>
                        @endif

                        <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100">
                            <p class="text-sm font-semibold text-amber-900">After payment, you’ll get an order number.</p>
                            <p class="text-sm text-stone-600 mt-1">{{ $isTakeout ? 'Takeout pickup' : 'Table service' }} will show on the receipt screen.</p>
                        </div>

                        <div class="mt-4 sm:mt-6 flex gap-2 sm:gap-3">
                            <a href="{{ route('kiosk.menu') }}" class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold py-3 sm:py-4 rounded-2xl text-base sm:text-lg transition-colors flex items-center justify-center">
                                Back
                            </a>
                            <button type="button" onclick="submitKioskPayment()" class="flex-[2] bg-amber-800 hover:bg-amber-900 text-amber-50 font-bold py-3 sm:py-4 rounded-2xl text-base sm:text-lg shadow-lg shadow-amber-900/20 transition-transform active:scale-95">
                                Pay now
                            </button>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6 lg:p-8 bg-white">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-400 mb-3 sm:mb-4">Payment method</p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 mb-4 sm:mb-5">
                            <button type="button" onclick="selectMethod('qr')" id="btn-qr" class="payment-method-btn p-4 rounded-2xl border-2 border-slate-200 hover:border-amber-500 hover:bg-amber-50 transition-all text-center">
                                <span class="block font-semibold text-slate-700">QR</span>
                                <span class="text-xs text-stone-500 mt-1">Scan to pay</span>
                            </button>
                            <button type="button" onclick="selectMethod('card')" id="btn-card" class="payment-method-btn p-4 rounded-2xl border-2 border-slate-200 hover:border-amber-500 hover:bg-amber-50 transition-all text-center">
                                <span class="block font-semibold text-slate-700">Card</span>
                                <span class="text-xs text-stone-500 mt-1">Tap or insert</span>
                            </button>
                            <button type="button" onclick="selectMethod('transfer')" id="btn-transfer" class="payment-method-btn p-4 rounded-2xl border-2 border-slate-200 hover:border-amber-500 hover:bg-amber-50 transition-all text-center">
                                <span class="block font-semibold text-slate-700">Transfer</span>
                                <span class="text-xs text-stone-500 mt-1">Bank transfer</span>
                            </button>
                        </div>

                        <div id="qr-form" class="payment-form hidden">
                            <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 sm:p-5 text-center">
                                <div class="w-36 h-36 sm:w-44 sm:h-44 mx-auto bg-white border-2 border-slate-200 rounded-2xl flex items-center justify-center mb-3 sm:mb-4">
                                    <svg class="size-10 sm:size-14 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h4v4H4V4zm8 0h4v4h-4V4zM4 12h4v4H4v-4zm8 8v-4h4v4h-4zm4-8h4v4h-4v-4zm0-8h4v4h-4V4zM8 8h8v8H8V8z" />
                                    </svg>
                                </div>
                                <p class="text-sm text-stone-600">Scan the QR code to complete payment.</p>
                            </div>
                        </div>

                        <div id="card-form" class="payment-form hidden">
                            <div class="bg-slate-50 rounded-2xl p-4 sm:p-5 text-center">
                                <p class="text-sm text-slate-600">Insert or tap your card.</p>
                            </div>
                        </div>

                        <div id="transfer-form" class="payment-form hidden">
                            <div class="bg-slate-50 rounded-2xl p-4 sm:p-5 text-center">
                                <p class="text-sm text-slate-600">Use bank transfer if needed.</p>
                            </div>
                        </div>

                        <form id="kioskPaymentForm" method="POST" action="{{ route('kiosk.pay') }}" class="mt-6">
                            @csrf
                            <input type="hidden" id="paymentMethod" name="payment_method" value="">
                            <button type="submit" id="submitPayment" class="hidden"></button>
                        </form>

                        <p class="mt-4 text-xs text-stone-400">Cash is disabled on kiosk checkout.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedMethod = null;

        function selectMethod(method) {
            selectedMethod = method;
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

        async function submitKioskPayment() {
            const form = document.getElementById('kioskPaymentForm');
            const formData = new FormData(form);

            const tableNumberInput = document.getElementById('table_number');
            if (tableNumberInput) {
                formData.append('table_number', tableNumberInput.value);
            }

            if (!selectedMethod) {
                alert('Select a payment method first.');
                return;
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    alert(data.message || 'Payment failed. Please try again.');
                    return;
                }

                window.location.href = data.redirect_url || '{{ route('kiosk.success') }}';
            } catch (error) {
                console.error(error);
                alert('Payment failed. Please try again.');
            }
        }

        selectMethod('qr');
    </script>
@endsection
