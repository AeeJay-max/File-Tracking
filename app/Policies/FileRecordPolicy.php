<?php

namespace App\Policies;

use App\Models\FileRecord;
use App\Models\FileTransfer;
use App\Models\User;

class FileRecordPolicy
{
    /**
     * before() — short-circuit checks before specific ability methods.
     *
     * Super Admin: can do everything except create files (no restriction on dept).
     * Admin:       can view dept files, cannot create.
     * Transfer:    always bypasses before() — handled by transfer() method.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Transfer is ownership-based — bypass before() entirely
        if ($ability === 'transfer') {
            return null;
        }

        // Director (super_admin): full system access including creating and sending files
        if ($user->role === 'super_admin') {
            return true;
        }

        return null;
    }

    /**
     * View a file:
     * - creator
     * - current holder
     * - departmental admin for files transferred to/from their department
     * - anyone who appeared in transfer history
     */
    public function view(User $user, FileRecord $file): bool
    {
        return $this->hasFileAccess($user, $file);
    }

    /** Download: same access rules as view. */
    public function download(User $user, FileRecord $file): bool
    {
        return $this->hasFileAccess($user, $file);
    }

    public function update(User $user, FileRecord $file): bool
    {
        return $this->hasFileAccess($user, $file);
    }

    /**
     * Transfer: ownership-based, not role-based.
     * Whoever currently holds the file can transfer it.
     * Archived and pending_assignment files cannot be transferred.
     */
    public function transfer(User $user, FileRecord $file): bool
    {
        if (in_array($file->status, ['archived', 'pending_assignment'], true)) {
            return false;
        }

        return (int) $file->current_user_id === $user->id;
    }

    /**
     * Create: Directors can create files.
     * Any authenticated user with can_create_file = true can create files.
     */
    public function create(User $user): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        return (bool) ($user->can_create_file ?? true);
    }

    // ──────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────

    private function hasFileAccess(User $user, FileRecord $file): bool
    {
        // Director (super_admin) sees all
        if ($user->role === 'super_admin') {
            return true;
        }

        // Creator
        if ((int) $file->created_by === $user->id) {
            return true;
        }

        // Current holder
        if ($file->current_user_id && (int) $file->current_user_id === $user->id) {
            return true;
        }

        // Departmental admin — can view files currently in, created in, or transferred to/from their department
        if ($user->role === 'admin' && $user->department_id) {
            if ((int) $user->department_id === (int) ($file->current_department_id ?? $file->department_id)) {
                return true;
            }
            if ((int) $user->department_id === (int) $file->department_id) {
                return true;
            }
            $isDeptInvolved = $file->movements()
                ->where(function ($q) use ($user) {
                    $q->where('from_department', $user->department_id)
                      ->orWhere('to_department', $user->department_id);
                })->exists();
            if ($isDeptInvolved) {
                return true;
            }
        }

        // Was involved in a transfer for this file
        return FileTransfer::where('file_id', $file->id)
            ->where(fn ($q) => $q
                ->where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id))
            ->exists();
    }
}
