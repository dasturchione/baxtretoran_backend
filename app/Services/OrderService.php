<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Jobs\SendWebPushJob;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();

            $status = match ($data['payment_method_id']) {
                1       => OrderStatus::ORDERED->value,
                default => OrderStatus::PAYMENT_PROCESS->value,
            };

            $order = Order::create([
                'user_id'           => $user->id,
                'payment_method_id' => $data['payment_method_id'],
                'delivery_type'     => $data['delivery_type'],
                'user_address_id'   => $data['address_id'] ?? null,
                'branch_id'         => $data['branch_id'] ?? null,
                'status'            => $status,
            ]);

            $this->addHistory($order, OrderStatus::from($status));

            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $product->id,
                    'quantity'    => $itemData['quantity'],
                    'combo_items' => $itemData['combo_items'] ? json_encode($itemData['combo_items']) : null,
                    'price'       => $product->price,
                ]);
            }

            if ($order->status === OrderStatus::ORDERED->value) {
                SendWebPushJob::dispatch(1, 'Yangi buyurtma', "Buyurtmachi", '/orders');
            }

            return $order->load('items');
        });
    }

    public function updateStatus(Order $order, OrderStatus $newStatus): array
    {
        // Joriy statusni aniqlaymiz
        $currentStatus = $order->status instanceof OrderStatus
            ? $order->status
            : OrderStatus::from($order->status);

        // Cancelled bo‘lsa → har doim ruxsat
        if ($newStatus === OrderStatus::CANCELLED) {
            return $this->applyStatus($order, $newStatus);
        }

        // Allowed statuslarni olib kelamiz
        $allowed = OrderStatus::flow()[$currentStatus->value] ?? [];

        if (!in_array($newStatus, $allowed, true)) {
            throw new \Exception("Statusni \"{$currentStatus->label()}\" dan \"{$newStatus->label()}\" ga o‘tkazish mumkin emas!");
        }

        return $this->applyStatus($order, $newStatus);
    }

    public function cancel(Order $order): bool
    {
        if ($this->canCancel($order)) {
            $order->update(['status' => OrderStatus::CANCELLED->value]);
            return true;
        }

        return false;
    }

    private function canCancel(Order $order): bool
    {
        return ($order->payment_method_id === 1 && $order->status === OrderStatus::ORDERED->value)
            || ($order->payment_method_id !== 1 && $order->status === OrderStatus::PAYMENT_FAILED->value);
    }

    /**
     * Statusni DB ga yozish + history yaratish
     */
    public function applyStatus(Order $order, OrderStatus $newStatus): array
    {
        DB::transaction(function () use ($order, $newStatus) {
            $order->update(['status' => $newStatus->value]);
            $this->addHistory($order, $newStatus);
        });

        return [
            'message' => "Status updated to {$newStatus->label()}",
            'order'   => $order->fresh('histories'),
        ];
    }

    protected function addHistory(Order $order, OrderStatus $status): void
    {
        $order->histories()->create(['status' => $status->value]);
    }
}
