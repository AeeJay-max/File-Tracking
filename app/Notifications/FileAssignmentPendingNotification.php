<?php

namespace App\Notifications;

use App\Models\Department;
use App\Models\FileTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        $channels = ['database'];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $file = $this->transfer->file;
        $sender = $this->transfer->sender;
        $folder = $file?->folder;
        $folderNumber = $folder->folder_number ?? 'FLD-GENERAL';
        $folderName = $folder->folder_name ?? 'General Folder';
        $url = route('admin.files.pending');

        $mail = (new MailMessage)
            ->subject('[File Tracking] File Assignment Pending: ' . ($file->file_number ?? 'Document'))
            ->greeting('Hello ' . ($notifiable->name ?? 'Department Admin') . ',')
            ->line('A file has been dispatched to your department (' . $this->targetDept->name . ') and is awaiting assignment to an officer.')
            ->line('**File Number:** ' . ($file->file_number ?? 'N/A'))
            ->line('**File Title:** ' . ($file->file_name ?? 'N/A'))
            ->line('**Folder Number:** ' . $folderNumber)
            ->line('**Folder Name:** ' . $folderName)
            ->line('**Transferred By:** ' . ($sender->name ?? 'System'));

        if (!empty($this->transfer->carried_by)) {
            $mail->line('**Carried By:** ' . $this->transfer->carried_by);
        }

        if (!empty($this->transfer->remarks)) {
            $mail->line('**Remarks / Directives:** ' . $this->transfer->remarks);
        }

        return $mail->action('Assign File to Officer', $url)
            ->line('Please log in and assign this file to a handling officer.');
    }

    public function toDatabase(object $notifiable): array
    {
        $file = $this->transfer->file;
        $sender = $this->transfer->sender;
        $folder = $file?->folder;
        $folderNumber = $folder->folder_number ?? 'FLD-GENERAL';
        $folderName = $folder->folder_name ?? 'General Folder';

        return [
            'type' => 'file_pending_assignment',
            'title' => 'File Needs Assignment',
            'message' => ($sender->name ?? 'System').' transferred '.
                             ($file->file_number ?? 'a file').
                             " [Folder: {$folderNumber}] to ".$this->targetDept->name.'. Please assign it to a user.',
            'icon' => 'user-plus',
            'color' => 'orange',
            'url' => route('admin.files.pending', [], false),
            'file_id' => $this->transfer->file_id,
            'file_uuid' => $file->uuid ?? null,
            'file_title' => $file->file_name ?? 'Unknown File',
            'file_number' => $file->file_number ?? '',
            'folder_number' => $folderNumber,
            'folder_name' => $folderName,
            'sender' => $sender->name ?? 'System',
            'dept' => $this->targetDept->name,
        ];
    }
}
