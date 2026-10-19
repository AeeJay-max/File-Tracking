<?php

namespace App\Channels;

use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class SafeMailChannel extends MailChannel
{
    /**
     * Send the given notification safely, catching any SMTP or network delivery errors
     * so that user actions, database records, and system workflows never fail.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            return parent::send($notifiable, $notification);
        } catch (Throwable $e) {
            Log::warning('Email notification failed to deliver via SMTP: '.$e->getMessage(), [
                'recipient' => method_exists($notifiable, 'routeNotificationFor')
                    ? $notifiable->routeNotificationFor('mail', $notification)
                    : ($notifiable->email ?? null),
                'notification' => get_class($notification),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
