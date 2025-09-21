<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminOrderController extends Controller
{

    protected $orderModel;
    protected $orderService;

    public function __construct(Order $orderModel, OrderService $orderService)
    {
        $this->orderModel = $orderModel;
        $this->orderService = $orderService;
    }

    /**
     * DRY uchun helper: orderni topish
     */
    protected function findOrderOrFail(int $id): Order
    {
        return $this->orderModel->with(['user', 'items.product', 'branch', 'address', 'deliver', 'histories'])
            ->findOrFail($id);
    }

    /**
     * DRY uchun helper: json response
     */
    protected function success($message, $data = [])
    {
        return response()->json([
            'message' => $message,
            'data'    => $data,
        ]);
    }

    protected function error($message, $data = [])
    {
        return response()->json([
            'message' => $message,
            'data'    => $data,
        ], 422);
    }

    public function index(Request $request)
    {
        $orders = $this->orderModel
            ->with(['user', 'items.product', 'branch', 'address'])
            ->filter()
            ->latest()
            ->paginate(20);

        return OrderResource::collection($orders);
    }


    public function show($id)
    {
        $order = $this->findOrderOrFail($id);
        return new OrderResource($order);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = $this->findOrderOrFail($id);

        $request->validate([
            'status' => ['required', Rule::in(array_column(OrderStatus::cases(), 'value'))],
        ]);

        $newStatus = OrderStatus::from((string) $request->status);

        try {
            $result = $this->orderService->updateStatus($order, $newStatus);
            return $this->success($result['message'], $result['order']);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    public function assignCourier(Request $request)
    {
        $validated = $request->validate([
            'order_ids'   => ['required', 'array', 'min:1'],
            'order_ids.*' => [
                'integer',
                Rule::exists('orders', 'id')->where(fn($q) => $q->where('delivery_type', 'delivery')),
            ],
            'delivery_id' => [
                'required',
                'integer',
                Rule::exists('delivers', 'id')->where(fn($q) => $q->where('status', 'free')),
            ],
        ]);

        DB::transaction(function () use ($validated) {
            Order::whereIn('id', $validated['order_ids'])
                ->update([
                    'deliver_id' => $validated['delivery_id'],
                    'status'     => OrderStatus::DELIVERING->value,
                ]);
        });

        return response()->json(['message' => 'Orders successfully assigned!']);
    }

    public function cancel($id)
    {
        $order = $this->findOrderOrFail($id);

        if ($order->status === OrderStatus::CANCELLED->value) {
            return response()->json(['message' => 'Buyurtma allaqachon bekor qilingan.'], 400);
        }

        $order->update(['status' => OrderStatus::CANCELLED->value]);

        return $this->success('Buyurtma bekor qilindi.', $order);
    }
}
