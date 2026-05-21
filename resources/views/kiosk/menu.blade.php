@extends('kiosk.layout')

@section('content')
<!-- Header -->
<header class="fixed top-0 left-0 right-0 bg-[#faf6f0] border-b border-amber-200/60 px-6 py-4 flex justify-between items-center z-50 h-20 shadow-sm">
    <div class="flex items-center gap-4">
        <a href="{{ route('kiosk.welcome') }}" class="text-3xl hover:scale-110 transition-transform bg-white w-12 h-12 flex items-center justify-center rounded-full shadow-sm border border-amber-100">⬅️</a>
        <h1 class="font-display text-2xl font-bold text-amber-950 tracking-tight">Golden Crumb</h1>
    </div>
    <div class="bg-amber-100 text-amber-900 px-5 py-2.5 rounded-full font-bold text-sm tracking-wide uppercase shadow-sm border border-amber-200/50">
        {{ str_replace('_', ' ', session('kiosk_order_type', '')) }}
    </div>
</header>

<!-- Left Sidebar: Categories -->
<aside class="fixed top-20 left-0 bottom-0 w-24 md:w-32 bg-[#faf6f0] border-r border-amber-200/50 overflow-y-auto hide-scrollbar z-40 py-6 flex flex-col items-center gap-6 shadow-[2px_0_10px_rgba(0,0,0,0.02)]">
    @foreach(['food' => '🍞', 'drinks' => '☕', 'pastry' => '🥐', 'promo' => '⭐'] as $cat => $icon)
        <button onclick="document.getElementById('cat-{{ $cat }}')?.scrollIntoView({behavior: 'smooth', block: 'start'})" 
            class="flex flex-col items-center gap-2 p-3 w-20 md:w-24 rounded-2xl transition-all hover:bg-amber-50 hover:scale-105 group focus:outline-none">
            <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center text-3xl group-hover:bg-amber-100 group-hover:shadow-inner transition-colors border border-amber-100 shadow-sm">
                {{ $icon }}
            </div>
            <span class="text-xs font-bold text-stone-600 uppercase tracking-wider group-hover:text-amber-800">{{ $cat }}</span>
        </button>
    @endforeach
</aside>

