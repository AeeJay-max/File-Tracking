<?php

namespace App\Notifications;

use App\Models\ActingAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChiefDirectorAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly ActingAssignment $assignment) {}

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
        $assigner = $this->assignment->assignedBy;
        $folder = $file?->folder;
        $folderNumber = $folder->folder_number ?? 'FLD-GENERAL';
        $folderName = $folder->folder_name ?? 'General Folder';
        $url = route('chief_director.files.show', $file->uuid ?? '');

        return (new MailMessage)
            ->subject('[File Tracking] Delegated Acting PermSec Authority: ' . ($file->file_number ?? 'File'))
            ->greeting('Hello ' . ($notifiable->name ?? 'Chief Director') . ',')
            ->line('The ' . ($assigner->name ?? 'Permanent Secretary') . ' has delegated Acting Permanent Secretary authority to you on file **' . ($file->file_number ?? '') . '**.')
            ->line('**File Title:** ' . ($file->file_name ?? 'N/A'))
            ->line('**Folder Number:** ' . $folderNumber)
            ->line('**Folder Name:** ' . $folderName)
            ->line('**Instructions:** ' . $this->assignment->instructions)
            ->action('Open File & Take Action', $url);
    }

    public function toDatabase(object $notifiable): array
    {
        $file = $this->assignment->file;
        $assigner = $this->assignment->assignedBy;
        $folder = $file?->folder;
        $folderNumber = $folder->folder_number ?? 'FLD-GENERAL';
        $folderName = $folder->folder_name ?? 'General Folder';

        return [
            'type' => 'acting_assignment_received',
            'title' => 'Assigned File — Acting PermSec Authority',
            'message' => ($assigner->name ?? 'Permanent Secretary').' delegated Acting PermSec authority on file '.($file->file_number ?? '')." [Folder: {$folderNumber}]",
            'icon' => 'user-shield',
            'color' => 'purple',
            'url' => route('chief_director.files.show', $file->uuid, false),
            'file_id' => $file->id ?? null,
            'file_uuid' => $file->uuid ?? null,
            'file_title' => $file->file_name ?? 'Unknown File',
            'file_number' => $file->file_number ?? '',
            'folder_number' => $folderNumber,
            'folder_name' => $folderName,
            'sender' => $assigner->name ?? 'Permanent Secretary',
            'remarks' => $this->assignment->instructions,
        ];
    }
}
