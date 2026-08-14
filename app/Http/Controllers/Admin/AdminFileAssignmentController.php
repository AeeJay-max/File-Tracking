<?php

namespace App\Http\Controllers\Admin;

use App\Events\FileTransferred;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\FileTransfer;
use App\Models\User;
use App\Notifications\FileTransferredNotification;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Department Admin: list and assign incoming department-owned files.
 *
 * A file reaches this state when it is transferred cross-department:
 *   current_user_id   = NULL
 *   current_department_id = this department
 *   status            = pending_assignment
 *
 * The admin selects a user in their department; the file becomes active.
 */
class AdminFileAssignmentController extends Controller
{
    /**
     * GET /admin/files/pending
     * List all unassigned files for the admin's department.
     */
    public function index()
    {
        $admin = Auth::user();
        $deptId = $admin->department_id;

        $pendingFiles = FileRecord::with([
            'movements' => fn ($query) => $query->latest('created_at')->with(['fromUser', 'fromDept']),
        ])
            ->where('current_department_id', $deptId)
            ->whereNull('current_user_id')
            ->where('status', 'pending_assignment')
            ->latest()
            ->get();

        $deptUsers = User::where('department_id', $deptId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'designation_id']);

        return view('admin.files.pending', compact('pendingFiles', 'deptUsers'));
    }

    /**
     * POST /admin/files/pending/{uuid}/assign
     * Assign a pending-department file to a specific user.
     */
    public function assign(Request $request, string $uuid)
    {
        $admin = Auth::user();
        $deptId = $admin->department_id;

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $file = FileRecord::where('uuid', $uuid)
            ->where('current_department_id', $deptId)
            ->whereNull('current_user_id')
            ->where('status', 'pending_assignment')
            ->firstOrFail();

        $targetUser = User::where('id', $request->user_id)
            ->where('department_id', $deptId)
            ->where('is_active', true)
            ->firstOrFail();

        $transfer = null;

        DB::transaction(function () use ($file, $admin, $targetUser, &$transfer) {
            // Record as an assignment movement (not a transfer)
            FileMovement::create([
                'file_id' => $file->id,
                'from_user' => $admin->id,
                'to_user' => $targetUser->id,
                'from_department' => $admin->department_id,
                'to_department' => $targetUser->department_id,
                'action' => 'transferred',
                'remarks' => 'Assigned to '.$targetUser->name.' by department admin '.$admin->name,
            ]);

            // Create a FileTransfer log so the user sees it in their "Received" tab
            $transfer = FileTransfer::create([
                'file_id' => $file->id,
                'sender_id' => $admin->id,
                'receiver_id' => $targetUser->id,
                'remarks' => 'Department assignment by '.$admin->name,
                'transferred_at' => now(),
            ]);

            $file->update([
                'current_user_id' => $targetUser->id,
                'current_department_id' => $targetUser->department_id,
                'status' => 'active',
            ]);
        });

        if ($transfer) {
            $targetUser->notify(new FileTransferredNotification($transfer));
            event(new FileTransferred($transfer));
        }

        DashboardService::clearUserCache($admin->id);
        DashboardService::clearUserCache($targetUser->id);
        DashboardService::clearAdminCache($deptId);
        DashboardService::clearSuperAdminCache();

        return redirect()->route('admin.files.pending')
            ->with('success', 'File "'.$file->file_number.'" assigned to '.$targetUser->name.'.');
    }
}
