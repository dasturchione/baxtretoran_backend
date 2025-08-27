<?php

namespace App\Services;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\PaymeUz;
use App\Models\SiteSetting;
use App\Http\Resources\PaymeTransationResource;

class PaymeService
{

    public function CheckPerformTransaction($data)
    {
        if(!isset($data['params']['account']) || !array_key_exists('order_id', $data['params']['account'])){
            return response()->json([
                'id'    => $data['id'],
                'error' => [
                    'code' => -32504,
                    'message'   => "Недостаточно привилегий для выполнения метода"
                    ]
                ]);
        }else{
            $order = Order::find($data['params']['account']['order_id']);
            if ($order && $order->status == "created") {
                $orderItems = OrderItem::where('order_id', $order->id)
                    ->selectRaw('SUM(price * quantity) as totalPrice, SUM(discount * quantity) as totalDiscount')
                    ->first();

                    // Yakuniy summa hisoblash
                    $totalPrice = $orderItems->totalPrice ?? 0;
                    $totalDiscount = $orderItems->totalDiscount ?? 0;
                    $totalAmount = $totalPrice - $totalDiscount;


                    $orderItems = OrderItem::where('order_id', $order->id)
                        ->with('product') // Product bilan bog‘lanish
                        ->get();

                    // Eng qimmat buyurtma elementini topamiz
                        $highestPriceOrderItem = $orderItems->sortByDesc(function ($orderItem) {
                            return $orderItem->price * $orderItem->quantity;
                        })->first();

                        // Items massivini yaratish
                            $items = $orderItems->map(function ($orderItem) use ($highestPriceOrderItem) {
                            $product = $orderItem->product;

                            // Agar eng qimmat buyurtma elementiga to‘g‘ri kelsa, promo-discountni qo‘shamiz
                            $discount = $orderItem->discount
                                ? ($orderItem->discount * $orderItem->quantity) * 100
                                : 0;

                            return [
                                "title" => $product->name_uz,
                                "price" => $orderItem->price * 100,
                                "count" => $orderItem->quantity,
                                "code" => $product->ikpu_code,
                                "vat_percent" => $product->vat_percent,
                                "package_code" => $product->package_code,
                            ];
                        });


                    if($order->payment_status == "unpaid"){
                        if ($totalAmount == $data['params']['amount'] / 100) {
                            $transaction = PaymeUz::where('order_id', $data['params']['account']['order_id'])->where('state', 1)->get();
                            if(count($transaction) == 0){
                                return response()->json([
                                    'result' => array(
                                        'allow' => true,
                                        "detail" => array(
                                            "receipt_type" => 0,
                                            "items" => $items,
                                        )
                                    )
                                ]);
                            }else{
                                return response()->json([
                                    'id'    => $data['id'],
                                    'error' => array(
                                        'code'    => -31099,
                                        'message' => array(
                                            'uz'    => "Buyurtma to'lovi hozirda amalga oshirilmoqda",
                                            'ru'    => "Оплата заказа в настоящее время обрабатывается",
                                            'en'    => "Order payment is currently being processed"
                                        )
                                    )
                                ]);
                            }

                        }else{
                            return response()->json([
                                'id'    => $data['id'],
                                'error' => [
                                    'code' => -31001,
                                    'message'   => [
                                        'uz' => "Noto'g'ri summa".$totalAmount,
                                        'ru' => "Неверная сумма",
                                        'en' => "Incorrect amount"
                                    ]
                                    ]
                                ]);
                        }
                    }else{
                        return response()->json([
                            'id'    => $data['id'],
                            'error' => [
                                'code' => -31050,
                                'message'   => [
                                    'uz' => "Faktura allaqachon to'langan",
                                    'ru' => "счет уже оплачен",
                                    'en' => "The bill has already been paid"
                                ]
                                ]
                            ]);
                    }

            }else{
                return response()->json([
                    'id'    => $data['id'],
                    'error' => [
                        'code' => -31050,
                        'message'   => [
                            'uz' => "Buyurtma topilmadi",
                            'ru' => "Заказ не найден",
                            'en' => "Order not found"
                        ]
                        ]
                    ]);
            }
        }
    }

