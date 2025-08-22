<?php

namespace App\Http\Controllers\Client;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductShowResource;
use App\Http\Resources\ProductGroupResource;

class ProductController extends Controller
{

    private $productModel;
    private $categoryModel;

    public function __construct(Product $productModel, Category $categoryModel)
    {
        $this->productModel = $productModel;
        $this->categoryModel = $categoryModel;
    }

    public function index(Request $request)
    {
        $products = $this->productModel::with(['category', 'modifiers'])
            ->orderBy('category_id')
            ->get();

        $grouped = $products->groupBy('category_id')->map(function ($items) {
            return [
                'category' => $items->first()->category,
                'items'    => $items
            ];
        })->values();

        return ProductGroupResource::collection($grouped);
    }

    public function show($slug)
    {
        try {
            $product = $this->productModel::where('slug', $slug)->with([
                'comboItems.product.category',
            ])->firstOrFail();

            return new ProductShowResource($product);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Product not found'], 404);
        }
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:50',
        ]);

        $name = $validated['q'];

        $products = $this->productModel::where(function ($query) use ($name) {
            $query->where('name_uz', 'like', "%{$name}%")
                ->orWhere('name_ru', 'like', "%{$name}%")
                ->orWhere('name_en', 'like', "%{$name}%");
        })
            ->with(['comboItems.product.category'])
            ->get();

        return ProductResource::collection($products);
    }

    public function recommend($productId)
    {
        $product = $this->productModel::with('recommendations.category')->findOrFail($productId);

        // 1️⃣ Admin belgilagan recommend mahsulotlar
        $recommended = $product->recommendations->where('id', '!=', $product->id);

        // 2️⃣ Agar admin hech narsa belgilamagan bo‘lsa → fallback
        if ($recommended->isEmpty()) {
            $recommended = $this->productModel::where('id', '!=', $product->id) // o‘zini chiqarib tashlaymiz
                ->inRandomOrder()
                ->limit(5)
                ->get();
        }

        return ProductResource::collection($recommended);
    }
}
