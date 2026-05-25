@extends('layouts.table')

@section('title', 'Menu')

@section('content')
<div class="table-container">
    <!-- Header -->
    <header class="table-header">
        <div class="flex items-center gap-4">
            <h1 class="font-display text-2xl font-bold text-amber-950">🥐 Golden Crumb</h1>
            @if (session('cafe_table_name'))
                <div class="bg-amber-100 text-amber-900 px-4 py-2 rounded-full font-bold text-sm">
                    {{ session('cafe_table_name') }}
                </div>
            @endif
        </div>
        <form method="POST" action="{{ route('table.leave') }}" class="text-xs">
            @csrf
            <button type="submit" class="text-stone-400 hover:text-stone-600 underline">Leave table</button>
        </form>
    </header>

    <!-- Left Sidebar: Categories -->
    <aside class="table-sidebar-left hide-scrollbar">
        @foreach(['drinks' => ['icon' => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=120&auto=format&fit=crop&q=80', 'label' => 'Drinks'],
            'food' => ['icon' => 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=120&auto=format&fit=crop&q=80', 'label' => 'Food']] as $cat => $data)
            <button onclick="document.getElementById('cat-{{ $cat }}')?.scrollIntoView({behavior: 'smooth', block: 'start'})"
                class="category-btn flex flex-col items-center gap-2 p-2 w-20 rounded-2xl transition-all hover:bg-amber-50 hover:scale-105 group focus:outline-none cursor-pointer shrink-0">
                <div class="w-14 h-14 rounded-full overflow-hidden border-2 border-amber-100 shadow-sm group-hover:border-amber-500 transition-colors shrink-0">
                    <img src="{{ $data['icon'] }}" class="category-img w-full h-full object-cover" alt="{{ $data['label'] }}">
                </div>
                <span class="text-[10px] font-bold text-stone-600 uppercase tracking-wider group-hover:text-amber-800 text-center">{{ $data['label'] }}</span>
            </button>
        @endforeach
    </aside>

    <!-- Right Sidebar: Cart -->
    <aside class="table-sidebar-right">
        <div class="p-4 border-b border-amber-200/50 bg-[#faf6f0] shrink-0 flex justify-between items-center">
            <h2 class="font-display text-lg font-semibold text-amber-950 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Your Order
            </h2>
            <span class="bg-amber-800 text-white text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full shadow-sm">{{ $cartCount }}</span>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3 hide-scrollbar">
            @forelse($cart as $line)
                <div class="bg-white p-3 rounded-xl shadow-sm border border-stone-200 flex gap-3 relative">
                    <div class="flex-1">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-semibold text-amber-950 text-sm leading-tight">{{ $line['name'] }}</h4>
                            <span class="font-semibold text-stone-800 text-sm">${{ number_format($line['unit_price'] * $line['quantity'], 2) }}</span>
                        </div>
                        <div class="text-xs text-stone-500 font-medium">Qty: {{ $line['quantity'] }}</div>
                        <form method="POST" action="{{ route('table.cart.update') }}" class="flex items-center gap-2 mt-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="menu_item_id" value="{{ $line['menu_item_id'] }}">
                            <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="0" max="99"
                                class="w-12 rounded-lg border border-amber-200 px-2 py-1 text-center text-xs focus:ring-2 focus:ring-amber-500">
                            <button type="submit" class="text-xs bg-amber-800 text-white px-2 py-1 rounded-lg font-medium hover:bg-amber-900">Update</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-center p-6 opacity-60">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-stone-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-stone-600 font-bold">Your cart is empty.</p>
                </div>
            @endforelse
        </div>

        <div class="p-4 bg-white border-t border-amber-200/50 shrink-0">
            <div class="flex justify-between items-center mb-4">
                <span class="font-bold text-stone-500">Total</span>
                <span class="font-display text-2xl font-semibold text-amber-950">${{ number_format($cartTotal, 2) }}</span>
            </div>
            @if(count($cart) > 0)
                <form method="POST" action="{{ route('table.order') }}">
                    @csrf
                    <button type="submit" class="w-full bg-amber-800 hover:bg-amber-900 text-amber-50 font-bold py-3 rounded-xl shadow-lg shadow-amber-900/20 transition-all flex items-center justify-center gap-2">
                        Send order to counter <span>→</span>
                    </button>
                </form>
            @else
                <button disabled class="w-full bg-stone-200 text-stone-400 font-bold py-3 rounded-xl cursor-not-allowed">
                    Send order to counter
                </button>
            @endif
        </div>
    </aside>

    <!-- Middle: Items Grid (Main Scrollable Area) -->
    <main class="table-main-content">
        <div class="max-w-5xl mx-auto">
            @if(session('success'))
                <div class="bg-amber-100 text-amber-900 p-4 rounded-xl mb-6 font-medium text-center shadow-sm border border-amber-200">
                    {{ session('success') }}
                </div>
            @endif

            @forelse($itemsByCategory as $category => $items)
                <div id="cat-{{ strtolower($category) }}" class="mb-12 scroll-mt-28">
                    <h2 class="font-display text-3xl font-bold text-amber-950 mb-6 capitalize flex items-center gap-4">
                        {{ $category }}
                        <div class="h-[2px] bg-amber-100 flex-1 rounded-full"></div>
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($items as $item)
                            @php
                                $img = $item->image ? asset('storage/' . $item->image) : 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=600&q=80';
                                if($category === 'drinks' && !$item->image) $img = 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=600&q=80';
                            @endphp
                            <button onclick="document.getElementById('add-{{ $item->id }}').click()" class="item-card bg-white rounded-3xl shadow-sm border border-stone-200 flex flex-col text-left relative overflow-hidden group hover:shadow-xl hover:border-amber-300 cursor-pointer">
                                <div class="w-full h-48 bg-stone-100 overflow-hidden relative">
                                    <img src="{{ $img }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="{{ $item->name }}">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                </div>
                                <div class="p-5 flex flex-col flex-1">
                                    <div class="flex justify-between items-start gap-2 mb-2">
                                        <h3 class="font-display text-xl font-bold text-amber-950 leading-tight">{{ $item->name }}</h3>
                                    </div>
                                    <p class="text-sm text-stone-500 line-clamp-2 mb-4 flex-1 font-medium leading-relaxed">{{ $item->description }}</p>
                                    <div class="mt-auto flex items-center justify-between">
                                        <span class="text-xl font-bold text-amber-800">{{ $item->formattedPrice() }}</span>
                                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-lg group-hover:bg-amber-800 group-hover:text-white transition-colors">+</div>
                                    </div>
                                </div>
                            </button>
                            <form id="add-{{ $item->id }}" method="POST" action="{{ route('table.cart.add', $item) }}" class="hidden">
                                @csrf
                            </form>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-20">
                    <p class="text-2xl text-stone-400 font-bold">Menu is being updated — check back soon!</p>
                </div>
            @endforelse
        </div>
    </main>
</div>
@endsection
