@extends('kiosk.layout')

@section('content')
<div class="flex-1 flex items-center justify-center relative min-h-screen">
    <!-- Background Image -->
    <div class="absolute inset-0 z-0">
        <img
            src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80"
            alt="Bakery background"
            class="w-full h-full object-cover"
        >
        <div class="absolute inset-0 bg-gradient-to-br from-amber-950/95 via-amber-900/80 to-amber-950/60"></div>
    </div>
    
    <div class="z-10 flex flex-col items-center w-full px-8 pb-12">
        <div class="text-7xl mb-6 drop-shadow-lg">🥐</div>
        <h1 class="font-display text-5xl md:text-7xl font-bold text-amber-50 mb-6 tracking-tight drop-shadow-xl text-center leading-[1.1]">
            Welcome to<br>Golden Crumb
        </h1>
        <p class="text-xl md:text-3xl text-amber-100 mb-16 font-medium text-center drop-shadow-md">Where will you be eating today?</p>
        
        <form method="POST" action="{{ route('kiosk.type') }}" class="flex gap-6 md:gap-12 w-full justify-center max-w-3xl">
            @csrf
            <!-- Dine In Button -->
            <button type="submit" name="order_type" value="dine_in" 
                class="flex-1 flex flex-col items-center justify-center py-16 px-6 rounded-[2.5rem] transition-all hover:scale-[1.03] hover:shadow-[0_0_40px_rgba(251,191,36,0.3)] group border-2 border-amber-200/50 bg-amber-950/60 backdrop-blur-md shadow-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-amber-400 opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                <span class="text-7xl md:text-8xl mb-6 group-hover:scale-110 transition-transform duration-500 drop-shadow-lg relative z-10">🍽️</span>
                <h2 class="font-display text-4xl font-bold text-amber-50 group-hover:text-white relative z-10">Dine In</h2>
                <p class="text-amber-200/90 mt-3 text-lg font-medium relative z-10">Eat at a table</p>
            </button>
            
            <!-- Takeout Button -->
            <button type="submit" name="order_type" value="takeout" 
                class="flex-1 flex flex-col items-center justify-center py-16 px-6 rounded-[2.5rem] transition-all hover:scale-[1.03] hover:shadow-[0_0_40px_rgba(251,191,36,0.3)] group border-2 border-amber-200/50 bg-amber-950/60 backdrop-blur-md shadow-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-amber-400 opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                <span class="text-7xl md:text-8xl mb-6 group-hover:scale-110 transition-transform duration-500 drop-shadow-lg relative z-10">🛍️</span>
                <h2 class="font-display text-4xl font-bold text-amber-50 group-hover:text-white relative z-10">Takeout</h2>
                <p class="text-amber-200/90 mt-3 text-lg font-medium relative z-10">Pack to go</p>
            </button>
        </form>
    </div>
</div>
@endsection
