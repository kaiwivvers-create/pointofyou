@extends('layouts.staff')

@section('title', 'Add Holiday/Day Off')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Add Holiday/Day Off</h1>
            <p class="staff-page-subtitle">Schedule a holiday or day off for the business.</p>
        </div>
        <a href="{{ route('holidays.index') }}" class="staff-btn-secondary">Back to Holidays</a>
    </div>

    <x-flash />

    <div class="staff-card p-6 max-w-2xl">
        <form method="POST" action="{{ route('holidays.store') }}">
            @csrf
            <div class="mb-6">
                <label for="name" class="staff-label">Name</label>
                <input type="text" id="name" name="name" required placeholder="e.g., Christmas, New Year, Staff Day Off" class="staff-input">
            </div>

            <div class="mb-6">
                <label for="date" class="staff-label">Date</label>
                <input type="date" id="date" name="date" required class="staff-input">
            </div>

            <div class="mb-6">
                <label for="type" class="staff-label">Type</label>
                <select id="type" name="type" required class="staff-input">
                    <option value="holiday">Holiday (Business-wide)</option>
                    <option value="day_off">Day Off (Specific)</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="is_recurring" name="is_recurring" value="1" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                    <span class="text-sm font-medium text-slate-700">Recurring (repeats every year)</span>
                </label>
            </div>

            <div class="mb-6">
                <label for="notes" class="staff-label">Notes (Optional)</label>
                <textarea id="notes" name="notes" rows="3" maxlength="1000" class="staff-input" placeholder="Any additional notes..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('holidays.index') }}" class="staff-btn-secondary">Cancel</a>
                <button type="submit" class="staff-btn-primary">Create</button>
            </div>
        </form>
    </div>
@endsection