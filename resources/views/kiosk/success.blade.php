@extends('kiosk.layout')

@section('content')
    @php
        $isTakeout = $orderType === 'takeout';
    @endphp

    <div class="h-full w-full flex items-center justify-center bg-amber-900 relative overflow-hidden p-4 sm:p-6">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            @for($i=0; $i<20; $i++)
                <div class="absolute w-3 h-3 bg-amber-200 rounded-full opacity-40 animate-pulse" style="top: {{ rand(10, 90) }}%; left: {{ rand(10, 90) }}%; animation-delay: {{ rand(0, 2000) }}ms; animation-duration: {{ rand(2000, 4000) }}ms"></div>
            @endfor
        </div>

        <div class="z-10 bg-[#faf6f0] rounded-[2rem] sm:rounded-[3rem] p-8 sm:p-10 lg:p-16 max-w-lg w-full text-center shadow-2xl relative border border-amber-200/60 my-6 sm:my-8 overflow-auto max-h-[90vh]">
            <div class="absolute -top-10 sm:-top-12 lg:-top-16 left-1/2 -translate-x-1/2 w-20 h-20 sm:w-24 sm:h-24 lg:w-32 lg:h-32 bg-green-600 rounded-full border-4 sm:border-6 lg:border-8 border-amber-900 flex items-center justify-center text-white text-3xl sm:text-4xl lg:text-5xl shadow-lg">
                ✓
            </div>

            <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-700 mt-6 sm:mt-8 lg:mt-10">{{ $pickupLabel }}</p>
            <h1 class="font-display text-2xl sm:text-3xl lg:text-4xl font-semibold text-amber-950 mt-2 sm:mt-3 mb-2">Order paid</h1>
            <p class="text-base sm:text-lg text-stone-600 mb-6 sm:mb-8 font-medium">
                {{ $isTakeout ? 'Your takeout order has been sent to the kitchen.' : 'Your table order has been sent to the kitchen.' }}
            </p>

            <div class="bg-white rounded-2xl p-4 sm:p-6 mb-6 sm:mb-8 border border-amber-200/60 shadow-sm">
                <p class="text-xs sm:text-sm text-stone-400 font-semibold mb-1 uppercase tracking-wide">Order Number</p>
                <p class="font-display text-3xl sm:text-4xl lg:text-5xl font-semibold text-amber-800 tracking-tight">#{{ str_pad($orderId, 4, '0', STR_PAD_LEFT) }}</p>
            </div>

            <p class="text-stone-600 mb-6 sm:mb-8 font-medium text-sm sm:text-base">
                {{ $isTakeout ? 'Please wait for your number to be called for pickup.' : 'Please wait for your number to be called or served to your table.' }}
            </p>

            <a href="{{ route('kiosk.welcome') }}" class="inline-block w-full bg-stone-200 hover:bg-stone-300 text-stone-800 font-bold py-3 sm:py-4 rounded-2xl text-base sm:text-lg lg:text-xl transition-colors">
                Start New Order
            </a>
        </div>
    </div>
@endsection
