@extends('layouts.staff')

@section('title', 'Staff Schedules')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Staff Schedules</h1>
            <p class="staff-page-subtitle">Manage work days, days off, and expected work hours.</p>
        </div>
        <button onclick="openCreateModal()" class="staff-btn-primary">Add Schedule</button>
    </div>

    <x-flash />

    <div class="staff-table-wrap">
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
    </script>
@endsection
