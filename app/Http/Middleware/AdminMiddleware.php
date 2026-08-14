<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check login first
        if (! Auth::check()) {
            abort(401, 'Unauthorized');
        }

        // 2. Safe role check
        $role = Auth::user()->role;

        if (! in_array($role, ['super_admin', 'admin'])) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
