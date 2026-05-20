<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTableSession
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('cafe_table_id')) {
            return redirect()
                ->route('table.welcome')
                ->with('error', 'Please scan your table QR code first.');
        }

        return $next($request);
    }
}
