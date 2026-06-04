@extends('kiosk.layout')

@section('content')
    @php
        $brandSettings = \App\Models\BrandSettings::getSettings();
    @endphp
<div class="kiosk-container">
    <!-- Header -->
    <header class="kiosk-header shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('kiosk.welcome') }}" class="hover:scale-115 transition-transform bg-white w-12 h-12 flex items-center justify-center rounded-full shadow-sm border border-amber-100 text-amber-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="font-display text-2xl font-bold text-primary-font tracking-tight">{{ $brandSettings->app_name }}</h1>
        </div>
        <div class="bg-amber-100 text-amber-900 px-5 py-2.5 rounded-full font-bold text-sm tracking-wide uppercase shadow-sm border border-amber-200/50">
            {{ str_replace('_', ' ', session('kiosk_order_type', '')) }}
        </div>
    </header>

    <!-- Left Sidebar: Categories -->
    <aside class="kiosk-sidebar-left hide-scrollbar">
        @if($menuItems->has('packets'))
            <button onclick="document.getElementById('cat-packets')?.scrollIntoView({behavior: 'smooth', block: 'start'})"
                class="category-btn flex flex-col items-center gap-2 p-2 w-24 rounded-2xl transition-all hover:bg-amber-50 hover:scale-105 group focus:outline-none cursor-pointer">
                <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-amber-100 shadow-sm group-hover:border-amber-500 transition-colors">
                    <img src="https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=120&auto=format&fit=crop&q=80" class="category-img" alt="Packets">
                </div>
                <span class="text-[11px] font-bold text-stone-600 uppercase tracking-wider group-hover:text-amber-800">Packets</span>
            </button>
        @endif
        @if($promos->isNotEmpty())
            <button onclick="document.querySelector('.promo-carousel')?.scrollIntoView({behavior: 'smooth', block: 'start'})"
                class="category-btn flex flex-col items-center gap-2 p-2 w-24 rounded-2xl transition-all hover:bg-amber-50 hover:scale-105 group focus:outline-none cursor-pointer">
                <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-amber-100 shadow-sm group-hover:border-amber-500 transition-colors">
                    <img src="https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=120&auto=format&fit=crop&q=80" class="category-img" alt="Promos">
                </div>
                <span class="text-[11px] font-bold text-stone-600 uppercase tracking-wider group-hover:text-amber-800">Promos</span>
            </button>
        @endif
        @foreach(\App\Models\MenuCategory::visible()->whereNotIn('name', ['promos', 'packets'])->get() as $category)
            <button onclick="document.getElementById('cat-{{ $category->name }}')?.scrollIntoView({behavior: 'smooth', block: 'start'})"
                class="category-btn flex flex-col items-center gap-2 p-2 w-24 rounded-2xl transition-all hover:bg-amber-50 hover:scale-105 group focus:outline-none cursor-pointer">
                <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-amber-100 shadow-sm group-hover:border-amber-500 transition-colors">
                    <img src="{{ \App\Models\MenuCategory::defaultIcon($category->name) }}" class="category-img" alt="{{ $category->label }}">
                </div>
                <span class="text-[11px] font-bold text-stone-600 uppercase tracking-wider group-hover:text-amber-800">{{ $category->label }}</span>
            </button>
        @endforeach
    </aside>

    <!-- Right Sidebar: Cart -->
    <aside id="kioskCartSidebar" class="kiosk-sidebar-right">
        <div class="p-6 border-b border-amber-200/50 bg-[#faf6f0] shrink-0 flex justify-between items-center">
            <h2 class="font-display text-2xl font-semibold text-amber-950 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-amber-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Your Order
            </h2>
            <div class="flex items-center gap-2">
                <button type="button" onclick="toggleKioskCart()" class="lg:hidden inline-flex items-center gap-2 rounded-full bg-amber-800 px-3 py-2 text-xs font-bold text-white shadow-sm">
                    Cart
                    <span class="bg-white/15 text-white text-[11px] font-bold w-6 h-6 flex items-center justify-center rounded-full">{{ count($cart) }}</span>
                </button>
                <button type="button" onclick="toggleKioskCart()" class="lg:hidden inline-flex w-9 h-9 items-center justify-center rounded-full border border-amber-200 bg-white text-amber-900 hover:bg-amber-50" aria-label="Close cart">
                    <span class="text-xl leading-none">×</span>
                </button>
                <span class="hidden lg:flex bg-amber-800 text-white text-sm font-bold w-8 h-8 items-center justify-center rounded-full shadow-sm">{{ count($cart) }}</span>
            </div>
        </div>

        <div class="cart-body flex-1 overflow-y-auto p-4 space-y-4 hide-scrollbar min-h-0">
            @php
                // Group cart items by menu_item_id, modifications, and flavor
                $groupedCart = [];
                if (!empty($cart) && is_array($cart)) {
                    foreach($cart as $index => $cartItem) {
                        $key = $cartItem['signature'] ?? ($cartItem['menu_item_id'] . '_' . md5(json_encode($cartItem['modifications'] ?? [])) . '_' . md5($cartItem['notes'] ?? '') . '_' . ($cartItem['flavor']['id'] ?? ''));
                        if (!isset($groupedCart[$key])) {
                            $groupedCart[$key] = [
                                'name' => $cartItem['name'],
                                'menu_item_id' => $cartItem['menu_item_id'],
                                'unit_price' => $cartItem['unit_price'],
                                'modifications' => $cartItem['modifications'] ?? [],
                                'flavor' => $cartItem['flavor'] ?? null,
                                'is_packet' => $cartItem['is_packet'] ?? false,
                                'packet_id' => $cartItem['packet_id'] ?? null,
                                'packet_contents' => $cartItem['packet_contents'] ?? null,
                                'items' => []
                            ];
                        }
                        $groupedCart[$key]['items'][] = [
                            'index' => $index,
                            'quantity' => $cartItem['quantity'],
                            'line_total' => $cartItem['line_total'],
                            'notes' => $cartItem['notes'] ?? ''
                        ];
                    }
                }
            @endphp

            @forelse($groupedCart as $groupKey => $group)
                @php
                    $totalQty = collect($group['items'])->sum('quantity');
                    $totalPrice = collect($group['items'])->sum('line_total');
                    $hasMultipleItems = count($group['items']) > 1;
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden relative">
                    <div class="p-4 flex items-center gap-4 hover:border-amber-300 transition-colors">
                        <div class="flex-1">
                            <div class="flex justify-between items-center">
                                <h4 class="font-semibold text-amber-950">{{ $group['name'] }}</h4>
                                <span class="font-semibold text-stone-800">${{ number_format($totalPrice, 2) }}</span>
                            </div>
                            <div class="text-sm text-stone-500 font-medium">Qty: {{ $totalQty }}</div>

                            @if(!empty($group['modifications']))
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($group['modifications'] as $mod)
                                        <div class="text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-100">
                                            + {{ $mod['name'] }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if(isset($group['is_packet']) && $group['is_packet'])
                                <div class="mt-2 text-xs text-stone-600 bg-stone-50 p-2 rounded-lg border border-stone-200">
                                    <span class="font-semibold">Packet contents:</span>
                                    <div class="mt-1 space-y-1">
                                        @if(isset($group['packet_contents']) && is_array($group['packet_contents']))
                                            @foreach($group['packet_contents'] as $content)
                                                <div class="flex justify-between">
                                                    <span>{{ $content['name'] }}</span>
                                                    <span class="text-stone-500">x{{ $content['quantity'] ?? 1 }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-stone-500 italic">See details in modal</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($hasMultipleItems)
                                <button onclick="toggleDropdown('{{$groupKey}}')" class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-sm hover:bg-amber-200 transition-colors border border-amber-200 cursor-pointer">
                                    <svg id="icon-{{$groupKey}}" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            @else
                                <button onclick="openEditModal({{ $group['items'][0]['index'] }}, '{{ addslashes($group['name']) }}', {{ $group['items'][0]['quantity'] }}, {{ $group['unit_price'] }}, '{{ addslashes($group['items'][0]['notes'] ?? '') }}', {{ !empty($group['modifications']) ? 'true' : 'false' }}, {{ !empty($group['flavor']) ? json_encode($group['flavor']) : 'null' }})" class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded-lg font-medium hover:bg-amber-200 transition-colors cursor-pointer">Edit</button>
                            @endif
                            <form method="POST" action="{{ route('kiosk.cart.remove', $group['items'][0]['index']) }}">
                                @csrf
                                <button type="submit" class="w-8 h-8 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center text-sm hover:bg-red-500 hover:text-white hover:border-red-500 transition-colors border border-stone-200 cursor-pointer">×</button>
                            </form>
                        </div>
                    </div>
                    
                    @if(!$hasMultipleItems && (!empty($group['flavor']) || !empty($group['items'][0]['notes'])))
                        <div class="px-4 pb-4 pt-0 space-y-2">
                            @if(!empty($group['flavor']))
                                <div class="text-xs text-stone-500 italic break-words bg-stone-50 p-2 rounded-lg border border-stone-100">
                                    Flavor: {{ $group['flavor']['name'] }}
                                </div>
                            @endif
                            @if(!empty($group['items'][0]['notes']))
                                <div class="text-xs text-stone-500 italic break-words bg-stone-50 p-2 rounded-lg border border-stone-100">
                                    "{{ $group['items'][0]['notes'] }}"
                                </div>
                            @endif
                        </div>
                    @elseif($hasMultipleItems && !empty($group['flavor']))
                        <div class="px-4 pb-2 pt-0">
                            <div class="text-xs text-stone-500 italic break-words bg-stone-50 p-2 rounded-lg border border-stone-100">
                                Flavor: {{ $group['flavor']['name'] }}
                            </div>
                        </div>
                    @endif

                    <!-- Dropdown for individual items -->
                    @if($hasMultipleItems)
                        <div id="dropdown-{{$groupKey}}" class="hidden border-t border-stone-100 bg-stone-50">
                            @foreach($group['items'] as $item)
                                <div class="p-3 pl-6 border-b border-stone-100 last:border-b-0">
                                    <div class="flex flex-col gap-1.5 w-full">
                                        <div class="flex justify-between items-center w-full">
                                            <div class="flex items-center gap-3">
                                                <span class="text-sm text-stone-500 font-medium">Item {{ $loop->iteration }}</span>
                                                <span class="text-sm text-stone-600">Qty: {{ $item['quantity'] }}</span>
                                                <span class="text-sm font-semibold text-stone-800">${{ number_format($item['line_total'], 2) }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button onclick="openEditModal({{ $item['index'] }}, '{{ addslashes($group['name']) }}', {{ $item['quantity'] }}, {{ $group['unit_price'] }}, '{{ addslashes($item['notes'] ?? '') }}', {{ !empty($group['modifications']) ? 'true' : 'false' }}, {{ !empty($group['flavor']) ? json_encode($group['flavor']) : 'null' }})" class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded-lg font-medium hover:bg-amber-200 transition-colors cursor-pointer">Edit</button>
                                                <form method="POST" action="{{ route('kiosk.cart.remove', $item['index']) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-lg font-medium hover:bg-red-200 transition-colors cursor-pointer">Remove</button>
                                                </form>
                                            </div>
                                        </div>
                                        @if(!empty($item['notes']))
                                            <div class="text-xs text-stone-500 italic break-words pr-2">
                                                "{{ $item['notes'] }}"
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-center p-6 opacity-60">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-stone-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-stone-600 font-bold text-lg">Your cart is empty.</p>
                    <p class="text-stone-400 text-sm mt-2 font-medium">Tap an item to add it.</p>
                </div>
            @endforelse
        </div>
        
        <div class="p-6 bg-white border-t border-amber-200/50 shrink-0 shadow-[0_-5px_15px_rgba(0,0,0,0.02)]">
            <div class="flex justify-between items-center mb-6">
                <span class="text-lg font-bold text-stone-500">Total</span>
                <span class="font-display text-4xl font-semibold text-amber-950">${{ number_format($cartTotal, 2) }}</span>
            </div>
            @if(count($cart) > 0)
                <button type="button" onclick="openCheckoutModal()" class="w-full bg-amber-800 hover:bg-amber-900 text-amber-50 font-bold py-4 rounded-2xl text-xl shadow-lg shadow-amber-900/20 transition-all active:scale-97 flex items-center justify-center gap-2 cursor-pointer">
                    Checkout <span>→</span>
                </button>
            @else
                <button disabled class="w-full bg-stone-200 text-stone-400 font-bold py-4 rounded-2xl text-xl cursor-not-allowed">
                    Checkout
                </button>
            @endif
        </div>
    </aside>

    <!-- Middle: Items Grid (Main Scrollable Area) -->
    <main class="kiosk-main-content">
        @if(session('success'))
            {{--<div class="bg-amber-100 text-amber-900 p-4 rounded-xl mb-8 font-medium text-center shadow-sm border border-amber-200">
                {{ session('success') }}
            </div>--}}
        @endif

        @include('partials.promo-carousel', ['promos' => $promos])

            @forelse($menuItems as $category => $items)
                <div id="cat-{{ strtolower($category) }}" class="mb-14">
                    <h2 class="font-display text-3xl font-bold text-amber-950 mb-6 capitalize flex items-center gap-4">
                        {{ $category }}
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($items as $item)
                            @php
                                $isPacket = $category === 'packets';
                                $imagePath = $item->image;
                                // Ensure path starts with storage/
                                if ($imagePath && !str_starts_with($imagePath, 'storage/')) {
                                    $imagePath = 'storage/' . $imagePath;
                                }
                                $img = $imagePath ? asset($imagePath) : 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=600&q=80';
                                if($category === 'drinks' && !$item->image) $img = 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=600&q=80';
                                if($category === 'pastry' && !$item->image) $img = 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600&q=80';
                                if($category === 'promo' && !$item->image) $img = 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=600&q=80';
                            @endphp
                            @if($isPacket)
                                <button onclick="openPacketModal({{ $item->toJson() }}, '{{ $img }}')" class="item-card bg-white rounded-3xl shadow-sm border border-stone-200 flex flex-col text-left relative overflow-hidden group hover:shadow-xl hover:border-amber-300 cursor-pointer">
                                    <div class="w-full h-48 bg-stone-100 overflow-hidden relative">
                                        <img src="{{ $img }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="{{ $item->name }}">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    </div>
                                    <div class="p-5 flex flex-col flex-1 w-full">
                                        <div class="flex justify-between items-start gap-2 mb-2">
                                            <h3 class="font-display text-xl font-bold text-amber-950 leading-tight">{{ $item->name }}</h3>
                                        </div>
                                        <p class="text-sm text-stone-500 line-clamp-2 mb-4 flex-1 font-medium leading-relaxed">{{ $item->description }}</p>
                                        <div class="mt-auto flex items-center justify-between w-full">
                                            <span class="text-xl font-bold text-amber-800">${{ number_format($item->fixed_price, 2) }}</span>
                                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-lg group-hover:bg-amber-800 group-hover:text-white transition-colors">+</div>
                                        </div>
                                    </div>
                                </button>
                            @else
                                <button onclick="openItemModal({{ $item->toJson() }}, '{{ $img }}')" class="item-card bg-white rounded-3xl shadow-sm border border-stone-200 flex flex-col text-left relative overflow-hidden group hover:shadow-xl hover:border-amber-300 cursor-pointer">
                                    <div class="w-full h-48 bg-stone-100 overflow-hidden relative">
                                        <img src="{{ $img }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="{{ $item->name }}">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    </div>
                                    <div class="p-5 flex flex-col flex-1 w-full">
                                        <div class="flex justify-between items-start gap-2 mb-2">
                                            <h3 class="font-display text-xl font-bold text-amber-950 leading-tight">{{ $item->name }}</h3>
                                        </div>
                                        <p class="text-sm text-stone-500 line-clamp-2 mb-4 flex-1 font-medium leading-relaxed">{{ $item->description }}</p>
                                        <div class="mt-auto flex items-center justify-between w-full">
                                            <span class="text-xl font-bold text-amber-800">{{ $item->formattedPrice() }}</span>
                                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-lg group-hover:bg-amber-800 group-hover:text-white transition-colors">+</div>
                                        </div>
                                    </div>
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-20">
                    <p class="text-2xl text-stone-400 font-bold">No menu items available.</p>
                </div>
            @endforelse
        </div>
    </main>
</div>

<!-- Item Modal Backdrop -->
<!-- Item Modal Backdrop -->
<div id="itemModalBackdrop" class="kiosk-modal-backdrop" onclick="if(event.target === this) closeItemModal()">
    <!-- Modal Content -->
    <div id="itemModalContent" class="kiosk-modal-content">
        <form id="itemForm" method="POST" action="" class="flex flex-col h-full max-h-[90vh]">
            @csrf
            
            <!-- Header Image Area -->
            <div class="bg-stone-100 h-56 relative shrink-0">
                <img id="modalImg" src="" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <button type="button" onclick="closeItemModal()" class="absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow hover:bg-white text-xl font-bold text-stone-700 z-10 transition-colors cursor-pointer">×</button>
                <div class="absolute bottom-6 left-6 right-6">
                    <h2 id="modalTitle" class="font-display text-4xl font-bold text-white leading-tight drop-shadow-md mb-1">Item Name</h2>
                    <span id="modalPrice" class="font-display text-2xl font-bold text-amber-300 drop-shadow">$0.00</span>
                </div>
            </div>
            
            <div class="p-6 overflow-y-auto hide-scrollbar flex-1 bg-white">
                <p id="modalDesc" class="text-stone-600 mb-8 font-medium leading-relaxed text-lg"></p>
                
                <!-- Packet Contents -->
                <div id="packetContentsSection" class="mb-8">
                    <h3 class="font-display text-xl font-bold text-amber-950 mb-4 flex items-center gap-2">
                        Packet Contents
                        <div class="flex-1 h-px bg-amber-100"></div>
                    </h3>
                    <div id="packetContentsList" class="space-y-2">
                        <!-- Populated by JS -->
                        <div class="text-sm text-stone-600 p-3 bg-stone-50 rounded-lg border border-stone-200">Loading...</div>
                    </div>
                </div>

                <!-- Flavors -->
                <div id="flavorsSection" class="hidden">
                    <h3 class="font-display text-xl font-bold text-amber-950 mb-4 flex items-center gap-2">
                        Choose Flavor
                        <div class="flex-1 h-px bg-amber-100"></div>
                    </h3>
                    <div id="flavorsList" class="space-y-3 mb-8">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Customizations -->
                <div id="customizationsSection" class="hidden">
                    <h3 class="font-display text-xl font-bold text-amber-950 mb-4 flex items-center gap-2">
                        Customize
                        <div class="flex-1 h-px bg-amber-100"></div>
                    </h3>
                    <div id="customizationsList" class="space-y-3 mb-8">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-8">
                    <h3 class="font-display text-xl font-bold text-amber-950 mb-3">Special Notes</h3>
                    <textarea id="itemNotes" rows="2" class="w-full bg-stone-50 border-2 border-stone-100 rounded-xl p-4 focus:ring-0 focus:border-amber-300 transition-colors text-stone-700 resize-none font-medium placeholder:text-stone-400" placeholder="Any special requests?"></textarea>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3" data-speech-wrapper>
                        <div class="flex items-center gap-2">
                            <select data-speech-lang class="rounded-full border border-amber-200 bg-white px-3 py-2 text-sm font-bold text-amber-900 shadow-sm focus:border-amber-400 focus:ring-0">
                                <option value="en-US">English</option>
                                <option value="id-ID">Indonesia</option>
                                <option value="auto" selected>Auto</option>
                            </select>
                            <button type="button" data-speech-to-text data-target="itemNotes" class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white px-4 py-2 text-sm font-bold text-amber-800 shadow-sm transition-colors hover:bg-amber-50 disabled:cursor-not-allowed disabled:opacity-60">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 1.75a3.75 3.75 0 00-3.75 3.75v5a3.75 3.75 0 107.5 0v-5A3.75 3.75 0 0012 1.75z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 11.5a7.5 7.5 0 0015 0" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19.25v3" />
                                </svg>
                                <span data-speech-label>Speak</span>
                            </button>
                        </div>
                        <p data-speech-status class="text-xs text-stone-400"></p>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="flex items-center justify-between bg-[#faf6f0] p-5 rounded-2xl border border-amber-200/50 mt-auto">
                    <span class="font-bold text-stone-700 text-lg">Quantity</span>
                    <div class="flex items-center gap-4 bg-white rounded-xl shadow-sm p-1.5 border border-amber-100">
                        <button type="button" onclick="updateQty(-1)" class="w-12 h-12 flex items-center justify-center text-2xl font-bold text-amber-700 hover:bg-amber-50 rounded-lg rounded-r-none transition-colors cursor-pointer">-</button>
                        <input type="number" id="qtyInput" name="quantity" value="1" min="1" max="20" class="w-12 text-center font-bold text-2xl text-stone-800 p-0 border-none focus:ring-0 appearance-none bg-transparent" readonly>
                        <button type="button" onclick="updateQty(1)" class="w-12 h-12 flex items-center justify-center text-2xl font-bold text-amber-700 hover:bg-amber-50 rounded-lg rounded-l-none transition-colors cursor-pointer">+</button>
                    </div>
                </div>
            </div>
            
            <!-- Footer Action -->
            <div class="p-6 bg-white border-t border-amber-100 shrink-0">
                <button type="button" onclick="submitAddItem()" class="w-full bg-amber-800 hover:bg-amber-900 text-amber-50 font-bold py-5 rounded-2xl text-xl shadow-xl shadow-amber-900/20 transition-transform active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                    Add to Order <span id="modalTotalBtn" class="ml-2 font-normal opacity-90"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal Backdrop -->
<div id="editModalBackdrop" class="kiosk-modal-backdrop" onclick="if(event.target === this) closeEditModal()">
    <div id="editModalContent" class="kiosk-modal-content max-w-md">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 id="editModalTitle" class="font-display text-2xl font-bold text-amber-950">Edit Item</h2>
                <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center text-sm hover:bg-red-500 hover:text-white transition-colors cursor-pointer">×</button>
            </div>

            <div class="space-y-6">
                <!-- No Options Message -->
                <div id="editNoOptions" class="hidden text-center py-8">
                    <p class="text-stone-500 font-medium">No additional options available for this item.</p>
                </div>

                <!-- Edit Notes -->
                <div>
                    <h3 class="font-bold text-stone-700 mb-2">Special Notes</h3>
                    <textarea id="editItemNotes" rows="2" class="w-full bg-stone-50 border-2 border-stone-100 rounded-xl p-3 focus:ring-0 focus:border-amber-300 transition-colors text-stone-700 resize-none font-medium placeholder:text-stone-400" placeholder="Any special requests?"></textarea>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3" data-speech-wrapper>
                        <div class="flex items-center gap-2">
                            <select data-speech-lang class="rounded-full border border-amber-200 bg-white px-3 py-2 text-sm font-bold text-amber-900 shadow-sm focus:border-amber-400 focus:ring-0">
                                <option value="en-US">English</option>
                                <option value="id-ID">Indonesia</option>
                                <option value="auto" selected>Auto</option>
                            </select>
                            <button type="button" data-speech-to-text data-target="editItemNotes" class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white px-4 py-2 text-sm font-bold text-amber-800 shadow-sm transition-colors hover:bg-amber-50 disabled:cursor-not-allowed disabled:opacity-60">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 1.75a3.75 3.75 0 00-3.75 3.75v5a3.75 3.75 0 107.5 0v-5A3.75 3.75 0 0012 1.75z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 11.5a7.5 7.5 0 0015 0" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19.25v3" />
                                </svg>
                                <span data-speech-label>Speak</span>
                            </button>
                        </div>
                        <p data-speech-status class="text-xs text-stone-400"></p>
                    </div>
                </div>

                <!-- Quantity -->
                <div id="editQuantitySection" class="flex items-center justify-between bg-[#faf6f0] p-5 rounded-2xl border border-amber-200/50">
                    <span class="font-bold text-stone-700 text-lg">Quantity</span>
                    <div class="flex items-center gap-4 bg-white rounded-xl shadow-sm p-1.5 border border-amber-100">
                        <button type="button" onclick="updateEditQty(-1)" class="w-12 h-12 flex items-center justify-center text-2xl font-bold text-amber-700 hover:bg-amber-50 rounded-lg rounded-r-none transition-colors cursor-pointer">-</button>
                        <input type="number" id="editQtyInput" value="1" min="1" max="20" class="w-12 text-center font-bold text-2xl text-stone-800 p-0 border-none focus:ring-0 appearance-none bg-transparent" readonly>
                        <button type="button" onclick="updateEditQty(1)" class="w-12 h-12 flex items-center justify-center text-2xl font-bold text-amber-700 hover:bg-amber-50 rounded-lg rounded-l-none transition-colors cursor-pointer">+</button>
                    </div>
                </div>

                <input type="hidden" id="editUnitPrice" value="0">

                <div class="flex gap-3">
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-stone-200 text-stone-700 font-bold py-3 rounded-xl hover:bg-stone-300 transition-colors cursor-pointer">Cancel</button>
                    <button type="button" onclick="saveEdit()" class="flex-1 bg-amber-800 hover:bg-amber-900 text-white font-bold py-3 rounded-xl transition-colors cursor-pointer">Save <span id="editTotalBtn" class="ml-1 font-normal opacity-90"></span></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal Backdrop -->
<div id="checkoutModalBackdrop" class="kiosk-modal-backdrop" onclick="if(event.target === this) closeCheckoutModal()">
    <div id="checkoutModalContent" class="kiosk-modal-content max-w-3xl max-h-[90vh] overflow-y-auto">
        @php
            $isTakeout = session('kiosk_order_type') === 'takeout';
            $cart = session('kiosk_cart', []);
            $cartTotal = collect($cart)->sum('line_total');
            $paymentSettings = \App\Models\PaymentSettings::getSettings();
        @endphp
        <div class="p-4 sm:p-6 lg:p-8 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 shrink-0">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-700">Checkout</p>
                <h1 class="font-display text-2xl sm:text-3xl font-semibold text-amber-950">Complete payment</h1>
                <p class="text-sm text-stone-500 mt-1">{{ $isTakeout ? 'Takeout order' : 'Dine-in order' }}</p>
            </div>
            <div class="px-3 py-1.5 sm:px-4 sm:py-2 rounded-full bg-amber-50 text-amber-800 font-bold border border-amber-200 text-sm sm:text-base">
                {{ $isTakeout ? 'Takeout' : 'Table' }}
            </div>
        </div>

        <div class="px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6 shrink-0">
            <x-flash />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr]">
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
                    <p class="text-sm font-semibold text-amber-900">After payment, you'll get an order number.</p>
                    <p class="text-sm text-stone-600 mt-1">{{ $isTakeout ? 'Takeout pickup' : 'Table service' }} will show on the receipt screen.</p>
                </div>

                <div class="mt-4 sm:mt-6 flex gap-2 sm:gap-3">
                    <button type="button" onclick="closeCheckoutModal()" class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold py-4 sm:py-5 px-6 rounded-2xl text-base sm:text-lg transition-colors flex items-center justify-center">
                        Back
                    </button>
                    <button type="button" onclick="submitKioskPayment()" class="flex-[2] bg-amber-800 hover:bg-amber-900 text-amber-50 font-bold py-4 sm:py-5 px-8 rounded-2xl text-base sm:text-lg shadow-lg shadow-amber-900/20 transition-transform active:scale-95">
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

<!-- Promo Modal Backdrop -->
<div id="promoModalBackdrop" class="kiosk-modal-backdrop" onclick="if(event.target === this) closePromoModal()">
    <div id="promoModalContent" class="kiosk-modal-content max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="relative">
            <img id="promoModalImg" src="" class="w-full h-64 object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
            <button type="button" onclick="closePromoModal()" class="absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow hover:bg-white text-xl font-bold text-stone-700 z-10 transition-colors cursor-pointer">×</button>
            <div class="absolute bottom-6 left-6 right-6">
                <h2 id="promoModalTitle" class="font-display text-3xl font-bold text-white leading-tight drop-shadow-md"></h2>
            </div>
        </div>
        <div class="p-6">
            <p id="promoModalDescription" class="text-stone-600 mb-6 font-medium leading-relaxed"></p>

            <div id="promoRulesSection" class="space-y-4">
                <!-- Promo rules will be populated here -->
            </div>

            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closePromoModal()" class="flex-1 bg-stone-200 text-stone-700 font-bold py-3 rounded-xl hover:bg-stone-300 transition-colors cursor-pointer">Close</button>
                <button type="button" onclick="applyPromo()" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 rounded-xl transition-colors cursor-pointer">Apply Promo</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentBasePrice = 0;
    let currentEditIndex = null;
    let selectedMethod = null;
    
    function toggleDropdown(groupKey) {
        const dropdown = document.getElementById('dropdown-' + groupKey);
        const icon = document.getElementById('icon-' + groupKey);
        
        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
        } else {
            dropdown.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    }

    function toggleKioskCart() {
        document.getElementById('kioskCartSidebar')?.classList.toggle('is-open');
    }
    
    function openEditModal(index, name, quantity, unitPrice, notes = '', hasModifications = false, flavor = null) {
        currentEditIndex = index;
        document.getElementById('editModalTitle').innerText = name;
        document.getElementById('editQtyInput').value = quantity;
        document.getElementById('editUnitPrice').value = unitPrice.toFixed(2);
        document.getElementById('editItemNotes').value = notes || '';

        const noOptionsSection = document.getElementById('editNoOptions');
        const quantitySection = document.getElementById('editQuantitySection');

        if (hasModifications) {
            noOptionsSection.classList.add('hidden');
            quantitySection.classList.remove('hidden');
        } else {
            noOptionsSection.classList.remove('hidden');
            quantitySection.classList.add('hidden');
        }

        const backdrop = document.getElementById('editModalBackdrop');
        backdrop.classList.add('show');
        updateEditTotal();
    }

    function closeEditModal() {
        const backdrop = document.getElementById('editModalBackdrop');
        backdrop.classList.remove('show');
        currentEditIndex = null;
    }
    
    function updateEditQty(change) {
        const input = document.getElementById('editQtyInput');
        let val = parseInt(input.value) + change;
        if (val < 1) val = 1;
        if (val > 20) val = 20;
        input.value = val;
        updateEditTotal();
    }
    
    function updateEditTotal() {
        const qty = parseInt(document.getElementById('editQtyInput').value || 1);
        const unitPrice = parseFloat(document.getElementById('editUnitPrice').value || 0);
        const total = unitPrice * qty;
        document.getElementById('editTotalBtn').innerText = `($${total.toFixed(2)})`;
    }
    
    function saveEdit() {
        if (currentEditIndex === null) return;

        const qty = parseInt(document.getElementById('editQtyInput').value);
        const unitPrice = parseFloat(document.getElementById('editUnitPrice').value);
        const notes = document.getElementById('editItemNotes').value;

        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/kiosk/cart/update/${currentEditIndex}`;

        // Get CSRF token from the hidden input in the page
        const csrfToken = document.querySelector('input[name="_token"]')?.value;
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PATCH';
        form.appendChild(methodInput);

        const qtyInput = document.createElement('input');
        qtyInput.type = 'hidden';
        qtyInput.name = 'quantity';
        qtyInput.value = qty;
        form.appendChild(qtyInput);

        const notesInput = document.createElement('input');
        notesInput.type = 'hidden';
        notesInput.name = 'notes';
        notesInput.value = notes;
        form.appendChild(notesInput);

        document.body.appendChild(form);
        form.submit();
    }

    function submitAddItem() {
        const form = document.getElementById('itemForm');
        const notes = document.getElementById('itemNotes')?.value || '';
        const existing = form.querySelector('input[name="notes"]');

        if (existing) {
            existing.remove();
        }

        const notesInput = document.createElement('input');
        notesInput.type = 'hidden';
        notesInput.name = 'notes';
        notesInput.value = notes;
        form.appendChild(notesInput);
        form.submit();
    }
    
    function openItemModal(item, imgSrc) {
        document.getElementById('itemForm').action = `/kiosk/cart/${item.id}`;
        document.getElementById('modalImg').src = imgSrc;
        document.getElementById('modalTitle').innerText = item.name;
        document.getElementById('modalDesc').innerText = item.description || '';
        
        // Handle both regular menu items (price) and packets (fixed_price)
        const price = item.price || item.fixed_price || 0;
        document.getElementById('modalPrice').innerText = '$' + parseFloat(price).toFixed(2);
        
        currentBasePrice = parseFloat(price);
        document.getElementById('qtyInput').value = 1;
        document.getElementById('itemNotes').value = '';
        
        // Hide packet contents section
        document.getElementById('packetContentsSection').classList.add('hidden');
        document.getElementById('packetContentsList').innerHTML = '';
        
        const flavorSection = document.getElementById('flavorsSection');
        const flavorList = document.getElementById('flavorsList');

        if (item.flavors && item.flavors.length > 0) {
            flavorSection.classList.remove('hidden');
            let html = '';
            item.flavors.forEach((flavor, index) => {
                let priceText = parseFloat(flavor.additional_price) > 0 ? `<span class="text-amber-700 font-bold text-lg">+$${parseFloat(flavor.additional_price).toFixed(2)}</span>` : '';
                html += `
                <label class="flex items-center justify-between p-4 rounded-xl border-2 border-stone-100 hover:border-amber-200 cursor-pointer transition-all has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 group shadow-sm hover:shadow">
                    <div class="flex items-center gap-4">
                        <div class="relative flex items-center">
                            <input type="radio" name="flavor" value="${flavor.id}" data-price="${flavor.additional_price}" onchange="updateModalTotal()" ${index === 0 ? 'checked' : ''} class="peer w-6 h-6 border-stone-300 text-amber-600 focus:ring-amber-500 transition-all cursor-pointer">
                        </div>
                        <span class="font-bold text-stone-700 group-hover:text-stone-900 text-lg">${flavor.name}</span>
                    </div>
                    ${priceText}
                </label>`;
            });
            flavorList.innerHTML = html;
        } else {
            flavorSection.classList.add('hidden');
            flavorList.innerHTML = '';
        }

        const custSection = document.getElementById('customizationsSection');
        const custList = document.getElementById('customizationsList');

        if (item.modifications && item.modifications.length > 0) {
            custSection.classList.remove('hidden');
            let html = '';
            item.modifications.forEach(mod => {
                let priceText = parseFloat(mod.additional_price) > 0 ? `<span class="text-amber-700 font-bold text-lg">+$${parseFloat(mod.additional_price).toFixed(2)}</span>` : '';
                html += `
                <label class="flex items-center justify-between p-4 rounded-xl border-2 border-stone-100 hover:border-amber-200 cursor-pointer transition-all has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 group shadow-sm hover:shadow">
                    <div class="flex items-center gap-4">
                        <div class="relative flex items-center">
                            <input type="checkbox" name="modifications[]" value="${mod.id}" data-price="${mod.additional_price}" onchange="updateModalTotal()" class="peer w-6 h-6 rounded border-stone-300 text-amber-600 focus:ring-amber-500 transition-all cursor-pointer">
                        </div>
                        <span class="font-bold text-stone-700 group-hover:text-stone-900 text-lg">${mod.name}</span>
                    </div>
                    ${priceText}
                </label>`;
            });
            custList.innerHTML = html;
        } else {
            custSection.classList.add('hidden');
            custList.innerHTML = '';
        }

        updateModalTotal();

        const backdrop = document.getElementById('itemModalBackdrop');
        backdrop.classList.add('show');
    }

    function openPacketModal(packet, imgSrc) {
        document.getElementById('itemForm').action = `/kiosk/packet/${packet.id}/add`;
        document.getElementById('modalImg').src = imgSrc;
        document.getElementById('modalTitle').innerText = packet.name;
        document.getElementById('modalDesc').innerText = packet.description || '';
        
        // Handle packets (fixed_price)
        const price = packet.fixed_price || 0;
        document.getElementById('modalPrice').innerText = '$' + parseFloat(price).toFixed(2);
        
        currentBasePrice = parseFloat(price);
        document.getElementById('qtyInput').value = 1;
        document.getElementById('itemNotes').value = '';
        
        // Hide flavors and customizations for packets
        document.getElementById('flavorsSection').classList.add('hidden');
        document.getElementById('flavorsList').innerHTML = '';
        document.getElementById('customizationsSection').classList.add('hidden');
        document.getElementById('customizationsList').innerHTML = '';
        
        // Show packet contents
        const packetContentsSection = document.getElementById('packetContentsSection');
        const packetContentsList = document.getElementById('packetContentsList');
        
        console.log('Opening packet modal with data:', packet);
        
        if (packet.items && packet.items.length > 0) {
            packetContentsSection.classList.remove('hidden');
            let html = '';
            packet.items.forEach(packetItem => {
                const qty = packetItem.pivot ? packetItem.pivot.quantity : 1;
                html += `<div class="text-sm text-stone-600 p-3 bg-stone-50 rounded-lg border border-stone-200">${packetItem.name} x${qty}</div>`;
            });
            packetContentsList.innerHTML = html;
        } else {
            packetContentsSection.classList.add('hidden');
            packetContentsList.innerHTML = '<div class="text-sm text-stone-500 italic">No items in this packet</div>';
        }

        updateModalTotal();

        const backdrop = document.getElementById('itemModalBackdrop');
        backdrop.classList.add('show');
    }

    function closeItemModal() {
        const backdrop = document.getElementById('itemModalBackdrop');
        backdrop.classList.remove('show');
    }
    
    function updateQty(change) {
        const input = document.getElementById('qtyInput');
        let val = parseInt(input.value) + change;
        if (val < 1) val = 1;
        if (val > 20) val = 20;
        input.value = val;
        updateModalTotal();
    }
    
    function updateModalTotal() {
        let total = currentBasePrice;
        
        // Add flavor price
        const flavorRadio = document.querySelector('#flavorsList input[type="radio"]:checked');
        if (flavorRadio) {
            total += parseFloat(flavorRadio.dataset.price || 0);
        }
        
        // Add modification prices
        const checkboxes = document.querySelectorAll('#customizationsList input[type="checkbox"]:checked');
        checkboxes.forEach(cb => {
            total += parseFloat(cb.dataset.price || 0);
        });
        
        const qty = parseInt(document.getElementById('qtyInput').value || 1);
        total = total * qty;
        
        document.getElementById('modalTotalBtn').innerText = `($${total.toFixed(2)})`;
    }

    function openCheckoutModal() {
        const backdrop = document.getElementById('checkoutModalBackdrop');
        backdrop.classList.add('show');
        selectMethod('qr');
    }

    function closeCheckoutModal() {
        const backdrop = document.getElementById('checkoutModalBackdrop');
        backdrop.classList.remove('show');
        selectedMethod = null;
    }

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

            const contentType = response.headers.get('content-type');
            
            if (!response.ok) {
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    alert(data.message || 'Payment failed. Please try again.');
                } else {
                    // If not JSON, check if we were redirected
                    if (response.redirected) {
                        window.location.href = response.url;
                    } else {
                        alert('Payment failed. Please try again.');
                    }
                }
                return;
            }

            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                window.location.href = data.redirect_url || '/kiosk/success';
            } else {
                // If not JSON but successful, check for redirect
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    window.location.href = '/kiosk/success';
                }
            }
        } catch (error) {
            console.error(error);
            alert('Payment failed. Please try again.');
        }
    }

    window.openPromoModal = function(promo, imgSrc) {
        document.getElementById('promoModalImg').src = imgSrc;
        document.getElementById('promoModalTitle').innerText = promo.title || 'Special Promotion';
        document.getElementById('promoModalDescription').innerText = promo.description || '';

        const rulesSection = document.getElementById('promoRulesSection');
        let rulesHtml = '';

        if (promo.rules && promo.rules.length > 0) {
            rulesHtml = '<h3 class="font-display text-xl font-bold text-amber-950 mb-4">Promo Details</h3>';
            promo.rules.forEach(rule => {
                if (rule.buy_item && rule.get_item) {
                    rulesHtml += `
                        <div class="bg-amber-50 rounded-xl p-4 border border-amber-200">
                            <p class="font-semibold text-amber-950">Buy ${rule.buy_quantity}x ${rule.buy_item.name}</p>
                            <p class="text-amber-800 mt-1">Get ${rule.get_quantity}x ${rule.get_item.name}</p>
                        </div>
                    `;
                }
            });
        }

        if (promo.discount_value) {
            rulesHtml += `
                <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                    <p class="font-semibold text-green-950">For $${promo.discount_value}</p>
                </div>
            `;
        }

        if (!rulesHtml) {
            rulesHtml = '<p class="text-stone-500 italic">No additional details available.</p>';
        }

        rulesSection.innerHTML = rulesHtml;

        const backdrop = document.getElementById('promoModalBackdrop');
        backdrop.classList.add('show');
    };

    window.closePromoModal = function() {
        const backdrop = document.getElementById('promoModalBackdrop');
        backdrop.classList.remove('show');
    };

    let currentPromo = null;

    window.openPromoModal = function(promo, imgSrc) {
        currentPromo = promo;
        document.getElementById('promoModalImg').src = imgSrc;
        document.getElementById('promoModalTitle').innerText = promo.title || 'Special Promotion';
        document.getElementById('promoModalDescription').innerText = promo.description || '';

        const rulesSection = document.getElementById('promoRulesSection');
        let rulesHtml = '';

        if (promo.rules && promo.rules.length > 0) {
            rulesHtml = '<h3 class="font-display text-xl font-bold text-amber-950 mb-4">Promo Details</h3>';
            promo.rules.forEach(rule => {
                let promoText = '';
                if (rule.buy_item) {
                    promoText = `Buy ${rule.buy_quantity}x ${rule.buy_item.name}`;
                }
                if (rule.get_item) {
                    promoText += promoText ? ', ' : '';
                    promoText += `Get ${rule.get_quantity}x ${rule.get_item.name}`;
                }
                if (promo.discount_value) {
                    promoText += promoText ? ', ' : '';
                    promoText += `For $${promo.discount_value}`;
                }

                if (promoText) {
                    rulesHtml += `
                        <div class="bg-amber-50 rounded-xl p-4 border border-amber-200">
                            <p class="font-semibold text-amber-950">${promoText}</p>
                        </div>
                    `;
                }
            });
        }

        if (promo.discount_value) {
            if (!rulesHtml) {
                rulesHtml = '<h3 class="font-display text-xl font-bold text-amber-950 mb-4">Promo Details</h3>';
            }
            rulesHtml += `
                <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                    <p class="font-semibold text-green-950">For $${promo.discount_value}</p>
                </div>
            `;
        }

        if (!rulesHtml) {
            rulesHtml = '<p class="text-stone-500 italic">No additional details available.</p>';
        }

        rulesSection.innerHTML = rulesHtml;

        const backdrop = document.getElementById('promoModalBackdrop');
        backdrop.classList.add('show');
    };

    window.applyPromo = function() {
        if (!currentPromo) return;

        // Store promo in session via AJAX
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/kiosk/promo/apply';

        const csrfToken = document.querySelector('input[name="_token"]')?.value;
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }

        const promoInput = document.createElement('input');
        promoInput.type = 'hidden';
        promoInput.name = 'promo_id';
        promoInput.value = currentPromo.id;
        form.appendChild(promoInput);

        document.body.appendChild(form);
        form.submit();
    };

</script>
@include('partials.speech-to-text')
@endsection
