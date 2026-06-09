@extends('layouts.staff')

@section('title', 'Add Schedule')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Add Schedule</h1>
            <p class="staff-page-subtitle">Set work days, days off, or holidays for staff.</p>
        </div>
        <a href="{{ route('staff-schedules.index') }}" class="staff-btn-secondary">Back to Schedules</a>
    </div>

    <x-flash />

    <div class="staff-card p-6 max-w-2xl">
        <form method="POST" action="{{ route('staff-schedules.store') }}">
            @csrf
            
            <div class="mb-6 p-4 bg-slate-50 rounded-lg">
                <p class="text-sm font-semibold text-slate-700 mb-2">Schedule for:</p>
                <div class="space-y-3">
                    <div>
                        <label for="role_id" class="staff-label">Role (Applies to all employees in this role)</label>
                        <select id="role_id" name="role_id" class="staff-input" onchange="toggleEmployeeSelection()">
                            <option value="">Select role (optional)</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-center text-slate-400 text-sm">— OR —</div>
                    <div>
                        <label for="user_id" class="staff-label">Specific Employee</label>
                        <select id="user_id" name="user_id" class="staff-input">
                            <option value="">Select employee (optional)</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->label() }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-2">Select either a role or a specific employee, but not both.</p>
            </div>

            <div class="mb-6">
                <label for="date" class="staff-label">Date</label>
                <input type="date" id="date" name="date" required class="staff-input">
            </div>

            <div class="mb-6">
                <label for="type" class="staff-label">Type</label>
                <select id="type" name="type" required class="staff-input">
                    <option value="work_day">Work Day</option>
                    <option value="day_off">Day Off</option>
                    <option value="holiday">Holiday</option>
                </select>
            </div>

            <div class="mb-6">
                <label for="expected_start_time" class="staff-label">Expected Start Time</label>
                <input type="time" id="expected_start_time" name="expected_start_time" value="09:00" class="staff-input">
                <p class="text-xs text-slate-500 mt-1">Default: 9:00 AM</p>
            </div>

            <div class="mb-6">
                <label for="expected_end_time" class="staff-label">Expected End Time</label>
                <input type="time" id="expected_end_time" name="expected_end_time" value="17:00" class="staff-input">
                <p class="text-xs text-slate-500 mt-1">Default: 5:00 PM</p>
            </div>

            <div class="mb-6">
                <label for="notes" class="staff-label">Notes (Optional)</label>
                <textarea id="notes" name="notes" rows="3" maxlength="1000" class="staff-input" placeholder="Any additional notes..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('staff-schedules.index') }}" class="staff-btn-secondary">Cancel</a>
                <button type="submit" class="staff-btn-primary">Create Schedule</button>
            </div>
        </form>
    </div>

    <script>
        function toggleEmployeeSelection() {
            const roleSelect = document.getElementById('role_id');
            const userSelect = document.getElementById('user_id');
            
            if (roleSelect.value) {
                userSelect.value = '';
                userSelect.disabled = true;
            } else {
                userSelect.disabled = false;
            }
        }

        document.getElementById('user_id').addEventListener('change', function() {
            const roleSelect = document.getElementById('role_id');
            if (this.value) {
                roleSelect.value = '';
                roleSelect.disabled = true;
            } else {
                roleSelect.disabled = false;
            }
        });
    </script>
@endsection
