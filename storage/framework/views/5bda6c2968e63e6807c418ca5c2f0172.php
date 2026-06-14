<?php $__env->startSection('title', 'Stock Categories'); ?>

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
            <h1 class="staff-page-title">Stock Categories</h1>
            <p class="staff-page-subtitle">Categories used by inventory and supply products.</p>
        </div>
        <?php if($canEditInventory): ?>
            <button onclick="openAddCategoryModal()" class="staff-btn-primary">Add Category</button>
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
                        <th>Description</th>
                        <th>Type</th>
                        <th>Products</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-semibold text-slate-900"><?php echo e($category->name); ?></td>
                            <td class="text-slate-600"><?php echo e($category->description ?? '-'); ?></td>
                            <td>
                                <?php if($category->type === 'ingredient'): ?>
                                    <span class="staff-badge-green">Ingredient</span>
                                <?php else: ?>
                                    <span class="staff-badge-yellow">Supply</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-slate-600"><?php echo e($category->products_count); ?></td>
                            <td class="text-right space-x-4">
                                <?php if($canEditInventory): ?>
                                    <button onclick="openEditCategoryModal(<?php echo e($category->toJson()); ?>)" class="staff-link">Edit</button>
                                    <form method="POST" action="<?php echo e(route('inventory.categories.destroy', $category)); ?>" class="inline" onsubmit="return confirm('Delete this stock category?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="staff-link-danger">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="py-16 text-center text-slate-500">No stock categories yet. Add your first category!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($canEditInventory): ?>
        <!-- Add Category Modal -->
        <div id="addCategoryModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="addCategoryModalContent">
                <div class="p-6 border-b border-slate-200">
                    <h2 class="text-xl font-semibold text-slate-900">Add Stock Category</h2>
                    <p class="text-sm text-slate-500 mt-1">Create a new stock category.</p>
                </div>
                <form method="POST" action="<?php echo e(route('inventory.categories.store')); ?>" class="p-6">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                            <input type="text" name="name" required maxlength="255" class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                            <textarea name="description" rows="3" maxlength="5000" class="staff-input"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                            <select name="type" required class="staff-input">
                                <option value="ingredient">Ingredient (for products like dairy, flour, etc.)</option>
                                <option value="supply">Supply (for takeout items like boxes, bags, etc.)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-8 flex flex-wrap gap-3 justify-end">
                        <button type="button" onclick="closeAddCategoryModal()" class="staff-btn-secondary">Cancel</button>
                        <button type="submit" class="staff-btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div id="editCategoryModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="editCategoryModalContent">
                <div class="p-6 border-b border-slate-200">
                    <h2 class="text-xl font-semibold text-slate-900">Edit Stock Category</h2>
                    <p class="text-sm text-slate-500 mt-1">Update stock category details.</p>
                </div>
                <form method="POST" action="<?php echo e(route('inventory.categories.update', ':categoryId')); ?>" id="editCategoryForm" class="p-6">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                            <input type="text" name="name" id="editCategoryName" required maxlength="255" class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                            <textarea name="description" id="editCategoryDescription" rows="3" maxlength="5000" class="staff-input"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                            <select name="type" id="editCategoryType" required class="staff-input">
                                <option value="ingredient">Ingredient (for products like dairy, flour, etc.)</option>
                                <option value="supply">Supply (for takeout items like boxes, bags, etc.)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-8 flex flex-wrap gap-3 justify-end">
                        <button type="button" onclick="closeEditCategoryModal()" class="staff-btn-secondary">Cancel</button>
                        <button type="submit" class="staff-btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openAddCategoryModal() {
                const modal = document.getElementById('addCategoryModal');
                const content = document.getElementById('addCategoryModalContent');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }, 10);
            }

            function closeAddCategoryModal() {
                const modal = document.getElementById('addCategoryModal');
                const content = document.getElementById('addCategoryModalContent');
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }, 200);
            }

            function openEditCategoryModal(category) {
                const modal = document.getElementById('editCategoryModal');
                const content = document.getElementById('editCategoryModalContent');
                const form = document.getElementById('editCategoryForm');

                form.action = '<?php echo e(route('inventory.categories.update', ':categoryId')); ?>'.replace(':categoryId', category.id);
                document.getElementById('editCategoryName').value = category.name || '';
                document.getElementById('editCategoryDescription').value = category.description || '';
                document.getElementById('editCategoryType').value = category.type || 'ingredient';


                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }, 10);
            }

            function closeEditCategoryModal() {
                const modal = document.getElementById('editCategoryModal');
                const content = document.getElementById('editCategoryModalContent');
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }, 200);
            }

            document.getElementById('addCategoryModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeAddCategoryModal();
            });

            document.getElementById('editCategoryModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeEditCategoryModal();
            });
        </script>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.staff', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/inventory/stock-categories.blade.php ENDPATH**/ ?>