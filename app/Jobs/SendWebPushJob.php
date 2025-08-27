<?php

namespace App\Jobs;

use App\Services\WebPushService;
use Illuminate\Queue\SerializesModels;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;

class SendWebPushJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userIds, $title, $body, $url;

    /**
     * $userIds null = hammaga, int = bitta foydalanuvchi, array = bir nechta
     */
    public function __construct($userIds, string $title, string $body, string $url)
    {
        $this->userIds = $userIds;
        $this->title = $title;
        $this->body = $body;
        $this->url = $url;
    }

    public function handle(WebPushService $service)
    {
        if (is_null($this->userIds)) {
            $service->sendToAll($this->title, $this->body, $this->url);
        } elseif (is_int($this->userIds)) {
            $service->sendToUser($this->userIds, $this->title, $this->body, $this->url);
        } elseif (is_array($this->userIds)) {
            $service->sendToUsers($this->userIds, $this->title, $this->body, $this->url);
        }
    }
}

