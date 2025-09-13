<?php

namespace App\Telegraph;

use DefStudio\Telegraph\Handlers\WebhookHandler;

class BotHandler extends WebhookHandler
{
    public function start(): void
    {
        $telegramId = $this->message->from()->id();
        $firstName = $this->message->from()->firstName();

        $this->chat->message("Salom, $firstName! Siz deliver sifatida tizimga qo‘shildingiz ✅")->send();
    }
}
