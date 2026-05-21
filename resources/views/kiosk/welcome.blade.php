@extends('kiosk.layout')

@section('content')

<div class="relative min-h-screen w-full flex flex-col justify-center">
    <!-- Split Background (Absolute so it doesn't constrain content height) -->
    <div class="absolute inset-0 z-0 flex flex-col">
        <!-- Top Image (60%) -->
        <div class="h-[60%] relative w-full overflow-hidden">
            <img
                src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80"
                alt="Bakery background"
                class="w-full h-full object-cover"
            >
            <div class="absolute inset-0 bg-black/40"></div>
        </div>
        
        <!-- Bottom Brown (40%) -->
        <div class="flex-1 w-full bg-amber-800 border-t-8 border-amber-900 shadow-[inset_0_10px_20px_rgba(0,0,0,0.2)]"></div>
    </div>
    
    <!-- Foreground Content (Naturally flows, no fixed heights to cause cramping) -->
    <div class="relative z-10 flex flex-col items-center justify-center w-full px-8 py-16 mt-10">
        
        <h1 class="font-display text-6xl md:text-8xl font-bold text-white tracking-tight drop-shadow-2xl text-center mb-16">
            Golden Crumb
        </h1>
        
        <!-- The Buttons -->
        <form method="POST" action="{{ route('kiosk.type') }}" class="flex gap-8 md:gap-12 w-full justify-center max-w-3xl mb-16">
            @csrf
            <!-- Dine In Button -->
            <button type="submit" name="order_type" value="dine_in" 
                class="kiosk-btn flex-1 py-16 px-6 rounded-3xl bg-white shadow-2xl border-b-4 border-amber-200 cursor-pointer">
                <h2 class="font-display text-4xl font-bold text-amber-900 mb-3">Dine In</h2>
                <p class="text-stone-500 text-xl font-medium">Eat at a table</p>
            </button>
            
            <!-- Takeout Button -->
            <button type="submit" name="order_type" value="takeout" 
                class="kiosk-btn flex-1 py-16 px-6 rounded-3xl bg-white shadow-2xl border-b-4 border-amber-200 cursor-pointer">
                <h2 class="font-display text-4xl font-bold text-amber-900 mb-3">Takeout</h2>
                <p class="text-stone-500 text-xl font-medium">Pack to go</p>
            </button>
        </form>

        <!-- Footer Text and Back Button -->
        <div class="flex flex-col items-center mt-4">
            <p class="text-2xl text-amber-100/90 mb-8 font-medium text-center">Where will you be eating today?</p>
            
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3 text-amber-200 hover:text-white font-bold text-xl transition-transform hover:-translate-x-2 bg-amber-900/50 px-8 py-4 rounded-full border border-amber-700">
                <span class="text-3xl leading-none">←</span> 
                Back to Home Page
            </a>
        </div>
    </div>
</div>
@endsection
