<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        // Not logged in → redirect to login (not 403 — avoids leaking route existence)
        if (! $user) {
            return redirect()->route('login');
        }

        // Wrong role → 403
        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }
}
