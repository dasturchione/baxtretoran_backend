<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymeService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\PaymeUz;
use App\Http\Resources\PaymeTransationResource;

class PaymeController extends Controller
{
    protected $paymeService;

    public function __construct(PaymeService $paymeService)
    {
        $this->paymeService = $paymeService;
    }

    public function handlePayme (Request $request){
        $data = $request->all();
        $method = $data['method'] ?? null;

        if (method_exists($this->paymeService, $method)) {
            $response = $this->paymeService->$method($data);
        } else {
            $response = response()->json(['error' => 'Unknown method']);
        }

        return $response;
    }

    public function generateUrl($id)
    {
        $order = Order::find($id );
        if ($order) {
            if($order->payment_status == "paid"){
                return response()->json([
                    'message' => "Order already paid"
                ], 409);
            }
            if($order->status == "waitingpay"){
                $order->status = "created";
            }
            $order->payment_method_id = 2;
            $order->update();
            $orderItems = OrderItem::where('order_id', $order->id)
                        ->selectRaw('SUM(price * quantity) as totalPrice, SUM(discount * quantity) as totalDiscount')
                        ->first();

            // Promo kod narxini olish
            $promoPrice = $order->promo_code
            ? PromoCode::where('id', $order->promo_code)->value('price')
            : 0;

            // Yakuniy summa hisoblash
            $totalPrice = $orderItems->totalPrice ?? 0;
            $totalDiscount = $orderItems->totalDiscount ?? 0;
            $totalAmount = ($totalPrice - $totalDiscount - $promoPrice) * 100;
        }else{
            return response()->json([
                'message'   => "Order not found"
            ], 404);
        }

        $merchantId = '675179bdd33fb8548ced73da';
        $paymeUrl  = "https://checkout.paycom.uz/";
        $paymeUrl .= base64_encode("m=$merchantId;ac.order_id=$id;a=$totalAmount");

        return response()->json([
            'url' => $paymeUrl
        ]);
    }

    public function transactions(Request $request)
    {
        $search = $request->input('search');
        if ($search) {
            $searchTerms = explode(' ', $search);

            $query = PaymeUz::query();

            foreach ($searchTerms as $term) {
                // Har bir qidiruvni dinamika asosida tekshiramiz
                $query->where(function ($query) use ($term) {
                    // order_id yoki amount, created_at bo'yicha qidirish
                    $query->orWhere('paycom_transaction_id', 'like', '%' . $term . '%')
                          ->orWhere('order_id', 'like', '%' . $term . '%')
                          ->orWhere('amount', 'like', '%' . $term . '%')
                          ->orWhereDate('created_at', 'like', '%' . $term . '%');
                });
            }

            // So'rovni bajarish va sahifalash
            $transactions = $query->latest()->paginate(10);

            return PaymeTransationResource::collection($transactions);
        }

        // Agar qidiruv bo'lmasa, barcha ma'lumotlarni qaytarish
        $transactions = PaymeUz::latest()->paginate(10);

        return PaymeTransationResource::collection($transactions);
    }


}
