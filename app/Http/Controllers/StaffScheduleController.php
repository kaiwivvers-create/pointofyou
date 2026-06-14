<?php

namespace App\Http\Controllers;

use App\Models\StaffSchedule;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class StaffScheduleController extends Controller
{
    public function index()
    {
        $schedules = StaffSchedule::with(['role'])
            ->orderBy('role_id')
            ->orderBy('day_of_week')
            ->paginate(50);
        
        $roles = Role::all();
        $users = User::whereNotNull('employee_id')->get();
        
        return view('staff-schedules.index', compact('schedules', 'roles', 'users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('staff-schedules.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'day_of_week' => 'required|integer|min:0|max:6',
            'is_day_off' => 'boolean',
            'expected_start_time' => 'nullable|date_format:H:i',
            'expected_end_time' => 'nullable|date_format:H:i|after:expected_start_time',
            'notes' => 'nullable|string',
        ]);

        $validated['is_day_off'] = $request->has('is_day_off');

        // Check if schedule already exists for this role and day
        $existing = StaffSchedule::where('role_id', $validated['role_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->first();

        if ($existing) {
            $existing->update($validated);
        } else {
            StaffSchedule::create($validated);
        }

        return redirect()->route('staff-schedules.index')->with('success', 'Schedule saved successfully.');
    }

    public function destroy(StaffSchedule $staffSchedule)
    {
        $staffSchedule->delete();
        return redirect()->back()->with('success', 'Schedule deleted successfully.');
    }

    public function filterAttendance(Request $request)
    {
        $query = \App\Models\Attendance::with('employee.user');

        if ($request->employee_id) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('user_id', $request->employee_id);
            });
        }

        if ($request->date_from) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $attendance = $query->orderBy('date', 'desc')->get();

        $formattedAttendance = $attendance->map(function($record) {
            return [
                'employee_name' => $record->employee->user->name ?? '-',
                'date' => $record->date->format('M d, Y'),
                'check_in' => $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : null,
                'check_out' => $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : null,
                'status' => $record->status,
                'hours_worked' => $record->hours_worked ? number_format($record->hours_worked, 2) : null,
                'overtime_hours' => $record->overtime_hours ? number_format($record->overtime_hours, 2) : null,
                'face_verified' => $record->face_verified ?? false,
            ];
        });

        return response()->json(['attendance' => $formattedAttendance]);
    }
}
