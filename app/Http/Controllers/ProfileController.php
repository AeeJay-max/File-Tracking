<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'contact_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
        ]);

        $user->fill([
            'name' => $request->string('name')->trim()->value(),
            'email' => $request->string('email')->trim()->lower()->value(),
            'phone' => $request->string('phone')->trim()->value() ?: null,
            'contact_number' => $request->string('contact_number')->trim()->value() ?: null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'profile-updated');
    }

    public function uploadPhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => 'required|file|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $user = $request->user();

        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $ext = strtolower($request->file('photo')->getClientOriginalExtension());
        $filename = 'avatars/'.Str::uuid().'.'.$ext;
        $request->file('photo')->storeAs('avatars', basename($filename), 'public');

        $user->update(['photo' => $filename]);

        return back()->with('status', 'photo-updated');
    }

    public function deletePhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->update(['photo' => null]);

        return back()->with('status', 'photo-deleted');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validateWithBag('updatePassword', [
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed|different:current_password',
            'password_confirmation' => 'required',
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        // Clear must_change_password flag once the user sets their own password
        if ($user->must_change_password) {
            $user->update(['must_change_password' => false]);
        }

        return back()->with('status', 'password-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
