@extends('layouts.table')

@section('title', 'Menu')

@section('content')
    @php
        $brandSettings = \App\Models\BrandSettings::getSettings();
    @endphp
<div class="kiosk-container">
    <!-- Header -->
    <header class="kiosk-header shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('table.welcome') }}" class="hover:scale-115 transition-transform bg-white w-12 h-12 flex items-center justify-center rounded-full shadow-sm border border-amber-100 text-amber-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="font-display text-2xl font-bold text-primary-font tracking-tight">{{ $brandSettings->app_name }}</h1>
        </div>
        @if (session('cafe_table_name'))
            <div class="bg-amber-100 text-amber-900 px-5 py-2.5 rounded-full font-bold text-sm tracking-wide uppercase shadow-sm border border-amber-200/50">
                {{ session('cafe_table_name') }}
            </div>
        @endif
    </header>

    <!-- Left Sidebar: Categories -->
    <aside class="kiosk-sidebar-left hide-scrollbar">
        @foreach(\App\Models\MenuCategory::visible()->get() as $category)
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
    <aside style="position:fixed;top:70px;right:0;bottom:0;width:350px;height:calc(100vh - 70px);z-index:40;display:flex;flex-direction:column;overflow:hidden;background-color:#faf6f0;border-left:1px solid rgba(217,119,6,0.15);">
        <div class="p-6 border-b border-amber-200/50 bg-[#faf6f0] shrink-0 flex justify-between items-center">
            <h2 class="font-display text-2xl font-semibold text-amber-950 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-amber-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Your Order
            </h2>
            <span class="bg-amber-800 text-white text-sm font-bold w-8 h-8 flex items-center justify-center rounded-full shadow-sm">{{ $cartCount }}</span>
        </div>

        <div class="hide-scrollbar" style="position:absolute;top:80px;left:0;right:0;bottom:140px;overflow-y:auto;padding:1rem;">
            @php
                // Group cart items by menu_item_id
                $groupedCart = [];
                if (!empty($cart) && is_array($cart)) {
                    foreach($cart as $index => $cartItem) {
                        $key = $cartItem['menu_item_id'];
                        if (!isset($groupedCart[$key])) {
                            $groupedCart[$key] = [
                                'name' => $cartItem['name'],
                                'menu_item_id' => $cartItem['menu_item_id'],
                                'unit_price' => $cartItem['unit_price'],
                                'items' => []
                            ];
                        }
                        $groupedCart[$key]['items'][] = [
                            'index' => $index,
                            'quantity' => $cartItem['quantity'],
                            'line_total' => $cartItem['unit_price'] * $cartItem['quantity'],
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
                <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
                    <div class="p-4 flex items-center gap-4 hover:border-amber-300 transition-colors">
                    <div class="flex-1">
                        <div class="flex justify-between items-center">
                            <h4 class="font-semibold text-amber-950">{{ $group['name'] }}</h4>
                            <span class="font-semibold text-stone-800">${{ number_format($totalPrice, 2) }}</span>
                        </div>
                        <div class="text-sm text-stone-500 font-medium">Qty: {{ $totalQty }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($hasMultipleItems)
                            <button onclick="toggleDropdown('{{$groupKey}}')" class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-sm hover:bg-amber-200 transition-colors border border-amber-200 cursor-pointer">
                                <svg id="icon-{{$groupKey}}" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        @else
                            <button onclick="openEditModal({{ $group['items'][0]['index'] }}, '{{ addslashes($group['name']) }}', {{ $group['items'][0]['quantity'] }}, {{ $group['unit_price'] }}, '{{ addslashes($group['items'][0]['notes'] ?? '') }}', {{ !empty($group['modifications']) ? 'true' : 'false' }})" class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded-lg font-medium hover:bg-amber-200 transition-colors cursor-pointer">Edit</button>
                        @endif
                        <form method="POST" action="{{ route('table.cart.remove.index', $group['items'][0]['index']) }}">
                            @csrf
                            <button type="submit" class="w-8 h-8 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center text-sm hover:bg-red-500 hover:text-white hover:border-red-500 transition-colors border border-stone-200 cursor-pointer">×</button>
                        </form>
                    </div>
                </div>
                
                @if(!$hasMultipleItems && !empty($group['items'][0]['notes']))
                    <div class="px-4 pb-4 pt-0">
                        <div class="text-xs text-stone-500 italic break-words bg-stone-50 p-2 rounded-lg border border-stone-100">
                            "{{ $group['items'][0]['notes'] }}"
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
                                            <button onclick="openEditModal({{ $item['index'] }}, '{{ addslashes($group['name']) }}', {{ $item['quantity'] }}, {{ $group['unit_price'] }}, '{{ addslashes($item['notes'] ?? '') }}', {{ !empty($group['modifications']) ? 'true' : 'false' }})" class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded-lg font-medium hover:bg-amber-200 transition-colors cursor-pointer">Edit</button>
                                            <form method="POST" action="{{ route('table.cart.remove.index', $item['index']) }}" class="inline">
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

        <div class="px-6 pt-4 pb-4 bg-white border-t border-amber-200/50 shadow-[0_-5px_15px_rgba(0,0,0,0.02)]" style="position:fixed;bottom:0;right:0;width:350px;z-index:41;">
            <div class="flex justify-between items-center mb-6">
                <span class="text-lg font-bold text-stone-500">Total</span>
                <span class="font-display text-4xl font-semibold text-amber-950">${{ number_format($cartTotal, 2) }}</span>
            </div>
            @if(count($cart) > 0)
                <form method="POST" action="{{ route('table.order') }}">
                    @csrf
                    <button type="submit" class="w-full bg-amber-800 hover:bg-amber-900 text-amber-50 font-bold py-4 rounded-2xl text-xl shadow-lg shadow-amber-900/20 transition-all active:scale-97 flex items-center justify-center gap-2 cursor-pointer">
                        Send order to counter <span>→</span>
                    </button>
                </form>
            @else
                <button disabled class="w-full bg-stone-200 text-stone-400 font-bold py-4 rounded-2xl text-xl cursor-not-allowed">
                    Send order to counter
                </button>
            @endif
        </div>
    </aside>

    <!-- Middle: Items Grid (Main Scrollable Area) -->
    <main class="kiosk-main-content">
        @if(session('success'))
            <div class="bg-amber-100 text-amber-900 p-4 rounded-xl mb-8 font-medium text-center shadow-sm border border-amber-200">
                {{ session('success') }}
            </div>
        @endif

            @forelse($itemsByCategory as $category => $items)
                <div id="cat-{{ strtolower($category) }}" class="mb-14">
                    <h2 class="font-display text-3xl font-bold text-amber-950 mb-6 capitalize flex items-center gap-4">
                        {{ $category }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($items as $item)
                            @php
                                $img = $item->image ? asset('storage/' . $item->image) : 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=600&q=80';
                                if($category === 'drinks' && !$item->image) $img = 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=600&q=80';
                            @endphp
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
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-20">
                    <p class="text-2xl text-stone-400 font-bold">No menu items available.</p>
                </div>
            @endforelse
    </main>
</div>

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
                    <textarea id="itemNotes" name="notes" rows="2" class="w-full bg-stone-50 border-2 border-stone-100 rounded-xl p-4 focus:ring-0 focus:border-amber-300 transition-colors text-stone-700 resize-none font-medium placeholder:text-stone-400" placeholder="Any special requests?"></textarea>
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
                <button type="submit" class="w-full bg-amber-800 hover:bg-amber-900 text-amber-50 font-bold py-5 rounded-2xl text-xl shadow-xl shadow-amber-900/20 transition-transform active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
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

<script>
    let currentBasePrice = 0;
    let currentEditIndex = null;

    function openItemModal(item, imgSrc) {
        document.getElementById('itemForm').action = `/table/cart/${item.id}`;
        document.getElementById('modalImg').src = imgSrc;
        document.getElementById('modalTitle').innerText = item.name;
        document.getElementById('modalDesc').innerText = item.description || '';
        document.getElementById('modalPrice').innerText = '$' + parseFloat(item.price).toFixed(2);

        currentBasePrice = parseFloat(item.price);
        document.getElementById('qtyInput').value = 1;
        document.getElementById('itemNotes').value = '';

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
        const checkboxes = document.querySelectorAll('#customizationsList input[type="checkbox"]:checked');
        checkboxes.forEach(cb => {
            total += parseFloat(cb.dataset.price || 0);
        });

        const qty = parseInt(document.getElementById('qtyInput').value || 1);
        total = total * qty;

        document.getElementById('modalTotalBtn').innerText = `($${total.toFixed(2)})`;
    }

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

    function openEditModal(index, name, quantity, unitPrice, notes = '', hasModifications = false) {
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
        const notes = document.getElementById('editItemNotes').value;

        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/table/cart/update/${currentEditIndex}`;

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

    // Promo Banner Carousel & Drag Functionality
    const promoBanner = document.getElementById('promoBanner');
    const promoContent = document.getElementById('promoContent');
    let isDown = false;
    let startX;
    let scrollLeft;
    let autoScrollInterval;
    let currentIndex = 0;

    if (promoBanner && promoContent) {
        const slides = promoContent.querySelectorAll('.promo-slide');
        const totalSlides = slides.length;

        // Auto-scroll every 3 seconds
        function startAutoScroll() {
            autoScrollInterval = setInterval(() => {
                if (!isDown) {
                    currentIndex = (currentIndex + 1) % totalSlides;
                    const slideWidth = promoBanner.offsetWidth;
                    promoBanner.scrollTo({
                        left: currentIndex * slideWidth,
                        behavior: 'smooth'
                    });
                }
            }, 3000);
        }

        function stopAutoScroll() {
            clearInterval(autoScrollInterval);
        }

        startAutoScroll();

        // Drag functionality
        promoBanner.addEventListener('mousedown', (e) => {
            isDown = true;
            promoContent.classList.add('dragging');
            startX = e.pageX - promoBanner.offsetLeft;
            scrollLeft = promoBanner.scrollLeft;
            stopAutoScroll();
        });

        promoBanner.addEventListener('mouseleave', () => {
            isDown = false;
            promoContent.classList.remove('dragging');
            startAutoScroll();
        });

        promoBanner.addEventListener('mouseup', () => {
            isDown = false;
            promoContent.classList.remove('dragging');
            startAutoScroll();
        });

        promoBanner.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - promoBanner.offsetLeft;
            const walk = (x - startX) * 2;
            promoBanner.scrollLeft = scrollLeft - walk;
        });

        // Update current index on scroll
        promoBanner.addEventListener('scroll', () => {
            const slideWidth = promoBanner.offsetWidth;
            currentIndex = Math.round(promoBanner.scrollLeft / slideWidth);
        });
    }
</script>
@endsection
