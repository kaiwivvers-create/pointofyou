@extends('layouts.staff')

@section('title', 'Live Tables')

@section('content')
    <div class="flex gap-4 flex-row" style="width: 100%; display: flex; height: calc(100vh - 120px);">
        <!-- Left Side: Live Tables Status -->
        <div class="flex-[1.1] min-w-0 bg-white rounded-lg shadow-sm p-4 flex flex-col">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Live Tables Status</h2>
            
            <div class="flex-1 overflow-y-auto space-y-2">
                @foreach (\App\Models\CafeTable::all() as $table)
                    @php
                        $activeOrder = \App\Models\Order::where('cafe_table_id', $table->id)
                            ->where('status', '!=', \App\Enums\OrderStatus::Paid)
                            ->latest()
                            ->first();
                        
                        $status = 'Idle';
                        $statusColor = 'bg-slate-100 text-slate-700';
                        
                        if ($activeOrder) {
                            if ($activeOrder->status === \App\Enums\OrderStatus::Pending) {
                                $status = 'Ordered';
                                $statusColor = 'bg-amber-100 text-amber-700';
                            } elseif ($activeOrder->status === \App\Enums\OrderStatus::Paid) {
                                $status = 'Not Paid';
                                $statusColor = 'bg-red-100 text-red-700';
                            } else {
                                $status = 'Eating';
                                $statusColor = 'bg-emerald-100 text-emerald-700';
                            }
                        }
                    @endphp
                    
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg cursor-pointer hover:bg-slate-100" onclick="selectTable({{ $table->id }}, '{{ $table->name }}')">
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-slate-900">{{ $table->name }}</span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusColor }}">{{ $status }}</span>
                        </div>
                        @if ($activeOrder)
                            <span class="text-sm text-slate-600">{{ $activeOrder->created_at->format('H:i') }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right Side: The Cart / Checkout Pane (Desktop) -->
        <div class="hidden lg:flex w-[40rem] min-w-[40rem] bg-white rounded-lg shadow-sm p-4 flex flex-col">
            <h2 class="text-lg font-bold text-slate-900 mb-4">The Cart / Checkout Pane</h2>
            
            <!-- Selected Table -->
            <div id="selected-table" class="mb-4 p-3 bg-emerald-50 rounded-lg">
                <p class="text-sm text-slate-600">Selected:</p>
                <p id="table-name" class="font-bold text-emerald-700">No table selected</p>
            </div>

            <!-- Current Order -->
            <div class="mb-4 flex-1 flex flex-col">
                <p class="text-sm font-semibold text-slate-600 mb-2">Current Order:</p>
                <div id="cart-items" class="flex-1 space-y-2 overflow-y-auto min-h-[150px]">
                    <p class="text-slate-500 text-center py-8">Cart is empty</p>
                </div>
            </div>

            <!-- Totals -->
            <div class="border-t border-slate-200 pt-4 mb-4">
                <div class="flex justify-between mb-2">
                    <span class="text-slate-600">Subtotal:</span>
                    <span id="subtotal" class="font-semibold">$0.00</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-slate-600">Tax:</span>
                    <span id="tax" class="font-semibold">$0.00</span>
                </div>
                <div class="flex justify-between text-lg font-bold">
                    <span class="text-slate-900">Total:</span>
                    <span id="total" class="text-emerald-600">$0.00</span>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="mb-4">
                <p class="text-sm font-semibold text-slate-600 mb-2">Payment:</p>
                <div class="flex gap-2">
                    <button onclick="setPaymentMethod('cash')" id="payment-cash" class="flex-1 py-2 rounded-lg font-medium bg-emerald-600 text-white">Cash</button>
                    <button onclick="setPaymentMethod('card')" id="payment-card" class="flex-1 py-2 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">Card</button>
                    <button onclick="setPaymentMethod('qr')" id="payment-qr" class="flex-1 py-2 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">QR Done</button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button onclick="clearCart()" class="flex-1 py-3 rounded-lg font-medium bg-red-100 text-red-700 hover:bg-red-200">Clear Table</button>
                <button onclick="processPayment()" class="flex-1 py-3 rounded-lg font-medium bg-emerald-600 text-white hover:bg-emerald-700">Print Bill</button>
            </div>
        </div>
    </div>

    <!-- Mobile Cart Floating Button -->
    <div class="lg:hidden fixed bottom-6 left-4 right-4 z-[100]">
        <button onclick="toggleMobileCart()" class="w-full bg-slate-900 text-white rounded-2xl shadow-2xl p-4 flex items-center justify-between border-2 border-slate-700">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span id="mobile-cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold">0</span>
                </div>
                <div class="text-left">
                    <p class="font-bold text-base">Table Order</p>
                    <p class="text-sm text-slate-300" id="mobile-cart-items-text">0 items</p>
                </div>
            </div>
            <div class="text-right">
                <p class="font-bold text-xl" id="mobile-cart-total">$0.00</p>
                <p class="text-xs text-slate-300">Tap to view</p>
            </div>
        </button>
    </div>

    <!-- Mobile Cart Bottom Sheet -->
    <div id="mobile-cart-sheet" class="lg:hidden fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50" onclick="toggleMobileCart()"></div>
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-2xl transform transition-transform duration-300 translate-y-full" id="mobile-cart-content" style="max-height: 80vh;">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                <h2 class="text-lg font-bold text-slate-800">Table Order</h2>
                <div class="flex items-center gap-2">
                    <button onclick="clearCart()" class="text-xs font-medium text-red-600 hover:bg-red-50 px-2 py-1 rounded transition-colors">Clear</button>
                    <button onclick="toggleMobileCart()" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
            
            <!-- Selected Table -->
            <div id="mobile-selected-table" class="mx-4 mt-4 p-3 bg-emerald-50 rounded-lg">
                <p class="text-sm text-slate-600">Selected:</p>
                <p id="mobile-table-name" class="font-bold text-emerald-700">No table selected</p>
            </div>

            <!-- Cart Items -->
            <div class="overflow-y-auto p-4" id="mobile-cart-items" style="max-height: calc(80vh - 280px);">
                <p class="text-slate-500 text-center py-8">Cart is empty</p>
            </div>

            <!-- Totals -->
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                <div class="space-y-2 mb-4 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span id="mobile-subtotal">$0.00</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Tax (10%)</span>
                        <span id="mobile-tax">$0.00</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-slate-900 border-t border-slate-200 pt-2 mt-2">
                        <span>Total</span>
                        <span id="mobile-total">$0.00</span>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="mb-4">
                    <p class="text-sm font-semibold text-slate-600 mb-2">Payment:</p>
                    <div class="flex gap-2">
                        <button onclick="setPaymentMethod('cash')" id="mobile-payment-cash" class="flex-1 py-2 rounded-lg font-medium bg-emerald-600 text-white">Cash</button>
                        <button onclick="setPaymentMethod('card')" id="mobile-payment-card" class="flex-1 py-2 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">Card</button>
                        <button onclick="setPaymentMethod('qr')" id="mobile-payment-qr" class="flex-1 py-2 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">QR Done</button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <button onclick="clearCart(); toggleMobileCart();" class="flex-1 py-3 rounded-lg font-medium bg-red-100 text-red-700 hover:bg-red-200">Clear Table</button>
                    <button onclick="processPayment(); toggleMobileCart();" class="flex-1 py-3 rounded-lg font-medium bg-emerald-600 text-white hover:bg-emerald-700">Print Bill</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Cart state
        let cart = [];
        let selectedTable = null;
        let selectedTableName = null;
        let paymentMethod = 'cash';

        // Select table
        function selectTable(tableId, tableName) {
            selectedTable = tableId;
            selectedTableName = tableName;
            document.getElementById('table-name').textContent = tableName;
            document.getElementById('mobile-table-name').textContent = tableName;
            
            // Load existing order for this table
            fetch('{{ route('cashier.orders.table', ':id') }}'.replace(':id', tableId))
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.order) {
                        cart = data.order.items.map(item => ({
                            id: item.menu_item_id,
                            name: item.item_name || 'Item',
                            price: item.price || 0,
                            quantity: item.quantity
                        }));
                        renderCart();
                    } else {
                        cart = [];
                        renderCart();
                    }
                })
                .catch(error => {
                    console.error('Error loading order:', error);
                    cart = [];
                    renderCart();
                });
        }

        // Set payment method
        function setPaymentMethod(method) {
            paymentMethod = method;
            
            // Desktop buttons
            document.getElementById('payment-cash').classList.remove('bg-emerald-600', 'text-white');
            document.getElementById('payment-cash').classList.add('bg-slate-100', 'text-slate-700');
            document.getElementById('payment-card').classList.remove('bg-emerald-600', 'text-white');
            document.getElementById('payment-card').classList.add('bg-slate-100', 'text-slate-700');
            document.getElementById('payment-qr').classList.remove('bg-emerald-600', 'text-white');
            document.getElementById('payment-qr').classList.add('bg-slate-100', 'text-slate-700');
            
            document.getElementById(`payment-${method}`).classList.remove('bg-slate-100', 'text-slate-700');
            document.getElementById(`payment-${method}`).classList.add('bg-emerald-600', 'text-white');

            // Mobile buttons
            document.getElementById('mobile-payment-cash').classList.remove('bg-emerald-600', 'text-white');
            document.getElementById('mobile-payment-cash').classList.add('bg-slate-100', 'text-slate-700');
            document.getElementById('mobile-payment-card').classList.remove('bg-emerald-600', 'text-white');
            document.getElementById('mobile-payment-card').classList.add('bg-slate-100', 'text-slate-700');
            document.getElementById('mobile-payment-qr').classList.remove('bg-emerald-600', 'text-white');
            document.getElementById('mobile-payment-qr').classList.add('bg-slate-100', 'text-slate-700');
            
            document.getElementById(`mobile-payment-${method}`).classList.remove('bg-slate-100', 'text-slate-700');
            document.getElementById(`mobile-payment-${method}`).classList.add('bg-emerald-600', 'text-white');
        }

        // Add to cart
        function addToCart(id, name, price) {
            if (!selectedTable) {
                alert('Please select a table first');
                return;
            }
            
            const existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({ id, name, price, quantity: 1 });
            }
            renderCart();
        }

        // Update quantity
        function updateQuantity(id, change) {
            const item = cart.find(item => item.id === id);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    cart = cart.filter(item => item.id !== id);
                }
                renderCart();
            }
        }

        // Render cart
        function renderCart() {
            const cartContainer = document.getElementById('cart-items');
            const mobileCartContainer = document.getElementById('mobile-cart-items');
            
            const cartHtml = cart.length === 0
                ? '<p class="text-slate-500 text-center py-8">Cart is empty</p>'
                : cart.map(item => `
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <div class="flex-1">
                            <p class="font-medium text-slate-900">${item.quantity}× ${item.name}</p>
                            <p class="text-sm text-slate-600">$${item.price.toFixed(2)}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="updateQuantity(${item.id}, -1)" class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 hover:bg-slate-300">-</button>
                            <button onclick="updateQuantity(${item.id}, 1)" class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 hover:bg-slate-300">+</button>
                        </div>
                    </div>
                `).join('');

            if (cartContainer) cartContainer.innerHTML = cartHtml;
            if (mobileCartContainer) mobileCartContainer.innerHTML = cartHtml;

            updateTotals();
            updateMobileCartButton();
        }

        // Update totals
        function updateTotals() {
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.10;
            const total = subtotal + tax;

            // Desktop totals
            if (document.getElementById('subtotal')) document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
            if (document.getElementById('tax')) document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
            if (document.getElementById('total')) document.getElementById('total').textContent = `$${total.toFixed(2)}`;

            // Mobile totals
            if (document.getElementById('mobile-subtotal')) document.getElementById('mobile-subtotal').textContent = `$${subtotal.toFixed(2)}`;
            if (document.getElementById('mobile-tax')) document.getElementById('mobile-tax').textContent = `$${tax.toFixed(2)}`;
            if (document.getElementById('mobile-total')) document.getElementById('mobile-total').textContent = `$${total.toFixed(2)}`;
            if (document.getElementById('mobile-cart-total')) document.getElementById('mobile-cart-total').textContent = `$${total.toFixed(2)}`;
        }

        function updateMobileCartButton() {
            const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.10;
            const total = subtotal + tax;

            if (document.getElementById('mobile-cart-count')) {
                document.getElementById('mobile-cart-count').textContent = itemCount;
            }
            if (document.getElementById('mobile-cart-items-text')) {
                document.getElementById('mobile-cart-items-text').textContent = itemCount === 1 ? '1 item' : `${itemCount} items`;
            }
            if (document.getElementById('mobile-cart-total')) {
                document.getElementById('mobile-cart-total').textContent = `$${total.toFixed(2)}`;
            }
        }

        function toggleMobileCart() {
            const sheet = document.getElementById('mobile-cart-sheet');
            const content = document.getElementById('mobile-cart-content');
            
            if (sheet.classList.contains('hidden')) {
                // Open
                sheet.classList.remove('hidden');
                setTimeout(() => {
                    content.classList.remove('translate-y-full');
                }, 10);
            } else {
                // Close
                content.classList.add('translate-y-full');
                setTimeout(() => {
                    sheet.classList.add('hidden');
                }, 300);
            }
        }

        // Clear cart
        function clearCart() {
            cart = [];
            selectedTable = null;
            selectedTableName = null;
            if (document.getElementById('table-name')) document.getElementById('table-name').textContent = 'No table selected';
            if (document.getElementById('mobile-table-name')) document.getElementById('mobile-table-name').textContent = 'No table selected';
            renderCart();
        }

        // Process payment
        async function processPayment() {
            if (!selectedTable) {
                alert('Please select a table first');
                return;
            }
            if (cart.length === 0) {
                alert('Cart is empty');
                return;
            }

            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.10;
            const total = subtotal + tax;

            const orderData = {
                items: cart,
                table_id: selectedTable,
                subtotal: subtotal,
                tax: tax,
                total: total,
                payment_method: paymentMethod,
            };

            try {
                const response = await fetch('{{ route('cashier.orders.create') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(orderData),
                });

                if (response.ok) {
                    clearCart();
                    alert('Order created successfully!');
                } else {
                    alert('Error creating order. Please try again.');
                }
            } catch (error) {
                console.error('Error processing payment:', error);
                alert('Error processing payment. Please try again.');
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            setPaymentMethod('cash');
        });
    </script>
@endsection
