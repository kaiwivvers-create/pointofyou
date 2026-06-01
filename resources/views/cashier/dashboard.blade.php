@extends('layouts.staff')

@section('title', 'Cashier Dashboard')

@section('content')
    <div class="flex gap-4" style="width: 100%; display: flex;">
        <!-- Left Side (50%): Live Tables Status -->
        <div style="width: 50%; flex: 1;" class="bg-white rounded-lg shadow-sm p-4 flex flex-col">
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

        <!-- Right Side (50%): The Cart / Checkout Pane -->
        <div style="width: 50%; flex: 1;" class="bg-white rounded-lg shadow-sm p-4 flex flex-col">
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
            
            // Load existing order for this table
            fetch(`/cashier/orders/table/${tableId}`)
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
            document.getElementById('payment-cash').classList.remove('bg-emerald-600', 'text-white');
            document.getElementById('payment-cash').classList.add('bg-slate-100', 'text-slate-700');
            document.getElementById('payment-card').classList.remove('bg-emerald-600', 'text-white');
            document.getElementById('payment-card').classList.add('bg-slate-100', 'text-slate-700');
            document.getElementById('payment-qr').classList.remove('bg-emerald-600', 'text-white');
            document.getElementById('payment-qr').classList.add('bg-slate-100', 'text-slate-700');
            
            document.getElementById(`payment-${method}`).classList.remove('bg-slate-100', 'text-slate-700');
            document.getElementById(`payment-${method}`).classList.add('bg-emerald-600', 'text-white');
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
            if (cart.length === 0) {
                cartContainer.innerHTML = '<p class="text-slate-500 text-center py-8">Cart is empty</p>';
            } else {
                cartContainer.innerHTML = cart.map(item => `
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
            }
            updateTotals();
        }

        // Update totals
        function updateTotals() {
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.10;
            const total = subtotal + tax;

            document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
            document.getElementById('total').textContent = `$${total.toFixed(2)}`;
        }

        // Clear cart
        function clearCart() {
            cart = [];
            selectedTable = null;
            selectedTableName = null;
            document.getElementById('table-name').textContent = 'No table selected';
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
