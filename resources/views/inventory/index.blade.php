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
                        <input type="text" id="movementProductName" readonly class="staff-input bg-slate-50">
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

    @if ($canEditInventory)
        @include('inventory.partials.bulk-purchase-modal', [
            'bulkProducts' => $bulkProducts,
            'bulkPurchaseTitle' => 'Bulk Purchase Inventory',
            'bulkPurchaseDescription' => 'Add multiple inventory items in one stock purchase.',
        ])
    @endif

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
