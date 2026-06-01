<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Permit;
use Illuminate\Support\Facades\Auth;

class CheckPermit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // Skip permit check for super admin, owner, and admin
        if ($user && ($user->role->value === 'super_admin' || $user->role->value === 'owner' || $user->role->value === 'admin')) {
            return $next($request);
        }
        
        // Check if user has an approved permit for today
        $permit = Permit::where('user_id', $user->id)
            ->where('start_date', '<=', today())
            ->where(function($q) {
                $q->where('end_date', '>=', today())
                  ->orWhereNull('end_date');
            })
            ->where('status', 'approved')
            ->first();
        
        if (!$permit) {
            // Return 403 error instead of redirecting
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You need an approved permit to access this page.'], 403);
            }
            
            abort(403, 'You need an approved permit to access this page.');
        }
        
        return $next($request);
    }
}
