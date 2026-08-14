<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\FileMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Read-only transfer monitoring for admin and super_admin.
 * Admins see only movements involving their department.
 * Super Admin sees all movements.
 */
class AdminTransferController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = FileMovement::with([
            'file',
            'fromUser',
            'toUser',
            'fromDept',
            'toDept',
        ])->where('action', 'transferred');

        // Department scope for regular admin
        if ($user->role !== 'super_admin') {
            $query->where(function ($q) use ($user) {
                $q->where('from_department', $user->department_id)
                    ->orWhere('to_department', $user->department_id);
            });
        }

        // Super admin: optionally filter by department
        if ($request->filled('department_id') && $user->role === 'super_admin') {
            $dept = Department::where('uuid', $request->department_id)->first();
            if ($dept) {
                $query->where(function ($q) use ($dept) {
                    $q->where('from_department', $dept->id)
                        ->orWhere('to_department', $dept->id);
                });
            }
        }

        // Search by file number or name
        if ($request->filled('search')) {
            $s = $request->string('search')->trim()->value();
            $query->whereHas('file', fn ($q) => $q
                ->where('file_number', 'like', "%{$s}%")
                ->orWhere('file_name', 'like', "%{$s}%"));
        }

        // Date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->date('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->date('to_date'));
        }

        $transfers = $query->latest()->paginate(25)->withQueryString();
        $departments = $user->role === 'super_admin'
            ? Department::orderBy('name')->get()
            : collect();

        return view('admin.transfers.index', compact('transfers', 'departments'));
    }
}
