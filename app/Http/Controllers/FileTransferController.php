<?php

namespace App\Http\Controllers;

use App\Events\FileTransferred;
use App\Models\Department;
use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\FileTransfer;
use App\Models\User;
use App\Notifications\FileAssignmentPendingNotification;
use App\Notifications\FileTransferredNotification;
use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FileTransferController extends Controller
{
    /**
     * Show the transfer form for a file.
     * Any user who is the current holder may transfer — ownership-based, not role-based.
     */
    public function create(FileRecord $file)
    {
        $this->authorize('transfer', $file);

        $currentUser = Auth::user();

        // Same department active users
        $sameDeptUsers = User::with(['department', 'designation'])
            ->where('department_id', $currentUser->department_id)
            ->where('id', '!=', $currentUser->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // All active users in system (excluding self) for direct person-to-person selection
        $allUsers = User::with(['department', 'designation'])
            ->where('id', '!=', $currentUser->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('files.transfer', compact('file', 'sameDeptUsers', 'allUsers', 'departments'));
    }

    /**
     * Execute an immediate file transfer — directly to a specific Person or Department.
     *
     * destination_type = 'user' | 'same' | 'other' | 'department'
     * If person/user: to_user_id required (can be any active user in any department)
     * If department:  department_id required
     */
    public function store(Request $request)
    {
        $request->validate([
            'file_record_uuid' => 'required|string|exists:file_records,uuid',
            'destination_type' => 'required|in:user,same,other,department',
            'to_user_id' => 'required_if:destination_type,user,same|nullable|integer|exists:users,id',
            'department_id' => 'required_if:destination_type,other,department|nullable|integer|exists:departments,id',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $file = FileRecord::where('uuid', $request->file_record_uuid)->firstOrFail();
        $currentUser = Auth::user();

        // Auth check — policy enforces current holder
        $this->authorize('transfer', $file);

        // SECURITY: re-verify ownership
        if ((int) $file->current_user_id !== $currentUser->id) {
            return back()->with('error', 'You no longer hold this file.');
        }

        $remarks = $request->string('remarks')->trim()->value() ?: null;

        if (in_array($request->destination_type, ['user', 'same'], true) || $request->filled('to_user_id')) {
            $toUserId = (int) $request->to_user_id;

            $targetUser = User::where('id', $toUserId)
                ->where('is_active', true)
                ->first();

            if (! $targetUser) {
                return back()->with('error', 'Invalid recipient selected. Please select an active user.');
            }

            return $this->transferToUser($file, $currentUser, $targetUser, $remarks);
        }

        return $this->transferToDepartment($file, $currentUser, (int) $request->department_id, $remarks);
    }

    /**
     * AJAX: search users by name, department, or designation.
     */
    public function searchUsers(Request $request)
    {
        $q = $request->string('q')->trim()->value();
        $currentUser = Auth::user();

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $users = User::with(['department:id,name', 'designation:id,name'])
            ->where('is_active', true)
            ->where('id', '!=', $currentUser->id)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhereHas('department', fn ($d) => $d->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('designation', fn ($ds) => $ds->where('name', 'like', "%{$q}%"));
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'department_id', 'designation_id', 'photo_url'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'dept_name' => $u->department->name ?? 'No Department',
                'designation_name' => $u->designation->name ?? '',
                'initials' => $u->initials,
                'photo_url' => $u->photo_url,
            ]);

        return response()->json($users);
    }

    /**
     * AJAX: search departments by name for autocomplete.
     */
    public function searchDepartments(Request $request)
    {
        $q = $request->string('q')->trim()->value();

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $departments = Department::where('name', 'like', "%{$q}%")
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name']);

        return response()->json($departments);
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    private function transferToUser(FileRecord $file, User $currentUser, User $targetUser, ?string $remarks): RedirectResponse
    {
        if ($targetUser->id === $currentUser->id) {
            return back()->with('error', 'You cannot transfer a file to yourself.');
        }

        $transfer = null;

        DB::transaction(function () use ($file, $currentUser, $targetUser, $remarks, &$transfer) {
            $transfer = FileTransfer::create([
                'file_id' => $file->id,
                'sender_id' => $currentUser->id,
                'receiver_id' => $targetUser->id,
                'remarks' => $remarks,
                'transferred_at' => now(),
            ]);

            FileMovement::create([
                'file_id' => $file->id,
                'from_user' => $currentUser->id,
                'to_user' => $targetUser->id,
                'from_department' => $currentUser->department_id,
                'to_department' => $targetUser->department_id,
                'action' => 'transferred',
                'remarks' => $remarks ?? 'Transferred to '.$targetUser->name,
            ]);

            $file->update([
                'current_user_id' => $targetUser->id,
                'current_department_id' => $targetUser->department_id,
                'remarks' => $remarks ?? $file->remarks,
                'status' => 'active',
            ]);
        });

        if ($transfer) {
            $targetUser->notify(new FileTransferredNotification($transfer));
            event(new FileTransferred($transfer));
        }

        DashboardService::clearUserCache($currentUser->id);
        DashboardService::clearUserCache($targetUser->id);

        return redirect()->route('files.index')
            ->with('success', 'File transferred successfully to '.$targetUser->name.'.');
    }

    private function transferToDepartment(FileRecord $file, User $currentUser, int $deptId, ?string $remarks): RedirectResponse
    {
        $targetDept = Department::findOrFail($deptId);

        // Cannot transfer to own department via "other dept" path
        if ((int) $targetDept->id === (int) $currentUser->department_id) {
            return back()->with('error', 'That is your own department. Use "Same Department" instead.');
        }

        $transfer = null;

        DB::transaction(function () use ($file, $currentUser, $targetDept, $remarks, &$transfer) {
            // Record the transfer with no receiver — department owns it now
            $transfer = FileTransfer::create([
                'file_id' => $file->id,
                'sender_id' => $currentUser->id,
                'receiver_id' => null,
                'remarks' => $remarks,
                'transferred_at' => now(),
            ]);

            FileMovement::create([
                'file_id' => $file->id,
                'from_user' => $currentUser->id,
                'to_user' => null,
                'from_department' => $currentUser->department_id,
                'to_department' => $targetDept->id,
                'action' => 'transferred',
                'remarks' => $remarks ?? 'Cross-department transfer to '.$targetDept->name,
            ]);

            // Department owns the file — no user assigned yet
            $file->update([
                'current_user_id' => null,
                'current_department_id' => $targetDept->id,
                'status' => 'pending_assignment',
            ]);
        });

        if ($transfer) {
            // Notify all admins of the receiving department
            $deptAdmins = User::where('department_id', $targetDept->id)
                ->where('role', 'admin')
                ->where('is_active', true)
                ->get();

            foreach ($deptAdmins as $admin) {
                $admin->notify(new FileAssignmentPendingNotification($transfer, $targetDept));
            }

            // Only broadcast the event if there is a concrete receiver to avoid null-receiver issues
            // Event listeners should handle receiver_id being null gracefully
            event(new FileTransferred($transfer));
        }

        DashboardService::clearUserCache($currentUser->id);
        DashboardService::clearAdminCache($currentUser->department_id);
        DashboardService::clearAdminCache($targetDept->id);
        DashboardService::clearSuperAdminCache();

        return redirect()->route('files.index')
            ->with('success', 'File transferred to '.$targetDept->name.'. The department admin will assign it to a user.');
    }
}
