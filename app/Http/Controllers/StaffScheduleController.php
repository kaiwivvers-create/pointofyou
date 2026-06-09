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
        $schedules = StaffSchedule::with(['user', 'role'])
            ->orderBy('date')
            ->paginate(20);
        
        $users = User::whereNotNull('employee_id')->get();
        $roles = Role::all();
        
        return view('staff-schedules.index', compact('schedules', 'users', 'roles'));
    }

    public function create()
    {
        $users = User::whereNotNull('employee_id')->get();
        $roles = Role::all();
        return view('staff-schedules.create', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'nullable|exists:roles,id',
            'user_id' => 'nullable|exists:users,id',
            'date' => 'required|date',
            'type' => 'required|in:work_day,day_off,holiday',
            'expected_start_time' => 'nullable|date_format:H:i',
            'expected_end_time' => 'nullable|date_format:H:i|after:expected_start_time',
            'notes' => 'nullable|string',
        ]);

        // Require either role_id or user_id
        if (!$request->role_id && !$request->user_id) {
            return redirect()->back()->with('error', 'Please select either a role or an employee.');
        }

        StaffSchedule::create($validated);

        return redirect()->route('staff-schedules.index')->with('success', 'Schedule created successfully.');
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
                'check_in' => $record->check_in ? $record->check_in->format('H:i') : null,
                'check_out' => $record->check_out ? $record->check_out->format('H:i') : null,
                'status' => $record->status,
                'hours_worked' => $record->hours_worked ? number_format($record->hours_worked, 2) : null,
                'overtime_hours' => $record->overtime_hours ? number_format($record->overtime_hours, 2) : null,
                'face_verified' => $record->face_verified ?? false,
            ];
        });

        return response()->json(['attendance' => $formattedAttendance]);
    }
}