    public function CreateTransaction($data)
    {
        if(!isset($data['params']['account']) || !array_key_exists('order_id', $data['params']['account'])){
            return response()->json([
                'id'    => $data['id'],
                'error' => [
                    'code' => -32504,
                    'message'   => "Недостаточно привилегий для выполнения метода"
                    ]
                ]);
        }else{
            $order = Order::find($data['params']['account']['order_id']);
            if($order){
                $orderItems = OrderItem::where('order_id', $data['params']['account']['order_id'])
                ->selectRaw('SUM(price * quantity) as totalPrice, SUM(discount * quantity) as totalDiscount')
                ->first();

                // Yakuniy summa hisoblash
                $totalPrice = $orderItems->totalPrice ?? 0;
                $totalDiscount = $orderItems->totalDiscount ?? 0;
                $totalAmount = $totalPrice - $totalDiscount;
            }


            $transaction = PaymeUz::where('order_id', $data['params']['account']['order_id'])->where('state', 1)->get();
            if (!$order) {
                return response()->json([
                    'id'    => $data['id'],
                    'error' => [
                        'code' => -31050,
                        'message'   => [
                            'uz' => "Buyurtma topilmadi",
                            'ru' => "Заказ не найден",
                            'en' => "Order not found"
                        ]
                        ]
                    ]);
            }else if($order->payment_status == "paid"){
                return response()->json([
                    'id'    => $data['id'],
                    'error' => [
                        'code' => -31050,
                        'message'   => [
                            'uz' => "Faktura allaqachon to'langan",
                            'ru' => "счет уже оплачен",
                            'en' => "The bill has already been paid"
                        ]
                        ]
                    ]);

            } else if ($totalAmount != $data['params']['amount'] / 100){
                return response()->json([
                    'id'    => $data['id'],
                    'error' => [
                        'code' => -31001,
                        'message'   => [
                            'uz' => "Noto'g'ri summa",
                            'ru' => "Неверная сумма",
                            'en' => "Incorrect amount"
                        ]
                        ]
                    ]);
            } else if(count($transaction) == 0){
                $transaction = new PaymeUz();
                $transaction->paycom_transaction_id = $data['params']['id'];
                $transaction->paycom_time           = $data['params']['time'];
                $transaction->paycom_time_datetime  = now();
                $transaction->amount                = $data['params']['amount'] / 100;
                $transaction->state                 = 1;
                $transaction->order_id              = $data['params']['account']['order_id'];
                $transaction->save();

                $order = Order::find($transaction->order_id);
                $order->payment_method_id = 2;
                $order->status = "waitingpay";
                $order->update();

                return response()->json([
                    'result' => array(
                        'create_time'   => $data['params']['time'],
                        'transaction'   => strval($transaction->id),
                        'state'         => $transaction->state,
                    )
                ]);
            } else if((count($transaction) == 1) and ($transaction->first()->paycom_time == $data['params']['time']) and ($transaction->first()->paycom_transaction_id == $data['params']['id'])){
                return response()->json([
                    'result' => array(
                        'create_time'   => $data['params']['time'],
                        'transaction'   => strval($transaction[0]->id),
                        'state'         => intval($transaction[0]->state),
                    )
                ]);
            } else {
                return response()->json([
                    'id'    => $data['id'],
                    'error' => array(
                        'code'    => -31099,
                        'message' => array(
                            'uz'    => "Buyurtma to'lovi hozirda amalga oshirilmoqda",
                            'ru'    => "Оплата заказа в настоящее время обрабатывается",
                            'en'    => "Order payment is currently being processed"
                        )
                    )
                ]);
            }
        }
    }

