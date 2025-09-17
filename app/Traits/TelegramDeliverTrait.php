<?php

namespace App\Traits;

use App\Models\Deliver;

trait TelegramDeliverTrait
{
    protected function getChatId(): ?int
    {
        if ($this->callbackQuery) {
            return $this->callbackQuery->from()->id();
        }

        if ($this->message) {
            return $this->message->from()->id();
        }

        return null;
    }

    protected function getMessageId(): ?int
    {
        if ($this->callbackQuery) {
            return $this->callbackQuery->message()->id();
        }

        if ($this->message) {
            return $this->message->id();
        }

        return null;
    }

    public function getFirstName(): ?string
    {
        if ($this->callbackQuery) {
            return $this->callbackQuery->from()->firstName();
        }

        if ($this->message) {
            return $this->message->from()->firstName();
        }

        return null;
    }


    protected function findDeliver(int $chatId): ?Deliver
    {
        return Deliver::where('telegram_id', $chatId)->first();
    }

    public function getDeliverByChatId(int $chatId): ?Deliver
    {
        return $this->findDeliver($chatId);
    }

    public function getActiveDeliverOrFail(int $chatId): ?Deliver
    {
        $deliver = $this->findDeliver($chatId);

        if (!$deliver) {
            $this->reply("❌ Siz tizimda topilmadingiz. Admin bilan bog‘laning.");
            return null;
        }

        if (!$deliver->is_active) {
            $this->reply("🚫 Sizning hisobingiz faol emas. Admin bilan bog‘laning.");
            return null;
        }

        return $deliver;
    }
}
