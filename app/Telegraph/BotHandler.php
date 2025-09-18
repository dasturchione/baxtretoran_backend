<?php

namespace App\Telegraph;

use App\Traits\TelegramDeliverTrait;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\Log;

class BotHandler extends WebhookHandler
{

    use TelegramDeliverTrait;

    public function start(): void
    {
        // Log::info('dd');
        $deliver = $this->getActiveDeliverOrFail($this->getChatId());

        if (!$deliver) {
            return; // rad javob allaqachon traitda chiqdi
        }

        $this->chat->message("Salom, " . $this->getFirstName() . " ! Kerakli menuni tanlang")
            ->keyboard(Keyboard::make()->buttons([
                Button::make('Faol buyurtmalar')->action('activeorders'),
            ]))
            ->send();
    }

    public function activeorders(): void
    {
        $deliver = $this->getActiveDeliverOrFail($this->getChatId());

        if (!$deliver) {
            return;
        }

        // Buyurtmalarni olish
        $orders = $deliver->orders()->where('status', 'delivering')->get();

        if ($orders->isEmpty()) {
            $this->chat
                ->edit($this->getMessageId())
                ->message("📭 Sizda faol buyurtmalar yo‘q")
                ->keyboard(Keyboard::make()->row([
                    Button::make('Bosh Sahifa')->action('start'),
                    Button::make('Barchasi')->action('activeorders'),
                ]))
                ->send();
            return;
        }

        $keyboard = Keyboard::make();

        foreach ($orders as $order) {
            $keyboard->buttons([
                Button::make("📦 Buyurtma #{$order->id}")
                    ->action('order_detail')
                    ->param('order_id', $order->id),
            ]);
        }

        // Oxirida bosh sahifa tugmasi
        $keyboard->buttons([
            Button::make("🏠 Bosh sahifa")->action('start'),
        ]);

        $this->chat
            ->edit($this->getMessageId())
            ->message("📋 Sizning faol buyurtmalaringiz:")
            ->keyboard($keyboard)
            ->send();
    }

    public function order_detail(): void
    {
        $deliver = $this->getActiveDeliverOrFail($this->getChatId());

        if (!$deliver) {
            return;
        }
        $data = json_decode($this->callbackQuery->data(), true);

        $orderId = $data['order_id'];
        Log::info("Order info" . $orderId);
        $order = $deliver->orders()->where('id', $orderId)->first();

        if (!$order) {
            $this->chat->message("❌ Buyurtma topilmadi yoki sizga tegishli emas.")->send();
            return;
        }

        // Buyurtma tafsilotlari
        $mapUrl = "https://yandex.com/maps/?ll={$order->address->long},{$order->address->lat}&z=16&pt={$order->address->long},{$order->address->lat},pm2rdl";

        // Buyurtma tafsilotlari HTML ko‘rinishda
        $text = "📦 Buyurtma #{$order->id}\n";
        $text .= "🛒 Status: {$order->status->value}\n";
        $text .= "💰 Summa: " . price_format($order->total_price) . "\n";
        $text .= "👤 Mijoz: {$order->user->name}\n";
        $text .= "📍 Manzil: <a href=\"{$mapUrl}\">{$order->address->name_uz}</a>\n";

        $keyboard = Keyboard::make()
            ->row([
                Button::make("✅ Qabul qilish")->action('delivered_order')->param('order_id', $order->id),
                Button::make("❌ Bekor qilish")->action('cancel_order')->param('order_id', $order->id),
            ])
            ->row([
                Button::make("⬅️ Ortga")->action('activeorders'),
                Button::make("🏠 Bosh sahifa")->action('start'),
            ]);

        $this->chat
            ->html($text)
            ->keyboard($keyboard)
            ->send();
    }
}