<!-- Right Sidebar: Cart -->
<aside class="fixed top-20 right-0 bottom-0 w-80 lg:w-96 bg-[#faf6f0] border-l border-amber-200/60 flex flex-col z-40 shadow-[-5px_0_20px_rgba(0,0,0,0.02)]">
    <div class="p-6 border-b border-amber-200/50 bg-[#faf6f0] shrink-0 flex justify-between items-center">
        <h2 class="font-display text-2xl font-semibold text-amber-950 flex items-center gap-2">
            🛍️ Your Order
        </h2>
        <span class="bg-amber-800 text-white text-sm font-bold w-8 h-8 flex items-center justify-center rounded-full">{{ count($cart) }}</span>
    </div>
    
    <div class="flex-1 overflow-y-auto p-4 space-y-4 hide-scrollbar">
        @forelse($cart as $index => $cartItem)
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-stone-200 flex gap-3 relative hover:border-amber-300 transition-colors">
                <div class="flex-1 pr-8">
                    <div class="flex justify-between items-start mb-1">
                        <h4 class="font-semibold text-amber-950 leading-tight">{{ $cartItem['name'] }}</h4>
                        <span class="font-semibold text-stone-800">${{ number_format($cartItem['line_total'], 2) }}</span>
                    </div>
                    <div class="text-sm text-stone-500 font-medium">Qty: {{ $cartItem['quantity'] }}</div>
                    @if(!empty($cartItem['modifications']))
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach($cartItem['modifications'] as $mod)
                                <div class="text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-100">
                                    + {{ $mod['name'] }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <form method="POST" action="{{ route('kiosk.cart.remove', $index) }}" class="absolute top-3 right-3">
                    @csrf
                    <button type="submit" class="w-7 h-7 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center text-sm hover:bg-red-500 hover:text-white hover:border-red-500 transition-colors border border-stone-200">×</button>
                </form>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center h-full text-center p-6 opacity-60">
                <span class="text-6xl mb-4 grayscale opacity-50">🛒</span>
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
            <form method="POST" action="{{ route('kiosk.checkout') }}">
                @csrf
                <button type="submit" class="w-full bg-amber-800 hover:bg-amber-900 text-amber-50 font-bold py-4 rounded-2xl text-xl shadow-lg shadow-amber-900/20 transition-transform active:scale-95 flex items-center justify-center gap-2">
                    Checkout <span>→</span>
                </button>
            </form>
        @else
            <button disabled class="w-full bg-stone-200 text-stone-400 font-bold py-4 rounded-2xl text-xl cursor-not-allowed">
                Checkout
            </button>
        @endif
    </div>
</aside>

<!-- Middle: Items Grid (Main Scrollable Area) -->
<main class="pt-24 pb-12 pl-24 md:pl-[140px] pr-80 lg:pr-[400px] min-h-screen bg-white">
    <div class="max-w-5xl mx-auto px-4 md:px-8">
        @if(session('success'))
            <div class="bg-amber-100 text-amber-900 p-4 rounded-xl mb-8 font-medium text-center shadow-sm border border-amber-200">
                {{ session('success') }}
            </div>
        @endif

        @forelse($menuItems as $category => $items)
            <div id="cat-{{ strtolower($category) }}" class="mb-14 scroll-mt-28">
                <h2 class="font-display text-3xl font-bold text-amber-950 mb-6 capitalize flex items-center gap-4">
                    {{ $category }}
                    <div class="h-[2px] bg-amber-100 flex-1 rounded-full"></div>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($items as $item)
                        @php
                            $img = 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=600&q=80';
                            if($category === 'drinks') $img = 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=600&q=80';
                            if($category === 'pastry') $img = 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600&q=80';
                            if($category === 'promo') $img = 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=600&q=80';
                        @endphp
                        <button onclick="openItemModal({{ $item->toJson() }}, '{{ $img }}')" class="item-card bg-white rounded-3xl shadow-sm border border-stone-200 flex flex-col text-left relative overflow-hidden group hover:shadow-xl hover:border-amber-300">
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

<!-- Item Modal Backdrop -->
<div id="itemModalBackdrop" class="fixed inset-0 bg-amber-950/60 backdrop-blur-md z-[100] hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <!-- Modal Content -->
    <div id="itemModalContent" class="bg-white rounded-[2rem] w-full max-w-lg max-h-[90vh] flex flex-col shadow-2xl overflow-hidden scale-95 transition-transform duration-300 relative border border-amber-100">
        <form id="itemForm" method="POST" action="" class="flex flex-col h-full max-h-[90vh]">
            @csrf
            
            <!-- Header Image Area -->
            <div class="bg-stone-100 h-56 relative shrink-0">
                <img id="modalImg" src="" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <button type="button" onclick="closeItemModal()" class="absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow hover:bg-white text-xl font-bold text-stone-700 z-10 transition-colors">×</button>
                <div class="absolute bottom-6 left-6 right-6">
                    <h2 id="modalTitle" class="font-display text-4xl font-bold text-white leading-tight drop-shadow-md mb-1">Item Name</h2>
                    <span id="modalPrice" class="font-display text-2xl font-bold text-amber-300 drop-shadow">$0.00</span>
                </div>
            </div>
            
            <div class="p-6 overflow-y-auto hide-scrollbar flex-1 bg-white">
                <p id="modalDesc" class="text-stone-600 mb-8 font-medium leading-relaxed text-lg"></p>
                
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

                <!-- Quantity -->
                <div class="flex items-center justify-between bg-[#faf6f0] p-5 rounded-2xl border border-amber-200/50 mt-auto">
                    <span class="font-bold text-stone-700 text-lg">Quantity</span>
                    <div class="flex items-center gap-4 bg-white rounded-xl shadow-sm p-1.5 border border-amber-100">
                        <button type="button" onclick="updateQty(-1)" class="w-12 h-12 flex items-center justify-center text-2xl font-bold text-amber-700 hover:bg-amber-50 rounded-lg rounded-r-none transition-colors">-</button>
                        <input type="number" id="qtyInput" name="quantity" value="1" min="1" max="20" class="w-12 text-center font-bold text-2xl text-stone-800 p-0 border-none focus:ring-0 appearance-none bg-transparent" readonly>
                        <button type="button" onclick="updateQty(1)" class="w-12 h-12 flex items-center justify-center text-2xl font-bold text-amber-700 hover:bg-amber-50 rounded-lg rounded-l-none transition-colors">+</button>
                    </div>
                </div>
            </div>
            
            <!-- Footer Action -->
            <div class="p-6 bg-white border-t border-amber-100 shrink-0">
                <button type="submit" class="w-full bg-amber-800 hover:bg-amber-900 text-amber-50 font-bold py-5 rounded-2xl text-xl shadow-xl shadow-amber-900/20 transition-transform active:scale-95 flex items-center justify-center gap-2">
                    Add to Order <span id="modalTotalBtn" class="ml-2 font-normal opacity-90"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentBasePrice = 0;
    
    function openItemModal(item, imgSrc) {
        document.getElementById('itemForm').action = `/kiosk/cart/${item.id}`;
        document.getElementById('modalImg').src = imgSrc;
        document.getElementById('modalTitle').innerText = item.name;
        document.getElementById('modalDesc').innerText = item.description || '';
        document.getElementById('modalPrice').innerText = '$' + parseFloat(item.price).toFixed(2);
        
        currentBasePrice = parseFloat(item.price);
        document.getElementById('qtyInput').value = 1;
        
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
        const content = document.getElementById('itemModalContent');
        backdrop.classList.remove('hidden');
        void backdrop.offsetWidth;
        backdrop.classList.remove('opacity-0');
        content.classList.remove('scale-95');
    }
    
    function closeItemModal() {
        const backdrop = document.getElementById('itemModalBackdrop');
        const content = document.getElementById('itemModalContent');
        backdrop.classList.add('opacity-0');
        content.classList.add('scale-95');
        setTimeout(() => {
            backdrop.classList.add('hidden');
        }, 300);
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
        const checkboxes = document.querySelectorAll('#customizationsList input[type="checkbox"]:checked');
        checkboxes.forEach(cb => {
            total += parseFloat(cb.dataset.price || 0);
        });
        
        const qty = parseInt(document.getElementById('qtyInput').value || 1);
        total = total * qty;
        
        document.getElementById('modalTotalBtn').innerText = `($${total.toFixed(2)})`;
    }
</script>
@endsection
