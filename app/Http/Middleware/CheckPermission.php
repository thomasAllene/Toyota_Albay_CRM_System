<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
     /**
     * Handle an incoming request.
     * @param  string  $permissions
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permissions): Response
    {

        if (Auth::check() && Auth::user()->usertype->name === 'SuperAdmin') {
            return $next($request);
        }

        if (Auth::check()  && !Auth::user()->usertype->hasPermission($permissions)) {
            // If not, return a 403 Forbidden response
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
