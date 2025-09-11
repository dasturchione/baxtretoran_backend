<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminOrderController extends Controller
{

    protected Order $orderModel;

    public function __construct(Order $orderModel)
    {
        $this->orderModel = $orderModel;
    }

    /**
     * DRY uchun helper: orderni topish
     */
    protected function findOrderOrFail(int $id): Order
    {
        return $this->orderModel->with(['user', 'items.product', 'branch', 'address', 'courier'])
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

    public function index(Request $request)
    {
        $orders = $this->orderModel
            ->with(['user', 'items.product', 'branch', 'address'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
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

        $newStatus = OrderStatus::from($request->status);

        $order->update(['status' => $newStatus->value]);

        return $this->success("Status updated to {$newStatus->label()}", $order);
    }

    public function assignCourier(Request $request, $id)
    {
        $order = $this->findOrderOrFail($id);

        if ($order->delivery_type !== 'delivery') {
            return response()->json(['message' => 'Faqat delivery buyurtmaga kuryer biriktiriladi.'], 400);
        }

        $request->validate([
            'courier_id' => ['required', Rule::exists('users', 'id')->where('role', 'courier')],
        ]);

        DB::transaction(function () use ($order, $request) {
            $order->update([
                'courier_id' => $request->courier_id,
                'status'     => OrderStatus::DELIVERING->value,
            ]);
        });

        return $this->success('Kuryer muvaffaqiyatli biriktirildi.', $order->load('courier'));
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
