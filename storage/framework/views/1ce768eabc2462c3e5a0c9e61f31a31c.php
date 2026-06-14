<?php $__env->startSection('title', 'Payroll'); ?>

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
            <h1 class="staff-page-title">Payroll</h1>
            <p class="staff-page-subtitle">Manage employees, salaries, and attendance.</p>
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

    <div class="staff-tabs mb-6">
        <button onclick="window.location.href='<?php echo e(route('payroll.index')); ?>'" class="staff-tab <?php echo e(request()->routeIs('payroll.index') ? 'staff-tab-active' : ''); ?>">Employees</button>
        <button onclick="window.location.href='<?php echo e(route('payroll.salaries')); ?>'" class="staff-tab <?php echo e(request()->routeIs('payroll.salaries') ? 'staff-tab-active' : ''); ?>">Salaries</button>
        <button onclick="window.location.href='<?php echo e(route('payroll.attendance')); ?>'" class="staff-tab <?php echo e(request()->routeIs('payroll.attendance') ? 'staff-tab-active' : ''); ?>">Attendance</button>
    </div>

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Base Salary</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-slate-600"><?php echo e($employee->employee_id); ?></td>
                            <td class="font-semibold text-slate-900"><?php echo e($employee->full_name); ?></td>
                            <td class="text-slate-600"><?php echo e($employee->email); ?></td>
                            <td class="text-slate-900"><?php echo e($employee->position); ?></td>
                            <td class="text-slate-900">$<?php echo e(number_format($employee->user->dbRole->base_salary ?? $employee->base_salary, 2)); ?></td>
                            <td>
                                <?php if($employee->status === 'active'): ?>
                                    <span class="staff-badge-green">Active</span>
                                <?php else: ?>
                                    <span class="staff-badge-muted">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right space-x-4">
                                <?php if($can('payroll', 'edit')): ?>
                                    <button onclick="openAttendanceModal(<?php echo e($employee->toJson()); ?>)" class="staff-link">Attendance</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-500">No employees yet. Add your first employee!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($employees->hasPages()): ?>
        <div class="mt-6 flex justify-center">
            <?php echo e($employees->links()); ?>

        </div>
    <?php endif; ?>

    <!-- Attendance Modal -->
    <div id="attendanceModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="attendanceModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Record Attendance</h2>
                <p class="text-sm text-slate-500 mt-1">Record check-in/check-out for employee.</p>
            </div>
            <form method="POST" action="<?php echo e(route('payroll.attendance.store')); ?>" class="p-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="employee_id" id="attendanceEmployeeId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Employee</label>
                        <input type="text" id="attendanceEmployeeName" readonly class="staff-input bg-slate-50">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Date</label>
                            <input type="date" name="date" required class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <select name="status" required class="staff-input">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="half_day">Half Day</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Check In</label>
                            <input type="time" name="check_in" class="staff-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Check Out</label>
                            <input type="time" name="check_out" class="staff-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="staff-input"></textarea>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="closeAttendanceModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Record Attendance</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAttendanceModal(employee) {
            const modal = document.getElementById('attendanceModal');
            const content = document.getElementById('attendanceModalContent');
            document.getElementById('attendanceEmployeeId').value = employee.id;
            document.getElementById('attendanceEmployeeName').value = employee.full_name;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeAttendanceModal() {
            const modal = document.getElementById('attendanceModal');
            const content = document.getElementById('attendanceModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        document.getElementById('addEmployeeModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddEmployeeModal();
        });

        document.getElementById('attendanceModal').addEventListener('click', function(e) {
            if (e.target === this) closeAttendanceModal();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.staff', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/payroll/index.blade.php ENDPATH**/ ?>