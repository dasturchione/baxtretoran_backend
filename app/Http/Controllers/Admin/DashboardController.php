<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // 🔹 Bugungi widgetlar (asosiy raqamlar)
    public function widgets()
    {
        $today = Carbon::today();

        // orders bilan order_items summasi
        $orders = Order::withSum('items', 'price')
            ->whereDate('created_at', $today)
            ->get();

        $total_sales  = $orders->sum('items_sum_price');
        $total_orders = $orders->count();

        $payments = $orders->groupBy('payment_method_id')
            ->map(fn($items) => $items->sum('items_sum_price'));

        $statuses = $orders->groupBy('status')
            ->map(fn($items) => $items->count());

        return response()->json([
            'total_sales'  => $total_sales,
            'total_orders' => $total_orders,
            'payments'     => $payments,
            'statuses'     => $statuses,
        ]);
    }

    // 🔹 So‘nggi buyurtmalar
    public function recentOrders()
    {
        $orders = Order::latest()->take(10)->get();
        return OrderResource::collection($orders);
    }

    // 🔹 Eng ko‘p sotilgan mahsulotlar
    public function topProducts()
    {
        $products = OrderItem::select('product_id', DB::raw('SUM(quantity) as qty'))
            ->with('product:id,name,price')
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->take(5)
            ->get();

        return response()->json($products);
    }

    // 🔹 Sotuvlar charti (filter bilan)
    public function chart(Request $request)
    {
        $period = $request->get('period', '7days'); // 7days, 30days, monthly

        $query = Order::withSum('items', 'price'); // order_items.price summasi

        if ($period === '7days') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($period === '30days') {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        if ($period === 'monthly') {
            $result = $query->get()
                ->groupBy(fn($order) => $order->created_at->format('Y-m'))
                ->map(fn($orders) => $orders->sum('items_sum_price'));

            $labels = $result->keys();
            $data   = $result->values();
        } else {
            $result = $query->get()
                ->groupBy(fn($order) => $order->created_at->format('Y-m-d'))
                ->map(fn($orders) => $orders->sum('items_sum_price'));

            $labels = $result->keys();
            $data   = $result->values();
        }

        return response()->json([
            'labels' => $labels,
            'data'   => $data,
        ]);
    }
}
