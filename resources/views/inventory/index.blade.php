@extends('layouts.staff')

@section('title', 'Inventory')

@php
    $user = auth()->user();
    $userPermissions = [];
    if ($user) {
        $userPermissions = \App\Models\Permission::where('role', $user->role->value)
            ->get()
            ->keyBy('permission');
    }
    
    $can = function($permission, $action = 'view') use ($user, $userPermissions) {
        if (!$user) return false;
        if ($user->isSuperAdmin()) return true;
        $perm = $userPermissions->get($permission);
        if (!$perm) return false;
        return $action === 'edit' ? $perm->can_edit : $perm->can_view;
    };

    $canEditInventory = $user && ($user->isSuperAdmin() || $user->isOwner() || $user->isAdmin());
@endphp

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Inventory</h1>
            <p class="staff-page-subtitle">Manage products, stock levels, and categories.</p>
        </div>
        @if ($canEditInventory)
            <div class="flex flex-wrap gap-3">
                <button onclick="openBulkPurchaseModal()" class="staff-btn-secondary">Bulk Purchase</button>
                <button onclick="openAddProductModal()" class="staff-btn-primary">Add Product</button>
            </div>
        @endif
    </div>

    <x-flash />

    <div class="staff-tabs mb-6">
        <button onclick="window.location.href='{{ route('inventory.index') }}'" class="staff-tab {{ request()->routeIs('inventory.index') ? 'staff-tab-active' : '' }}">Products</button>
        <button onclick="window.location.href='{{ route('inventory.supplies') }}'" class="staff-tab {{ request()->routeIs('inventory.supplies') ? 'staff-tab-active' : '' }}">Supplies</button>
        <button onclick="window.location.href='{{ route('inventory.categories') }}'" class="staff-tab {{ request()->routeIs('inventory.categories') ? 'staff-tab-active' : '' }}">Menu Categories</button>
        <button onclick="window.location.href='{{ route('inventory.stock-categories') }}'" class="staff-tab {{ request()->routeIs('inventory.stock-categories') ? 'staff-tab-active' : '' }}">Stock Categories</button>
        <button onclick="window.location.href='{{ route('inventory.stock-movements') }}'" class="staff-tab {{ request()->routeIs('inventory.stock-movements') ? 'staff-tab-active' : '' }}">Stock Movements</button>
        <button onclick="window.location.href='{{ route('inventory.bulk-purchases.history') }}'" class="staff-tab {{ request()->routeIs('inventory.bulk-purchases.history') ? 'staff-tab-active' : '' }}">Bulk History</button>
    </div>

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Purchase Price</th>
                        <th>Selling Price</th>
                        <th>Unit</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $product->name }}</td>
                            <td class="text-slate-600">{{ $product->sku }}</td>
                            <td>{{ $product->category ? $product->category->name : '-' }}</td>
                            <td>
                                <span class="{{ $product->stock_quantity <= $product->min_stock_level ? 'text-red-600 font-semibold' : 'text-slate-900' }}">
                                    {{ $product->stock_quantity }}
                                </span>
                                @if ($product->stock_quantity <= $product->min_stock_level)
                                    <span class="text-xs text-red-500 ml-1">(Low)</span>
                                @endif
                            </td>
                            <td class="text-slate-900">${{ number_format($product->purchase_price, 2) }}</td>
                            <td class="text-slate-900">${{ number_format($product->selling_price, 2) }}</td>
                            <td class="text-slate-600">{{ $product->unit }}</td>
                            <td class="text-right space-x-4">
                                @if ($canEditInventory)
                                    <button onclick="openEditProductModal({{ $product->toJson() }})" class="staff-link">Edit</button>
                                    <button onclick="openStockMovementModal({{ $product->toJson() }})" class="staff-link">Add Stock</button>
                                    <form action="{{ route('inventory.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="staff-link text-red-600">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-500">No products yet. Add your first product!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(isset($gifts) && $gifts->isNotEmpty())
        <h2 class="text-2xl font-bold text-slate-900 mt-8 mb-4">Gifts</h2>
        <div class="staff-table-wrap">
            <div class="overflow-x-auto">
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Description</th>
                            <th>Cost</th>
                            <th>Purchase Price</th>
                            <th>Stock</th>
                            <th>Order</th>
                            <th>Active</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gifts as $gift)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $gift->name }}</td>
                                <td class="text-slate-600">{{ $gift->sku ?? '-' }}</td>
                                <td class="text-slate-600">{{ $gift->description ?? '-' }}</td>
                                <td class="text-slate-900">${{ number_format($gift->cost, 2) }}</td>
                                <td class="text-slate-900">${{ number_format($gift->purchase_price ?? 0, 2) }}</td>
                                <td>
                                    <span class="{{ $gift->stock_quantity <= 5 ? 'text-red-600 font-semibold' : 'text-slate-900' }}">
                                        {{ $gift->stock_quantity }}
                                    </span>
                                    @if ($gift->stock_quantity <= 5)
                                        <span class="text-xs text-red-500 ml-1">(Low)</span>
                                    @endif
                                </td>
                                <td class="text-slate-600">{{ $gift->order }}</td>
                                <td>
                                    @if($gift->is_active)
                                        <span class="text-green-600 font-semibold">Yes</span>
                                    @else
                                        <span class="text-red-600 font-semibold">No</span>
                                    @endif
                                </td>
                                <td class="text-right space-x-4">
                                    @if ($canEditInventory)
                                        <button onclick="openGiftEditModal({{ $gift->toJson() }})" class="staff-link">Edit</button>
                                        <button onclick="openGiftStockMovementModal({{ $gift->toJson() }})" class="staff-link">Add Stock</button>
                                        <form action="{{ route('admin.gifts.destroy', $gift) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this gift?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="staff-link text-red-600">Delete</button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.gifts.index') }}" class="staff-link">View</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($products->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $products->links() }}
        </div>
    @endif

    <!-- Add Product Modal -->
    <div id="addProductModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="addProductModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add Product</h2>
                <p class="text-sm text-slate-500 mt-1">Add a new product to inventory.</p>
            </div>
            <form method="POST" action="{{ route('inventory.products.store') }}" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                        <input type="text" name="name" required maxlength="255" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">SKU</label>
                        <input type="text" name="sku" required maxlength="100" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                        <select name="inventory_category_id" class="staff-input">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Purchase Price</label>
                            <input type="number" step="0.01" name="purchase_price" required class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Selling Price</label>
                            <input type="number" step="0.01" name="selling_price" required class="staff-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Stock Quantity</label>
                            <input type="number" name="stock_quantity" required class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Min Stock Level</label>
                            <input type="number" name="min_stock_level" required class="staff-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Unit</label>
                        <input type="text" name="unit" value="pcs" required maxlength="50" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" rows="3" maxlength="5000" class="staff-input"></textarea>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeAddProductModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="editProductModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Edit Product</h2>
                <p class="text-sm text-slate-500 mt-1">Update product details.</p>
            </div>
            <form method="POST" action="" id="editProductForm" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                        <input type="text" name="name" id="editProductName" required maxlength="255" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">SKU</label>
                        <input type="text" name="sku" id="editProductSku" required maxlength="100" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                        <select name="inventory_category_id" id="editProductCategory" class="staff-input">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Purchase Price</label>
                            <input type="number" step="0.01" name="purchase_price" id="editProductPurchasePrice" required class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Selling Price</label>
                            <input type="number" step="0.01" name="selling_price" id="editProductSellingPrice" required class="staff-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Stock Quantity</label>
                            <input type="number" name="stock_quantity" id="editProductStock" required class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Min Stock Level</label>
                            <input type="number" name="min_stock_level" id="editProductMinStock" required class="staff-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Unit</label>
                        <input type="text" name="unit" id="editProductUnit" required maxlength="50" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" id="editProductDescription" rows="3" maxlength="5000" class="staff-input"></textarea>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeEditProductModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Gift Stock Movement Modal -->
    <div id="giftStockMovementModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="giftStockMovementModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add Gift Stock</h2>
                <p class="text-sm text-slate-500 mt-1">Record stock in, out, or adjustment for gifts.</p>
            </div>
            <form method="POST" action="{{ url('/inventory/gifts/stock-movement') }}" class="p-6">
                @csrf
                <input type="hidden" name="gift_id" id="giftMovementGiftId">
                <input type="hidden" name="_method" value="POST">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Gift</label>
                        <input type="text" id="giftMovementGiftName" readonly class="staff-input bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                        <select name="type" required class="staff-input">
                            <option value="in">Stock In</option>
                            <option value="out">Stock Out</option>
                            <option value="adjustment">Adjustment</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Quantity</label>
                        <input type="number" name="quantity" required class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Reference</label>
                        <input type="text" name="reference" maxlength="255" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" maxlength="1000" class="staff-input"></textarea>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeGiftStockMovementModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Record Movement</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Gift Edit Modal -->
    <div id="giftEditModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="giftEditModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Edit Gift</h2>
                <p class="text-sm text-slate-500 mt-1">Update gift details in inventory.</p>
            </div>
            <form method="POST" action="{{ url('/inventory/gifts/inventory-update') }}" class="p-6">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="gift_id" id="giftEditGiftId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">SKU</label>
                        <input type="text" name="sku" id="giftEditSku" required maxlength="100" class="staff-input">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Cost</label>
                            <input type="number" step="0.01" name="cost" id="giftEditCost" required class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Purchase Price</label>
                            <input type="number" step="0.01" name="purchase_price" id="giftEditPurchasePrice" class="staff-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Stock Quantity</label>
                        <input type="number" name="stock_quantity" id="giftEditStock" required class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Order</label>
                        <input type="number" name="order" id="giftEditOrder" required class="staff-input">
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeGiftEditModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stock Movement Modal -->
    <div id="stockMovementModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="stockMovementModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add Stock Movement</h2>
                <p class="text-sm text-slate-500 mt-1">Record stock in, out, or adjustment.</p>
            </div>
            <form method="POST" action="{{ route('inventory.stock-movements.store') }}" class="p-6">
                @csrf
                <input type="hidden" name="product_id" id="movementProductId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Product</label>
                        <div class="flex gap-2">
                            <input type="text" id="movementProductName" readonly class="staff-input bg-slate-50 flex-1">
                            <button type="button" onclick="openInventoryBarcodeScanner()" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 whitespace-nowrap">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V8a1 1 0 011-1H5a1 1 0 00-1 1v.01M4 12h2a1 1 0 001-1V12a1 1 0 011-1H4a1 1 0 00-1 1v.01M16 12h2a1 1 0 001-1V12a1 1 0 011-1h-2a1 1 0 00-1 1v.01M12 16h2a1 1 0 001-1V16a1 1 0 011-1h-2a1 1 0 00-1 1v.01M12 20h2a1 1 0 001-1V20a1 1 0 011-1h-2a1 1 0 00-1 1v.01"></path></svg>
                                Scan
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                        <select name="type" required class="staff-input">
                            <option value="in">Stock In</option>
                            <option value="out">Stock Out</option>
                            <option value="adjustment">Adjustment</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Quantity</label>
                            <input type="number" name="quantity" required class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Unit Cost</label>
                            <input type="number" step="0.01" name="unit_cost" id="movementUnitCost" class="staff-input bg-slate-50" readonly>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Reference</label>
                        <input type="text" name="reference" maxlength="255" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" maxlength="1000" class="staff-input"></textarea>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeStockMovementModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Record Movement</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Inventory Barcode Scanner Modal -->
    <div id="inventory-barcode-scanner-modal" class="fixed inset-0 bg-slate-900/90 backdrop-blur-sm hidden items-center justify-center z-[10000] p-4 transition-opacity opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform scale-95 transition-transform" id="inventory-barcode-scanner-modal-content">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-800">Scan Barcode</h3>
                <button onclick="closeInventoryBarcodeScanner()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-6">
                <div id="inventory-reader" class="w-full bg-black rounded-lg overflow-hidden" style="min-height: 300px;"></div>
                <p class="text-center text-sm text-slate-500 mt-4">Point your camera at a barcode to scan</p>
                
                <!-- Manual barcode input fallback -->
                <div class="mt-4 pt-4 border-t border-slate-200">
                    <p class="text-xs text-slate-500 mb-2">Or enter barcode manually:</p>
                    <div class="flex gap-2">
                        <input type="text" id="inventory-manual-barcode-input" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter barcode...">
                        <button onclick="submitInventoryManualBarcode()" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Add</button>
                    </div>
                </div>
            </div>
            
            <div class="p-4 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
                <button onclick="closeInventoryBarcodeScanner()" class="w-full py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    @if ($canEditInventory)
        @include('inventory.partials.bulk-purchase-modal', [
            'bulkProducts' => $bulkProducts,
            'bulkPurchaseTitle' => 'Bulk Purchase Inventory',
            'bulkPurchaseDescription' => 'Add multiple inventory items in one stock purchase.',
        ])
    @endif

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        // Inventory barcode scanner
        let inventoryHtml5QrcodeScanner = null;
        let inventoryIsScanning = false;

        const inventoryProducts = {!! json_encode($products->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'barcode' => $p->barcode,
                'purchase_price' => $p->purchase_price,
            ];
        })) !!};

        function openInventoryBarcodeScanner() {
            const modal = document.getElementById('inventory-barcode-scanner-modal');
            const content = document.getElementById('inventory-barcode-scanner-modal-content');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
            }, 10);

            // Initialize the scanner
            if (!inventoryHtml5QrcodeScanner) {
                inventoryHtml5QrcodeScanner = new Html5Qrcode("inventory-reader");
            }

            // Check if HTTPS is required for camera access
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                console.warn('Camera access may require HTTPS on mobile devices');
                alert('Note: Camera access on mobile devices requires HTTPS. If the scanner doesn\'t work, please use the manual barcode input below.');
            }

            // Get available cameras
            Html5Qrcode.getCameras().then(devices => {
                console.log('Available cameras:', devices);
                
                if (devices && devices.length > 0) {
                    const cameraId = devices[0].id;
                    const config = { 
                        fps: 10, 
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0
                    };
                    
                    inventoryHtml5QrcodeScanner.start(
                        cameraId,
                        config,
                        (decodedText, decodedResult) => {
                            console.log('Barcode scanned:', decodedText);
                            handleInventoryBarcodeScanned(decodedText);
                            closeInventoryBarcodeScanner();
                        },
                        (errorMessage) => {
                            console.log('Scanning in progress...');
                        }
                    ).then(() => {
                        inventoryIsScanning = true;
                    }).catch(err => {
                        console.error("Error starting scanner:", err);
                        alert("Unable to start camera scanner. Please use the manual barcode input below.");
                        inventoryIsScanning = false;
                    });
                } else {
                    console.error('No cameras found');
                    alert('No cameras detected. Please use the manual barcode input below.');
                }
            }).catch(err => {
                console.error("Error getting cameras:", err);
                alert('Unable to access camera. Please use the manual barcode input below.');
            });
        }

        function closeInventoryBarcodeScanner() {
            const modal = document.getElementById('inventory-barcode-scanner-modal');
            const content = document.getElementById('inventory-barcode-scanner-modal-content');
            
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);

            // Stop the scanner
            if (inventoryHtml5QrcodeScanner && inventoryIsScanning) {
                inventoryHtml5QrcodeScanner.stop().then(() => {
                    inventoryHtml5QrcodeScanner.clear();
                    inventoryIsScanning = false;
                }).catch((err) => {
                    console.error("Error stopping scanner:", err);
                    inventoryIsScanning = false;
                });
            }
        }

        function handleInventoryBarcodeScanned(barcode) {
            console.log('Inventory barcode scanned:', barcode);
            console.log('Available products:', inventoryProducts);
            
            const product = inventoryProducts.find(p => p.barcode === barcode);
            if (product) {
                console.log('Product found:', product);
                // Open stock movement modal with the found product
                openStockMovementModal({
                    id: product.id,
                    name: product.name,
                    purchase_price: product.purchase_price
                });
            } else {
                console.log('Product not found for barcode:', barcode);
                alert('Product with barcode ' + barcode + ' not found. Please try manual entry or check if the product has a barcode assigned.');
            }
        }

        function submitInventoryManualBarcode() {
            const input = document.getElementById('inventory-manual-barcode-input');
            const barcode = input.value.trim();
            
            if (barcode) {
                handleInventoryBarcodeScanned(barcode);
                input.value = '';
            }
        }
    </script>

    <script>
        function openAddProductModal() {
            const modal = document.getElementById('addProductModal');
            const content = document.getElementById('addProductModalContent');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeAddProductModal() {
            const modal = document.getElementById('addProductModal');
            const content = document.getElementById('addProductModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function openStockMovementModal(product) {
            const modal = document.getElementById('stockMovementModal');
            const content = document.getElementById('stockMovementModalContent');
            document.getElementById('movementProductId').value = product.id;
            document.getElementById('movementProductName').value = product.name;
            document.getElementById('movementUnitCost').value = Number(product.purchase_price || 0).toFixed(2);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeStockMovementModal() {
            const modal = document.getElementById('stockMovementModal');
            const content = document.getElementById('stockMovementModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function openGiftStockMovementModal(gift) {
            const modal = document.getElementById('giftStockMovementModal');
            const content = document.getElementById('giftStockMovementModalContent');
            document.getElementById('giftMovementGiftId').value = gift.id;
            document.getElementById('giftMovementGiftName').value = gift.name;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeGiftStockMovementModal() {
            const modal = document.getElementById('giftStockMovementModal');
            const content = document.getElementById('giftStockMovementModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function openGiftEditModal(gift) {
            const modal = document.getElementById('giftEditModal');
            const content = document.getElementById('giftEditModalContent');
            document.getElementById('giftEditGiftId').value = gift.id;
            document.getElementById('giftEditSku').value = gift.sku || '';
            document.getElementById('giftEditCost').value = gift.cost;
            document.getElementById('giftEditPurchasePrice').value = gift.purchase_price || '';
            document.getElementById('giftEditStock').value = gift.stock_quantity;
            document.getElementById('giftEditOrder').value = gift.order;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeGiftEditModal() {
            const modal = document.getElementById('giftEditModal');
            const content = document.getElementById('giftEditModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function openEditProductModal(product) {
            const modal = document.getElementById('editProductModal');
            const content = document.getElementById('editProductModalContent');
            const form = document.getElementById('editProductForm');

            form.action = '{{ route('inventory.products.update', ':productId') }}'.replace(':productId', product.id);
            document.getElementById('editProductName').value = product.name || '';
            document.getElementById('editProductSku').value = product.sku || '';
            document.getElementById('editProductCategory').value = product.inventory_category_id || '';
            document.getElementById('editProductPurchasePrice').value = product.purchase_price || '';
            document.getElementById('editProductSellingPrice').value = product.selling_price || '';
            document.getElementById('editProductStock').value = product.stock_quantity || 0;
            document.getElementById('editProductMinStock').value = product.min_stock_level || 0;
            document.getElementById('editProductUnit').value = product.unit || 'pcs';
            document.getElementById('editProductDescription').value = product.description || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeEditProductModal() {
            const modal = document.getElementById('editProductModal');
            const content = document.getElementById('editProductModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        document.getElementById('addProductModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddProductModal();
        });

        document.getElementById('editProductModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditProductModal();
        });

        document.getElementById('stockMovementModal').addEventListener('click', function(e) {
            if (e.target === this) closeStockMovementModal();
        });
    </script>
@endsection
