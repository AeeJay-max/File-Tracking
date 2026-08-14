<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminDesignationController extends Controller
{
    /**
     * Resolve designation by UUID.
     * Super Admin can access any designation.
     * Admin can only access their own department's designations.
     */
    private function resolveDesignation(string $uuid): Designation
    {
        $user = auth()->user();
        $query = Designation::where('uuid', $uuid);

        if ($user->role !== 'super_admin') {
            $query->where('department_id', $user->department_id);
        }

        return $query->firstOrFail();
    }

    public function index()
    {
        $user = auth()->user();
        $query = Designation::with('department')->latest();

        // Super admin sees all; admin sees only their department
        if ($user->role !== 'super_admin') {
            $query->where('department_id', $user->department_id);
        }

        $designations = $query->paginate(15);

        return view('admin.designations.index', compact('designations'));
    }

    public function create()
    {
        $user = auth()->user();

        // Super admin must choose a department; admin uses their own
        $departments = $user->role === 'super_admin'
            ? Department::orderBy('name')->get()
            : collect();

        return view('admin.designations.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $isSuper = $user->role === 'super_admin';

        // Validation: super admin must provide department_id
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
            'department_id' => $isSuper ? 'required|exists:departments,id' : 'nullable',
        ]);

        $departmentId = $isSuper
            ? (int) $request->department_id
            : $user->department_id;

        if (! $departmentId) {
            return back()
                ->withInput()
                ->with('error', 'No department assigned. Please select a department.');
        }

        try {
            DB::transaction(function () use ($request, $departmentId) {
                Designation::create([
                    'department_id' => $departmentId,
                    'name' => $request->string('name')->trim()->value(),
                    'is_active' => (bool) $request->status,
                ]);
            });

            return redirect()->route('admin.designations.index')
                ->with('success', 'Designation created successfully.');
        } catch (\Throwable $e) {
            Log::error('Designation creation failed', [
                'user_id' => auth()->id(),
                'department_id' => $departmentId,
                'name' => $request->name,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create designation. Please verify the input and try again.');
        }
    }

    public function show(string $designation)
    {
        return redirect()->route('admin.designations.index');
    }

    public function edit(string $designation)
    {
        $model = $this->resolveDesignation($designation);
        $user = auth()->user();
        $departments = $user->role === 'super_admin'
            ? Department::orderBy('name')->get()
            : collect();

        return view('admin.designations.edit', [
            'designation' => $model,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, string $designation)
    {
        $model = $this->resolveDesignation($designation);
        $user = auth()->user();
        $isSuper = $user->role === 'super_admin';

        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
            'department_id' => $isSuper ? 'required|exists:departments,id' : 'nullable',
        ]);

        try {
            $data = [
                'name' => $request->string('name')->trim()->value(),
                'is_active' => (bool) $request->status,
            ];

            if ($isSuper && $request->filled('department_id')) {
                $data['department_id'] = (int) $request->department_id;
            }

            $model->update($data);

            return redirect()->route('admin.designations.index')
                ->with('success', 'Designation updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Designation update failed: '.$e->getMessage());

            return back()->withInput()->with('error', 'Update failed: '.$e->getMessage());
        }
    }

    public function destroy(string $designation)
    {
        $model = $this->resolveDesignation($designation);

        if ($model->users()->count() > 0) {
            return back()->with('error', 'Cannot delete a designation that has users assigned to it.');
        }

        $model->delete();

        return redirect()->route('admin.designations.index')
            ->with('success', 'Designation deleted.');
    }
}
