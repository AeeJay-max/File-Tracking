<?php

namespace App\Notifications;

use App\Models\FileTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        $url = route('files.show', $file->uuid ?? '');

        $mail = (new MailMessage)
            ->subject('[File Tracking] File Transferred: ' . ($file->file_number ?? 'Document'))
            ->greeting('Hello ' . ($notifiable->name ?? 'Officer') . ',')
            ->line('A file has been transferred to you by ' . ($sender->name ?? 'System') . '.')
            ->line('**File Number:** ' . ($file->file_number ?? 'N/A'))
            ->line('**File Title:** ' . ($file->file_name ?? 'N/A'));

        if (!empty($this->transfer->carried_by)) {
            $mail->line('**Carried By:** ' . $this->transfer->carried_by);
        }

        if (!empty($this->transfer->remarks)) {
            $mail->line('**Remarks / Notes:** ' . $this->transfer->remarks);
        }

        return $mail->action('View File in System', $url)
            ->line('Please review and take any necessary actions.');
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
            ? route('files.timeline', $file->uuid, false)
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
