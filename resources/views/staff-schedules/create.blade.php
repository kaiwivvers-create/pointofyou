@extends('layouts.staff')

@section('title', 'Add Schedule')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Add/Update Schedule</h1>
            <p class="staff-page-subtitle">Set work days and recurring shifts for roles.</p>
        </div>
        <a href="{{ route('staff-schedules.index') }}" class="staff-btn-secondary">Back to Schedules</a>
    </div>

    <x-flash />

    <div class="staff-card p-6 max-w-2xl">
        <form method="POST" action="{{ route('staff-schedules.store') }}">
            @csrf
            
            <div class="mb-6">
                <label for="role_id" class="staff-label">Role</label>
                <select id="role_id" name="role_id" class="staff-input" required>
                    <option value="">Select role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->label() }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1">This schedule applies to all employees with this role.</p>
            </div>

            <div class="mb-6">
                <label for="day_of_week" class="staff-label">Day of Week</label>
                <select id="day_of_week" name="day_of_week" class="staff-input" required>
                    <option value="1">Monday</option>
                    <option value="2">Tuesday</option>
                    <option value="3">Wednesday</option>
                    <option value="4">Thursday</option>
                    <option value="5">Friday</option>
                    <option value="6">Saturday</option>
                    <option value="0">Sunday</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="is_day_off" name="is_day_off" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-600" onchange="toggleTimeInputs()">
                    <span class="text-sm font-medium text-slate-700">This is a regular Day Off</span>
                </label>
            </div>

            <div id="time-inputs" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="expected_start_time" class="staff-label">Start Time</label>
                    <input type="time" id="expected_start_time" name="expected_start_time" class="staff-input">
                </div>
                <div>
                    <label for="expected_end_time" class="staff-label">End Time</label>
                    <input type="time" id="expected_end_time" name="expected_end_time" class="staff-input">
                </div>
            </div>

            <div class="mb-6">
                <label for="notes" class="staff-label">Notes (Optional)</label>
                <textarea id="notes" name="notes" rows="3" class="staff-input"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('staff-schedules.index') }}" class="staff-btn-secondary">Cancel</a>
                <button type="submit" class="staff-btn-primary">Save Schedule</button>
            </div>
        </form>
    </div>

    <script>
        function toggleTimeInputs() {
            const isDayOff = document.getElementById('is_day_off').checked;
            const timeInputs = document.getElementById('time-inputs');
            const startInput = document.getElementById('expected_start_time');
            const endInput = document.getElementById('expected_end_time');

            if (isDayOff) {
                timeInputs.style.opacity = '0.5';
                startInput.disabled = true;
                endInput.disabled = true;
                startInput.value = '';
                endInput.value = '';
                startInput.removeAttribute('required');
                endInput.removeAttribute('required');
            } else {
                timeInputs.style.opacity = '1';
                startInput.disabled = false;
                endInput.disabled = false;
                startInput.setAttribute('required', 'required');
                endInput.setAttribute('required', 'required');
            }
        }

        // Initialize state
        toggleTimeInputs();
    </script>
@endsection
