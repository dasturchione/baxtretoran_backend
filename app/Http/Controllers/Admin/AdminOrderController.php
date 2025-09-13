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

        // Frontdan string keladi → enum obyektga aylantiramiz
        $newStatus = OrderStatus::from((string) $request->status);

        // Order modelda status string bo‘lsa → enumga aylantiramiz
        $currentStatus = $order->status instanceof OrderStatus
            ? $order->status
            : OrderStatus::from($order->status);

        // Cancelled doimiy ruxsat
        if ($newStatus === OrderStatus::CANCELLED) {
            DB::transaction(function () use ($order, $newStatus) {
                $order->update(['status' => $newStatus->value]);
                $order->histories()->create(['status' => $newStatus->value]);
            });

            return $this->success("Status updated to {$newStatus->label()}", $order->fresh('histories'));
        }

        $allowed = OrderStatus::flow()[$currentStatus->value] ?? [];

        if (!in_array($newStatus, $allowed, true)) {
            return $this->error("Statusni \"{$currentStatus->label()}\" dan \"{$newStatus->label()}\" ga o‘tkazish mumkin emas!");
        }

        DB::transaction(function () use ($order, $newStatus) {
            $order->update(['status' => $newStatus->value]);
            $order->histories()->create(['status' => $newStatus->value]);
        });

        return $this->success("Status updated to {$newStatus->label()}", $order->fresh('histories'));
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
