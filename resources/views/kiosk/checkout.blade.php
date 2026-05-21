@extends('kiosk.layout')

@section('content')
<div class="h-full w-full flex items-center justify-center bg-[#faf6f0] relative overflow-hidden">
    <div class="absolute top-0 w-full h-1/2 bg-amber-800 rounded-b-[4rem] z-0"></div>

    <div class="z-10 bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-stone-200">
        <div class="p-8 text-center border-b border-amber-200/50">
            <div class="w-20 h-20 bg-amber-100 text-amber-800 rounded-full flex items-center justify-center text-4xl mx-auto mb-4 border border-amber-200/50">
                💳
            </div>
            <h1 class="font-display text-3xl font-semibold text-amber-950 mb-2">Checkout</h1>
            <p class="text-stone-500">Complete your order details.</p>
        </div>

        <form method="POST" action="{{ route('kiosk.pay') }}" class="p-8">
            @csrf
            
            @if(session('kiosk_order_type') === 'dine_in')
                <div class="mb-8">
                    <label for="table_number" class="block text-sm font-bold text-stone-700 mb-3">Where are you sitting?</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-stone-400">
                            🪑
                        </div>
                        <input type="text" id="table_number" name="table_number" required placeholder="e.g. Table 12 or Window Seat" class="w-full pl-12 pr-4 py-4 bg-[#faf6f0] border-2 border-amber-200/60 rounded-2xl focus:ring-0 focus:border-amber-500 font-medium text-stone-800 transition-colors placeholder:font-normal" autocomplete="off">
                    </div>
                </div>
            @endif

            <div class="bg-amber-50 rounded-2xl p-6 mb-8 flex justify-between items-center border border-amber-100">
                <span class="text-amber-900 font-semibold">Order Total</span>
                <span class="font-display text-2xl font-semibold text-amber-950">${{ number_format(collect(session('kiosk_cart', []))->sum('line_total'), 2) }}</span>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('kiosk.menu') }}" class="w-1/3 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold py-4 rounded-2xl text-lg transition-colors flex items-center justify-center">
                    Back
                </a>
                <button type="submit" class="w-2/3 bg-amber-800 hover:bg-amber-900 text-amber-50 font-bold py-4 rounded-2xl text-xl shadow-lg shadow-amber-900/20 transition-transform active:scale-95">
                    Pay Now
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
