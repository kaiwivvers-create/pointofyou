@extends('layouts.staff')

@section('title', 'POS Checkout')

@section('content')
<div class="flex gap-4 h-full items-stretch flex-row" style="height: calc(100vh - 120px);">
    <!-- Left Pane: Menu Grid & Categories -->
    <div class="flex-[1.1] min-w-0 flex flex-col overflow-hidden bg-slate-50 border border-slate-200 rounded-xl shadow-sm">
        
        <!-- Top Bar: Search and Categories -->
        <div class="bg-white p-4 border-b border-slate-200 shrink-0">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between mb-4">
                <h1 class="text-xl font-bold text-slate-800">Point of Sale</h1>
                <div class="relative w-full lg:w-[34rem] xl:w-[40rem] flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" id="search-input" placeholder="Search or Scan Barcode..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <button onclick="openBarcodeScanner()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-sm transition-colors flex items-center gap-2 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        <span class="hidden sm:inline">Scan</span>
                    </button>
                    <button onclick="openDeviceConnectionModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm transition-colors flex items-center gap-2 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span class="hidden sm:inline">Connect Device</span>
                    </button>
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

    <!-- Right Pane: Cart (Desktop) -->
    <div class="hidden lg:flex w-[40rem] min-w-[40rem] flex-col bg-white border border-slate-200 rounded-xl shadow-sm shrink-0">
        <div class="p-4 border-b border-slate-200 bg-emerald-50 rounded-t-xl shrink-0 flex justify-between items-center">
            <div>
                <p class="text-xs text-emerald-600 font-bold uppercase tracking-wider">Today's Sales (You)</p>
                <p class="text-xl font-black text-emerald-700">${{ number_format($todayTotal ?? 0, 2) }}</p>
            </div>
            <div class="text-right">
                <svg class="w-8 h-8 text-emerald-400 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
        <div class="p-4 border-b border-slate-200 bg-slate-50 shrink-0 flex justify-between items-center">
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

<!-- Mobile Cart Floating Button -->
<div class="lg:hidden fixed bottom-6 left-4 right-4 z-[100]">
    <button onclick="toggleMobileCart()" class="w-full bg-slate-900 text-white rounded-2xl shadow-2xl p-4 flex items-center justify-between border-2 border-slate-700">
        <div class="flex items-center gap-3">
            <div class="relative">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span id="mobile-cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold">0</span>
            </div>
            <div class="text-left">
                <p class="font-bold text-base">Current Order</p>
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
            <h2 class="text-lg font-bold text-slate-800">Current Order</h2>
            <div class="flex items-center gap-2">
                <button onclick="clearCart()" class="text-xs font-medium text-red-600 hover:bg-red-50 px-2 py-1 rounded transition-colors">Clear</button>
                <button onclick="toggleMobileCart()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
        
        <!-- Cart Items -->
        <div class="overflow-y-auto p-4" id="mobile-cart-items" style="max-height: calc(80vh - 200px);">
            <div class="text-center py-10 text-slate-400 flex flex-col items-center">
                <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p>Cart is empty</p>
                <p class="text-xs mt-1">Scan or click items to add</p>
            </div>
        </div>

        <!-- Cart Totals & Actions -->
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            <div class="space-y-2 mb-4 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal</span>
                    <span id="mobile-cart-subtotal">$0.00</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Tax (10%)</span>
                    <span id="mobile-cart-tax">$0.00</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-slate-900 border-t border-slate-200 pt-2 mt-2">
                    <span>Total</span>
                    <span id="mobile-cart-total-display">$0.00</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button onclick="submitOrder(true); toggleMobileCart();" id="mobile-pay-later-btn" class="w-full py-4 bg-amber-600 hover:bg-amber-700 text-white border border-amber-700 rounded-xl font-bold text-lg shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2" disabled>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Pay Later</span>
                </button>
                <button onclick="openCheckoutModal(); toggleMobileCart();" id="mobile-checkout-btn" class="w-full py-4 bg-blue-700 hover:bg-blue-800 text-white border border-blue-800 rounded-xl font-bold text-lg shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2" disabled>
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

            <!-- Order Type -->
            <div>
                <p class="text-sm font-semibold text-slate-700 mb-2">Order Type</p>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <button type="button" onclick="setOrderType('walk-in')" id="type-walk-in" class="py-2 border-2 border-blue-500 bg-blue-50 text-blue-700 rounded-lg font-medium text-sm transition-colors">Walk-in</button>
                    <button type="button" onclick="setOrderType('dine-in')" id="type-dine-in" class="py-2 border-2 border-slate-200 text-slate-600 rounded-lg font-medium text-sm hover:border-slate-300 transition-colors">Dine-in (Table)</button>
                </div>
                
                <div id="table-selection-container" class="hidden">
                    <p class="text-sm font-semibold text-slate-700 mb-2">Select Table</p>
                    <select id="checkout-table-id" class="w-full rounded-lg border-slate-200 p-2.5 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Select Table --</option>
                        @isset($tables)
                            @foreach($tables as $table)
                                @if(strtolower($table->name) !== 'walk-in' && strtolower($table->name) !== 'takeout')
                                    <option value="{{ $table->id }}">{{ $table->name }}</option>
                                @endif
                            @endforeach
                        @endisset
                    </select>
                </div>
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
                
                <!-- Dynamic Payment Details -->
                <div id="payment-details-container" class="mt-4 p-4 border border-slate-200 rounded-lg bg-white hidden">
                    <!-- Populated by JS -->
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
            <button id="print-receipt-btn" type="button" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md transition-colors">
                View Receipt
            </button>
            <button onclick="closeSuccessModal()" class="w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-colors">
                New Order
            </button>
        </div>
    </div>
