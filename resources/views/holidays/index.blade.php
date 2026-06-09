@extends('layouts.staff')

@section('title', 'Holidays & Day Offs')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Holidays & Day Offs</h1>
            <p class="staff-page-subtitle">Manage business holidays and day off schedules.</p>
        </div>
        <a href="{{ route('holidays.create') }}" class="staff-btn-primary">Add Holiday/Day Off</a>
    </div>

    <x-flash />

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Recurring</th>
                        <th>Notes</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($holidays as $holiday)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $holiday->name }}</td>
                            <td class="text-slate-900">{{ $holiday->date->format('M d, Y') }}</td>
                            <td>
                                @if ($holiday->type === 'holiday')
                                    <span class="staff-badge-yellow">Holiday</span>
                                @else
                                    <span class="staff-badge-blue">Day Off</span>
                                @endif
                            </td>
                            <td>
                                @if ($holiday->is_recurring)
                                    <span class="text-emerald-600 font-medium">Yes</span>
                                @else
                                    <span class="text-slate-400">No</span>
                                @endif
                            </td>
                            <td class="text-slate-600 max-w-xs truncate">{{ $holiday->notes ?? '-' }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="staff-link text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center text-slate-500">No holidays or day offs yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection