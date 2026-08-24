<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        try {
            $request->user()->sendEmailVerificationNotification();
            return back()->with('status', 'verification-link-sent');
        } catch (\Throwable $e) {
            Log::error('Failed to send email verification notification: '.$e->getMessage());

            // In local/testing environments where SMTP SSL fails, auto-verify and let user proceed
            if (app()->environment('local', 'testing')) {
                $request->user()->markEmailAsVerified();
                return redirect()->intended(route('dashboard', absolute: false))
                    ->with('status', 'Email verified automatically for local environment.');
            }

            return back()->with('error', 'Unable to connect to email server. Please contact your system administrator.');
        }
    }
}
