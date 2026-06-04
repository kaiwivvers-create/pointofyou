@extends('layouts.staff')

@section('title', 'POS Checkout')

@section('content')
<div class="flex gap-4 h-full items-stretch" style="height: calc(100vh - 120px);">
    <!-- Left Pane: Menu Grid & Categories -->
    <div class="flex-[1.1] min-w-0 flex flex-col overflow-hidden bg-slate-50 border border-slate-200 rounded-xl shadow-sm">
        
        <!-- Top Bar: Search and Categories -->
        <div class="bg-white p-4 border-b border-slate-200 shrink-0">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between mb-4">
                <h1 class="text-xl font-bold text-slate-800">Point of Sale</h1>
                <div class="relative w-full lg:w-[34rem] xl:w-[40rem]">
                    <input type="text" id="search-input" placeholder="Search or Scan Barcode..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            
            <!-- Category Tabs -->
            <div class="flex overflow-x-auto gap-2 pb-2 hidden-scrollbar" id="category-tabs">
                <button onclick="filterCategory('All')" class="category-btn px-4 py-2 rounded-full text-sm font-medium bg-blue-600 text-white shadow-sm shrink-0 transition-colors" data-category="All">All Items</button>
                <!-- Categories will be populated here by JS -->
            </div>
        </div>

        <!-- Items Grid -->
        <div class="flex-1 overflow-y-auto p-4 bg-slate-50">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="items-grid">
                <!-- Items will be populated here by JS -->
            </div>
        </div>
    </div>

    <!-- Right Pane: Cart -->
    <div class="w-[40rem] min-w-[40rem] flex flex-col bg-white border border-slate-200 rounded-xl shadow-sm shrink-0">
        <div class="p-4 border-b border-slate-200 bg-slate-50 rounded-t-xl shrink-0 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-800">Current Order</h2>
            <button onclick="clearCart()" class="text-xs font-medium text-red-600 hover:bg-red-50 px-2 py-1 rounded transition-colors">Clear</button>
        </div>
        
        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-4" id="cart-items">
            <div class="text-center py-10 text-slate-400 flex flex-col items-center">
                <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p>Cart is empty</p>
                <p class="text-xs mt-1">Scan or click items to add</p>
            </div>
        </div>

        <!-- Cart Totals & Actions -->
        <div class="p-4 border-t border-slate-200 bg-slate-50 rounded-b-xl shrink-0">
            <div class="space-y-2 mb-4 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal</span>
                    <span id="cart-subtotal">$0.00</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Tax (10%)</span>
                    <span id="cart-tax">$0.00</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-slate-900 border-t border-slate-200 pt-2 mt-2">
                    <span>Total</span>
                    <span id="cart-total">$0.00</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button onclick="submitOrder(true)" id="pay-later-btn" class="w-full py-4 bg-amber-600 hover:bg-amber-700 text-white border border-amber-700 rounded-xl font-bold text-lg shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2" disabled>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Pay Later</span>
                </button>
                <button onclick="openCheckoutModal()" id="checkout-btn" class="w-full py-4 bg-blue-700 hover:bg-blue-800 text-white border border-blue-800 rounded-xl font-bold text-lg shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2" disabled>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a5 5 0 00-10 0v2M5 9h14l1 11H4L5 9z"></path>
                    </svg>
                    <span>Pay Now</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div id="checkout-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4 transition-opacity opacity-0">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg transform scale-95 transition-transform" id="checkout-modal-content">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-800">Complete Checkout</h3>
            <button onclick="closeCheckoutModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div class="p-6 space-y-6">
            <div class="text-center">
                <p class="text-sm text-slate-500 font-medium uppercase tracking-wider mb-1">Total Amount</p>
                <p class="text-4xl font-bold text-blue-600" id="checkout-total-display">$0.00</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600">
                Walk-in order. No table will be assigned.
            </div>

            <!-- Payment Method -->
            <div>
                <p class="text-sm font-semibold text-slate-700 mb-2">Payment Method</p>
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" onclick="setPaymentMethod('cash')" id="pay-cash" class="py-2 border-2 border-blue-500 bg-blue-50 text-blue-700 rounded-lg font-medium text-sm transition-colors">Cash</button>
                    <button type="button" onclick="setPaymentMethod('card')" id="pay-card" class="py-2 border-2 border-slate-200 text-slate-600 rounded-lg font-medium text-sm hover:border-slate-300 transition-colors">Card</button>
                    <button type="button" onclick="setPaymentMethod('qr')" id="pay-qr" class="py-2 border-2 border-slate-200 text-slate-600 rounded-lg font-medium text-sm hover:border-slate-300 transition-colors">QR</button>
                    <button type="button" onclick="setPaymentMethod('transfer')" id="pay-transfer" class="py-2 border-2 border-slate-200 text-slate-600 rounded-lg font-medium text-sm hover:border-slate-300 transition-colors">Transfer</button>
                </div>
                
                <div class="mt-3">
                    <button type="button" onclick="setPaymentMethod('later')" id="pay-later" class="w-full py-3 border-2 border-dashed border-amber-300 bg-amber-50 text-amber-700 rounded-lg font-medium hover:bg-amber-100 transition-colors">
                        Pay Later (Submit as Pending)
                    </button>
                </div>
            </div>
        </div>
        
        <div class="p-6 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
            <button onclick="submitOrder(false)" id="submit-order-btn" class="w-full py-4 bg-slate-900 hover:bg-black text-white rounded-xl font-bold text-lg shadow-md transition-all flex justify-center items-center gap-2">
                <svg class="w-5 h-5 hidden" id="submit-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" class="animate-spin"></path></svg>
                Confirm & Create Paid Order
            </button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden items-center justify-center z-[60] p-4 transition-opacity opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm transform scale-95 transition-transform text-center p-8" id="success-modal-content">
        <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 mb-2">Order Created!</h2>
        <p class="text-slate-500 mb-2">The order has been placed successfully.</p>
        <div class="py-3 px-4 bg-slate-100 rounded-lg inline-block mb-8">
            <span class="text-sm text-slate-500 font-medium">Order Number</span>
            <p class="text-2xl font-black text-slate-800" id="success-order-number">#---</p>
        </div>
        
        <div class="space-y-3">
            <a id="print-receipt-btn" href="#" target="_blank" class="block w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md transition-colors">
                Print Receipt
            </a>
            <button onclick="closeSuccessModal()" class="w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-colors">
                New Order
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const itemsData = {!! json_encode($itemsJson) !!};
    const posCategories = {!! json_encode($posCategories ?? []) !!};
    let currentCategory = 'All';
    let cart = [];
    
    // Checkout state
    let paymentMethod = 'cash';
    let isSubmitting = false;

    // Barcode scanner logic
    let barcodeBuffer = '';
    let barcodeTimeout = null;

    document.addEventListener('DOMContentLoaded', () => {
        initCategories();
        renderItems();
        setupSearch();
        setupScanner();
    });

    function initCategories() {
        const categories = posCategories.length > 0
            ? posCategories
            : [...new Set(itemsData.map(i => i.category).filter(Boolean))];
        const tabsContainer = document.getElementById('category-tabs');
        
        categories.forEach(cat => {
            const btn = document.createElement('button');
            btn.className = 'category-btn px-4 py-2 rounded-full text-sm font-medium bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 shadow-sm shrink-0 transition-colors';
            btn.dataset.category = cat;
            btn.textContent = cat;
            btn.onclick = () => filterCategory(cat);
            tabsContainer.appendChild(btn);
        });
    }

    function filterCategory(cat) {
        currentCategory = cat;
        
        // Update styling
        document.querySelectorAll('.category-btn').forEach(btn => {
            if (btn.dataset.category === cat) {
                btn.classList.remove('bg-white', 'border-slate-200', 'text-slate-600');
                btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
            } else {
                btn.classList.add('bg-white', 'border-slate-200', 'text-slate-600');
                btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
            }
        });

        document.getElementById('search-input').value = '';
        renderItems();
    }

    function setupSearch() {
        document.getElementById('search-input').addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            renderItems(query);
        });
    }

    function setupScanner() {
        document.addEventListener('keypress', (e) => {
            // If typing in an input, don't interfere
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }

            if (e.key === 'Enter' && barcodeBuffer.length > 0) {
                handleBarcodeScanned(barcodeBuffer);
                barcodeBuffer = '';
            } else {
                barcodeBuffer += e.key;
                
                // Clear buffer if taking too long (not a scanner)
                clearTimeout(barcodeTimeout);
                barcodeTimeout = setTimeout(() => {
                    barcodeBuffer = '';
                }, 100); // 100ms between keystrokes is fast enough for human but slow enough for scanner
            }
        });
    }

    function handleBarcodeScanned(barcode) {
        const item = itemsData.find(i => i.barcode === barcode);
        if (item) {
            addToCart(item);
            // Flash effect or sound could go here
        } else {
            alert('Item with barcode ' + barcode + ' not found.');
        }
    }

    function renderItems(searchQuery = '') {
        const grid = document.getElementById('items-grid');
        grid.innerHTML = '';

        let filtered = itemsData;

        if (currentCategory !== 'All') {
            filtered = filtered.filter(i => i.category === currentCategory);
        }

        if (searchQuery) {
            filtered = filtered.filter(i => 
                i.name.toLowerCase().includes(searchQuery) || 
                (i.barcode && i.barcode.toLowerCase().includes(searchQuery))
            );
        }

        filtered.forEach(item => {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-xl border border-slate-200 p-3 shadow-sm hover:shadow-md cursor-pointer hover:border-blue-300 transition-all flex flex-col h-full';
            card.onclick = () => addToCart(item);

            const imgHtml = item.image 
                ? `<img src="${item.image}" class="w-full h-24 object-cover rounded-lg mb-3">`
                : `<div class="w-full h-24 bg-slate-100 rounded-lg mb-3 flex items-center justify-center text-slate-300"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>`;

            card.innerHTML = `
                ${imgHtml}
                <p class="font-bold text-slate-800 text-sm leading-tight flex-1 mb-1">${item.name}</p>
                <p class="text-blue-600 font-semibold text-sm">$${item.price.toFixed(2)}</p>
            `;
            grid.appendChild(card);
        });
    }

    function addToCart(item) {
        const existing = cart.find(i => i.id === item.id && i.type === item.type);
        if (existing) {
            existing.quantity++;
        } else {
            cart.push({ ...item, quantity: 1 });
        }
        renderCart();
    }

    function updateCartQuantity(index, delta) {
        cart[index].quantity += delta;
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cart-items');
        
        if (cart.length === 0) {
            container.innerHTML = `
                <div class="text-center py-10 text-slate-400 flex flex-col items-center">
                    <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <p>Cart is empty</p>
                    <p class="text-xs mt-1">Scan or click items to add</p>
                </div>`;
            document.getElementById('checkout-btn').disabled = true;
            document.getElementById('pay-later-btn').disabled = true;
        } else {
            container.innerHTML = cart.map((item, idx) => `
                <div class="flex items-center justify-between p-3 bg-white border border-slate-100 rounded-lg shadow-sm mb-2 hover:border-slate-200 transition-colors">
                    <div class="flex-1 pr-3">
                        <p class="font-bold text-slate-800 text-sm">${item.name}</p>
                        <p class="text-xs text-slate-500 font-medium">$${item.price.toFixed(2)}</p>
                    </div>
                    <div class="flex items-center bg-slate-50 rounded-lg p-1 border border-slate-200 shrink-0">
                        <button onclick="updateCartQuantity(${idx}, -1)" class="w-7 h-7 flex items-center justify-center rounded-md text-slate-600 hover:bg-white hover:shadow-sm transition-all">-</button>
                        <span class="w-8 text-center font-bold text-slate-800 text-sm">${item.quantity}</span>
                        <button onclick="updateCartQuantity(${idx}, 1)" class="w-7 h-7 flex items-center justify-center rounded-md text-slate-600 hover:bg-white hover:shadow-sm transition-all">+</button>
                    </div>
                </div>
            `).join('');
            document.getElementById('checkout-btn').disabled = false;
            document.getElementById('pay-later-btn').disabled = false;
        }

        updateTotals();
    }

    function updateTotals() {
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = subtotal * 0.10; // Assuming 10% tax. Adjust if dynamic.
        const total = subtotal + tax;

        document.getElementById('cart-subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('cart-tax').textContent = '$' + tax.toFixed(2);
        document.getElementById('cart-total').textContent = '$' + total.toFixed(2);
        document.getElementById('checkout-total-display').textContent = '$' + total.toFixed(2);
    }

    function clearCart() {
        cart = [];
        renderCart();
        document.getElementById('checkout-btn').disabled = true;
        document.getElementById('pay-later-btn').disabled = true;
    }

    // Modal logic
    function openCheckoutModal() {
        if (cart.length === 0) return;
        
        const modal = document.getElementById('checkout-modal');
        const content = document.getElementById('checkout-modal-content');
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        }, 10);
    }

    function closeCheckoutModal() {
        const modal = document.getElementById('checkout-modal');
        const content = document.getElementById('checkout-modal-content');
        
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    function setPaymentMethod(method) {
        paymentMethod = method;
        const methods = ['cash', 'card', 'qr', 'transfer'];
        methods.forEach(m => {
            const el = document.getElementById('pay-' + m);
            if (m === method) {
                el.className = 'py-2 border-2 border-blue-500 bg-blue-50 text-blue-700 rounded-lg font-medium text-sm transition-colors';
            } else {
                el.className = 'py-2 border-2 border-slate-200 text-slate-600 rounded-lg font-medium text-sm hover:border-slate-300 transition-colors';
            }
        });
    }

    async function submitOrder(forceLater = false) {
        if (cart.length === 0 || isSubmitting) return;

        isSubmitting = true;
        const btn = forceLater ? document.getElementById('pay-later-btn') : document.getElementById('submit-order-btn');
        const spinner = document.getElementById('submit-spinner');
        
        btn.disabled = true;
        btn.classList.add('opacity-75');
        if (!forceLater) {
            spinner.classList.remove('hidden');
        }

        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = subtotal * 0.10;
        const total = subtotal + tax;

        const payload = {
            items: cart.map(item => ({
                id: item.id,
                name: item.name,
                price: item.price,
                quantity: item.quantity,
                type: item.type
            })),
            order_type: 'walk-in',
            table_id: null,
            subtotal: subtotal,
            tax: tax,
            total: total,
            payment_method: forceLater ? 'later' : paymentMethod
        };

        try {
            const response = await fetch('{{ route('cashier.orders.create') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                closeCheckoutModal();
                showSuccessModal(data.order_id, data.order_number, data.receipt_url);
            } else {
                alert('Error creating order: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Checkout error:', error);
            alert('A network error occurred while submitting the order.');
        } finally {
            isSubmitting = false;
            btn.disabled = false;
            btn.classList.remove('opacity-75');
            if (!forceLater) {
                spinner.classList.add('hidden');
            }
        }
    }

    function showSuccessModal(orderId, orderNumber, receiptUrl) {
        const modal = document.getElementById('success-modal');
        const content = document.getElementById('success-modal-content');
        
        document.getElementById('success-order-number').textContent = '#' + (orderNumber || orderId);
        document.getElementById('print-receipt-btn').href = receiptUrl || '{{ route("cashier.receipt", ":id") }}'.replace(':id', orderId);
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        }, 10);
    }

    function closeSuccessModal() {
        const modal = document.getElementById('success-modal');
        const content = document.getElementById('success-modal-content');
        
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            clearCart();
            setPaymentMethod('cash');
        }, 300);
    }
</script>
@endpush
