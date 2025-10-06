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
    public $userId; // qo‘shdik

    public function __construct(int $userId, string $type, array $data)
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.notifications.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'UserNotification';
    }

    public function broadcastWith()
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
        ];
    }
}

