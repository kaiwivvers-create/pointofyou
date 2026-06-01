@extends('layouts.staff')

@section('title', 'Staff Schedules')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Staff Schedules</h1>
            <p class="staff-page-subtitle">Manage work days, days off, and expected work hours.</p>
        </div>
        <a href="{{ route('staff-schedules.create') }}" class="staff-btn-primary">Add Schedule</a>
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
@endsection
