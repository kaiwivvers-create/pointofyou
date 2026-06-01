<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $user = Auth::user();
        if (!$user->employee_id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have an employee record.'], 400);
            }
            return redirect()->back()->with('error', 'You do not have an employee record.');
        }

        $today = today();
        $existingAttendance = Attendance::where('employee_id', $user->employee_id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You have already checked in today.'], 400);
            }
            return redirect()->back()->with('error', 'You have already checked in today.');
        }

        $now = now();
        
        // Get expected start time from staff schedule or default to 9 AM
        $schedule = \App\Models\StaffSchedule::where('user_id', $user->id)
            ->where('date', $today)
            ->where('type', 'work_day')
            ->first();
        
        $startTime = $schedule 
            ? now()->setTimeFromTimeString($schedule->expected_start_time->format('H:i:s'))
            : now()->setHour(9)->setMinute(0)->setSecond(0);
        
        $isLate = $now->gt($startTime);
        $lateMinutes = $isLate ? $now->diffInMinutes($startTime) : 0;

        if ($existingAttendance) {
            $existingAttendance->update([
                'check_in' => $now->format('H:i:s'),
                'status' => $isLate ? 'late' : 'present',
                'hours_worked' => 0,
            ]);
        } else {
            Attendance::create([
                'employee_id' => $user->employee_id,
                'date' => $today,
                'check_in' => $now->format('H:i:s'),
                'status' => $isLate ? 'late' : 'present',
                'hours_worked' => 0,
            ]);
        }

        $message = $isLate 
            ? "Checked in successfully. You are {$lateMinutes} minutes late." 
            : "Checked in successfully. On time!";
        
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'success' => true]);
        }
        return redirect()->back()->with('success', $message);
    }

    public function checkOut(Request $request)
    {
        $user = Auth::user();
        if (!$user->employee_id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have an employee record.'], 400);
            }
            return redirect()->back()->with('error', 'You do not have an employee record.');
        }

        $today = today();
        $attendance = Attendance::where('employee_id', $user->employee_id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You need to check in first.'], 400);
            }
            return redirect()->back()->with('error', 'You need to check in first.');
        }

        if ($attendance->check_out) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You have already checked out today.'], 400);
            }
            return redirect()->back()->with('error', 'You have already checked out today.');
        }

        $now = now();
        $checkInTime = $attendance->check_in;
        
        // Calculate hours worked using time values
        // check_in is stored as time (H:i:s), so we need to combine with today's date
        // But if it's already a Carbon object, use it directly
        if ($checkInTime instanceof \Carbon\Carbon) {
            $checkInDateTime = $checkInTime;
        } else {
            $checkInDateTime = \Carbon\Carbon::parse($today->format('Y-m-d') . ' ' . $checkInTime);
        }
        $hoursWorked = $checkInDateTime->diffInMinutes($now) / 60;
        
        // Calculate overtime (more than 8 hours)
        $overtimeHours = $hoursWorked > 8 ? $hoursWorked - 8 : 0;

        $attendance->update([
            'check_out' => $now->format('H:i:s'),
            'hours_worked' => $hoursWorked,
            'overtime_hours' => $overtimeHours,
        ]);

        $message = "Checked out successfully. Worked {$hoursWorked} hours today.";
        
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'success' => true]);
        }
        return redirect()->back()->with('success', $message);
    }

    public function getCurrentStatus()
    {
        $user = Auth::user();
        if (!$user->employee_id) {
            return response()->json(['status' => 'no_employee']);
        }

        $today = today();
        $attendance = Attendance::where('employee_id', $user->employee_id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json(['status' => 'not_checked_in']);
        }

        if (!$attendance->check_in) {
            return response()->json(['status' => 'not_checked_in']);
        }

        if (!$attendance->check_out) {
            // Convert time to Jakarta time for display
            try {
                // check_in is stored as time (H:i:s), combine with date
                if ($attendance->check_in instanceof \Carbon\Carbon) {
                    $checkInTime = $attendance->check_in->setTimezone('Asia/Jakarta')->format('H:i');
                } else {
                    $checkInDateTime = \Carbon\Carbon::parse($attendance->date . ' ' . $attendance->check_in)
                        ->setTimezone('Asia/Jakarta');
                    $checkInTime = $checkInDateTime->format('H:i');
                }
            } catch (\Exception $e) {
                $checkInTime = $attendance->check_in;
            }
            
            return response()->json([
                'status' => 'checked_in',
                'check_in_time' => $checkInTime,
            ]);
        }

        // Convert times to Jakarta time for display
        try {
            // check_in and check_out are stored as time (H:i:s), combine with date
            if ($attendance->check_in instanceof \Carbon\Carbon) {
                $checkInTime = $attendance->check_in->setTimezone('Asia/Jakarta')->format('H:i');
            } else {
                $checkInDateTime = \Carbon\Carbon::parse($attendance->date . ' ' . $attendance->check_in)
                    ->setTimezone('Asia/Jakarta');
                $checkInTime = $checkInDateTime->format('H:i');
            }
            
            if ($attendance->check_out instanceof \Carbon\Carbon) {
                $checkOutTime = $attendance->check_out->setTimezone('Asia/Jakarta')->format('H:i');
            } else {
                $checkOutDateTime = \Carbon\Carbon::parse($attendance->date . ' ' . $attendance->check_out)
                    ->setTimezone('Asia/Jakarta');
                $checkOutTime = $checkOutDateTime->format('H:i');
            }
        } catch (\Exception $e) {
            $checkInTime = $attendance->check_in;
            $checkOutTime = $attendance->check_out;
        }

        return response()->json([
            'status' => 'checked_out',
            'check_in_time' => $checkInTime,
            'check_out_time' => $checkOutTime,
            'hours_worked' => $attendance->hours_worked,
        ]);
    }
}
