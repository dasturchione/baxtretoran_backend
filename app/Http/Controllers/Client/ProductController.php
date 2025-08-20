<?php

namespace App\Http\Controllers\Client;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
            $product = Product::where('slug', $slug)->with([
                'comboItems.product.category',
            ])->firstOrFail();

            return new ProductShowResource($product);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Product not found'], 404);
        }
    }
}
