<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index()
    {
        $designations = Designation::with('department')->latest()->paginate(15);

        return view('designations.index', compact('designations'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('designations.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        Designation::create([
            'department_id' => (int) $request->department_id,
            'name' => $request->string('name')->trim()->value(),
            'is_active' => (bool) $request->status,
        ]);

        return redirect()->route('designations.index')
            ->with('success', 'Designation created successfully.');
    }

    /**
     * Route model binding resolves by Designation::getRouteKeyName() = 'uuid'
     */
    public function edit(Designation $designation)
    {
        $departments = Department::orderBy('name')->get();

        return view('designations.edit', compact('designation', 'departments'));
    }

    public function update(Request $request, Designation $designation)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $designation->update([
            'department_id' => (int) $request->department_id,
            'name' => $request->string('name')->trim()->value(),
            'is_active' => (bool) $request->status,
        ]);

        return redirect()->route('designations.index')
            ->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        if ($designation->users()->count() > 0) {
            return back()->with('error', 'Cannot delete a designation that has users assigned to it.');
        }

        $designation->delete();

        return redirect()->route('designations.index')
            ->with('success', 'Designation deleted successfully.');
    }
}
