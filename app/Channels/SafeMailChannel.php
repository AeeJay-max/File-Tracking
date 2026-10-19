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
            // If DNS resolution or IPv6 connection failed, retry with explicit IPv4 resolution
            $originalHost = config('mail.mailers.smtp.host');
            if ($originalHost && ! filter_var($originalHost, FILTER_VALIDATE_IP)) {
                $ipv4 = @gethostbyname($originalHost);
                if ($ipv4 && $ipv4 !== $originalHost) {
                    try {
                        config(['mail.mailers.smtp.host' => $ipv4]);
                        app('mail.manager')->purge('smtp');
                        $result = parent::send($notifiable, $notification);
                        config(['mail.mailers.smtp.host' => $originalHost]);
                        return $result;
                    } catch (Throwable $e2) {
                        config(['mail.mailers.smtp.host' => $originalHost]);
                    }
                }
            }

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
