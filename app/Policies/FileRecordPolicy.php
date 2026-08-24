<?php

namespace App\Policies;

use App\Models\FileRecord;
use App\Models\FileTransfer;
use App\Models\User;
use Illuminate\Support\Str;

class FileRecordPolicy
{
    /**
     * before() — short-circuit checks before specific ability methods.
     *
     * Transfer & Create are handled explicitly by their method handlers.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Transfer and create are handled by their method logic
        if ($ability === 'transfer' || $ability === 'create') {
            return null;
        }

        return null;
    }

    /**
     * View a file:
     * - Records department (global system access)
     * - Super Admin (system audit access)
     * - Creator / Current holder
     * - Departmental users for files currently in, created in, or transferred to/from their department
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
     * Transfer: ownership-based.
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
     * Create: ONLY users in the Records department can create files.
     * Super Admin ONLY manages user accounts (does not create files).
     * Other departments cannot create files — they only receive files from Records.
     */
    public function create(User $user): bool
    {
        if ($user->role === 'super_admin') {
            return false;
        }

        if (! $user->department) {
            return false;
        }

        $code = strtoupper((string) $user->department->code);
        $name = Str::lower((string) $user->department->name);

        return $code === 'REC' || $name === 'records' || Str::contains($name, 'record');
    }

    // ──────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────

    private function hasFileAccess(User $user, FileRecord $file): bool
    {
        // Super Admin (Director) ONLY manages user accounts — blocked from viewing files or file history
        if ($user->role === 'super_admin') {
            return false;
        }

        // Records department staff & admin see ALL files system-wide
        if ($user->department) {
            $code = strtoupper((string) $user->department->code);
            $name = Str::lower((string) $user->department->name);
            if ($code === 'REC' || $name === 'records' || Str::contains($name, 'record')) {
                return true;
            }
        }

        // Creator
        if ((int) $file->created_by === $user->id) {
            return true;
        }

        // Current holder
        if ($file->current_user_id && (int) $file->current_user_id === $user->id) {
            return true;
        }

        // Departmental access for non-Records departments (created in, currently in, or transferred to/from)
        if ($user->department_id) {
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