    public function PerformTransaction($data)
    {
        $ldate = date('Y-m-d H:i:s');
        $transaction = PaymeUz::where('paycom_transaction_id', $data['params']['id'])->first();
        if(!$transaction){
            return response()->json([
                'id'    => $data['id'],
                'error' => [
                    'code' => -31003,
                    'message'   => "Транзакция не найдена"
                    ]
                ]);
        } else if($transaction->state == 1){

            $currentMillis = intval(microtime(true) * 1000);
            $transaction = PaymeUz::where('paycom_transaction_id', $data['params']['id'])->first();
            $transaction->state = 2;
            $transaction->perform_time = $ldate;
            $transaction->perform_time_unix = str_replace('.', '', $currentMillis);
            $transaction->update();

            $order = Order::find($transaction->order_id);
            $order->payment_status = "paid";
            $order->status = "ordered";
            $order->update();

            $sitesetting = SiteSetting::first();
            $user = $order->user;

            // Admin uchun xabar
            $userIds = explode(',', $sitesetting->tg_noti_user_id); // Vergul bilan bo‘lingan stringni arrayga aylantirish

                                // Har bir foydalanuvchi uchun xabar yuboramiz
            foreach ($userIds as $userId) {
                try {
                    $this->telegramController->bot('sendMessage', [
                        'chat_id' => trim($userId),
                        'text' => "📦 *Yangi buyurtma!* 🚨\n\n" .
                                "🆔 *ID*: #" . $order->id . "\n" .
                                "💳 *To‘lov usuli*: Payme\n" .
                                "💰 *Qiymati*: " . $transaction->amount . " so‘m\n" .
                                "✅ *Holati*: To‘lov tasdiqlandi\n" .
                                "⏰ *Vaqt*: " . $order->created_at->format('d-m-Y H:i:s') . "\n\n" .
                                "👤 *Buyurtmachi*: " . $user->name . "\n" .
                                "📞 *Telefon*: " . $user->phone . "\n" .
                                "📍 *Manzil*: " . $order->address_name . "\n" .
                                "🗺️ *Xarita*: [Yandex xarita](" . "https://yandex.com/maps/?ll=" . $order->address_longlat . "&z=14" . ")\n",
                        'parse_mode' => 'Markdown'
                    ]);
                } catch (\Exception $e) {
                    // Xatolarni logga yozib qo'yish
                    \Log::error("Telegram xabar yuborishda xato: " . $e->getMessage());
                }
            }

            // Foydalanuvchi uchun xabar
            $this->telegramController->bot('sendMessage', [
                'chat_id' => $user->chat_id,
                'text' => "✅ *To‘lov tasdiqlandi!* \n\n" .
                         "🆔 *ID*: " . $order->id . "\n" .
                         "💳 *To‘lov usuli*: Payme\n" .
                         "💰 *Qiymati*: " . $transaction->amount . " so‘m\n" .
                         "🚚 *Status*: Buyurtma yuborildi\n\n" .
                         "📞 *Savollar bo‘lsa*: \n" . $sitesetting->c_phone . "\n" . // Qo‘shimcha ma'lumot
                         "⏰ *Vaqt*: " . $order->created_at->format('d-m-Y H:i:s'), // Buyurtma vaqti
                'parse_mode' => 'Markdown',
                "reply_markup" => json_encode([
                    "resize_keyboard" => true,
                    "one_time_keyboard" => true,
                    "inline_keyboard" => [
                        [["text" => "🛍 Buyurtmani ko'rish", "web_app" => [
                            'url' => "https://app.vipplast.uz/profile/orders?orderId=".$order->id."&tabOrder=all"
                        ]]],
                    ]
                ]),
            ]);

            return response()->json([
                'result' => [
                    'perform_time'  => intval($transaction->perform_time_unix),
                    'transaction'   => strval($transaction->id),
                    'state'         => intval($transaction->state),
                ]
            ]);
        } else if($transaction->state == 2){
            return response()->json([
                'result' => [
                    'perform_time'  => intval($transaction->perform_time_unix),
                    'transaction'   => strval($transaction->id),
                    'state'         => intval($transaction->state),
                ]
            ]);
        }
    }

