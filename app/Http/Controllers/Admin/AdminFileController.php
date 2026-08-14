<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminFileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = FileRecord::with(['department', 'currentDepartment', 'creator', 'currentHolder']);

        // Department isolation — admin sees only their dept (current ownership, not origin)
        if ($user->role !== 'super_admin') {
            $query->where('current_department_id', $user->department_id);
        }

        // Super admin can filter by department UUID
        if ($request->filled('department_id') && $user->role === 'super_admin') {
            $dept = Department::where('uuid', $request->department_id)->first();
            if ($dept) {
                $query->where('current_department_id', $dept->id);
            }
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(fn ($q) => $q
                ->where('file_number', 'like', "%{$search}%")
                ->orWhere('file_name', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $allowed = ['active', 'archived', 'draft', 'pending_assignment'];
            if (in_array($request->status, $allowed, true)) {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->date('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->date('to_date'));
        }

        $files = $query->latest()->paginate(20)->withQueryString();
        $fileIds = $files->pluck('id');

        // Resolve previous holder per file using a single query (no N+1).
        // "Previous holder" = to_user of the second-most-recent 'transferred' movement.
        $previousHolderIds = FileMovement::select('file_id', 'to_user')
            ->whereIn('file_id', $fileIds)
            ->where('action', 'transferred')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('file_id')
            ->map(fn ($moves) => $moves->skip(1)->first()?->to_user)
            ->filter(); // remove nulls

        // Pre-fetch all previous-holder users in ONE query — eliminates N+1 in the view.
        $prevHolderCache = User::whereIn('id', $previousHolderIds->values())
            ->get(['id', 'name'])
            ->keyBy('id');

        $departments = $user->role === 'super_admin'
            ? Department::orderBy('name')->get()
            : collect();

        return view('admin.files.index', compact(
            'files',
            'departments',
            'previousHolderIds',
            'prevHolderCache'
        ));
    }
}
