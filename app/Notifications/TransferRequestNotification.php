<?php

namespace App\Notifications;

use App\Models\TransferRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the SOURCE department admin when a new cross-department
 * transfer request is submitted by one of their users.
 */
class TransferRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly TransferRequest $transferReq) {}

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
        $file = $this->transferReq->file;
        $sender = $this->transferReq->sender;
        $folder = $file?->folder;
        $folderNumber = $folder->folder_number ?? 'FLD-GENERAL';
        $folderName = $folder->folder_name ?? 'General Folder';
        $url = route('admin.transfers');

        return (new MailMessage)
            ->subject('[File Tracking] Transfer Request: ' . ($file->file_number ?? 'Document'))
            ->greeting('Hello ' . ($notifiable->name ?? 'Department Admin') . ',')
            ->line('A new transfer request has been submitted by ' . ($sender->name ?? 'an officer') . '.')
            ->line('**File:** ' . ($file->file_number ?? '') . ' - ' . ($file->file_name ?? ''))
            ->line('**Folder Number:** ' . $folderNumber)
            ->line('**Folder Name:** ' . $folderName)
            ->line('**From:** ' . ($this->transferReq->fromDept->name ?? 'N/A') . ' to ' . ($this->transferReq->toDept->name ?? 'N/A'))
            ->action('Review Transfer Requests', $url);
    }

    public function toDatabase(object $notifiable): array
    {
        $file = $this->transferReq->file;
        $folder = $file?->folder;
        $folderNumber = $folder->folder_number ?? 'FLD-GENERAL';
        $folderName = $folder->folder_name ?? 'General Folder';

        return [
            'type' => 'transfer_requested',
            'title' => 'Transfer Requested',
            'message' => 'New transfer request from '.($this->transferReq->sender->name ?? 'Unknown')." for {$file->file_number} [Folder: {$folderNumber}]",
            'icon' => 'paper-plane',
            'color' => 'green',
            'url' => route('admin.transfers', [], false),
            'file_title' => $this->transferReq->file->file_name ?? 'Unknown File',
            'file_number' => $this->transferReq->file->file_number ?? '',
            'file_uuid' => $this->transferReq->file->uuid ?? null,
            'folder_number' => $folderNumber,
            'folder_name' => $folderName,
            'from_dept' => $this->transferReq->fromDept->name ?? '',
            'to_dept' => $this->transferReq->toDept->name ?? '',
            'target_user' => $this->transferReq->receiver->name ?? '',
            'request_uuid' => $this->transferReq->uuid,
        ];
    }
}
