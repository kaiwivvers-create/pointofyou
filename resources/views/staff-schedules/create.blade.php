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
            <div class="mb-6">
                <label for="user_id" class="staff-label">Employee</label>
                <select id="user_id" name="user_id" required class="staff-input">
                    <option value="">Select employee</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->label() }})</option>
                    @endforeach
                </select>
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
                <textarea id="notes" name="notes" rows="3" class="staff-input" placeholder="Any additional notes..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('staff-schedules.index') }}" class="staff-btn-secondary">Cancel</a>
                <button type="submit" class="staff-btn-primary">Create Schedule</button>
            </div>
        </form>
    </div>
@endsection
