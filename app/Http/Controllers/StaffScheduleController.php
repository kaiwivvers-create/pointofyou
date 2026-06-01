<?php

namespace App\Http\Controllers;

use App\Models\StaffSchedule;
use App\Models\User;
use Illuminate\Http\Request;

class StaffScheduleController extends Controller
{
    public function index()
    {
        $schedules = StaffSchedule::with('user')
            ->orderBy('date')
            ->paginate(20);
        
        $users = User::whereNotNull('employee_id')->get();
        
        return view('staff-schedules.index', compact('schedules', 'users'));
    }

    public function create()
    {
        $users = User::whereNotNull('employee_id')->get();
        return view('staff-schedules.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'type' => 'required|in:work_day,day_off,holiday',
            'expected_start_time' => 'nullable|date_format:H:i',
            'expected_end_time' => 'nullable|date_format:H:i|after:expected_start_time',
            'notes' => 'nullable|string',
        ]);

        StaffSchedule::create($validated);

        return redirect()->route('staff-schedules.index')->with('success', 'Schedule created successfully.');
    }

    public function destroy(StaffSchedule $staffSchedule)
    {
        $staffSchedule->delete();
        return redirect()->back()->with('success', 'Schedule deleted successfully.');
    }
}
