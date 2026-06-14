<?php $__env->startSection('title', 'Super Admin'); ?>

<?php $__env->startSection('content'); ?>
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Super Admin Dashboard</h1>
            <p class="staff-page-subtitle">Full access to staff, menu, tables, and payments.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <div class="staff-stat-card">
            <p class="staff-stat-value"><?php echo e($staffCount); ?></p>
            <p class="staff-stat-label">Staff users</p>
        </div>
        <div class="staff-stat-card">
            <p class="staff-stat-value"><?php echo e($menuCount); ?></p>
            <p class="staff-stat-label">Menu items</p>
        </div>
        <div class="staff-stat-card">
            <p class="staff-stat-value"><?php echo e($tableCount); ?></p>
            <p class="staff-stat-label">Tables</p>
        </div>
        <div class="staff-stat-card">
            <p class="staff-stat-value"><?php echo e($pendingOrders); ?></p>
            <p class="staff-stat-label">Awaiting payment</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Quick Actions Portal -->
        <div class="staff-card p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Quick Actions Portal</h2>
            
            <a href="<?php echo e(route('admin.tables.index')); ?>" class="staff-btn-primary w-full mb-4 block text-center">View Table QR Codes</a>
            
            <div class="border-t border-slate-200 pt-4 mt-4">
                <h3 class="text-sm font-medium text-slate-700 mb-2">Create New User Account:</h3>
                <button onclick="openCreateModal()" class="staff-btn-secondary w-full">Create User</button>
            </div>
        </div>

        <!-- System Utilities -->
        <div class="staff-card p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">System Utilities</h2>
            
            <a href="<?php echo e(route('super-admin.database.index')); ?>" class="staff-btn-secondary w-full mb-4 block text-center">
                Download Backup
            </a>
            
            <div class="border-t border-slate-200 pt-4 mt-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    <span class="text-sm font-medium text-slate-700">App Broken: Hopefully Not</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div id="createModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div id="createModalContent" class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-hidden transform transition-all duration-200 scale-95 opacity-0">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add staff user</h2>
                <p class="text-sm text-slate-500 mt-1">Create a new staff account.</p>
            </div>
            <form method="POST" action="<?php echo e(route('super-admin.users.store')); ?>" class="p-6">
                <?php echo csrf_field(); ?>
                <?php echo $__env->make('super-admin.users._form', ['roles' => $roles, 'user' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeCreateModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Create user</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            const modal = document.getElementById('createModal');
            const content = document.getElementById('createModalContent');
            const form = modal.querySelector('form');
            
            if (!modal || !content || !form) {
                console.error('Modal elements not found');
                return;
            }
            
            // Reset form and clear all values
            form.reset();
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const passwordConfirmInput = document.getElementById('password_confirmation');
            const roleInput = document.getElementById('role');
            
            if (nameInput) nameInput.value = '';
            if (emailInput) emailInput.value = '';
            if (passwordInput) passwordInput.value = '';
            if (passwordConfirmInput) passwordConfirmInput.value = '';
            if (roleInput) roleInput.value = '';
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeCreateModal() {
            const modal = document.getElementById('createModal');
            const content = document.getElementById('createModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) closeCreateModal();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.staff', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/super-admin/dashboard.blade.php ENDPATH**/ ?>