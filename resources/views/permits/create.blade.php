@extends('layouts.staff')

@section('title', 'Request Permit')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Request Permit</h1>
            <p class="staff-page-subtitle">Submit a leave request, overtime request, or other permit.</p>
        </div>
        <a href="{{ route('permits.index') }}" class="staff-btn-secondary">Back to Permits</a>
    </div>

    <x-flash />

    <div class="staff-card p-6 max-w-2xl">
        <form method="POST" action="{{ route('permits.store') }}">
            @csrf
            <div class="mb-6">
                <label for="type" class="staff-label">Permit Type</label>
                <select id="type" name="type" required class="staff-input">
                    <option value="">Select type</option>
                    <option value="leave">Leave</option>
                    <option value="overtime">Overtime</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="mb-6">
                <label for="start_date" class="staff-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" required class="staff-input">
            </div>

            <div class="mb-6">
                <label for="end_date" class="staff-label">End Date (Optional)</label>
                <input type="date" id="end_date" name="end_date" class="staff-input">
                <p class="text-xs text-slate-500 mt-1">Leave blank for single-day permits</p>
            </div>

            <div class="mb-6">
                <label for="reason" class="staff-label">Reason</label>
                <textarea id="reason" name="reason" rows="4" required maxlength="5000" class="staff-input" placeholder="Explain why you need this permit..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('permits.index') }}" class="staff-btn-secondary">Cancel</a>
                <button type="submit" class="staff-btn-primary">Submit Request</button>
            </div>
        </form>
    </div>
@endsection
