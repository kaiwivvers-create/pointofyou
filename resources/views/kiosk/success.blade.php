@extends('kiosk.layout')

@section('content')
<div class="h-full w-full flex items-center justify-center bg-amber-800 relative overflow-hidden">
    <!-- Confetti / Success Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        @for($i=0; $i<20; $i++)
            <div class="absolute w-3 h-3 bg-amber-200 rounded-full opacity-40 animate-pulse" style="top: {{ rand(10, 90) }}%; left: {{ rand(10, 90) }}%; animation-delay: {{ rand(0, 2000) }}ms; animation-duration: {{ rand(2000, 4000) }}ms"></div>
        @endfor
    </div>

    <div class="z-10 bg-[#faf6f0] rounded-[3rem] p-12 max-w-lg w-full text-center shadow-2xl relative border border-amber-200/60">
        <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-24 h-24 bg-green-600 rounded-full border-8 border-amber-800 flex items-center justify-center text-white text-4xl shadow-lg">
            ✓
        </div>
        
        <h1 class="font-display text-4xl font-semibold text-amber-950 mt-6 mb-2">Order Paid!</h1>
        <p class="text-lg text-stone-600 mb-8 font-medium">Your order has been sent to the kitchen.</p>
        
        <div class="bg-white rounded-2xl p-6 mb-8 border border-amber-200/60 shadow-sm">
            <p class="text-sm text-stone-400 font-semibold mb-1 uppercase tracking-wide">Order Number</p>
            <p class="font-display text-5xl font-semibold text-amber-800 tracking-tight">#{{ str_pad($orderId, 4, '0', STR_PAD_LEFT) }}</p>
        </div>
        
        <p class="text-stone-600 mb-8 font-medium">Please wait for your number to be called or served to your table.</p>
        
        <a href="{{ route('kiosk.welcome') }}" class="inline-block w-full bg-stone-200 hover:bg-stone-300 text-stone-800 font-bold py-4 rounded-2xl text-xl transition-colors">
            Start New Order
        </a>
    </div>
</div>
@endsection
