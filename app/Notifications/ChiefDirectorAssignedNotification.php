<?php

namespace App\Notifications;

use App\Models\ActingAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ChiefDirectorAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly ActingAssignment $assignment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $file = $this->assignment->file;
        $assigner = $this->assignment->assignedBy;

        return [
            'type' => 'acting_assignment_received',
            'title' => 'Assigned File — Acting PermSec Authority',
            'message' => ($assigner->name ?? 'Permanent Secretary').' delegated Acting PermSec authority to you on file '.($file->file_number ?? ''),
            'icon' => 'user-shield',
            'color' => 'purple',
            'url' => route('chief_director.files.show', $file->uuid, false),
            'file_id' => $file->id ?? null,
            'file_uuid' => $file->uuid ?? null,
            'file_title' => $file->file_name ?? 'Unknown File',
            'file_number' => $file->file_number ?? '',
            'sender' => $assigner->name ?? 'Permanent Secretary',
            'remarks' => $this->assignment->instructions,
        ];
    }
}
