<?php $__env->startSection('title', 'Takeout Supplies'); ?>

<?php
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
?>

<?php $__env->startSection('content'); ?>
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Takeout Supplies</h1>
            <p class="staff-page-subtitle">Manage boxes, spoons, bags, and other supply items consumed on takeout orders.</p>
        </div>
        <?php if($canEditInventory): ?>
            <div class="flex flex-wrap gap-3">
                <button onclick="openBulkPurchaseModal()" class="staff-btn-secondary">Bulk Purchase</button>
                <button onclick="openAddSupplyModal()" class="staff-btn-primary">Add Supply</button>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($component)) { $__componentOriginal5168fdb0c14fd91c6598264bc4be63f2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.flash','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2)): ?>
<?php $attributes = $__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2; ?>
<?php unset($__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5168fdb0c14fd91c6598264bc4be63f2)): ?>
<?php $component = $__componentOriginal5168fdb0c14fd91c6598264bc4be63f2; ?>
<?php unset($__componentOriginal5168fdb0c14fd91c6598264bc4be63f2); ?>
<?php endif; ?>



    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Takeout Rate</th>
                        <th>Purchase Price</th>
                        <th>Selling Price</th>
                        <th>Unit</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-semibold text-slate-900"><?php echo e($product->name); ?></td>
                            <td class="text-slate-600"><?php echo e($product->sku); ?></td>
                            <td><?php echo e($product->category ? $product->category->name : '-'); ?></td>
                            <td>
                                <span class="<?php echo e($product->stock_quantity <= $product->min_stock_level ? 'text-red-600 font-semibold' : 'text-slate-900'); ?>">
                                    <?php echo e($product->stock_quantity); ?>

                                </span>
                                <?php if($product->stock_quantity <= $product->min_stock_level): ?>
                                    <span class="text-xs text-red-500 ml-1">(Low)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="staff-badge-green">Auto x<?php echo e($product->consume_per_item); ?></span>
                            </td>
                            <td class="text-slate-900">$<?php echo e(number_format($product->purchase_price, 2)); ?></td>
                            <td class="text-slate-900">$<?php echo e(number_format($product->selling_price, 2)); ?></td>
                            <td class="text-slate-600"><?php echo e($product->unit); ?></td>
                            <td class="text-right space-x-4">
                                <?php if($canEditInventory): ?>
                                    <button onclick="openEditSupplyModal(<?php echo e($product->toJson()); ?>)" class="staff-link">Edit</button>
                                    <button onclick="openStockMovementModal(<?php echo e($product->toJson()); ?>)" class="staff-link">Add Stock</button>
                                    <form action="<?php echo e(route('inventory.products.destroy', $product)); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this supply?')" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="staff-link text-red-600">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="py-16 text-center text-slate-500">No takeout supplies yet. Add your first supply item!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($products->hasPages()): ?>
        <div class="mt-6 flex justify-center">
            <?php echo e($products->links()); ?>

        </div>
    <?php endif; ?>

    <!-- Add Supply Modal -->
    <div id="addSupplyModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="addSupplyModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add Supply</h2>
                <p class="text-sm text-slate-500 mt-1">Add an item that gets consumed by takeout orders.</p>
            </div>
            <form method="POST" action="<?php echo e(route('inventory.products.store')); ?>" class="p-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="consume_on_takeout" value="1">
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
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <label class="block text-sm font-medium text-slate-700 mb-1">Quantity per takeout item</label>
                        <input type="number" min="1" name="consume_per_item" value="1" class="staff-input">
                        <p class="mt-1 text-xs text-slate-500">Example: 1 box for each item ordered, or 2 spoons per takeout order line.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" rows="3" maxlength="5000" class="staff-input"></textarea>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeAddSupplyModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Save Supply</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Supply Modal -->
    <div id="editSupplyModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="editSupplyModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Edit Supply</h2>
                <p class="text-sm text-slate-500 mt-1">Update takeout supply details.</p>
            </div>
            <form method="POST" action="" id="editSupplyForm" class="p-6">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="consume_on_takeout" value="1">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                        <input type="text" name="name" id="editSupplyName" required maxlength="255" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">SKU</label>
                        <input type="text" name="sku" id="editSupplySku" required maxlength="100" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                        <select name="inventory_category_id" id="editSupplyCategory" class="staff-input">
                            <option value="">Select Category</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Purchase Price</label>
                            <input type="number" step="0.01" name="purchase_price" id="editSupplyPurchasePrice" required class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Selling Price</label>
                            <input type="number" step="0.01" name="selling_price" id="editSupplySellingPrice" required class="staff-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Stock Quantity</label>
                            <input type="number" name="stock_quantity" id="editSupplyStock" required class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Min Stock Level</label>
                            <input type="number" name="min_stock_level" id="editSupplyMinStock" required class="staff-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Unit</label>
                        <input type="text" name="unit" id="editSupplyUnit" required maxlength="50" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Quantity per takeout item</label>
                        <input type="number" min="1" name="consume_per_item" id="editSupplyConsumePerItem" value="1" class="staff-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" id="editSupplyDescription" rows="3" maxlength="5000" class="staff-input"></textarea>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeEditSupplyModal()" class="staff-btn-secondary">Cancel</button>
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
            <form method="POST" action="<?php echo e(route('inventory.stock-movements.store')); ?>" class="p-6">
                <?php echo csrf_field(); ?>
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

    <?php if($canEditInventory): ?>
        <?php echo $__env->make('inventory.partials.bulk-purchase-modal', [
            'bulkProducts' => $bulkProducts,
            'bulkPurchaseTitle' => 'Bulk Purchase Supplies',
            'bulkPurchaseDescription' => 'Add multiple takeout supply items in one purchase.',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <script>
        function openAddSupplyModal() {
            const modal = document.getElementById('addSupplyModal');
            const content = document.getElementById('addSupplyModalContent');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeAddSupplyModal() {
            const modal = document.getElementById('addSupplyModal');
            const content = document.getElementById('addSupplyModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function openEditSupplyModal(product) {
            const modal = document.getElementById('editSupplyModal');
            const content = document.getElementById('editSupplyModalContent');
            const form = document.getElementById('editSupplyForm');

            form.action = '<?php echo e(route('inventory.products.update', ':productId')); ?>'.replace(':productId', product.id);
            document.getElementById('editSupplyName').value = product.name || '';
            document.getElementById('editSupplySku').value = product.sku || '';
            document.getElementById('editSupplyCategory').value = product.inventory_category_id || '';
            document.getElementById('editSupplyPurchasePrice').value = product.purchase_price || '';
            document.getElementById('editSupplySellingPrice').value = product.selling_price || '';
            document.getElementById('editSupplyStock').value = product.stock_quantity || 0;
            document.getElementById('editSupplyMinStock').value = product.min_stock_level || 0;
            document.getElementById('editSupplyUnit').value = product.unit || 'pcs';
            document.getElementById('editSupplyConsumePerItem').value = product.consume_per_item || 1;
            document.getElementById('editSupplyDescription').value = product.description || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeEditSupplyModal() {
            const modal = document.getElementById('editSupplyModal');
            const content = document.getElementById('editSupplyModalContent');
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

        document.getElementById('addSupplyModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddSupplyModal();
            }
        });

        document.getElementById('editSupplyModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditSupplyModal();
            }
        });

        document.getElementById('stockMovementModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeStockMovementModal();
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.staff', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/inventory/supplies.blade.php ENDPATH**/ ?>