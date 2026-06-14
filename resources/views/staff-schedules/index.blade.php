@extends('layouts.staff')

@section('title', 'Staff Schedules')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Staff Schedules & Attendance</h1>
            <p class="staff-page-subtitle">Manage work days, days off, and view check-in/check-out records.</p>
        </div>
        <a href="{{ route('staff-schedules.create') }}" class="staff-btn-primary">Add Schedule</a>
    </div>

    <x-flash />

    <!-- Tabs -->
    <div class="flex gap-2 mb-6">
        <button onclick="switchTab('schedules')" id="tab-schedules" class="px-4 py-2 rounded-lg font-medium bg-slate-900 text-white">Schedules</button>
        <button onclick="switchTab('attendance')" id="tab-attendance" class="px-4 py-2 rounded-lg font-medium bg-slate-100 text-slate-700 hover:bg-slate-200">Attendance Records</button>
    </div>

    <div id="schedules-tab" class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Day of Week</th>
                        <th>Type</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Notes</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    @endphp
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td class="font-semibold text-emerald-600">{{ $schedule->role->label() }}</td>
                            <td class="text-slate-900 font-medium">{{ $days[$schedule->day_of_week] }}</td>
                            <td>
                                @if ($schedule->is_day_off)
                                    <span class="staff-badge-blue">Day Off</span>
                                @else
                                    <span class="staff-badge-green">Work Day</span>
                                @endif
                            </td>
                            <td>{{ $schedule->expected_start_time ? $schedule->expected_start_time->format('H:i') : '-' }}</td>
                            <td>{{ $schedule->expected_end_time ? $schedule->expected_end_time->format('H:i') : '-' }}</td>
                            <td class="text-slate-500 max-w-xs truncate" title="{{ $schedule->notes }}">{{ $schedule->notes ?: '-' }}</td>
                            <td class="text-right">
                                <form action="{{ route('staff-schedules.destroy', $schedule) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this schedule?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-900 p-2 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12">
                                <div class="inline-flex items-center justify-center size-12 rounded-full bg-slate-100 text-slate-400 mb-4">
                                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-900 mb-1">No schedules found</h3>
                                <p class="text-sm text-slate-500">Get started by creating a new staff schedule.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($schedules->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $schedules->links() }}
            </div>
        @endif
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
                            <td class="text-slate-900">{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}</td>
                            <td class="text-slate-900">{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}</td>
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
                const response = await fetch('{{ url('/staff-schedules/attendance/filter') }}', {
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
