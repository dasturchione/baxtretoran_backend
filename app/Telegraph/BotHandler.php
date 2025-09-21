<?php

namespace App\Telegraph;

use App\Enums\OrderStatus;
use App\Services\OrderService;
use App\Traits\TelegramDeliverTrait;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\Log;

class BotHandler extends WebhookHandler
{

    use TelegramDeliverTrait;

    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function start(): void
    {
        // Log::info('dd');
        $deliver = $this->getActiveDeliverOrFail($this->getChatId());

        if (!$deliver) {
            return; // rad javob allaqachon traitda chiqdi
        }

        $this->chat->message("Salom, " . $this->getFirstName() . " ! Kerakli menuni tanlang")
            ->keyboard(Keyboard::make()->row([
                Button::make('Faol buyurtmalar')->action('activeorders'),
                Button::make('Barcha buyurtmalar')->action('allorders'),
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
                    Button::make('Barchasi')->action('allorders'),
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

    public function allOrders(): void
    {
        $deliver = $this->getActiveDeliverOrFail($this->getChatId());
        if (!$deliver) return;

        $data = json_decode($this->callbackQuery->data(), true);

        $page = $data['page'] ?? 1;
        Log::info($page);
        $perPage = 10;

        $ordersQuery = $deliver->orders()->orderBy('created_at', 'desc');
        $orders = $ordersQuery->paginate($perPage, ['*'], 'page', $page);

        if ($orders->isEmpty()) {
            $this->chat
                ->edit($this->getMessageId())
                ->message("📭 Sizda buyurtmalar yo‘q")
                ->keyboard(Keyboard::make()->row([
                    Button::make('Bosh Sahifa')->action('start'),
                ]))
                ->send();
            return;
        }

        $keyboard = Keyboard::make();
        $row = [];

        foreach ($orders as $index => $order) {
            $row[] = Button::make("📦 Buyurtma #{$order->id}")
                ->action('order_detail')
                ->param('order_id', $order->id);

            // Har 2 tugmadan keyin yangi qatorga qo‘yish
            if (count($row) == 2) {
                $keyboard->row($row);
                $row = [];
            }
        }

        // Agar oxirida 1 ta tugma qolsa
        if (!empty($row)) {
            $keyboard->row($row);
        }

        // Pagination tugmalari
        $paginationRow = [];
        if ($orders->currentPage() > 1) {
            $paginationRow[] = Button::make("⬅️ Orqaga")->action('allorders')->param('page', $orders->currentPage() - 1);
        }

        $paginationRow[] = Button::make("{$orders->currentPage()}/{$orders->lastPage()}")->action('noop'); // faqat ko‘rsatish
        if ($orders->currentPage() < $orders->lastPage()) {
            $paginationRow[] = Button::make("Oldinga ➡️")->action('allorders')->param('page', $orders->currentPage() + 1);
        }

        $keyboard->row($paginationRow);

        // Bosh sahifa tugmasi
        $keyboard->row([Button::make("🏠 Bosh sahifa")->action('start')]);

        $this->chat
            ->edit($this->getMessageId())
            ->message("📋 Sizning buyurtmalaringiz (sahifa {$orders->currentPage()} / {$orders->lastPage()}):")
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
        $order = $deliver->orders()->where('id', $orderId)->first();

        if (!$order) {
            $this->chat->message("❌ Buyurtma topilmadi yoki sizga tegishli emas.")->send();
            return;
        }

        // Buyurtma tafsilotlari
        $mapUrl = "https://yandex.com/maps/?ll={$order->address->long},{$order->address->lat}&z=16&pt={$order->address->long},{$order->address->lat},pm2rdl";

        // Buyurtma tafsilotlari HTML ko‘rinishda
        $text = "📦 Buyurtma #{$order->id}\n";
        $text .= "🛒 Status: {$order->status->label()}\n";
        $text .= "💰 Summa: " . price_format($order->total_price) . "\n";
        $text .= "👤 Mijoz: {$order->user->name}\n";
        $text .= "📍 Manzil: <a href=\"{$mapUrl}\">{$order->address->name_uz}</a>\n";

        $keyboard = Keyboard::make()
            ->row([
                Button::make("✅ Topshirildi")->action('delivered_order')->param('order_id', $order->id),
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

    // BotHandler.php

    public function delivered_order(): void
    {
        $deliver = $this->getActiveDeliverOrFail($this->getChatId());
        if (!$deliver) {
            return;
        }

        $data = json_decode($this->callbackQuery->data(), true);
        $orderId = $data['order_id'] ?? null;

        if (!$orderId) {
            $this->chat->message("❌ Noto'g'ri so'rov.")->send();
            return;
        }

        $order = $deliver->orders()->where('id', $orderId)->first();

        if (!$order) {
            $this->chat->message("❌ Buyurtma topilmadi yoki sizga tegishli emas.")->send();
            return;
        }

        // Inline tugmalar: action nomlari -> BotHandler metodlariga mos bo'lishi kerak
        $keyboard = Keyboard::make()->row([
            Button::make("✅ Tasdiqlash")->action('confirm_delivery')->param('order_id', $order->id),
            Button::make("❌ Bekor qilish")->action('cancel_delivery')->param('order_id', $order->id),
        ]);

        if ($order->payment_method_id == 1 && $order->payment_status == 'unpaid') {
            $text = "💰 Diqqat: bu buyurtma naqd to'lanishi kerak va hali to'lanmagan. Pul olgandan keyin 'Tasdiqlash' tugmasini bosing.";
        } else {
            $text = "Buyurtmani yetkazib berdingizmi? Iltimos, 'Tasdiqlash' tugmasini bosing.";
        }

        $this->chat
            ->edit($this->getMessageId())
            ->message($text)
            ->keyboard($keyboard)
            ->send();
    }

    /**
     * confirm_delivery action - tugma bosilganda chaqiriladi
     */
    public function confirm_delivery(): void
    {
        $deliver = $this->getActiveDeliverOrFail($this->getChatId());
        if (!$deliver) {
            return;
        }

        $data = json_decode($this->callbackQuery->data(), true);
        $orderId = $data['order_id'] ?? null;

        if (!$orderId) {
            $this->chat->message("❌ Noto‘g‘ri parametr.")->send();
            return;
        }

        $order = $deliver->orders()->where('id', $orderId)->first();

        if (!$order) {
            $this->chat->message("❌ Buyurtma topilmadi.")->send();
            return;
        }

        // 1️⃣ Allaqachon yetkazilgan bo‘lsa
        if ($order->status === OrderStatus::DELIVERED) {
            $this->chat
                ->edit($this->getMessageId())
                ->message("ℹ️ Buyurtma #{$order->id} allaqachon tasdiqlangan.")
                ->keyboard(Keyboard::make())
                ->send();
            return;
        }

        // 2️⃣ Buyurtma bekor qilingan bo‘lsa
        if ($order->status === OrderStatus::CANCELLED) {
            $this->chat
                ->edit($this->getMessageId())
                ->message("⛔️ Buyurtma #{$order->id} allaqachon bekor qilingan. Uni tasdiqlab bo‘lmaydi.")
                ->keyboard(Keyboard::make())
                ->send();
            return;
        }

        // 3️⃣ Normal holat – tasdiqlash
        try {
            $this->orderService->updateStatus($order, OrderStatus::DELIVERED);

            $this->chat
                ->edit($this->getMessageId())
                ->message("✅ Buyurtma #{$order->id} tasdiqlandi va Active Ordersga ko‘chdi.")
                ->keyboard(Keyboard::make())
                ->send();
        } catch (\Throwable $e) {
            Log::error('Confirm delivery failed', [
                'error'    => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            $this->chat->message("❌ Xatolik yuz berdi. Iltimos, administratorga murojaat qiling.")->send();
        }
    }


    /**
     * cancel_delivery action - bekor qilsa bu ishlaydi
     */
    public function cancel_delivery(): void
    {
        // Shunchaki xabarni yangilab tugmalarni olib tashlaymiz
        $this->chat
            ->edit($this->getMessageId())
            ->message("⛔️ Buyurtma yetkazib berilishi bekor qilindi.")
            ->keyboard(Keyboard::make())
            ->send();
    }
}
