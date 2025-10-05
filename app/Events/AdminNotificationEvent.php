<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AdminNotificationEvent implements ShouldBroadcast
{
    public $type;
    public $data;

    /**
     * @param string $type Example: 'neworder', 'newclient'
     * @param array $data JSON data for the event
     */
    public function __construct(string $type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('admin.notifications');
    }

    public function broadcastAs()
    {
        return 'AdminNotification';
    }

    public function broadcastWith()
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
        ];
    }
}
