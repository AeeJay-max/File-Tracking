<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * If the authenticated user has must_change_password = true,
 * redirect every request to the profile/password page except:
 * - the profile edit page itself (GET /profile)
 * - the password update action (PUT /profile/password)
 * - logout
 * - email verification routes (safety net — these should never have
 *   this middleware applied, but guarding here prevents a loop if the
 *   route structure ever changes)
 *
 * IMPORTANT: This middleware must NOT be registered in the global web
 * stack (bootstrap/app.php). It must only be applied to route groups
 * that already require 'verified'. Applying it globally causes an
 * infinite redirect loop for users who are both unverified AND have
 * must_change_password = true:
 *   /verify-email → ForcePasswordChange → /profile
 *   /profile      → verified            → /verify-email  (∞)
 */
class ForcePasswordChangeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('impersonator_id')) {
            return $next($request);
        }

        $user = Auth::user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs(
            'impersonation.start',
            'impersonation.stop',
            'profile.edit',
            'profile.password.update',
            'logout',
            // Verification safety net — prevents loop if middleware
            // is ever mistakenly applied to unverified-accessible routes.
            'verification.notice',
            'verification.send',
            'verification.verify',
        )) {
            return $next($request);
        }

        return redirect()->route('profile.edit')
            ->with('warning', 'You must change your password before continuing.');
    }
}
