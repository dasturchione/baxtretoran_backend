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

            $order = Order::create([
                'user_id'           => $user->id,
                'payment_method_id' => $data['payment_method_id'],
                'delivery_type'     => $data['delivery_type'],
                'user_address_id'   => $data['address_id'] ?? null,
                'branch_id'         => $data['branch_id'] ?? null,
                'status'            => $data['payment_method_id'] == 1
                    ? OrderStatus::ORDERED->value
                    : OrderStatus::PAYMENT_PROCESS->value,
            ]);

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
}
