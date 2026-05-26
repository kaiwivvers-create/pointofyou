<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Permission;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('admin.login');
        }
        
        // Super admin has all permissions
        if ($user->isSuperAdmin()) {
            return $next($request);
        }
        
        // Check permission from database
        $userPermission = Permission::where('role', $user->role->value)
            ->where('permission', $permission)
            ->first();
        
        if (!$userPermission || !$userPermission->can_view) {
            abort(403, 'You do not have permission to access this page.');
        }
        
        return $next($request);
    }
}
