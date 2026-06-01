<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAttendance
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        // Skip for super admin
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }
        
        // Check if user has an employee record
        if (!$user || !$user->employee_id) {
            return $next($request);
        }
        
        // Get today's attendance
        $attendance = \App\Models\Attendance::where('employee_id', $user->employee_id)
            ->where('date', today())
            ->first();
        
        // If no attendance record or not checked in, restrict access
        if (!$attendance || !$attendance->check_in) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You must check in before performing this action.',
                    'requires_checkin' => true
                ], 403);
            }

            return redirect()->route($user->dashboard_route)
                ->with('error', 'You must check in before performing this action.');
        }

        // If already checked out, restrict access
        if ($attendance->check_out) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You have already checked out. You cannot perform this action.',
                    'already_checked_out' => true
                ], 403);
            }

            return redirect()->route($user->dashboard_route)
                ->with('error', 'You have already checked out. You cannot perform this action.');
        }
        
        return $next($request);
    }
}
