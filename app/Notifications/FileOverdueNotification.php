<?php

namespace App\Notifications;

use App\Models\FileRecord;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the current file holder and current department HOD when Records Admin pings an overdue file.
 */
class FileOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly FileRecord $file,
        public readonly User $pingedBy
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
        $hoursHeld = $this->file->hoursWithCurrentHolder();
        $folder = $this->file->folder;
        $folderNumber = $folder->folder_number ?? 'FLD-GENERAL';
        $folderName = $folder->folder_name ?? 'General Folder';
        $url = route('files.show', $this->file->uuid);

        return (new MailMessage)
            ->error()
            ->subject('⚠️ [ACTION REQUIRED] Overdue File Notice: ' . $this->file->file_number)
            ->greeting('Hello ' . ($notifiable->name ?? 'Officer') . ',')
            ->line('**URGENT NOTICE:** File **' . $this->file->file_number . '** ("' . $this->file->file_name . '") has been held for over ' . $hoursHeld . ' hours or has passed its return deadline.')
            ->line('**Folder Number:** ' . $folderNumber)
            ->line('**Folder Name:** ' . $folderName)
            ->line('This file was pinged by ' . ($this->pingedBy->name ?? 'Records Department Admin') . '.')
            ->line('Immediate action is required. Please process and return the file to the Records Department promptly.')
            ->action('View Overdue File Now', $url)
            ->line('If you have already processed this file, please update its status in the system.');
    }

    public function toDatabase(object $notifiable): array
    {
        $hoursHeld = $this->file->hoursWithCurrentHolder();
        $folder = $this->file->folder;
        $folderNumber = $folder->folder_number ?? 'FLD-GENERAL';
        $folderName = $folder->folder_name ?? 'General Folder';

        return [
            'type' => 'file_overdue',
            'title' => '⚠️ OVERDUE FILE ACTION REQUIRED',
            'message' => 'File '.$this->file->file_number.' ("'.$this->file->file_name.'") [Folder: '.$folderNumber.'] has been held for over '.$hoursHeld.' hours. Immediate action is required and the file must be returned to the Records Department.',
            'icon' => 'triangle-exclamation',
            'color' => 'red',
            'url' => route('files.show', $this->file->uuid, false),
            'file_id' => $this->file->id,
            'file_uuid' => $this->file->uuid,
            'file_number' => $this->file->file_number,
            'file_title' => $this->file->file_name,
            'folder_number' => $folderNumber,
            'folder_name' => $folderName,
            'pinged_by' => $this->pingedBy->name ?? 'Records Admin',
            'hours_held' => $hoursHeld,
        ];
    }
}
