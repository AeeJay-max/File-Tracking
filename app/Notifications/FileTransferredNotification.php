<?php

namespace App\Notifications;

use App\Models\FileTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the file receiver upon a direct transfer.
 * No approval notifications — all transfers are immediate.
 */
class FileTransferredNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly FileTransfer $transfer) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $file = $this->transfer->file;
        $sender = $this->transfer->sender;
        $receiver = $this->transfer->receiver;
        $isDepartmentTransfer = $sender
            && $receiver
            && (int) $sender->department_id !== (int) $receiver->department_id;

        $url = $isDepartmentTransfer && in_array($notifiable->role ?? null, ['admin', 'super_admin'], true)
            ? route('admin.files.timeline', $file->uuid, false)
            : route('files.show', $file->uuid, false);

        return [
            'type' => 'file_received',
            'title' => 'File Transferred',
            'message' => ($sender->name ?? 'System').' transferred '.($file->file_number ?? 'a file'),
            'icon' => 'exchange-alt',
            'color' => 'blue',
            'url' => $url,
            'file_id' => $this->transfer->file_id,
            'file_uuid' => $file->uuid ?? null,
            'file_title' => $file->file_name ?? 'Unknown File',
            'file_number' => $file->file_number ?? '',
            'sender' => $sender->name ?? 'System',
            'remarks' => $this->transfer->remarks,
        ];
    }
}
