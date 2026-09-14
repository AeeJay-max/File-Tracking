<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ChiefDirectorDirectorController extends Controller
{
    /**
     * Display list of departmental Directors (role = admin only).
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'admin')
            ->where(function ($q) {
                $q->whereDoesntHave('designation', fn ($d) => $d->where('name', 'Permanent Secretary'))
                  ->where('email', '!=', 'permsec@filetrack.local');
            })
            ->with(['department', 'designation']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('department', fn ($d) => $d->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        $directors = $query->orderBy('name')->paginate(15);
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();

        return view('chief_director.directors.index', compact('directors', 'departments'));
    }

    /**
     * Display details of a specific departmental Director.
     */
    public function show(User $director)
    {
        // Server-side check: Chief Director can ONLY view Directors (role = admin and not PermSec)
        if ($director->role !== 'admin' || $director->isPermanentSecretary()) {
            abort(403, 'Chief Director can only view departmental Director accounts.');
        }

        $director->load(['department', 'designation']);

        // File stats for this Director's department
        $departmentFileCount = \App\Models\FileRecord::where('current_department_id', $director->department_id)->count();

        return view('chief_director.directors.show', compact('director', 'departmentFileCount'));
    }

    /**
     * Show edit form for a departmental Director.
     */
    public function edit(User $director)
    {
        // Server-side check: Chief Director can ONLY edit Directors (role = admin and not PermSec)
        if ($director->role !== 'admin' || $director->isPermanentSecretary()) {
            abort(403, 'Chief Director can only manage departmental Director accounts.');
        }

        return view('chief_director.directors.edit', compact('director'));
    }

    /**
     * Update a departmental Director account information.
     */
    public function update(Request $request, User $director): RedirectResponse
    {
        // Server-side check: Chief Director can ONLY manage Directors (role = admin and not PermSec)
        if ($director->role !== 'admin' || $director->isPermanentSecretary()) {
            abort(403, 'Chief Director can only manage departmental Director accounts.');
        }

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users')->ignore($director->id)],
            'phone'     => ['nullable', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
        ]);

        // CRITICAL SECURITY ENFORCEMENT:
        // Chief Director cannot change role! Role must remain 'admin'.
        $validated['role'] = 'admin';

        $director->update($validated);

        AuditLog::create([
            'user_id'        => Auth::id(),
            'action'         => 'updated_director_account',
            'auditable_type' => User::class,
            'auditable_id'   => $director->id,
            'description'    => "Chief Director updated account for Director {$director->name} ({$director->display_title}).",
        ]);

        DashboardService::clearAdminCache();
        DashboardService::clearChiefDirectorCache(Auth::id());

        return redirect()->route('chief_director.directors.index')
            ->with('success', "Director account for {$director->name} ({$director->display_title}) updated successfully.");
    }

    /**
     * Toggle active status of a departmental Director account.
     */
    public function toggleStatus(User $director): RedirectResponse
    {
        // Server-side check: Chief Director can ONLY manage Directors (role = admin and not PermSec)
        if ($director->role !== 'admin' || $director->isPermanentSecretary()) {
            abort(403, 'Chief Director can only manage departmental Director accounts.');
        }

        $newStatus = ! $director->is_active;
        $director->update(['is_active' => $newStatus]);

        $statusLabel = $newStatus ? 'activated' : 'deactivated';

        AuditLog::create([
            'user_id'        => Auth::id(),
            'action'         => 'toggled_director_status',
            'auditable_type' => User::class,
            'auditable_id'   => $director->id,
            'description'    => "Chief Director {$statusLabel} account for Director {$director->name} ({$director->display_title}).",
        ]);

        DashboardService::clearAdminCache();
        DashboardService::clearChiefDirectorCache(Auth::id());

        return back()->with('success', "Director account for {$director->name} successfully {$statusLabel}.");
    }
}