</div>

<!-- Barcode Scanner Modal -->
<div id="barcode-scanner-modal" class="fixed inset-0 bg-slate-900/90 backdrop-blur-sm hidden items-center justify-center z-[70] p-4 transition-opacity opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform scale-95 transition-transform" id="barcode-scanner-modal-content">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-800">Scan Barcode</h3>
            <div class="flex items-center gap-2">
                <button onclick="flipCamera()" id="flip-camera-btn" class="text-slate-500 hover:text-slate-700 p-2 rounded-lg hover:bg-slate-100 transition-colors hidden" title="Flip Camera">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
                <button onclick="closeBarcodeScanner()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div id="reader" class="w-full bg-black rounded-lg overflow-hidden" style="min-height: 300px;"></div>
            <p class="text-center text-sm text-slate-500 mt-4">Point your camera at a barcode to scan</p>
            
            <!-- Manual barcode input fallback -->
            <div class="mt-4 pt-4 border-t border-slate-200">
                <p class="text-xs text-slate-500 mb-2">Or enter barcode manually:</p>
                <div class="flex gap-2">
                    <input type="text" id="manual-barcode-input" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter barcode...">
                    <button onclick="submitManualBarcode()" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Add</button>
                </div>
            </div>
        </div>
        
        <div class="p-4 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
            <button onclick="closeBarcodeScanner()" class="w-full py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold transition-colors">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Device Connection Modal -->
<div id="device-connection-modal" class="fixed inset-0 bg-slate-900/90 backdrop-blur-sm hidden items-center justify-center z-[70] p-4 transition-opacity opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform scale-95 transition-transform" id="device-connection-modal-content">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-800">Connect Device</h3>
            <button onclick="closeDeviceConnectionModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div class="p-6">
            <!-- QR Code Display -->
            <div id="qr-section" class="text-center mb-6">
                <p class="text-sm text-slate-600 mb-4">Scan this QR code with your phone to connect for barcode scanning</p>
                <div id="qr-code-container" class="flex justify-center mb-4">
                    <div id="qr-code" class="w-48 h-48 bg-slate-100 rounded-lg flex items-center justify-center">
                        <div class="text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm">Generating QR...</p>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-slate-500">Session expires in 1 hour</p>
            </div>

            <!-- Add New Session Section (shown when sessions exist) -->
            <div id="add-session-section" class="hidden text-center mb-6">
                <p class="text-sm text-slate-600 mb-4">You have active sessions. You can add a new session if needed.</p>
                <button onclick="createNewSessionFromModal()" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold transition-colors">
                    Add New Session
                </button>
            </div>

            <!-- Connected Devices -->
            <div id="connected-devices-section" class="hidden">
                <h4 class="text-sm font-semibold text-slate-800 mb-3">Connected Devices</h4>
                <div id="connected-devices-list" class="space-y-2">
                    <!-- Connected devices will be listed here -->
                </div>
            </div>
        </div>

        <div class="p-4 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
            <button onclick="closeDeviceConnectionModal()" class="w-full py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold transition-colors">
                Close
            </button>
        </div>
    </div>
    </div>
</div>

