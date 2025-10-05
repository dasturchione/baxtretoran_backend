<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $type;
    public $data;

    public function __construct(string $type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    // Kanal
    public function broadcastOn()
    {
        return new PrivateChannel('user.notifications.' . $this->userId);
    }

    // Frontend tomonda event nomi
    public function broadcastAs()
    {
        return 'user.notification';
    }

    public function broadcastWith()
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
        ];
    }
}