    public function CancelTransaction($data)
    {
        $ldate = date('Y-m-d H:i:s');
        $transaction = PaymeUz::where('paycom_transaction_id', $data['params']['id'])->first();
        if(!$transaction){
            return response()->json([
                'id'    => $data['id'],
                'error' => [
                    'code' => -31003,
                    'message'   => "Транзакция не найдена"
                    ]
                ]);
        } else if($transaction->state == 1){
            $currentMillis = intval(microtime(true) * 1000);
            $transaction = PaymeUz::where('paycom_transaction_id', $data['params']['id'])->first();
            $transaction->state = -1;
            $transaction->reason = $data['params']['reason'];
            $transaction->cancel_time  = str_replace('.', '', $currentMillis);
            $transaction->update();

            $order = Order::find($transaction->order_id);
            $order->payment_status = "unpaid";
            $order->status = "ordered";
            $order->update();

            return response()->json([
                'result' => [
                    "state" => intval($transaction->state),
                    "cancel_time" => intval($transaction->cancel_time),
                    "transaction" => strval($transaction->id)
                ]
            ]);
        } else if($transaction->state == 2){
            return response()->json([
                'id'    => $data['id'],
                'error' => [
                    'code' => -31007,
                    'message'   => "Заказ выполнен. Невозможно отменить транзакцию. Товар или услуга предоставлена покупателю в полном объеме."
                    ]
                ]);
        } else if(($transaction->state == -1) or ($transaction->state == -2)){
            return response()->json([
                'result' => [
                    "state" => intval($transaction->state),
                    "cancel_time" => intval($transaction->cancel_time),
                    "transaction" => strval($transaction->id)
                ]
            ]);
        }
    }

    public function GetStatement($data){
        $from = $data['params']['from'];
        $to = $data['params']['to'];
        $transactions = PaymeUz::getTransactionsByTimeRange($from, $to);
        return response()->json([
            'result' => [
                'transactions' => PaymeTransationResource::collection($transactions),
            ],
        ]);
    }

    public function CheckTransaction($data)
    {
        $transaction = PaymeUz::where('paycom_transaction_id', $data['params']['id'])->first();
        if(!$transaction){
            return response()->json([
                'id'    => $data['id'],
                'error' => [
                    'code' => -31003,
                    'message'   => "Транзакция не найдена"
                    ]
                ]);
        } else if($transaction->state == 1){
            return response()->json([
                'result' => [
                    'create_time'   => intval($transaction->paycom_time),
                    'perform_time'  => intval($transaction->perform_time_unix),
                    'cancel_time'   => intval($transaction->cancel_time),
                    'transaction'   => strval($transaction->id),
                    'state'         => intval($transaction->state),
                    'reason'        => $transaction->reason ? intval($transaction->reason) : null,
                ]
            ]);
        } else if($transaction->state == 2){
            return response()->json([
                'result' => [
                    'create_time'   => intval($transaction->paycom_time),
                    'perform_time'  => intval($transaction->perform_time_unix),
                    'cancel_time'   => intval($transaction->cancel_time),
                    'transaction'   => strval($transaction->id),
                    'state'         => intval($transaction->state),
                    'reason'        => $transaction->reason ? intval($transaction->reason) : null,
                ]
            ]);
        } else if($transaction->state == -1){
            return response()->json([
                'result' => [
                    'create_time'   => intval($transaction->paycom_time),
                    'perform_time'  => intval($transaction->perform_time_unix),
                    'cancel_time'   => intval($transaction->cancel_time),
                    'transaction'   => strval($transaction->id),
                    'state'         => intval($transaction->state),
                    'reason'        => $transaction->reason ? intval($transaction->reason) : null,
                ]
            ]);
        } else if($transaction->state == -2){
            return response()->json([
                'result' => [
                    'create_time'   => intval($transaction->paycom_time),
                    'perform_time'  => intval($transaction->perform_time_unix),
                    'cancel_time'   => intval($transaction->cancel_time),
                    'transaction'   => strval($transaction->id),
                    'state'         => intval($transaction->state),
                    'reason'        => $transaction->reason ? intval($transaction->reason) : null,
                ]
            ]);
        }

    }
}
