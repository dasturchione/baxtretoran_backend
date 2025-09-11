<?php

namespace App\Http\Controllers\Client;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;

class UserOrderController extends Controller
{

    protected $productModel;
    protected $orderModel;
    protected $orderitemModel;
    protected $orderService;

    public function __construct(Product $productModel, Order $orderModel, OrderItem $orderitemModel, OrderService $orderService)
    {
        $this->productModel = $productModel;
        $this->orderModel = $orderModel;
        $this->orderitemModel = $orderitemModel;
        $this->orderService = $orderService;
    }

    public function index()
    {
        $orders = Auth::user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function show($id)
    {
        $order = Auth::user()
            ->orders()
            ->with(['items.product', 'address', 'paymentMethod'])
            ->findOrFail($id);

        return new OrderResource($order);
    }


    public function store(OrderStoreRequest $request)
    {
        $order = $this->orderService->create($request->validated());

        return response()->json([
            'message' => 'Buyurtma muvaffaqiyatli yaratildi.',
            'data'    => $order,
        ]);
    }

    public function cancel($id)
    {
        $order = Auth::user()->orders()->findOrFail($id);

        if (! $this->orderService->cancel($order)) {
            return response()->json(['message' => 'Bu buyurtmani bekor qilish mumkin emas.'], 400);
        }

        return response()->json([
            'message' => 'Buyurtma muvaffaqiyatli bekor qilindi.',
            'order'   => $order,
        ]);
    }
}
