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
        \Log::info('Check-in attempt started');
        $user = Auth::user();
        if (!$user->employee_id) {
            \Log::info('No employee_id for user ' . $user->id);
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have an employee record.'], 400);
            }
            return redirect()->back()->with('error', 'You do not have an employee record.');
        }

        $today = today()->format('Y-m-d');
        $existingAttendance = Attendance::where('employee_id', $user->employee_id)
            ->whereDate('date', today())
            ->first();

        \Log::info('Existing attendance check: ' . ($existingAttendance ? 'found' : 'not found') . ' for date ' . $today);

        if ($existingAttendance && $existingAttendance->check_in) {
            \Log::info('Already checked in today for user ' . $user->id);
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
        
        try {
            if ($schedule && $schedule->expected_start_time) {
                $startTime = now()->setTimeFromTimeString($schedule->expected_start_time->format('H:i:s'));
            } else {
                $startTime = now()->setHour(9)->setMinute(0)->setSecond(0);
            }
        } catch (\Exception $e) {
            \Log::error('Error parsing schedule time: ' . $e->getMessage());
            $startTime = now()->setHour(9)->setMinute(0)->setSecond(0);
        }
        
        $isLate = $now->gt($startTime);
        $lateMinutes = $isLate ? $now->diffInMinutes($startTime) : 0;

        // Check if face verification was provided
        $faceVerified = $request->has('face_image') && !empty($request->face_image);

        if ($existingAttendance) {
            $existingAttendance->update([
                'check_in' => $now->format('H:i:s'),
                'status' => $isLate ? 'late' : 'present',
                'hours_worked' => 0,
                'face_verified' => $faceVerified,
            ]);
            \Log::info('Attendance updated for user ' . $user->id . ' on ' . $today);
        } else {
            Attendance::create([
                'employee_id' => $user->employee_id,
                'date' => $today,
                'check_in' => $now->format('H:i:s'),
                'status' => $isLate ? 'late' : 'present',
                'hours_worked' => 0,
                'face_verified' => $faceVerified,
            ]);
            \Log::info('Attendance created for user ' . $user->id . ' on ' . $today);
        }

        $message = $isLate 
            ? "Checked in successfully. You are {$lateMinutes} minutes late." 
            : "Checked in successfully. On time!";
        
        \Log::info('Check-in successful for user ' . $user->id);
        
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
            ->whereDate('date', $today)
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
        // check_in might be stored as time (H:i:s) or datetime
        if (strlen($checkInTime) > 8) {
            // Already includes date, use it directly
            $checkInDateTime = \Carbon\Carbon::parse($checkInTime);
        } else {
            // Only time, combine with today's date
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
        try {
            $user = Auth::user();
            \Log::info('getCurrentStatus called for user ' . $user->id);
            
            if (!$user->employee_id) {
                \Log::info('No employee_id for user ' . $user->id);
                return response()->json(['status' => 'no_employee']);
            }

            $today = today()->format('Y-m-d');
            \Log::info('Checking attendance for user ' . $user->id . ', employee_id: ' . $user->employee_id . ', date: ' . $today);
            
            $attendance = Attendance::where('employee_id', $user->employee_id)
                ->whereDate('date', today())
                ->first();

            if (!$attendance) {
                \Log::info('No attendance record found for user ' . $user->id . ' on ' . $today);
                return response()->json(['status' => 'not_checked_in']);
            }

            \Log::info('Attendance record found: check_in=' . ($attendance->check_in ?? 'null') . ', check_out=' . ($attendance->check_out ?? 'null') . ', status=' . $attendance->status);

            // Check if check_in is null
            if (!$attendance->check_in) {
                \Log::info('check_in is null/empty for user ' . $user->id);
                return response()->json(['status' => 'not_checked_in']);
            }

            if (!$attendance->check_out) {
                $checkInTime = $attendance->check_in->format('H:i');
                \Log::info('User is checked in, returning checked_in status with time: ' . $checkInTime);
                
                return response()->json([
                    'status' => 'checked_in',
                    'check_in_time' => $checkInTime,
                ]);
            }

            $checkInTime = $attendance->check_in->format('H:i');
            $checkOutTime = $attendance->check_out->format('H:i');

            \Log::info('User is checked out, returning checked_out status');

            return response()->json([
                'status' => 'checked_out',
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'hours_worked' => $attendance->hours_worked,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getCurrentStatus: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['status' => 'error', 'message' => 'An error occurred while checking attendance status.'], 500);
        }
    }
}
