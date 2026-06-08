@extends('layouts.staff')

@section('title', 'Staff Schedules')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Staff Schedules & Attendance</h1>
            <p class="staff-page-subtitle">Manage work days, days off, and view check-in/check-out records.</p>
        </div>
        <button onclick="openCreateModal()" class="staff-btn-primary">Add Schedule</button>
    </div>

    <x-flash />

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
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Expected Start</th>
                        <th>Expected End</th>
                        <th>Notes</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $schedule->user ? $schedule->user->name : '-' }}</td>
                            <td class="text-slate-900">{{ $schedule->date->format('M d, Y') }}</td>
                            <td>
                                @if ($schedule->type === 'work_day')
                                    <span class="staff-badge-green">Work Day</span>
                                @elseif ($schedule->type === 'day_off')
                                    <span class="staff-badge-blue">Day Off</span>
                                @else
                                    <span class="staff-badge-yellow">Holiday</span>
                                @endif
                            </td>
                            <td class="text-slate-900">{{ $schedule->expected_start_time ? $schedule->expected_start_time->format('H:i') : '09:00' }}</td>
                            <td class="text-slate-900">{{ $schedule->expected_end_time ? $schedule->expected_end_time->format('H:i') : '17:00' }}</td>
                            <td class="text-slate-600 max-w-xs truncate">{{ $schedule->notes ?? '-' }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('staff-schedules.destroy', $schedule) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="staff-link text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-500">No schedules yet.</td>
                        </tr>
                    @endforelse
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
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
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
                    @php
                        $attendanceRecords = \App\Models\Attendance::with('employee.user')
                            ->orderBy('date', 'desc')
                            ->paginate(20);
                    @endphp
                    @forelse ($attendanceRecords as $attendance)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $attendance->employee->user->name ?? '-' }}</td>
                            <td class="text-slate-900">{{ $attendance->date->format('M d, Y') }}</td>
                            <td class="text-slate-900">{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}</td>
                            <td class="text-slate-900">{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}</td>
                            <td>
                                @if ($attendance->status === 'present')
                                    <span class="staff-badge-green">Present</span>
                                @elseif ($attendance->status === 'late')
                                    <span class="staff-badge-yellow">Late</span>
                                @else
                                    <span class="staff-badge-red">Absent</span>
                                @endif
                            </td>
                            <td class="text-slate-900">{{ $attendance->hours_worked ? number_format($attendance->hours_worked, 2) . 'h' : '-' }}</td>
                            <td class="text-slate-900">{{ $attendance->overtime_hours ? number_format($attendance->overtime_hours, 2) . 'h' : '-' }}</td>
                            <td>
                                @if ($attendance->face_verified)
                                    <span class="text-emerald-600 font-medium">✓ Verified</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-500">No attendance records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($attendanceRecords->hasPages())
            <div class="mt-6 flex justify-center">
                {{ $attendanceRecords->links() }}
            </div>
        @endif
    </div>

    @if ($schedules->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $schedules->links() }}
        </div>
    @endif

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 transform transition-all duration-200 scale-95 opacity-0" id="createModalContent">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">Add Schedule</h2>
            </div>
            <form method="POST" action="{{ route('staff-schedules.store') }}" class="p-6">
                @csrf
                <div class="mb-4">
                    <label for="user_id" class="staff-label">Employee</label>
                    <select id="user_id" name="user_id" required class="staff-input">
                        <option value="">Select employee</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->label() }})</option>
                        @endforeach
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
                const response = await fetch('/staff-schedules/attendance/filter', {
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
@endsection
