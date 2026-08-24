<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ForcePasswordChangeMiddleware;
use App\Http\Middleware\NoCacheMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ── Named middleware aliases ─────────────────────────────
        $middleware->alias([
            'super_admin' => SuperAdminMiddleware::class,
            'admin' => AdminMiddleware::class,
            'role' => RoleMiddleware::class,
            'no.cache' => NoCacheMiddleware::class,
            'security.headers' => SecurityHeadersMiddleware::class,
            'force.pwd.change' => ForcePasswordChangeMiddleware::class,
        ]);

        // ── Append security headers to every web response ─────────
        // ForcePasswordChangeMiddleware is intentionally NOT here.
        // Applying it globally would run it on /verify-email, causing
        // an infinite redirect loop for users who are both unverified
        // AND have must_change_password = true. It is applied explicitly
        // only on route groups that require a verified, authenticated user.
        $middleware->validateCsrfTokens(except: [
            'login',
        ]);

        $middleware->web(prepend: [
            \App\Http\Middleware\CatchTokenMismatchMiddleware::class,
        ]);
        $middleware->web(append: [
            SecurityHeadersMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if (Auth::check()) {
                Auth::guard('web')->logout();
            }

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Your session or token has expired. Please log in again.',
                    'redirect' => route('login'),
                ], 419);
            }

            return redirect()->route('login')
                ->with('status', 'Your session or security token has expired. You have been automatically logged out. Please log in again.');
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Unauthenticated. Please log in again.',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->route('login')
                ->with('status', 'Your session has ended. Please log in to continue.');
        });
    })->create();
