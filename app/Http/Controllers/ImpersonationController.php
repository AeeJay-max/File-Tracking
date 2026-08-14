<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(Request $request, User $user): RedirectResponse
    {
        $impersonator = $request->user();

        if ($request->session()->has('impersonator_id')) {
            return back()->with('error', 'Stop the current impersonation session before starting another one.');
        }

        if (! $impersonator || ! $impersonator->canImpersonate($user)) {
            abort(403, 'You are not allowed to impersonate this user.');
        }

        $request->session()->put('impersonator_id', $impersonator->id);
        $request->session()->put('impersonator_name', $impersonator->name);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'You are now impersonating '.$user->name.'.');
    }

    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->get('impersonator_id');

        if (! $impersonatorId) {
            return redirect()->route('dashboard');
        }

        $impersonator = User::find($impersonatorId);

        if (! $impersonator) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Original impersonator account no longer exists.');
        }

        Auth::login($impersonator);
        $request->session()->forget(['impersonator_id', 'impersonator_name']);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Impersonation stopped.');
    }
}
