<?php

namespace App\Services;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use App\Models\PushSubscription;

class WebPushService
{
    protected WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => 'mailto:no-reply@yourdomain.com', // complaint uchun email
                'publicKey' => env('VAPID_PUBLIC_KEY'),
                'privateKey' => env('VAPID_PRIVATE_KEY'),
            ]
        ]);
    }

    /**
     * 1️⃣ Bitta foydalanuvchiga yuborish
     */
    public function sendToUser(int $userId, string $title, string $body, string $url)
    {
        $subscriptions = PushSubscription::where('user_id', $userId)->get();
        $this->send($subscriptions, $title, $body, $url);
    }

    /**
     * 2️⃣ Bir nechta foydalanuvchiga yuborish
     */
    public function sendToUsers(array $userIds, string $title, string $body, string $url)
    {
        $subscriptions = PushSubscription::whereIn('user_id', $userIds)->get();
        $this->send($subscriptions, $title, $body, $url);
    }

    /**
     * 3️⃣ Hammaga yuborish
     */
    public function sendToAll(string $title, string $body, string $url)
    {
        $subscriptions = PushSubscription::all();
        $this->send($subscriptions, $title, $body, $url);
    }

    /**
     * Push yuborishning umumiy funksiyasi
     */
    protected function send($subscriptions, string $title, string $body, string $url)
    {
        foreach($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->endpoint,
                'publicKey' => $sub->p256dh,
                'authToken' => $sub->auth
            ]);

            $this->webPush->sendOneNotification($subscription, json_encode([
                'title' => $title,
                'body' => $body,
                'url' => $url,
            ]));
        }
    }
}
