<?php

namespace App\Notifications;

use App\Models\Department;
use App\Models\FileTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to every admin of a department when a file is transferred TO that
 * department and is waiting to be assigned to a user (pending_assignment).
 */
class FileAssignmentPendingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly FileTransfer $transfer,
        public readonly Department $targetDept,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $file = $this->transfer->file;
        $sender = $this->transfer->sender;

        return [
            'type' => 'file_pending_assignment',
            'title' => 'File Needs Assignment',
            'message' => ($sender->name ?? 'System').' transferred '.
                             ($file->file_number ?? 'a file').
                             ' to '.$this->targetDept->name.'. Please assign it to a user.',
            'icon' => 'user-plus',
            'color' => 'orange',
            'url' => route('admin.files.pending', [], false),
            'file_id' => $this->transfer->file_id,
            'file_uuid' => $file->uuid ?? null,
            'file_title' => $file->file_name ?? 'Unknown File',
            'file_number' => $file->file_number ?? '',
            'sender' => $sender->name ?? 'System',
            'dept' => $this->targetDept->name,
        ];
    }
}
