<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    /** Resolve user by UUID, scoped to admin's department */
    private function resolveUser(string $uuid): User
    {
        return User::where('uuid', $uuid)
            ->where('department_id', Auth::user()->department_id)
            ->firstOrFail();
    }

    public function index()
    {
        $users = User::where('department_id', Auth::user()->department_id)
            ->where('role', 'user')
            ->with('designation')
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $designations = Designation::where('department_id', Auth::user()->department_id)->get();

        return view('admin.users.create', compact('designations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc|max:255|unique:users,email',
            'designation_id' => 'required|exists:designations,id',
            'contact_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'can_create_file' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('Password@123'),
            'designation_id' => $request->designation_id,
            'department_id' => Auth::user()->department_id,
            'role' => 'user',
            'contact_number' => $request->contact_number,
            'can_create_file' => $request->boolean('can_create_file'),
            'must_change_password' => true,
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->storePhoto($request);
        }

        $user = User::create($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created. Default password is Password@123 — they will be prompted to change it on first login.');
    }

    public function show(string $user)
    {
        // show() not used — redirect to index
        return redirect()->route('admin.users.index');
    }

    public function edit(string $user)
    {
        $userModel = $this->resolveUser($user);
        $designations = Designation::where('department_id', Auth::user()->department_id)->get();

        return view('admin.users.edit', ['user' => $userModel, 'designations' => $designations]);
    }

    public function update(Request $request, string $user)
    {
        $userModel = $this->resolveUser($user);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc|max:255|unique:users,email,'.$userModel->id,
            'designation_id' => 'required|exists:designations,id',
            'contact_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'password' => 'nullable|min:8|confirmed',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'can_create_file' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'designation_id' => $request->designation_id,
            'contact_number' => $request->contact_number,
            'can_create_file' => $request->boolean('can_create_file'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            if ($userModel->photo && Storage::disk('public')->exists($userModel->photo)) {
                Storage::disk('public')->delete($userModel->photo);
            }
            $data['photo'] = $this->storePhoto($request);
        }

        $userModel->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(string $user)
    {
        $userModel = $this->resolveUser($user);

        if ($userModel->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $userModel->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    private function storePhoto(Request $request): string
    {
        $file = $request->file('photo');
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid().'.'.$extension;

        return $file->storeAs('uploads/users', $filename, 'public');
    }
}
