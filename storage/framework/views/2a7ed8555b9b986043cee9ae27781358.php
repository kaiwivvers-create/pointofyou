<?php $__env->startSection('title', 'Bulk Purchase History'); ?>

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
            <h1 class="staff-page-title">Bulk Purchase History</h1>
            <p class="staff-page-subtitle">Review stock bought in bulk for inventory and takeout supplies.</p>
        </div>
        <div class="flex gap-3">
            <a href="<?php echo e(route('inventory.bulk-purchases.export.csv', request()->all())); ?>" class="staff-btn-secondary">Export CSV</a>
            <?php if($canEditInventory): ?>
                <button onclick="openBulkPurchaseModal()" class="staff-btn-primary">New Bulk Purchase</button>
            <?php endif; ?>
        </div>
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



    <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-3 bg-white p-4 rounded-2xl border border-slate-200">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">From</label>
            <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="staff-input">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">To</label>
            <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="staff-input">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Item Type</label>
            <select name="product_type" class="staff-input">
                <option value="">All</option>
                <option value="inventory" <?php if(request('product_type') === 'inventory'): echo 'selected'; endif; ?>>Inventory</option>
                <option value="supply" <?php if(request('product_type') === 'supply'): echo 'selected'; endif; ?>>Supply</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Sort By</label>
            <select name="sort_by" class="staff-input">
                <option value="date" <?php if(request('sort_by', 'date') === 'date'): echo 'selected'; endif; ?>>Date</option>
                <option value="quantity" <?php if(request('sort_by') === 'quantity'): echo 'selected'; endif; ?>>Quantity</option>
                <option value="amount" <?php if(request('sort_by') === 'amount'): echo 'selected'; endif; ?>>Amount</option>
            </select>
        </div>
        <div class="flex items-end gap-3">
            <select name="sort_direction" class="staff-input flex-1">
                <option value="desc" <?php if(request('sort_direction', 'desc') === 'desc'): echo 'selected'; endif; ?>>High to Low</option>
                <option value="asc" <?php if(request('sort_direction') === 'asc'): echo 'selected'; endif; ?>>Low to High</option>
            </select>
            <button type="submit" class="staff-btn-primary">Filter</button>
        </div>
    </form>

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                        <th>Total</th>
                        <th>Type</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-slate-600"><?php echo e($movement->created_at->format('M d, Y H:i')); ?></td>
                            <td class="font-semibold text-slate-900"><?php echo e($movement->reference ?? 'Bulk Purchase'); ?></td>
                            <td class="text-slate-900"><?php echo e($movement->product?->name ?? '-'); ?></td>
                            <td class="font-semibold text-slate-900"><?php echo e($movement->quantity); ?></td>
                            <td class="text-slate-900">$<?php echo e(number_format($movement->unit_cost ?? 0, 2)); ?></td>
                            <td class="text-slate-900">$<?php echo e(number_format(($movement->unit_cost ?? 0) * $movement->quantity, 2)); ?></td>
                            <td class="text-slate-600"><?php echo e($movement->product?->consume_on_takeout ? 'Supply' : 'Inventory'); ?></td>
                            <td class="text-slate-600"><?php echo e($movement->notes ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-500">No bulk purchases recorded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($movements->hasPages()): ?>
        <div class="mt-6 flex justify-center">
            <?php echo e($movements->links()); ?>

        </div>
    <?php endif; ?>

    <?php if($canEditInventory): ?>
        <?php echo $__env->make('inventory.partials.bulk-purchase-modal', [
            'bulkProducts' => \App\Models\Product::query()->orderBy('name')->get(['id', 'name', 'stock_quantity', 'purchase_price']),
            'bulkPurchaseTitle' => 'Bulk Purchase Inventory',
            'bulkPurchaseDescription' => 'Record a new bulk stock purchase.',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.staff', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/inventory/bulk-purchases.blade.php ENDPATH**/ ?>