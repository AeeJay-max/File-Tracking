<?php

namespace App\Support;

use Illuminate\Notifications\DatabaseNotification;

class NotificationPresenter
{
    public static function present(DatabaseNotification $notification): array
    {
        $data = $notification->data ?? [];
        $type = $data['type'] ?? 'info';

        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? self::defaultTitle($type),
            'message' => $data['message'] ?? 'New notification',
            'icon' => $data['icon'] ?? self::defaultIcon($type),
            'color' => self::safeColor($data['color'] ?? self::defaultColor($type)),
            'url' => $data['url'] ?? route('notifications.index', [], false),
            'created_at' => optional($notification->created_at)->toIso8601String(),
            'read_at' => optional($notification->read_at)->toIso8601String(),
            'relative_time' => self::relativeTime($notification->created_at),
            'is_unread' => is_null($notification->read_at),
        ];
    }

    public static function relativeTime($date): string
    {
        if (! $date) {
            return '';
        }

        $now = now();
        $seconds = $date->diffInSeconds($now);

        if ($seconds < 60) {
            return 'Just now';
        }

        $minutes = $date->diffInMinutes($now);
        if ($minutes < 60) {
            return $minutes.' min ago';
        }

        $hours = $date->diffInHours($now);
        if ($hours < 24) {
            return $hours.' '.str('hour')->plural($hours).' ago';
        }

        if ($date->isYesterday()) {
            return 'Yesterday';
        }

        $days = $date->diffInDays($now);

        return $days.' '.str('day')->plural($days).' ago';
    }

    private static function defaultTitle(string $type): string
    {
        return match ($type) {
            'file_received', 'file_transferred' => 'File Transferred',
            'transfer_requested' => 'Transfer Requested',
            'transfer_approved' => 'Transfer Approved',
            'transfer_rejected' => 'Transfer Rejected',
            'acting_assignment_received' => 'Assigned File — Acting PermSec',
            'acting_assignment_completed' => 'Delegated Action Completed',
            default => 'Notification',
        };
    }

    private static function defaultIcon(string $type): string
    {
        return match ($type) {
            'file_received', 'file_transferred' => 'exchange-alt',
            'transfer_requested' => 'paper-plane',
            'transfer_approved' => 'circle-check',
            'transfer_rejected' => 'circle-xmark',
            'acting_assignment_received' => 'user-shield',
            'acting_assignment_completed' => 'circle-check',
            default => 'bell',
        };
    }

    private static function defaultColor(string $type): string
    {
        return match ($type) {
            'file_received', 'file_transferred' => 'blue',
            'transfer_requested', 'transfer_approved', 'acting_assignment_completed' => 'green',
            'acting_assignment_received' => 'purple',
            'transfer_rejected' => 'red',
            default => 'gray',
        };
    }

    private static function safeColor(string $color): string
    {
        return in_array($color, ['blue', 'green', 'red', 'yellow', 'orange', 'purple', 'indigo', 'gray'], true) ? $color : 'gray';
    }

}
