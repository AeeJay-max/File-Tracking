<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FileTransferred implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transfer;

    public function __construct($transfer)
    {
        $this->transfer = $transfer;
    }

    public function broadcastOn(): array
    {
        // Only broadcast when there is a real receiver
        if (! $this->transfer->receiver_id) {
            return [];
        }

        return [new PrivateChannel('user.'.$this->transfer->receiver_id)];
    }

    public function broadcastAs(): string
    {
        return 'file.transferred';
    }
}
