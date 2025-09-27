<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // 🔹 Bugungi widgetlar (asosiy raqamlar)
    public function widgets()
    {
        // Barcha KPIlar uchun umumiy oxirgi sanani olish
        // Users
        $lastUserDate  = User::max('created_at');
        $userDates = collect(range(0, 9))
            ->map(fn($i) => \Carbon\Carbon::parse($lastUserDate)->subDays($i)->toDateString())
            ->reverse();

        $usersRaw = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $userDates->first())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $usersDaily = $userDates->map(fn($date) => [
            'label' => $date,
            'value' => $usersRaw[$date] ?? 0,
        ])->values();

        $usersCount = $usersRaw->sum();

        // Orders
        $lastOrderDate = Order::max('created_at');
        $orderDates = collect(range(0, 9))
            ->map(fn($i) => \Carbon\Carbon::parse($lastOrderDate)->subDays($i)->toDateString())
            ->reverse();

        $ordersRaw = Order::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $orderDates->first())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $ordersDaily = $orderDates->map(fn($date) => [
            'label' => $date,
            'value' => $ordersRaw[$date] ?? 0,
        ])->values();

        $ordersCount = $ordersRaw->sum();

        // Revenue
        $lastRevenueDate = Order::whereIn('status', ['delivered', 'picked_up'])->max('created_at');
        $revenueDates = collect(range(0, 9))
            ->map(fn($i) => \Carbon\Carbon::parse($lastRevenueDate)->subDays($i)->toDateString())
            ->reverse();

        $revenueRaw = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('DATE(order_items.created_at) as date, SUM(price * quantity) as total')
            ->whereIn('orders.status', ['delivered', 'picked_up'])
            ->where('order_items.created_at', '>=', $revenueDates->first())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $revenueDaily = $revenueDates->map(fn($date) => [
            'label' => $date,
            'value' => (float) ($revenueRaw[$date] ?? 0),
        ])->values();

        $revenueTotal = $revenueDaily->sum('value');

        return response()->json([
            'users' => [
                'count' => $usersCount,
                'chart' => $usersDaily,
            ],
            'orders' => [
                'count' => $ordersCount,
                'chart' => $ordersDaily,
            ],
            'revenue' => [
                'total' => $revenueTotal,
                'chart' => $revenueDaily,
            ],
        ]);
    }

    public function ordersWidget(Request $request)
    {
        $year  = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        // Enum dan barcha statuslarni olamiz
        $statuses = array_map(fn($case) => $case->value, OrderStatus::cases());

        // Statuslar bo‘yicha umumiy count
        $statusCounts = Order::select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('status', $statuses)
            ->groupBy('status')
            ->pluck('count', 'status');
        // Enum bo‘yicha bo‘sh statuslar 0 bilan
        $statusCounts = collect($statuses)->mapWithKeys(fn($status) => [
            $status => $statusCounts[$status] ?? 0
        ]);

        $ordersCount = $statusCounts->sum();

        // Oy bo‘yicha kunlar
        // Oy bo‘yicha barcha kunlar
        $daysInMonth = $startOfMonth->daysInMonth;
        $dates = collect(range(1, $daysInMonth))
            ->map(fn($day) => \Carbon\Carbon::create($year, $month, $day)->toDateString());

        // Orders oxirgi 1 oy bo‘yicha statuslar bilan, bitta query
        $ordersRaw = Order::selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('status', $statuses)
            ->groupBy('date', 'status')
            ->get()
            ->map(fn($r) => [
                'date' => \Carbon\Carbon::parse($r->date)->toDateString(),
                'status' => $r->status instanceof \App\Enums\OrderStatus ? $r->status->value : $r->status, // <-- shu yer
                'count' => $r->count,
            ]);

        $chart = $dates->map(function ($date) use ($statuses, $ordersRaw) {
            $item = ['date' => $date];
            foreach ($statuses as $status) {
                $row = $ordersRaw->first(fn($r) => $r['date'] === $date && $r['status'] === $status);
                $item[$status] = $row['count'] ?? 0;
            }
            return $item;
        });

        $paidOrders = Order::with(['items', 'paymentMethod'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('status', [OrderStatus::DELIVERED->value, OrderStatus::PICKED_UP->value])
            ->get();

        // Jami delivered/picked_up
        $totalPaid = $paidOrders->sum(fn($o) => $o->items->sum(fn($i) => $i->price * $i->quantity));

        // Payment method bo‘yicha
        $incomeByPayment = $paidOrders->groupBy(fn($o) => $o->paymentMethod?->name ?? 'unknown')
            ->map(fn($orders) => collect($orders)->sum(fn($o) => $o->items->sum(fn($i) => $i->price * $i->quantity)));

        // Kutilayotgan tushim (delivered va picked_up bo‘lmagan, cancelled bo‘lmagan)
        $pendingOrders = Order::with('items')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', [OrderStatus::DELIVERED->value, OrderStatus::PICKED_UP->value, OrderStatus::CANCELLED->value])
            ->get();

        $pendingIncome = $pendingOrders->sum(fn($o) => $o->items->sum(fn($i) => $i->price * $i->quantity));

        return response()->json([
            'total' => $ordersCount,
            'statusCounts' => $statusCounts,
            'chart' => $chart,
            'statuses' => $statuses,
            'prices' => [
                'total' => $totalPaid,
                'click' => $incomeByPayment['click'] ?? 0,
                'payme' => $incomeByPayment['payme'] ?? 0,
                'cash' => $incomeByPayment['cash'] ?? 0,
                'pending' => $pendingIncome,
            ]
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
