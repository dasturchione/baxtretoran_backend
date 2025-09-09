<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{

    private $orderModel;

    public function __construct(Order $orderModel)
    {
        $this->orderModel = $orderModel;
    }

    public function index(Request $request)
    {
        $perPage = $request->query('paginate');
        $query = $this->orderModel->filter()->with('items.product');

        if ($perPage) {
            $orders = $query->paginate($perPage);
        } else {
            $orders = $query->get();
        }
        return OrderResource::collection($orders);
    }
}
