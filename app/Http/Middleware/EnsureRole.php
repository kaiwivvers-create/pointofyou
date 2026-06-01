<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // Check database role slug first, fallback to enum
        $userRole = $user->dbRole ? $user->dbRole->slug : $user->role->value;

        $allowed = in_array($userRole, $roles);

        if (! $allowed) {
            abort(403, 'You do not have access to this area.');
        }

        return $next($request);
    }
}
