<?php

namespace App\Http\Controllers\Client;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
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


    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            'items.*.combo_items' => ['array'],
            'items.*.combo_items.*' => [Rule::exists('products', 'id')],
        ]);

        // qo‘shimcha tekshiruv
        $validator->after(function ($validator) use ($request) {
            foreach ($request->items ?? [] as $index => $item) {
                $product = $this->productModel::find($item['product_id'] ?? null);
                if ($product && $product->type === 'combo') {
                    if (empty($item['combo_items'])) {
                        $validator->errors()->add("items.$index.combo_items", "Combo mahsulot uchun combo_items majburiy.");
                    }
                } else {
                    if (!empty($item['combo_items'])) {
                        $validator->errors()->add("items.$index.combo_items", "Oddiy mahsulotga combo_items berilmasligi kerak.");
                    }
                }
            }
        });

        $validated = $validator->validate();
        $order = DB::transaction(function () use ($validated) {
            $user = Auth::user();
            $order = $this->orderModel::create([
                'user_id'           => $user->id,
                'delivery_type'     => $validated['delivery_type'],
                'user_address_id'   => $validated['address_id'] ?? null,
                'branch_id'         => $validated['branch_id'] ?? null,
                'total_price'       => 0, // keyin hisoblaymiz
            ]);

            foreach ($validated['items'] as $itemData) {
                $product = $this->productModel::findOrFail($itemData['product_id']);
                $linePrice = $product->price * $itemData['quantity'];

                $orderItem = $this->orderitemModel::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $itemData['quantity'],
                    'price'      => $product->price,
                ]);
            }

            return $order;
        });
        return;
    }
}
