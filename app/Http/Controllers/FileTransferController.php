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
use Illuminate\Support\Str;

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
        $isRecordsStaff = ($currentUser->department?->code === 'REC' || Str::contains(Str::lower($currentUser->department?->name ?? ''), 'record'));

        // Same department active users
        $sameDeptUsers = User::with(['department', 'designation'])
            ->where('department_id', $currentUser->department_id)
            ->where('id', '!=', $currentUser->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Filter all active users based on role assignment limits:
        // Records staff can only pick PermSec or staff inside Records.
        // Departmental Admins can only pick officers in their own department (+ PermSec / Records Admin).
        $allUsersQuery = User::with(['department', 'designation'])
            ->where('id', '!=', $currentUser->id)
            ->where('is_active', true);

        if ($isRecordsStaff && $currentUser->role === 'user') {
            $sameDeptUsers = User::with(['department', 'designation'])
                ->where('department_id', $currentUser->department_id)
                ->where('role', 'admin')
                ->where('id', '!=', $currentUser->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
            $allUsers = $sameDeptUsers;
        } else if ($isRecordsStaff) {
            $allUsersQuery->where(function ($q) use ($currentUser) {
                $q->where('department_id', $currentUser->department_id)
                  ->orWhere('email', 'permsec@filetrack.local')
                  ->orWhereHas('designation', fn ($d) => $d->where('name', 'Permanent Secretary'));
            });
            $allUsers = $allUsersQuery->orderBy('name')->get();
        } else {
            $allUsersQuery->where(function ($q) use ($currentUser) {
                $q->where('department_id', $currentUser->department_id)
                  ->orWhere('email', 'permsec@filetrack.local')
                  ->orWhereHas('department', fn ($dept) => $dept->where('code', 'REC'))
                  ->orWhereHas('designation', fn ($d) => $d->where('name', 'Permanent Secretary'));
            });
            $allUsers = $allUsersQuery->orderBy('name')->get();
        }

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
            'return_minutes' => 'nullable|integer|min:1',
            'is_urgent' => 'nullable|boolean',
        ]);

        $file = FileRecord::where('uuid', $request->file_record_uuid)->firstOrFail();
        $currentUser = Auth::user();

        // Auth check — policy enforces current holder
        $this->authorize('transfer', $file);

        // SECURITY: re-verify ownership
        if ((int) $file->current_user_id !== $currentUser->id && (int) $file->current_department_id !== (int) $currentUser->department_id) {
            return back()->with('error', 'You no longer hold this file.');
        }

        $remarks = $request->string('remarks')->trim()->value() ?: null;
        $isRecordsStaff = ($currentUser->department?->code === 'REC' || Str::contains(Str::lower($currentUser->department?->name ?? ''), 'record'));

        // RULE: Only lock destination if Permanent Secretary was the immediate sender who assigned the recommended department.
        // If file returned to Records from another department, Records staff can assign any department or internal person.
        $lastMovement = $file->movements()->latest('created_at')->first();
        $lastSender = $lastMovement?->fromUser;
        $isLastSenderPermSec = $lastSender && (
            $lastSender->designation?->name === 'Permanent Secretary' ||
            $lastSender->email === 'permsec@filetrack.local'
        );

        if ($isRecordsStaff && $isLastSenderPermSec && $file->recommended_department_id) {
            if ($request->destination_type !== 'department' || (int) $request->department_id !== (int) $file->recommended_department_id) {
                return back()->with('error', 'The Permanent Secretary has locked the destination to a specific department. You cannot override it.');
            }
        }

        // RULE 0: General users in Records are ONLY permitted to send files to their Records Department Admin.
        if ($isRecordsStaff && $currentUser->role === 'user') {
            $targetUser = $request->filled('to_user_id') ? User::find((int) $request->to_user_id) : null;
            $isRecordsAdminTarget = $targetUser
                && $targetUser->role === 'admin'
                && (int) $targetUser->department_id === (int) $currentUser->department_id;

            if (! $isRecordsAdminTarget) {
                return back()->with('error', 'General users in Records are only permitted to send files to their Records Department Admin.');
            }
        }

        // RULE 1: Non-Records staff CANNOT dispatch files directly to other departments.
        // Cross-department transfers from non-Records departments MUST route back to Records Admin.
        if (! $isRecordsStaff) {
            $isSameDeptUser = false;
            if ($request->filled('to_user_id')) {
                $targetUser = User::find((int) $request->to_user_id);
                if ($targetUser && (int) $targetUser->department_id === (int) $currentUser->department_id) {
                    $isSameDeptUser = true;
                }
            }

            if (! $isSameDeptUser) {
                $recAdmin = $this->getRecordsAdmin();
                $targetDept = $request->filled('department_id') ? Department::find((int) $request->department_id) : null;
                if (! $targetDept && $request->filled('to_user_id')) {
                    $targetUser = User::find((int) $request->to_user_id);
                    $targetDept = $targetUser?->department;
                }
                if ($targetDept) {
                    $file->update(['recommended_department_id' => $targetDept->id]);
                }
                return $this->transferToUser($file, $currentUser, $recAdmin, 'Non-records department transfer returned to Records. Recommended Dept: '.($targetDept?->name ?? 'None').'. Notes: '.($remarks ?? 'N/A'));
            }
        }

        // RULE 2: Permanent Secretary Pre-requisite Rule for Cross-Department Dispatches.
        // A file CANNOT be dispatched to any OTHER handling department until it has been sent to and reviewed by the Permanent Secretary.
        // Internal transfers within the same department (e.g., Records Admin <-> Records Officer) do NOT require PermSec review.
        if (! $file->hasBeenToPermSec()) {
            $isPermSecRecipient = false;
            $isSameDeptRecipient = false;

            if (in_array($request->destination_type, ['user', 'same'], true) || $request->filled('to_user_id')) {
                $targetUser = User::find((int) $request->to_user_id);
                if ($targetUser) {
                    if ($targetUser->designation?->name === 'Permanent Secretary' || $targetUser->email === 'permsec@filetrack.local') {
                        $isPermSecRecipient = true;
                    }
                    if ((int) $targetUser->department_id === (int) $currentUser->department_id) {
                        $isSameDeptRecipient = true;
                    }
                }
            }

            if (! $isPermSecRecipient && ! $isSameDeptRecipient) {
                return back()->with('error', 'This file has not been reviewed by the Permanent Secretary yet. It must be sent to the Permanent Secretary before it can be dispatched to another department.');
            }
        }

        if (in_array($request->destination_type, ['user', 'same'], true) || $request->filled('to_user_id')) {
            $toUserId = (int) $request->to_user_id;

            $targetUser = User::where('id', $toUserId)
                ->where('is_active', true)
                ->first();

            if (! $targetUser) {
                return back()->with('error', 'Invalid recipient selected. Please select an active user.');
            }

            $isPermSecRecipient = ($targetUser->designation?->name === 'Permanent Secretary' || $targetUser->email === 'permsec@filetrack.local');
            $isSameDeptRecipient = ((int) $targetUser->department_id === (int) $currentUser->department_id);
            $isRecAdminRecipient = ($targetUser->department?->code === 'REC' || Str::contains(Str::lower($targetUser->department?->name ?? ''), 'record'));

            // Records Admin cannot assign individual officers in other handling departments directly
            if ($isRecordsStaff && ! $isPermSecRecipient && ! $isSameDeptRecipient) {
                return back()->with('error', 'Records staff cannot assign individual officers in other departments directly. Please select "Send to Department" so the Departmental Admin can assign an officer.');
            }

            // Departmental Admins / staff can only assign officers within their own department (or return to Records)
            if (! $isRecordsStaff && ! $isSameDeptRecipient && ! $isRecAdminRecipient && ! $isPermSecRecipient) {
                return back()->with('error', 'Departmental Admins can only assign officers within their own department.');
            }

            $returnMinutes = $request->filled('return_minutes') ? (int) $request->return_minutes : null;
            $isUrgent = $request->boolean('is_urgent');

            return $this->transferToUser($file, $currentUser, $targetUser, $remarks, $returnMinutes, $isUrgent);
        }

        $returnMinutes = $request->filled('return_minutes') ? (int) $request->return_minutes : null;
        $isUrgent = $request->boolean('is_urgent');

        return $this->transferToDepartment($file, $currentUser, (int) $request->department_id, $remarks, $returnMinutes, $isUrgent);
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

    /**
     * Mark file operations as completed (Records Department only).
     * Sets status to 'completed', sets completed_at timestamp, and stops time counting.
     */
    public function completeOperations(FileRecord $file, Request $request): RedirectResponse
    {
        $currentUser = Auth::user();
        $isRecordsStaff = ($currentUser->department?->code === 'REC' || Str::contains(Str::lower($currentUser->department?->name ?? ''), 'record'));

        if (! $isRecordsStaff) {
            return back()->with('error', 'Only Records department staff can mark file operations as completed.');
        }

        if ($file->status === 'completed') {
            return back()->with('info', 'This file operations have already been completed.');
        }

        $remarks = $request->string('remarks')->trim()->value() ?: 'File operations completed by Records.';

        $file->update([
            'status' => 'completed',
            'completed_at' => now(),
            'return_deadline' => null,
        ]);

        FileMovement::create([
            'file_id' => $file->id,
            'from_user' => $currentUser->id,
            'to_user' => $currentUser->id,
            'from_department' => $currentUser->department_id,
            'to_department' => $currentUser->department_id,
            'action' => 'completed',
            'remarks' => $remarks,
        ]);

        return back()->with('success', 'File operations marked as completed. Time counting has been closed.');
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    private function transferToUser(FileRecord $file, User $currentUser, User $targetUser, ?string $remarks, ?int $returnMinutes = null, bool $isUrgent = false): RedirectResponse
    {
        if ($targetUser->id === $currentUser->id) {
            return back()->with('error', 'You cannot transfer a file to yourself.');
        }

        $transfer = null;

        DB::transaction(function () use ($file, $currentUser, $targetUser, $remarks, $returnMinutes, $isUrgent, &$transfer) {
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

            $updateData = [
                'current_user_id' => $targetUser->id,
                'current_department_id' => $targetUser->department_id,
                'remarks' => $remarks ?? $file->remarks,
                'status' => 'active',
                'is_urgent' => $isUrgent || $file->is_urgent,
            ];

            if ($returnMinutes && $returnMinutes > 0) {
                $updateData['return_deadline'] = now()->addMinutes($returnMinutes);
            }

            $file->update($updateData);
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

    private function transferToDepartment(FileRecord $file, User $currentUser, int $deptId, ?string $remarks, ?int $returnMinutes = null, bool $isUrgent = false): RedirectResponse
    {
        $targetDept = Department::findOrFail($deptId);

        // Cannot transfer to own department via "other dept" path
        if ((int) $targetDept->id === (int) $currentUser->department_id) {
            return back()->with('error', 'That is your own department. Use "Same Department" instead.');
        }

        $transfer = null;

        DB::transaction(function () use ($file, $currentUser, $targetDept, $remarks, $returnMinutes, $isUrgent, &$transfer) {
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

            $updateData = [
                'current_user_id' => null,
                'current_department_id' => $targetDept->id,
                'status' => 'pending_assignment',
                'is_urgent' => $isUrgent || $file->is_urgent,
            ];

            if ($returnMinutes && $returnMinutes > 0) {
                $updateData['return_deadline'] = now()->addMinutes($returnMinutes);
            }

            // Department owns the file — no user assigned yet
            $file->update($updateData);
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

    /**
     * Permanent Secretary "Mark as Done":
     * Flags recommended target department and returns file to Records Admin.
     */
    public function permsecDone(Request $request, FileRecord $file): RedirectResponse
    {
        $currentUser = Auth::user();
        if ((int) $file->current_user_id !== $currentUser->id) {
            return back()->with('error', 'You do not currently hold this file.');
        }

        $request->validate([
            'recommended_department_id' => 'nullable|exists:departments,id',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $recAdmin = $this->getRecordsAdmin();
        $targetDept = $request->filled('recommended_department_id')
            ? Department::find($request->recommended_department_id)
            : null;

        $userRemarks = $request->string('remarks')->trim()->value();
        $fullRemarks = 'Permanent Secretary completed review.';
        if ($targetDept) {
            $fullRemarks .= ' Recommended Next Department: '.$targetDept->name.'.';
        }
        if ($userRemarks) {
            $fullRemarks .= ' Notes: '.$userRemarks;
        }

        $file->update([
            'has_permsec_reviewed' => true,
            'recommended_department_id' => $targetDept ? $targetDept->id : $file->recommended_department_id,
        ]);

        return $this->transferToUser($file, $currentUser, $recAdmin, $fullRemarks);
    }

    /**
     * Department Officer "Mark as Done":
     * Completes assigned work and sends the file back to their Department Admin.
     */
    public function officerDone(Request $request, FileRecord $file): RedirectResponse
    {
        $currentUser = Auth::user();
        if ((int) $file->current_user_id !== $currentUser->id) {
            return back()->with('error', 'You do not currently hold this file.');
        }

        $request->validate([
            'recommended_department_id' => 'nullable|exists:departments,id',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $deptAdmin = User::where('department_id', $currentUser->department_id)
            ->where('role', 'admin')
            ->where('is_active', true)
            ->first();

        if (! $deptAdmin) {
            return back()->with('error', 'Department Admin not found for your department.');
        }

        $targetDept = $request->filled('recommended_department_id')
            ? Department::find($request->recommended_department_id)
            : null;

        $userRemarks = $request->string('remarks')->trim()->value();
        $fullRemarks = 'Officer '.$currentUser->name.' completed assigned task.';
        if ($targetDept) {
            $fullRemarks .= ' Highlighted Next Dept: '.$targetDept->name.'.';
        }
        if ($userRemarks) {
            $fullRemarks .= ' Notes: '.$userRemarks;
        }

        if ($targetDept) {
            $file->update(['recommended_department_id' => $targetDept->id]);
        }

        return $this->transferToUser($file, $currentUser, $deptAdmin, $fullRemarks);
    }

    /**
     * Department Admin "Send Back to Records":
     * Sends the file back to Records Admin highlighting the next department to handle it.
     */
    public function adminReturnToRecords(Request $request, FileRecord $file): RedirectResponse
    {
        $currentUser = Auth::user();
        if ((int) $file->current_user_id !== $currentUser->id && (int) $file->current_department_id !== (int) $currentUser->department_id) {
            return back()->with('error', 'Your department does not currently hold this file.');
        }

        $request->validate([
            'recommended_department_id' => 'nullable|exists:departments,id',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $recAdmin = $this->getRecordsAdmin();
        $targetDept = $request->filled('recommended_department_id')
            ? Department::find($request->recommended_department_id)
            : null;

        $userRemarks = $request->string('remarks')->trim()->value();
        $fullRemarks = 'Department Admin returned file to Records.';
        if ($targetDept) {
            $fullRemarks .= ' Recommended Next Department: '.$targetDept->name.'.';
        }
        if ($userRemarks) {
            $fullRemarks .= ' Notes: '.$userRemarks;
        }

        if ($targetDept) {
            $file->update(['recommended_department_id' => $targetDept->id]);
        }

        return $this->transferToUser($file, $currentUser, $recAdmin, $fullRemarks);
    }

    /**
     * Records Admin "Send to Recommended Department":
     * Dispatches the file flagged by Permanent Secretary or Dept Admin to the target handling department.
     */
    public function dispatchRecommendedDepartment(Request $request, FileRecord $file): RedirectResponse
    {
        $currentUser = Auth::user();

        if (! $file->hasBeenToPermSec()) {
            return back()->with('error', 'This file has not been reviewed by the Permanent Secretary yet. It must be submitted to the Permanent Secretary before it can be dispatched to any department.');
        }

        if (! $file->recommended_department_id) {
            return back()->with('error', 'No recommended handling department is flagged for this file.');
        }

        $targetDeptId = (int) $file->recommended_department_id;
        $file->update(['recommended_department_id' => null]);

        return $this->transferToDepartment($file, $currentUser, $targetDeptId, 'Records Admin dispatched file to recommended handling department.');
    }

    private function getRecordsAdmin(): User
    {
        $recAdmin = User::whereHas('department', function ($q) {
            $q->where('code', 'REC')->orWhere('name', 'like', '%record%');
        })->where('role', 'admin')->first();

        if (! $recAdmin) {
            $recAdmin = User::whereHas('department', function ($q) {
                $q->where('code', 'REC')->orWhere('name', 'like', '%record%');
            })->first();
        }

        if (! $recAdmin) {
            $recAdmin = User::where('role', 'admin')->firstOrFail();
        }

        return $recAdmin;
    }
}