<!-- Receipt Modal -->
<div id="receipt-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto transform scale-95 transition-transform duration-300" id="receipt-modal-content">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-slate-900">Receipt</h2>
                <button onclick="closeReceiptModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="receipt-content">
                <div class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900"></div>
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <button onclick="printReceipt()" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-colors flex-1">Print Receipt</button>
                <button onclick="closeReceiptModal()" class="w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-colors flex-1">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    const itemsData = {!! json_encode($itemsJson) !!};
    const posCategories = {!! json_encode($posCategories ?? []) !!};
    const taxRate = {{ \App\Models\PaymentSettings::getSettings()->tax_rate ?? 10 }} / 100;
    let currentCategory = 'All';
    let cart = [];

    // Checkout state
    let paymentMethod = 'cash';
    let isSubmitting = false;

    // Barcode scanner logic
    let barcodeBuffer = '';
    let barcodeTimeout = null;
    let html5QrcodeScanner = null;
    let isScanning = false;
    let currentCameraId = null;
    let cameraDevices = [];
    let currentCameraIndex = 0;

    // Device connection logic
    let currentSessionCode = null;
    let devicePollingInterval = null;

    document.addEventListener('DOMContentLoaded', () => {
        initCategories();
        renderItems();
        setupSearch();
        setupScanner();
        // Load active sessions on page load
        loadActiveSessions();
        // Load cart from localStorage
        loadCartFromStorage();
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
        console.log('Barcode scanned:', barcode);
        console.log('Available items:', itemsData);
        console.log('Item barcodes:', itemsData.map(i => ({ name: i.name, barcode: i.barcode })));
        
        const item = itemsData.find(i => i.barcode === barcode);
        if (item) {
            console.log('Item found:', item);
            addToCart(item);
            // Flash effect or sound could go here
        } else {
            console.log('Item not found for barcode:', barcode);
            alert('Item with barcode ' + barcode + ' not found. Please try manual entry or check if the item has a barcode assigned.');
        }
    }

    function submitManualBarcode() {
        const input = document.getElementById('manual-barcode-input');
        const barcode = input.value.trim();
        
        if (barcode) {
            handleBarcodeScanned(barcode);
            input.value = '';
        }
    }

    async function openBarcodeScanner() {
        const modal = document.getElementById('barcode-scanner-modal');
        const content = document.getElementById('barcode-scanner-modal-content');
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        }, 10);

        // Check if HTTPS is required for camera access
        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            console.warn('Camera access may require HTTPS on mobile devices');
            alert('Note: Camera access on mobile devices requires HTTPS. If the scanner doesn\'t work, please use the manual barcode input below.');
        }

        // Clean up any existing scanner instance before starting a new one
        if (html5QrcodeScanner && isScanning) {
            try {
                await html5QrcodeScanner.stop();
                await html5QrcodeScanner.clear();
                isScanning = false;
            } catch (err) {
                console.warn('Error cleaning up previous scanner:', err);
            }
            html5QrcodeScanner = null;
        }

        // Initialize a new scanner instance
        html5QrcodeScanner = new Html5Qrcode("reader");

        // Get available cameras
        try {
            cameraDevices = await Html5Qrcode.getCameras();
            console.log('Available cameras:', cameraDevices);
            
            if (cameraDevices && cameraDevices.length > 0) {
                // Start with rear camera (environment) if available
                currentCameraIndex = cameraDevices.findIndex(d => d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('environment'));
                if (currentCameraIndex === -1) currentCameraIndex = 0;
                currentCameraId = cameraDevices[currentCameraIndex].id;
                console.log('Using camera:', currentCameraId, cameraDevices[currentCameraIndex].label);

                // Show flip button only if there are multiple cameras
                const flipBtn = document.getElementById('flip-camera-btn');
                if (flipBtn) {
                    if (cameraDevices.length > 1) {
                        flipBtn.classList.remove('hidden');
                    } else {
                        flipBtn.classList.add('hidden');
                    }
                }
            } else {
                console.error('No cameras found');
                alert('No cameras detected. Please use the manual barcode input below.');
                return;
            }
        } catch (err) {
            console.error("Error getting cameras:", err);
            alert('Unable to access camera. Please use the manual barcode input below.');
            return;
        }

        const config = { 
            fps: 10, 
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
            disableFlip: false
        };
        
        console.log('Starting scanner with config:', config);
        
        try {
            await html5QrcodeScanner.start(
                currentCameraId || { facingMode: "environment" },
                config,
                (decodedText, decodedResult) => {
                    console.log('Barcode scanned successfully:', decodedText);
                    // Barcode scanned successfully
                    handleBarcodeScanned(decodedText);
                    closeBarcodeScanner();
                },
                (errorMessage) => {
                    // Scanning in progress, ignore errors
                    console.log('Scanning in progress...');
                }
            );
            isScanning = true;
        } catch (err) {
            console.error("Error starting scanner:", err);
            if (err.name === 'NotReadableError') {
                alert('Camera is already in use by another application. Please close other apps that might be using the camera, or use the manual barcode input below.');
            } else {
                alert("Unable to start camera scanner. Please use the manual barcode input below.");
            }
            isScanning = false;
        }
    }

    async function flipCamera() {
        if (!html5QrcodeScanner || !isScanning || cameraDevices.length === 0) return;

        try {
            // Stop and clear current scanner completely
            await html5QrcodeScanner.stop();
            await html5QrcodeScanner.clear();
            
            // Destroy and recreate the scanner instance
            html5QrcodeScanner = null;
            html5QrcodeScanner = new Html5Qrcode("reader");
            
            // Switch to next camera
            currentCameraIndex = (currentCameraIndex + 1) % cameraDevices.length;
            currentCameraId = cameraDevices[currentCameraIndex].id;

            const config = { fps: 10, qrbox: { width: 300, height: 150 } };
            
            await html5QrcodeScanner.start(
                currentCameraId,
                config,
                (decodedText, decodedResult) => {
                    handleBarcodeScanned(decodedText);
                    closeBarcodeScanner();
                },
                (errorMessage) => {
                    // Scanning in progress, ignore errors
                }
            );
        } catch (err) {
            console.error("Error switching camera:", err);
            alert("Unable to switch camera. Please try again.");
        }
    }

    function closeBarcodeScanner() {
        const modal = document.getElementById('barcode-scanner-modal');
        const content = document.getElementById('barcode-scanner-modal-content');
        
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);

        // Stop the scanner
        if (html5QrcodeScanner && isScanning) {
            html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear();
                isScanning = false;
            }).catch((err) => {
                console.error("Error stopping scanner:", err);
                isScanning = false;
            });
        }
    }

    // Device Connection Functions
    async function openDeviceConnectionModal() {
        const modal = document.getElementById('device-connection-modal');
        const content = document.getElementById('device-connection-modal-content');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        }, 10);

        // Check if there are active sessions
        const response = await fetch('{{ url('/device-sessions/active') }}');
        const data = await response.json();

        if (data.sessions && data.sessions.length > 0) {
            // There are active sessions
            if (data.sessions.length === 1) {
                // Only one session - show its QR
                const session = data.sessions[0];
                currentSessionCode = session.session_code;
                localStorage.setItem('deviceSessionCode', currentSessionCode);
                document.getElementById('qr-section').classList.remove('hidden');
                document.getElementById('add-session-section').classList.remove('hidden');
                document.getElementById('connected-devices-section').classList.remove('hidden');
                generateQRCode(session.qr_url);
                await loadConnectedDevices();
                // Start polling if not already
                if (!devicePollingInterval) {
                    startDevicePolling();
                }
            } else {
                // Multiple sessions - show list
                await loadConnectedDevices();
            }
        } else {
            // No active sessions, auto-create one and show QR
            await createDeviceSession();
        }
    }

    function closeDeviceConnectionModal() {
        const modal = document.getElementById('device-connection-modal');
        const content = document.getElementById('device-connection-modal-content');
        
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    async function createDeviceSession() {
        try {
            const response = await fetch('{{ url('/device-sessions') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            const data = await response.json();

            if (data.error) {
                console.error('Error creating device session:', data.error);
                alert('Error creating device session: ' + data.error);
                return;
            }

            if (data.session_code) {
                currentSessionCode = data.session_code;
                // Save session code to localStorage for persistence
                localStorage.setItem('deviceSessionCode', currentSessionCode);
                console.log('Session created with code:', currentSessionCode);
                console.log('Starting polling...');
                // Show QR section, hide other sections
                document.getElementById('qr-section').classList.remove('hidden');
                document.getElementById('add-session-section').classList.add('hidden');
                document.getElementById('connected-devices-section').classList.add('hidden');
                generateQRCode(data.qr_url);
                // Load connected devices to show the new session in the list
                loadConnectedDevices();
                // Start polling now that we have a session code
                if (!devicePollingInterval) {
                    startDevicePolling();
                }
            }
        } catch (error) {
            console.error('Error creating device session:', error);
            alert('Error creating device session: ' + error.message);
        }
    }

    function generateQRCode(url) {
        console.log('Generating QR code for URL:', url);
        const qrContainer = document.getElementById('qr-code');
        qrContainer.innerHTML = '';

        new QRCode(qrContainer, {
            text: url,
            width: 192,
            height: 192,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        console.log('QR code generated');
    }

    async function loadConnectedDevices() {
        try {
            console.log('Loading connected devices for modal...');
            const response = await fetch('{{ url('/device-sessions/active') }}');
            const data = await response.json();

            console.log('Connected devices response:', data);

            const devicesSection = document.getElementById('connected-devices-section');
            const devicesList = document.getElementById('connected-devices-list');
            const addSessionSection = document.getElementById('add-session-section');
            const qrSection = document.getElementById('qr-section');

            if (data.sessions && data.sessions.length > 0) {
                console.log('Found active sessions, showing devices list');
                devicesSection.classList.remove('hidden');
                addSessionSection.classList.remove('hidden');
                // Only hide QR section if there are multiple sessions
                if (data.sessions.length > 1) {
                    qrSection.classList.add('hidden');
                }
                devicesList.innerHTML = '';

                data.sessions.forEach(session => {
                    const deviceItem = document.createElement('div');
                    deviceItem.className = 'bg-slate-100 rounded-lg p-3 flex justify-between items-center';
                    const isConnected = currentSessionCode === session.session_code;
                    deviceItem.innerHTML = `
                        <div>
                            <p class="text-sm font-medium text-slate-800">Session: ${session.session_code} ${isConnected ? '<span class="text-emerald-600 text-xs">(Connected)</span>' : ''}</p>
                            <p class="text-xs text-slate-500">Expires: ${new Date(session.expires_at).toLocaleTimeString()}</p>
                        </div>
                        <div class="flex gap-2">
                            ${!isConnected ? `<button onclick="connectToSession('${session.session_code}', '${session.qr_url}')" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">
                                Connect
                            </button>` : ''}
                            <button onclick="viewSessionQR('${session.session_code}', '${session.qr_url}')" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                View QR
                            </button>
                            <button onclick="disconnectDevice('${session.session_code}')" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                Disconnect
                            </button>
                        </div>
                    `;
                    devicesList.appendChild(deviceItem);
                });
            } else {
                console.log('No active sessions, showing QR section');
                devicesSection.classList.add('hidden');
                addSessionSection.classList.add('hidden');
                qrSection.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error loading connected devices:', error);
        }
    }

    async function loadActiveSessions() {
        try {
            console.log('Loading active sessions...');
            // First, check for active sessions in the database
            const response = await fetch('{{ url('/device-sessions/active') }}');
            const data = await response.json();

            console.log('Active sessions response:', data);

            if (data.sessions && data.sessions.length > 0) {
                // Use the first active session
                currentSessionCode = data.sessions[0].session_code;
                localStorage.setItem('deviceSessionCode', currentSessionCode);
                console.log('Using active session from database:', currentSessionCode);
                console.log('Starting polling for restored session...');
                startDevicePolling();
            } else {
                console.log('No active sessions in database, checking localStorage...');
                // No active sessions in database, check localStorage
                const savedSessionCode = localStorage.getItem('deviceSessionCode');
                if (savedSessionCode) {
                    currentSessionCode = savedSessionCode;
                    console.log('Restored session code from localStorage:', currentSessionCode);
                    // Verify it's still valid by checking the database
                    const verifyResponse = await fetch(`{{ url('/device-sessions') }}/${currentSessionCode}`);
                    if (verifyResponse.ok) {
                        console.log('Session is still valid, starting polling...');
                        startDevicePolling();
                    } else {
                        console.log('Session is no longer valid, clearing from localStorage');
                        currentSessionCode = null;
                        localStorage.removeItem('deviceSessionCode');
                    }
                } else {
                    console.log('No saved session in localStorage');
                }
            }
        } catch (error) {
            console.error('Error loading active sessions:', error);
        }
    }

    async function createNewSessionFromModal() {
        // Hide the add session section and devices section
        document.getElementById('add-session-section').classList.add('hidden');
        document.getElementById('connected-devices-section').classList.add('hidden');
        // Show the QR section
        document.getElementById('qr-section').classList.remove('hidden');
        // Create a new session
        await createDeviceSession();
    }

    function viewSessionQR(sessionCode, qrUrl) {
        // Show QR section
        document.getElementById('qr-section').classList.remove('hidden');
        // Generate QR code
        generateQRCode(qrUrl);
    }

    function connectToSession(sessionCode, qrUrl) {
        // Set this as the current session
        currentSessionCode = sessionCode;
        localStorage.setItem('deviceSessionCode', currentSessionCode);

        console.log('Connected to session:', currentSessionCode);

        // Show QR section
        document.getElementById('qr-section').classList.remove('hidden');
        // Generate QR code
        generateQRCode(qrUrl);

        // Start polling if not already
        if (!devicePollingInterval) {
            startDevicePolling();
        }

        // Reload the devices list to update the connected status
        loadConnectedDevices();
    }

    async function disconnectDevice(sessionCode) {
        try {
            const response = await fetch(`{{ url('/device-sessions') }}/${sessionCode}/deactivate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (response.ok) {
                // Clear session code from localStorage if this was the current session
                if (currentSessionCode === sessionCode) {
                    currentSessionCode = null;
                    localStorage.removeItem('deviceSessionCode');
                    // Stop polling
                    if (devicePollingInterval) {
                        clearInterval(devicePollingInterval);
                        devicePollingInterval = null;
                    }
                }

                // Reload the devices list
                await loadConnectedDevices();

                // Check if there are any remaining sessions
                const checkResponse = await fetch('{{ url('/device-sessions/active') }}');
                const checkData = await checkResponse.json();

                if (!checkData.sessions || checkData.sessions.length === 0) {
                    // No sessions left, hide QR section and show it ready for new session
                    document.getElementById('qr-section').classList.add('hidden');
                    document.getElementById('add-session-section').classList.add('hidden');
                    document.getElementById('connected-devices-section').classList.add('hidden');
                    // Clear the QR code
                    document.getElementById('qr-code').innerHTML = '';
                } else if (currentSessionCode === null && checkData.sessions.length > 0) {
                    // If we disconnected the current session but there are others, auto-connect to the first one
                    const firstSession = checkData.sessions[0];
                    connectToSession(firstSession.session_code, firstSession.qr_url);
                }
            }
        } catch (error) {
            console.error('Error disconnecting device:', error);
            alert('Error disconnecting device');
        }
    }

    function startDevicePolling() {
        console.log('Starting device polling with session code:', currentSessionCode);
        // Poll every 2 seconds for items from connected devices
        devicePollingInterval = setInterval(async () => {
            if (currentSessionCode) {
                console.log('Polling for cart items...');
                await checkDeviceCartItems();
            }
        }, 2000);
    }

    let devicePollErrorCount = 0;

    async function checkDeviceCartItems() {
        try {
            if (!currentSessionCode) {
                return;
            }
            
            const response = await fetch(`{{ url('/device-sessions') }}/${currentSessionCode}/cart-items`);
            
            // Check if we got a JSON response (not an HTML redirect/error page)
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                devicePollErrorCount++;
                if (devicePollErrorCount >= 3) {
                    console.warn('Device polling stopped: server is not returning JSON (likely session expired). Errors:', devicePollErrorCount);
                    if (devicePollingInterval) {
                        clearInterval(devicePollingInterval);
                        devicePollingInterval = null;
                    }
                }
                return;
            }

            const data = await response.json();
            devicePollErrorCount = 0; // Reset on success
            
            if (data.items && data.items.length > 0) {
                console.log('Adding items to cart:', data.items);
                data.items.forEach(item => {
                    const cartItem = {
                        id: item.id,
                        name: item.name,
                        price: parseFloat(item.price),
                        barcode: item.barcode,
                        type: item.type,
                        quantity: 1
                    };
                    addToCart(cartItem);
                });
                
                // Clear the device session cart after adding to POS cart
                await fetch(`{{ url('/device-sessions') }}/${currentSessionCode}/clear-cart`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
            }
        } catch (error) {
            devicePollErrorCount++;
            if (devicePollErrorCount >= 3) {
                console.warn('Device polling stopped due to repeated errors:', error.message);
                if (devicePollingInterval) {
                    clearInterval(devicePollingInterval);
                    devicePollingInterval = null;
                }
            }
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
        saveCartToStorage();
        renderCart();
    }

    function loadCartFromStorage() {
        try {
            const savedCart = localStorage.getItem('posCart');
            if (savedCart) {
                cart = JSON.parse(savedCart);
                renderCart();
            }
        } catch (error) {
            console.error('Error loading cart from storage:', error);
        }
    }

    function saveCartToStorage() {
        try {
            localStorage.setItem('posCart', JSON.stringify(cart));
        } catch (error) {
            console.error('Error saving cart to storage:', error);
        }
    }

    function clearCartStorage() {
        try {
            localStorage.removeItem('posCart');
        } catch (error) {
            console.error('Error clearing cart from storage:', error);
        }
    }

    function updateCartQuantity(index, delta) {
        cart[index].quantity += delta;
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        saveCartToStorage();
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cart-items');
        const mobileContainer = document.getElementById('mobile-cart-items');
        
        const cartHtml = cart.length === 0
            ? `<div class="text-center py-10 text-slate-400 flex flex-col items-center">
                <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p>Cart is empty</p>
                <p class="text-xs mt-1">Scan or click items to add</p>
            </div>`
            : cart.map((item, idx) => `
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

        if (container) container.innerHTML = cartHtml;
        if (mobileContainer) mobileContainer.innerHTML = cartHtml;

        const hasItems = cart.length > 0;
        
        // Desktop buttons
        if (document.getElementById('checkout-btn')) document.getElementById('checkout-btn').disabled = !hasItems;
        if (document.getElementById('pay-later-btn')) document.getElementById('pay-later-btn').disabled = !hasItems;
        
        // Mobile buttons
        if (document.getElementById('mobile-checkout-btn')) document.getElementById('mobile-checkout-btn').disabled = !hasItems;
        if (document.getElementById('mobile-pay-later-btn')) document.getElementById('mobile-pay-later-btn').disabled = !hasItems;

        // Update mobile floating button
        updateMobileCartButton();

        updateTotals();
    }

    function updateTotals() {
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = subtotal * taxRate;
        const total = subtotal + tax;

        // Desktop totals
        if (document.getElementById('cart-subtotal')) document.getElementById('cart-subtotal').textContent = '$' + subtotal.toFixed(2);
        if (document.getElementById('cart-tax')) document.getElementById('cart-tax').textContent = '$' + tax.toFixed(2);
        if (document.getElementById('cart-total')) document.getElementById('cart-total').textContent = '$' + total.toFixed(2);
        if (document.getElementById('checkout-total-display')) document.getElementById('checkout-total-display').textContent = '$' + total.toFixed(2);

        // Mobile totals
        if (document.getElementById('mobile-cart-subtotal')) document.getElementById('mobile-cart-subtotal').textContent = '$' + subtotal.toFixed(2);
        if (document.getElementById('mobile-cart-tax')) document.getElementById('mobile-cart-tax').textContent = '$' + tax.toFixed(2);
        if (document.getElementById('mobile-cart-total-display')) document.getElementById('mobile-cart-total-display').textContent = '$' + total.toFixed(2);
        if (document.getElementById('mobile-cart-total')) document.getElementById('mobile-cart-total').textContent = '$' + total.toFixed(2);
    }

    function updateMobileCartButton() {
        const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = total * taxRate;
        const finalTotal = total + tax;

        if (document.getElementById('mobile-cart-count')) {
            document.getElementById('mobile-cart-count').textContent = itemCount;
        }
        if (document.getElementById('mobile-cart-items-text')) {
            document.getElementById('mobile-cart-items-text').textContent = itemCount === 1 ? '1 item' : `${itemCount} items`;
        }
        if (document.getElementById('mobile-cart-total')) {
            document.getElementById('mobile-cart-total').textContent = '$' + finalTotal.toFixed(2);
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

    function clearCart() {
        cart = [];
        clearCartStorage();
        renderCart();
        if (document.getElementById('checkout-btn')) document.getElementById('checkout-btn').disabled = true;
        if (document.getElementById('pay-later-btn')) document.getElementById('pay-later-btn').disabled = true;
        if (document.getElementById('mobile-checkout-btn')) document.getElementById('mobile-checkout-btn').disabled = true;
        if (document.getElementById('mobile-pay-later-btn')) document.getElementById('mobile-pay-later-btn').disabled = true;
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

    const paymentSettings = {!! json_encode(\App\Models\PaymentSettings::getSettings()) !!};

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

        const container = document.getElementById('payment-details-container');
        container.innerHTML = '';
        container.classList.remove('hidden');

        if (method === 'cash') {
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * taxRate;
            const total = subtotal + tax;
            
            container.innerHTML = `
                <div class="space-y-3">
                    <p class="text-sm font-semibold text-slate-700">Cash Payment</p>
                    <p class="text-xs text-slate-500 mb-2">${paymentSettings.cash_instructions || 'Accept cash payments'}</p>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Amount Paid</label>
                        <input type="number" id="cash-amount-paid" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter amount..." step="0.01" min="${total}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Change Due</label>
                        <div id="cash-change-due" class="text-lg font-bold text-slate-800">$0.00</div>
                    </div>
                </div>
            `;
            
            const amountInput = document.getElementById('cash-amount-paid');
            const changeDisplay = document.getElementById('cash-change-due');
            
            // Auto-focus the input to make it faster
            setTimeout(() => {
                if (amountInput) amountInput.focus();
            }, 50);
            
            amountInput.addEventListener('input', (e) => {
                let paidStr = e.target.value;
                const paid = parseFloat(paidStr) || 0;
                const change = paid - total;
                
                if (change >= 0) {
                    changeDisplay.textContent = '$' + change.toFixed(2);
                    changeDisplay.classList.remove('text-red-500');
                    changeDisplay.classList.add('text-slate-800');
                } else {
                    changeDisplay.textContent = '-$' + Math.abs(change).toFixed(2);
                    changeDisplay.classList.add('text-red-500');
                    changeDisplay.classList.remove('text-slate-800');
                }
            });
        } else if (method === 'card') {
            container.innerHTML = `
                <div class="space-y-2 text-center py-2">
                    <svg class="w-8 h-8 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    <p class="text-sm font-semibold text-slate-700">Card Payment</p>
                    <p class="text-sm text-slate-600">${paymentSettings.card_instructions || 'Insert or tap card on the terminal'}</p>
                </div>
            `;
        } else if (method === 'qr') {
            const qrImage = paymentSettings.qr_code_image ? '/app-storage/' + paymentSettings.qr_code_image : null;
            const qrContent = qrImage 
                ? `<img src="${qrImage}" class="w-48 h-48 mx-auto object-contain mb-3" alt="QR Code">`
                : `<div class="w-48 h-48 mx-auto bg-slate-100 flex items-center justify-center text-slate-400 mb-3"><span class="text-xs">No QR configured</span></div>`;
                
            container.innerHTML = `
                <div class="text-center py-2">
                    <p class="text-sm font-semibold text-slate-700 mb-3">QR Payment</p>
                    ${qrContent}
                    <p class="text-sm text-slate-600">${paymentSettings.qr_code_instructions || 'Scan the QR code to pay'}</p>
                </div>
            `;
        } else if (method === 'transfer') {
            container.innerHTML = `
                <div class="space-y-3">
                    <p class="text-sm font-semibold text-slate-700 border-b border-slate-100 pb-2">Bank Transfer Details</p>
                    <p class="text-sm text-slate-600 mb-2">${paymentSettings.transfer_instructions || 'Transfer to the account details below'}</p>
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-slate-500">Bank Name</span>
                            <span class="text-sm font-medium text-slate-800">${paymentSettings.bank_name || '-'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-slate-500">Account Number</span>
                            <span class="text-sm font-medium text-slate-800">${paymentSettings.account_number || '-'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-slate-500">Account Name</span>
                            <span class="text-sm font-medium text-slate-800">${paymentSettings.account_name || '-'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-slate-500">Bank Address</span>
                            <span class="text-sm font-medium text-slate-800 text-right">${paymentSettings.bank_address || '-'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-slate-500">SWIFT/BIC</span>
                            <span class="text-sm font-medium text-slate-800">${paymentSettings.swift_code || '-'}</span>
                        </div>
                    </div>
                </div>
            `;
        } else {
            container.classList.add('hidden');
        }
    }

    let currentOrderType = 'walk-in';

    function setOrderType(type) {
        currentOrderType = type;
        
        const walkInBtn = document.getElementById('type-walk-in');
        const dineInBtn = document.getElementById('type-dine-in');
        const tableContainer = document.getElementById('table-selection-container');
        
        walkInBtn.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700', 'border-slate-200', 'text-slate-600');
        dineInBtn.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700', 'border-slate-200', 'text-slate-600');
        
        if (type === 'walk-in') {
            walkInBtn.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
            dineInBtn.classList.add('border-slate-200', 'text-slate-600');
            tableContainer.classList.add('hidden');
        } else {
            dineInBtn.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
            walkInBtn.classList.add('border-slate-200', 'text-slate-600');
            tableContainer.classList.remove('hidden');
        }
    }

    async function submitOrder(forceLater = false) {
        if (cart.length === 0 || isSubmitting) return;

        let selectedTableId = null;
        if (currentOrderType === 'dine-in') {
            const tableSelect = document.getElementById('checkout-table-id');
            if (!tableSelect.value) {
                alert('Please select a table for Dine-in orders.');
                return;
            }
            selectedTableId = tableSelect.value;
        }

        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = subtotal * taxRate;
        const total = subtotal + tax;

        if (!forceLater && paymentMethod === 'cash') {
            const amountInput = document.getElementById('cash-amount-paid');
            if (amountInput) {
                const paid = parseFloat(amountInput.value) || 0;
                if (paid < total) {
                    alert('Please enter a sufficient cash amount to cover the total.');
                    return;
                }
            }
        }

        isSubmitting = true;
        const btn = forceLater ? document.getElementById('pay-later-btn') : document.getElementById('submit-order-btn');
        const spinner = document.getElementById('submit-spinner');
        
        btn.disabled = true;
        btn.classList.add('opacity-75');
        if (!forceLater) {
            spinner.classList.remove('hidden');
        }

        const payload = {
            items: cart.map(item => ({
                id: item.id,
                name: item.name,
                price: item.price,
                quantity: item.quantity,
                type: item.type
            })),
            order_type: currentOrderType,
            table_id: selectedTableId,
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
                clearCartStorage();
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
        
        const btn = document.getElementById('print-receipt-btn');
        btn.onclick = function() {
            closeSuccessModal();
            viewReceipt(orderId);
        };
        
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

    function viewReceipt(orderId) {
        const modal = document.getElementById('receipt-modal');
        const modalContent = document.getElementById('receipt-modal-content');
        const content = document.getElementById('receipt-content');
        
        // Show modal with animation
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }, 10);
        
        // Load content
        content.innerHTML = '<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900"></div></div>';
        
        fetch('{{ route('cashier.receipt', ':id') }}'.replace(':id', orderId))
            .then(response => response.text())
            .then(html => {
                content.innerHTML = html;
            })
            .catch(error => {
                content.innerHTML = '<div class="text-center py-8 text-red-600">Failed to load receipt.</div>';
            });
    }
    
    function closeReceiptModal() {
        const modal = document.getElementById('receipt-modal');
        const modalContent = document.getElementById('receipt-modal-content');
        
        // Animate out
        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }
    
    function printReceipt() {
        const content = document.getElementById('receipt-content');
        const printWindow = window.open('', '', 'width=600,height=800');
        printWindow.document.write('<html><head><title>Receipt</title>');
        printWindow.document.write('<style>body{font-family:monospace;padding:20px;}.receipt-header{text-align:center;margin-bottom:20px;}.receipt-item{display:flex;justify-content:space-between;margin:5px 0;}.receipt-total{border-top:1px solid #000;margin-top:10px;padding-top:10px;font-weight:bold;}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(content.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
</script>
@endpush
