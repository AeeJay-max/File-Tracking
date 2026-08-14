<?php

namespace App\Notifications;

use App\Models\TransferRequest;
use Illuminate\Bus\Queueable;
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
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'transfer_requested',
            'title' => 'Transfer Requested',
            'message' => 'New transfer request from '.($this->transferReq->sender->name ?? 'Unknown'),
            'icon' => 'paper-plane',
            'color' => 'green',
            'url' => route('admin.transfers', [], false),
            'file_title' => $this->transferReq->file->file_name ?? 'Unknown File',
            'file_number' => $this->transferReq->file->file_number ?? '',
            'file_uuid' => $this->transferReq->file->uuid ?? null,
            'from_dept' => $this->transferReq->fromDept->name ?? '',
            'to_dept' => $this->transferReq->toDept->name ?? '',
            'target_user' => $this->transferReq->receiver->name ?? '',
            'request_uuid' => $this->transferReq->uuid,
        ];
    }
}
