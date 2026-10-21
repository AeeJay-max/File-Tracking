<?php

namespace App\Notifications;

use App\Models\ActingAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActingActionCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly ActingAssignment $assignment, public readonly string $actionTaken) {}

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
        $file = $this->assignment->file;
        $actor = $this->assignment->assignedTo;
        $folder = $file?->folder;
        $folderNumber = $folder->folder_number ?? 'FLD-GENERAL';
        $folderName = $folder->folder_name ?? 'General Folder';
        $dest = $this->assignment->return_destination === 'permsec_office' ? "Permanent Secretary's Office" : "Records Department";
        $url = route('files.show', $file->uuid ?? '');

        return (new MailMessage)
            ->subject('[File Tracking] Delegated Action Completed: ' . ($file->file_number ?? 'File'))
            ->greeting('Hello ' . ($notifiable->name ?? 'Officer') . ',')
            ->line('Chief Director ' . ($actor->name ?? '') . ' has completed the delegated action on file **' . ($file->file_number ?? '') . '** and returned it to ' . $dest . '.')
            ->line('**File Title:** ' . ($file->file_name ?? 'N/A'))
            ->line('**Folder Number:** ' . $folderNumber)
            ->line('**Folder Name:** ' . $folderName)
            ->line('**Action Taken:** ' . $this->actionTaken)
            ->action('View File', $url);
    }

    public function toDatabase(object $notifiable): array
    {
        $file = $this->assignment->file;
        $actor = $this->assignment->assignedTo;
        $folder = $file?->folder;
        $folderNumber = $folder->folder_number ?? 'FLD-GENERAL';
        $folderName = $folder->folder_name ?? 'General Folder';
        $dest = $this->assignment->return_destination === 'permsec_office' ? "Permanent Secretary's Office" : "Records Department";

        return [
            'type' => 'acting_assignment_completed',
            'title' => 'Delegated Action Completed',
            'message' => ($actor->name ?? 'Chief Director').' completed action on file '.($file->file_number ?? '')." [Folder: {$folderNumber}] and returned it to {$dest}.",
            'icon' => 'check-circle',
            'color' => 'green',
            'url' => route('files.show', $file->uuid, false),
            'file_id' => $file->id ?? null,
            'file_uuid' => $file->uuid ?? null,
            'file_title' => $file->file_name ?? 'Unknown File',
            'file_number' => $file->file_number ?? '',
            'folder_number' => $folderNumber,
            'folder_name' => $folderName,
            'sender' => $actor->name ?? 'Chief Director',
            'remarks' => $this->actionTaken,
        ];
    }
}
