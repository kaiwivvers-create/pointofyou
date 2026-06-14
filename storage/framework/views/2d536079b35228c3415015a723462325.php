<?php $__env->startSection('title', 'Expenses'); ?>

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
?>

<?php $__env->startSection('content'); ?>
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Expenses</h1>
            <p class="staff-page-subtitle">Automatic expense history from stock purchases.</p>
        </div>
        <a href="<?php echo e(route('expenses.export.csv', request()->all())); ?>" class="staff-btn-secondary">Export CSV</a>
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

    <div class="staff-tabs mb-6">
        <button onclick="window.location.href='<?php echo e(route('expenses.index')); ?>'" class="staff-tab <?php echo e(request()->routeIs('expenses.index') ? 'staff-tab-active' : ''); ?>">Expenses</button>
        <button onclick="window.location.href='<?php echo e(route('expenses.categories')); ?>'" class="staff-tab <?php echo e(request()->routeIs('expenses.categories') ? 'staff-tab-active' : ''); ?>">Categories</button>
    </div>

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
            <select name="item_type" class="staff-input">
                <option value="">All</option>
                <option value="inventory" <?php if(request('item_type') === 'inventory'): echo 'selected'; endif; ?>>Inventory</option>
                <option value="supply" <?php if(request('item_type') === 'supply'): echo 'selected'; endif; ?>>Supply</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Sort By</label>
            <select name="sort_by" class="staff-input">
                <option value="date" <?php if(request('sort_by', 'date') === 'date'): echo 'selected'; endif; ?>>Date</option>
                <option value="amount" <?php if(request('sort_by') === 'amount'): echo 'selected'; endif; ?>>Amount</option>
                <option value="title" <?php if(request('sort_by') === 'title'): echo 'selected'; endif; ?>>Title</option>
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
                        <th>Title</th>
                        <th>Category</th>
                        <th>Item Type</th>
                        <th>Qty</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-semibold text-slate-900"><?php echo e($expense->title); ?></td>
                            <td><?php echo e($expense->category ? $expense->category->name : '-'); ?></td>
                            <td class="text-slate-600"><?php echo e($expense->item_type ? ucfirst($expense->item_type) : '-'); ?></td>
                            <td class="font-semibold text-slate-900"><?php echo e($expense->quantity); ?></td>
                            <td class="font-semibold text-slate-900">$<?php echo e(number_format($expense->amount, 2)); ?></td>
                            <td class="text-slate-600"><?php echo e($expense->expense_date->format('M d, Y')); ?></td>
                            <td class="text-slate-600"><?php echo e($expense->reference ?? '-'); ?></td>
                            <td>
                                <span class="staff-badge-green">Automatic</span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-500">No expenses recorded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($expenses->hasPages()): ?>
        <div class="mt-6 flex justify-center">
            <?php echo e($expenses->links()); ?>

        </div>
    <?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.staff', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/expenses/index.blade.php ENDPATH**/ ?>