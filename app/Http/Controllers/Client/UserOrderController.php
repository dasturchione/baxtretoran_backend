<?php

namespace App\Http\Controllers\Client;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Jobs\SendWebPushJob;
use Illuminate\Support\Facades\Validator;

class UserOrderController extends Controller
{

    private $productModel;
    private $orderModel;
    private $orderitemModel;

    public function __construct(Product $productModel, Order $orderModel, OrderItem $orderitemModel)
    {
        $this->productModel = $productModel;
        $this->orderModel = $orderModel;
        $this->orderitemModel = $orderitemModel;
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


    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method_id' => 'required',
            'delivery_type' => ['required', Rule::in(['delivery', 'takeaway'])],
            'address_id' => [
                Rule::requiredIf(fn() => $request->delivery_type === 'delivery'),
                Rule::exists('user_addresses', 'id')->where(fn($q) => $q->where('user_id', Auth::id())),
            ],
            'branch_id' => [
                Rule::requiredIf(fn() => $request->delivery_type === 'takeaway'),
                Rule::exists('branches', 'id'),
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],

            // combo_items optional bo‘lib turadi, lekin keyin tekshiramiz
            'items.*.combo_items' => ['nullable', 'array'],
            'items.*.combo_items.*' => [Rule::exists('products', 'id')],
        ]);

        // qo‘shimcha tekshiruv
        $validator->after(function ($validator) use ($request) {
            foreach ($request->items ?? [] as $index => $item) {
                $product = $this->productModel::find($item['product_id'] ?? null);
                if ($product && $product->type === 'combo') {
                    if (empty($item['combo_items'])) {
                        $validator->errors()->add("items.$index.combo_items", "combo_items is required for combo products.");
                    }
                } else {
                    if (!empty($item['combo_items'])) {
                        $validator->errors()->add("items.$index.combo_items", "A normal product should not be given combo_items.");
                    }
                }
            }
        });

        $validated = $validator->validate();
        $order = DB::transaction(function () use ($validated) {
            $user = Auth::user();
            $order = $this->orderModel::create([
                'user_id'           => $user->id,
                'payment_method_id' => $validated['payment_method_id'],
                'delivery_type'     => $validated['delivery_type'],
                'user_address_id'   => $validated['address_id'] ?? null,
                'branch_id'         => $validated['branch_id'] ?? null,
                'status'            => $validated['payment_method_id'] == 1
                    ? OrderStatus::ORDERED->value
                    : OrderStatus::PAYMENT_PROCESS->value,
            ]);

            foreach ($validated['items'] as $itemData) {
                $product = $this->productModel::findOrFail($itemData['product_id']);
                $linePrice = $product->price * $itemData['quantity'];

                $orderItem = $this->orderitemModel::create([
                    'order_id'      => $order->id,
                    'product_id'    => $product->id,
                    'quantity'      => $itemData['quantity'],
                    'combo_items'   => $itemData['combo_items'] ? json_encode($itemData['combo_items']) : null,
                    'price'         => $product->price,
                ]);
            }
            if ($order->status === OrderStatus::ORDERED->value) {
                SendWebPushJob::dispatch(1, 'Yangi buyurtma', "Buyurtmachi", '/orders');
            }
            return $order->load('items');
        });
        return response()->json(['message' => 'Order created successfully', 'data' => $order]);
    }

    public function cancel($id)
    {
        $order = Auth::user()
            ->orders()
            ->where('id', $id)
            ->firstOrFail();

        $canCancel = false;

        if ($order->payment_method_id === 1 && $order->status === OrderStatus::ORDERED->value) {
            $canCancel = true;
        }

        if ($order->payment_method_id !== 1 && $order->status === OrderStatus::PAYMENT_FAILED->value) {
            $canCancel = true;
        }

        if (! $canCancel) {
            return response()->json([
                'message' => 'Bu buyurtmani bekor qilish mumkin emas.'
            ], 400);
        }

        $order->update([
            'status' => OrderStatus::CANCELLED->value
        ]);

        return response()->json([
            'message' => 'Buyurtma muvaffaqiyatli bekor qilindi.',
            'order'   => $order
        ]);
    }
}
