<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCommentResource;
use App\Models\Product;
use App\Models\ProductComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductCommentController extends Controller
{
    // 🔹 Izoh qoldirish
    public function store(Request $request, $productId)
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $product = Product::findOrFail($productId);

        $comment = ProductComment::create([
            'product_id' => $product->id,
            'user_id'    => Auth::id(),
            'rating'     => $request->rating,
            'comment'    => $request->comment,
        ]);

        return new ProductCommentResource($comment);
    }

    // 🔹 Mahsulot izohlarini olish
    public function index($id)
    {
        $product = Product::with('comments.user')->findOrFail($id);
        return ProductCommentResource::collection($product->comments);
    }
}
