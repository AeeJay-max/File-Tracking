<?php

namespace App\Http\Controllers;

use App\Events\FileTransferred;
use App\Models\ActingAssignment;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\FileTransfer;
use App\Models\User;
use App\Notifications\ActingActionCompletedNotification;
use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChiefDirectorController extends Controller
{
    /**
     * Chief Director Dashboard
     */
    public function dashboard(DashboardService $dashboardService)
    {
        $user = Auth::user();

        // Stats
        $stats = $dashboardService->getChiefDirectorStats($user->id);

        // Departmental Directors (role = admin)
        $directors = User::where('role', 'admin')
            ->with(['department', 'designation'])
            ->orderBy('name')
            ->get();

        // Active Delegated Assignments
        $activeAssignments = ActingAssignment::where('assigned_to', $user->id)
            ->where('status', 'active')
            ->with(['file.currentDepartment', 'file.currentHolder', 'assignedBy'])
            ->latest('assigned_at')
            ->get();

        // Completed Delegated Assignments History
        $completedAssignments = ActingAssignment::where('assigned_to', $user->id)
            ->where('status', 'completed')
            ->with(['file', 'assignedBy'])
            ->latest('completed_at')
            ->limit(10)
            ->get();

        return view('chief_director.dashboard', compact(
            'stats',
            'directors',
            'activeAssignments',
            'completedAssignments'
        ));
    }

    /**
     * View assigned file with Permanent Secretary instructions panel.
     */
    public function showFile(FileRecord $file)
    {
        $user = Auth::user();
        $this->authorize('view', $file);

        $activeAssignment = ActingAssignment::where('file_id', $file->id)
            ->where('assigned_to', $user->id)
            ->where('status', 'active')
            ->first();

        $assignmentHistory = ActingAssignment::where('file_id', $file->id)
            ->where('assigned_to', $user->id)
            ->with('assignedBy')
            ->latest('assigned_at')
            ->get();

        $file->load(['movements.fromUser', 'movements.toUser', 'movements.fromDept', 'movements.toDept', 'department', 'currentDepartment', 'currentHolder', 'creator']);

        return view('chief_director.files.show', compact('file', 'activeAssignment', 'assignmentHistory'));
    }

    /**
     * Complete action on an assigned file under Acting PermSec authority.
     */
    public function completeAction(FileRecord $file, Request $request): RedirectResponse
    {
        $currentUser = Auth::user();

        if ($currentUser->role !== 'chief_director') {
            return back()->with('error', 'Unauthorized action.');
        }

        // SERVER-SIDE SECURITY VERIFICATION:
        // Must verify active assignment belongs to current file and current Chief Director
        $assignment = ActingAssignment::where('file_id', $file->id)
            ->where('assigned_to', $currentUser->id)
            ->where('authority_type', 'acting_permsec')
            ->where('status', 'active')
            ->first();

        if (! $assignment) {
            return back()->with('error', 'No active Acting Permanent Secretary delegation found for this file.');
        }

        $validated = $request->validate([
            'action_taken' => ['required', 'string', 'max:3000'],
            'next_action'  => ['nullable', 'string', 'max:2000'],
            'return_to'    => ['required', 'in:records,permsec_office'],
        ]);

        // ENFORCE PERMANENT SECRETARY'S DESIGNATED RETURN DESTINATION:
        if ($assignment->return_destination && $validated['return_to'] !== $assignment->return_destination) {
            $requiredLabel = $assignment->return_destination === 'permsec_office' ? "Permanent Secretary's Office" : "Records Department";
            return back()->with('error', "The Permanent Secretary directed that this file must be returned to the {$requiredLabel}.");
        }

        $returnDestination = $assignment->return_destination ?? $validated['return_to'];


        DB::transaction(function () use ($file, $currentUser, $assignment, $validated, $returnDestination) {
            // Determine recipient
            $recipient = null;
            if ($returnDestination === 'permsec_office') {
                $recipient = User::where('id', $assignment->assigned_by)->first()
                    ?? User::where(function ($q) {
                        $q->whereHas('designation', fn ($d) => $d->where('name', 'Permanent Secretary'))
                          ->orWhere('email', 'permsec@filetrack.local');
                    })->first();
            }

            if (! $recipient) {
                // Return to Records Admin
                $recipient = $this->getRecordsAdmin();
            }

            // Update assignment
            $assignment->update([
                'acted_at'            => now(),
                'completed_at'        => now(),
                'status'              => 'completed',
                'required_action'     => $validated['action_taken'],
                'next_step'           => $validated['next_action'] ?? $assignment->next_step,
                'return_destination'  => $returnDestination,
            ]);

            // Update file custody
            $file->update([
                'current_user_id'       => $recipient->id,
                'current_department_id' => $recipient->department_id,
                'status'                => 'active',
                'remarks'               => "Action completed by Chief Director: {$validated['action_taken']}",
            ]);

            $destLabel = $returnDestination === 'permsec_office' ? "Permanent Secretary's Office" : "Records Department";

            // Record FileTransfer
            FileTransfer::create([
                'file_id'     => $file->id,
                'sender_id'   => $currentUser->id,
                'receiver_id' => $recipient->id,
                'status'      => 'accepted',
                'remarks'     => "Acting PermSec action completed by Chief Director ({$currentUser->name}). Action Taken: {$validated['action_taken']}. Returned to {$destLabel}.",
            ]);

            // Record FileMovement
            FileMovement::create([
                'file_id'         => $file->id,
                'from_user'       => $currentUser->id,
                'to_user'         => $recipient->id,
                'from_department' => $currentUser->department_id,
                'to_department'   => $recipient->department_id,
                'action'          => 'acting_permsec_completed',
                'remarks'         => "Chief Director completed delegated action: {$validated['action_taken']}. File returned to {$destLabel}.",
            ]);

            // Record AuditLog
            AuditLog::create([
                'user_id'        => $currentUser->id,
                'action'         => 'acting_permsec_completed',
                'auditable_type' => FileRecord::class,
                'auditable_id'   => $file->id,
                'description'    => "Chief Director completed Acting PermSec action on file {$file->file_number} and returned it to {$destLabel}.",
                'metadata'       => json_encode(['assignment_uuid' => $assignment->uuid, 'return_to' => $returnDestination]),
            ]);

            // Notify Recipient
            $recipient->notify(new ActingActionCompletedNotification($assignment, $validated['action_taken']));

            // Broadcast event
            if ($transfer) {
                event(new FileTransferred($transfer));
            }


            // Clear caches
            DashboardService::clearAdminCache();
            DashboardService::clearSuperAdminCache();
            DashboardService::clearChiefDirectorCache($currentUser->id);
            DashboardService::clearUserCache($recipient->id);
        });

        $currentUser->markFileNotificationsRead($file);

        return redirect()->route('chief_director.dashboard')
            ->with('success', "Action on file {$file->file_number} successfully completed and file returned to ".($returnDestination === 'permsec_office' ? "Permanent Secretary's Office." : "Records Department."));
    }

    /**
     * Resolve Records Admin user helper.
     */
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
