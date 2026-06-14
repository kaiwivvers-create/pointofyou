<?php $__env->startSection('title', 'Staff Schedules'); ?>

<?php $__env->startSection('content'); ?>
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Staff Schedules & Attendance</h1>
            <p class="staff-page-subtitle">Manage work days, days off, and view check-in/check-out records.</p>
        </div>
        <button onclick="openCreateModal()" class="staff-btn-primary">Add Schedule</button>
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

    <!-- Tabs -->
    <div class="flex gap-2 mb-6">
        <button onclick="switchTab('schedules')" id="tab-schedules" class="px-4 py-2 rounded-lg font-medium bg-slate-900 text-white">Schedules</button>
        <button onclick="switchTab('attendance')" id="tab-attendance" class="px-4 py-2 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">Attendance Records</button>
    </div>

    <!-- Schedules Tab -->
    <div id="schedules-tab" class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Scheduled For</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Expected Start</th>
                        <th>Expected End</th>
                        <th>Notes</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-semibold text-slate-900">
                                <?php if($schedule->role): ?>
                                    <span class="text-emerald-600"><?php echo e($schedule->role->label()); ?></span>
                                    <span class="text-xs text-slate-400">(All <?php echo e($schedule->role->label()); ?>)</span>
                                <?php elseif($schedule->user): ?>
                                    <?php echo e($schedule->user->name); ?>

                                    <span class="text-xs text-slate-400">(<?php echo e($schedule->user->role->label()); ?>)</span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="text-slate-900"><?php echo e($schedule->date->format('M d, Y')); ?></td>
                            <td>
                                <?php if($schedule->type === 'work_day'): ?>
                                    <span class="staff-badge-green">Work Day</span>
                                <?php elseif($schedule->type === 'day_off'): ?>
                                    <span class="staff-badge-blue">Day Off</span>
                                <?php else: ?>
                                    <span class="staff-badge-yellow">Holiday</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-slate-900"><?php echo e($schedule->expected_start_time ? $schedule->expected_start_time->format('H:i') : '09:00'); ?></td>
                            <td class="text-slate-900"><?php echo e($schedule->expected_end_time ? $schedule->expected_end_time->format('H:i') : '17:00'); ?></td>
                            <td class="text-slate-600 max-w-xs truncate"><?php echo e($schedule->notes ?? '-'); ?></td>
                            <td class="text-right">
                                <form method="POST" action="<?php echo e(route('staff-schedules.destroy', $schedule)); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="staff-link text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-500">No schedules yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Attendance Tab -->
    <div id="attendance-tab" class="staff-table-wrap hidden">
        <!-- Filters -->
        <div class="flex flex-wrap gap-4 mb-4 p-4 bg-slate-50 rounded-lg">
            <div class="flex-1 min-w-[200px]">
                <label class="staff-label text-sm">Employee</label>
                <select id="filter-employee" onchange="filterAttendance()" class="staff-input text-sm">
                    <option value="">All Employees</option>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="staff-label text-sm">Date From</label>
                <input type="date" id="filter-date-from" onchange="filterAttendance()" class="staff-input text-sm">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="staff-label text-sm">Date To</label>
                <input type="date" id="filter-date-to" onchange="filterAttendance()" class="staff-input text-sm">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="staff-label text-sm">Status</label>
                <select id="filter-status" onchange="filterAttendance()" class="staff-input text-sm">
                    <option value="">All Status</option>
                    <option value="present">Present</option>
                    <option value="late">Late</option>
                    <option value="absent">Absent</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Status</th>
                        <th>Hours Worked</th>
                        <th>Overtime</th>
                        <th>Face Verified</th>
                    </tr>
                </thead>
                <tbody id="attendance-tbody">
                    <?php
                        $attendanceRecords = \App\Models\Attendance::with('employee.user')
                            ->orderBy('date', 'desc')
                            ->paginate(20);
                    ?>
                    <?php $__empty_1 = true; $__currentLoopData = $attendanceRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-semibold text-slate-900"><?php echo e($attendance->employee->user->name ?? '-'); ?></td>
                            <td class="text-slate-900"><?php echo e($attendance->date->format('M d, Y')); ?></td>
                            <td class="text-slate-900"><?php echo e($attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-'); ?></td>
                            <td class="text-slate-900"><?php echo e($attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-'); ?></td>
                            <td>
                                <?php if($attendance->status === 'present'): ?>
                                    <span class="staff-badge-green">Present</span>
                                <?php elseif($attendance->status === 'late'): ?>
                                    <span class="staff-badge-yellow">Late</span>
                                <?php else: ?>
                                    <span class="staff-badge-red">Absent</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-slate-900"><?php echo e($attendance->hours_worked ? number_format($attendance->hours_worked, 2) . 'h' : '-'); ?></td>
                            <td class="text-slate-900"><?php echo e($attendance->overtime_hours ? number_format($attendance->overtime_hours, 2) . 'h' : '-'); ?></td>
                            <td>
                                <?php if($attendance->face_verified): ?>
                                    <span class="text-emerald-600 font-medium">✓ Verified</span>
                                <?php else: ?>
                                    <span class="text-slate-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-500">No attendance records yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($attendanceRecords->hasPages()): ?>
            <div class="mt-6 flex justify-center">
                <?php echo e($attendanceRecords->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    <?php if($schedules->hasPages()): ?>
        <div class="mt-6 flex justify-center">
            <?php echo e($schedules->links()); ?>

        </div>
    <?php endif; ?>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 transform transition-all duration-200 scale-95 opacity-0" id="createModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add Schedule</h2>
            </div>
            <form method="POST" action="<?php echo e(route('staff-schedules.store')); ?>" class="p-6">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <label for="user_id" class="staff-label">Employee</label>
                    <select id="user_id" name="user_id" required class="staff-input">
                        <option value="">Select employee</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> (<?php echo e($user->role->label()); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="date" class="staff-label">Date</label>
                    <input type="date" id="date" name="date" required class="staff-input">
                </div>

                <div class="mb-4">
                    <label for="type" class="staff-label">Type</label>
                    <select id="type" name="type" required class="staff-input">
                        <option value="work_day">Work Day</option>
                        <option value="day_off">Day Off</option>
                        <option value="holiday">Holiday</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="expected_start_time" class="staff-label">Expected Start Time</label>
                    <input type="time" id="expected_start_time" name="expected_start_time" value="09:00" class="staff-input">
                    <p class="text-xs text-slate-500 mt-1">Default: 9:00 AM</p>
                </div>

                <div class="mb-4">
                    <label for="expected_end_time" class="staff-label">Expected End Time</label>
                    <input type="time" id="expected_end_time" name="expected_end_time" value="17:00" class="staff-input">
                    <p class="text-xs text-slate-500 mt-1">Default: 5:00 PM</p>
                </div>

                <div class="mb-4">
                    <label for="notes" class="staff-label">Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="3" maxlength="1000" class="staff-input" placeholder="Any additional notes..."></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeCreateModal()" class="staff-btn-secondary">Cancel</button>
                    <button type="submit" class="staff-btn-primary">Create Schedule</button>
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

        function switchTab(tab) {
            const schedulesTab = document.getElementById('schedules-tab');
            const attendanceTab = document.getElementById('attendance-tab');
            const tabSchedules = document.getElementById('tab-schedules');
            const tabAttendance = document.getElementById('tab-attendance');

            if (tab === 'schedules') {
                schedulesTab.classList.remove('hidden');
                attendanceTab.classList.add('hidden');
                tabSchedules.classList.add('bg-slate-900', 'text-white');
                tabSchedules.classList.remove('bg-slate-100', 'text-slate-700');
                tabAttendance.classList.remove('bg-slate-900', 'text-white');
                tabAttendance.classList.add('bg-slate-100', 'text-slate-700');
            } else {
                schedulesTab.classList.add('hidden');
                attendanceTab.classList.remove('hidden');
                tabAttendance.classList.add('bg-slate-900', 'text-white');
                tabAttendance.classList.remove('bg-slate-100', 'text-slate-700');
                tabSchedules.classList.remove('bg-slate-900', 'text-white');
                tabSchedules.classList.add('bg-slate-100', 'text-slate-700');
            }
        }

        async function filterAttendance() {
            const employeeId = document.getElementById('filter-employee').value;
            const dateFrom = document.getElementById('filter-date-from').value;
            const dateTo = document.getElementById('filter-date-to').value;
            const status = document.getElementById('filter-status').value;

            const tbody = document.getElementById('attendance-tbody');
            tbody.innerHTML = '<tr><td colspan="8" class="py-8 text-center text-slate-500">Loading...</td></tr>';

            try {
                const response = await fetch('<?php echo e(url('/staff-schedules/attendance/filter')); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        employee_id: employeeId,
                        date_from: dateFrom,
                        date_to: dateTo,
                        status: status,
                    }),
                });

                const data = await response.json();

                if (data.attendance && data.attendance.length > 0) {
                    tbody.innerHTML = data.attendance.map(record => `
                        <tr>
                            <td class="font-semibold text-slate-900">${record.employee_name || '-'}</td>
                            <td class="text-slate-900">${record.date}</td>
                            <td class="text-slate-900">${record.check_in || '-'}</td>
                            <td class="text-slate-900">${record.check_out || '-'}</td>
                            <td>
                                ${record.status === 'present' ? '<span class="staff-badge-green">Present</span>' : 
                                  record.status === 'late' ? '<span class="staff-badge-yellow">Late</span>' : 
                                  '<span class="staff-badge-red">Absent</span>'}
                            </td>
                            <td class="text-slate-900">${record.hours_worked ? record.hours_worked + 'h' : '-'}</td>
                            <td class="text-slate-900">${record.overtime_hours ? record.overtime_hours + 'h' : '-'}</td>
                            <td>
                                ${record.face_verified ? '<span class="text-emerald-600 font-medium">✓ Verified</span>' : '<span class="text-slate-400">-</span>'}
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" class="py-16 text-center text-slate-500">No attendance records found.</td></tr>';
                }
            } catch (error) {
                console.error('Error filtering attendance:', error);
                tbody.innerHTML = '<tr><td colspan="8" class="py-16 text-center text-red-500">Error loading attendance records.</td></tr>';
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.staff', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/staff-schedules/index.blade.php ENDPATH**/ ?>