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
        // Director sees Directors, Departmental Admins, and Users
        $query = User::with(['department', 'designation'])
            ->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

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
            'role' => 'required|in:super_admin,admin,user',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'contact_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'role', 'department_id', 'designation_id', 'contact_number']);
        $data['password'] = Hash::make('Password@123');
        $data['can_create_file'] = true;
        $data['must_change_password'] = true;
        $data['email_verified_at'] = now();

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->storePhoto($request);
        }

        $user = User::create($data);

        $roleLabel = match($user->role) {
            'super_admin' => 'Director',
            'admin'       => 'Departmental Admin',
            default       => 'User',
        };

        return redirect()->route('users.index')->with('success', "{$roleLabel} account created. Default password is Password@123 — they will be prompted to change it on first login.");
    }

    public function show(User $user)
    {
        $user->load(['department', 'designation']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $departments = Department::orderBy('name')->get();
        $designations = Designation::with('department')->orderBy('name')->get();

        return view('users.edit', compact('user', 'departments', 'designations'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|max:255|unique:users,email,'.$user->id,
            'role' => 'required|in:super_admin,admin,user',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'contact_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'password' => 'nullable|min:8|confirmed',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'role', 'department_id', 'designation_id', 'contact_number']);

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

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User account deleted successfully.');
    }

    private function storePhoto(Request $request): string
    {
        $file = $request->file('photo');
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid().'.'.strtolower($extension);

        return $file->storeAs('uploads/users', $filename, 'public');
    }
}
