<?php

namespace App\Notifications;

use App\Models\ActingAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ActingActionCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly ActingAssignment $assignment, public readonly string $actionTaken) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $file = $this->assignment->file;
        $actor = $this->assignment->assignedTo;
        $dest = $this->assignment->return_destination === 'permsec_office' ? "Permanent Secretary's Office" : "Records Department";

        return [
            'type' => 'acting_assignment_completed',
            'title' => 'Delegated Action Completed',
            'message' => ($actor->name ?? 'Chief Director').' completed action on file '.($file->file_number ?? '')." and returned it to {$dest}.",
            'icon' => 'check-circle',
            'color' => 'green',
            'url' => route('files.show', $file->uuid, false),
            'file_id' => $file->id ?? null,
            'file_uuid' => $file->uuid ?? null,
            'file_title' => $file->file_name ?? 'Unknown File',
            'file_number' => $file->file_number ?? '',
            'sender' => $actor->name ?? 'Chief Director',
            'remarks' => $this->actionTaken,
        ];
    }
}
