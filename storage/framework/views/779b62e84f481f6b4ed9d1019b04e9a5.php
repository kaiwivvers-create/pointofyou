<?php $__env->startSection('title', 'Permits'); ?>

<?php $__env->startSection('content'); ?>
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Permits</h1>
            <p class="staff-page-subtitle">Manage leave requests, overtime requests, and other permits.</p>
        </div>
        <button onclick="openCreateModal()" class="staff-btn-primary">Request Permit</button>
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
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Approved By</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $permits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-semibold text-slate-900"><?php echo e($permit->user ? $permit->user->name : '-'); ?></td>
                            <td class="text-slate-600"><?php echo e(ucfirst($permit->type)); ?></td>
                            <td class="text-slate-900"><?php echo e($permit->start_date->format('M d, Y')); ?></td>
                            <td class="text-slate-900"><?php echo e($permit->end_date ? $permit->end_date->format('M d, Y') : '-'); ?></td>
                            <td class="text-slate-600 max-w-xs truncate"><?php echo e($permit->reason); ?></td>
                            <td>
                                <?php if($permit->status === 'pending'): ?>
                                    <span class="staff-badge-yellow">Pending</span>
                                <?php elseif($permit->status === 'approved'): ?>
                                    <span class="staff-badge-green">Approved</span>
                                <?php else: ?>
                                    <span class="staff-badge-red">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-slate-600"><?php echo e($permit->approvedBy ? $permit->approvedBy->name : '-'); ?></td>
                            <td class="text-right space-x-2">
                                <?php if($permit->status === 'pending'): ?>
                                    <form method="POST" action="<?php echo e(route('permits.approve', $permit)); ?>" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="staff-link text-emerald-600">Approve</button>
                                    </form>
                                    <button onclick="openRejectModal(<?php echo e($permit->id); ?>)" class="staff-link text-red-600">Reject</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-500">No permit requests yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($permits->hasPages()): ?>
        <div class="mt-6 flex justify-center">
            <?php echo e($permits->links()); ?>

        </div>
    <?php endif; ?>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 transform transition-all duration-200 scale-95 opacity-0" id="createModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Request Permit</h2>
            </div>
            <form method="POST" action="<?php echo e(route('permits.store')); ?>" class="p-6">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <label for="type" class="staff-label">Permit Type</label>
                    <select id="type" name="type" required class="staff-input">
                        <option value="">Select type</option>
                        <option value="leave">Leave</option>
                        <option value="overtime">Overtime</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="start_date" class="staff-label">Start Date</label>
                    <input type="date" id="start_date" name="start_date" required class="staff-input">
                </div>

                <div class="mb-4">
                    <label for="end_date" class="staff-label">End Date (Optional)</label>
                    <input type="date" id="end_date" name="end_date" class="staff-input">
                    <p class="text-xs text-slate-500 mt-1">Leave blank for single-day permits</p>
                </div>

                <div class="mb-4">
                    <label for="reason" class="staff-label">Reason</label>
                    <textarea id="reason" name="reason" rows="4" required maxlength="5000" class="staff-input" placeholder="Explain why you need this permit..."></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeCreateModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-200 scale-95 opacity-0" id="rejectModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Reject Permit</h2>
            </div>
            <form method="POST" action="" id="rejectForm" class="p-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_method" value="PATCH">
                <div class="mb-4">
                    <label for="rejection_reason" class="staff-label">Rejection Reason</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="3" required maxlength="5000" class="staff-input"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeRejectModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary bg-red-600 hover:bg-red-700">Reject</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            const modal = document.getElementById('createModal');
            const content = document.getElementById('createModalContent');
            
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
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 200);
        }

        function openRejectModal(permitId) {
            const modal = document.getElementById('rejectModal');
            const content = document.getElementById('rejectModalContent');
            const form = document.getElementById('rejectForm');
            
            form.action = '<?php echo e(route('permits.reject', ':id')); ?>'.replace(':id', permitId);
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeRejectModal() {
            const modal = document.getElementById('rejectModal');
            const content = document.getElementById('rejectModalContent');
            
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 200);
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.staff', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/permits/index.blade.php ENDPATH**/ ?>