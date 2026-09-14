<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActiveMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::guard('web')->logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Your account is disabled. Please go see the Chief Director for account activation.',
                        'redirect' => route('login'),
                    ], 403);
                }

                return redirect()->route('login')
                    ->with('account_disabled', 'Your account is disabled. Please go see the Chief Director for account activation.');
            }
        }

        return $next($request);
    }
}
