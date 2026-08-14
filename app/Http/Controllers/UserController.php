<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * UserController — Super Admin only.
 * Manages ADMIN accounts exclusively.
 * Super Admin cannot create regular users (only admins).
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        // Super Admin sees only admins (not users, not other super_admins)
        $query = User::with(['department', 'designation'])
            ->where('role', 'admin')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $users = $query->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('users.index', compact('users', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $designations = Designation::with('department')->orderBy('name')->get();

        return view('users.create', compact('departments', 'designations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|max:255|unique:users,email',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'contact_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Super Admin can ONLY create Admin accounts — no privilege escalation.
        // Default password is Password@123, user must change on first login.
        $data = $request->only(['name', 'email', 'department_id', 'designation_id', 'contact_number']);
        $data['password'] = Hash::make('Password@123');
        $data['role'] = 'admin';
        $data['can_create_file'] = false;
        $data['must_change_password'] = true;

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->storePhoto($request);
        }

        $user = User::create($data);

        return redirect()->route('users.index')->with('success', 'Admin account created. Default password is Password@123 — they will be prompted to change it on first login.');
    }

    public function show(User $user)
    {
        // Only show admins
        if ($user->role !== 'admin') {
            abort(403, 'Access denied.');
        }
        $user->load(['department', 'designation']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        // Only edit admins
        if ($user->role !== 'admin') {
            abort(403, 'Access denied.');
        }
        $departments = Department::orderBy('name')->get();
        $designations = Designation::with('department')->orderBy('name')->get();

        return view('users.edit', compact('user', 'departments', 'designations'));
    }

    public function update(Request $request, User $user)
    {
        // Only update admins
        if ($user->role !== 'admin') {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|max:255|unique:users,email,'.$user->id,
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'contact_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'password' => 'nullable|min:8|confirmed',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        // role stays 'admin' — cannot be changed via this form
        $data = $request->only(['name', 'email', 'department_id', 'designation_id', 'contact_number']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $data['photo'] = $this->storePhoto($request);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Admin updated successfully.');
    }

    public function destroy(User $user)
    {
        // Only delete admins
        if ($user->role !== 'admin') {
            abort(403, 'Access denied.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Admin deleted successfully.');
    }

    private function storePhoto(Request $request): string
    {
        $file = $request->file('photo');
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid().'.'.strtolower($extension);

        return $file->storeAs('uploads/users', $filename, 'public');
    }
}
