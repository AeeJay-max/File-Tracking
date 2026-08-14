<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $departments = $query->latest()->paginate(15)->withQueryString();

        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'required|string|max:50|unique:departments,code|alpha_num',
            'is_active' => 'required|boolean',
        ]);

        Department::create([
            'name' => $request->string('name')->trim()->value(),
            'code' => strtoupper($request->string('code')->trim()->value()),
            'is_active' => (bool) $request->is_active,
        ]);

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function show(Department $department)
    {
        return view('departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,'.$department->id,
            'code' => 'required|string|max:50|unique:departments,code,'.$department->id.'|alpha_num',
            'is_active' => 'required|boolean',
        ]);

        $department->update([
            'name' => $request->string('name')->trim()->value(),
            'code' => strtoupper($request->string('code')->trim()->value()),
            'is_active' => (bool) $request->is_active,
        ]);

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        DB::beginTransaction();

        try {
            $department->designations()->delete();
            $department->users()->delete();
            $department->delete();

            DB::commit();

            return redirect()->route('departments.index')
                ->with('success', 'Department and related users/designations deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Department deletion failed: '.$e->getMessage());
        }
    }

    /**
     * AJAX: Create a department inline from the File Creation page.
     * Any authenticated user may use this endpoint.
     * Returns JSON: { success: true, department: { id, name } }
     */
    public function storeAjax(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ], [
            'name.unique' => 'A department with this name already exists.',
        ]);

        // Auto-generate a unique code from the name (uppercase alphanumeric, max 8 chars)
        $baseCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $validated['name']));
        $baseCode = substr($baseCode, 0, 8) ?: 'DEPT';
        $code = $baseCode;
        $counter = 1;
        while (Department::where('code', $code)->exists()) {
            $code = substr($baseCode, 0, 7).$counter++;
        }

        $department = Department::create([
            'name' => trim($validated['name']),
            'code' => $code,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
            ],
        ]);
    }
}
