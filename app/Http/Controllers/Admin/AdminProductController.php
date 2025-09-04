<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\CrudService;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{

    private $productModel;
    private $categoryModel;
    private $crudService;

    public function __construct(Product $productModel, Category $categoryModel, CrudService $crudService)
    {
        $this->productModel = $productModel;
        $this->categoryModel = $categoryModel;
        $this->crudService = $crudService;
    }

    public function index(Request $request)
    {
        // Query param: ?paginate=10
        $perPage = $request->query('paginate');
        $query = $this->productModel->active();

        if ($perPage) {
            $product = $query->paginate($perPage);
        } else {
            $product = $query->get();
        }
        return ProductResource::collection($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_uz' => 'required|string|max:255',
            'name_ru' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'ingredient_uz' => 'nullable|string',
            'ingredient_ru' => 'nullable|string',
            'ingredient_en' => 'nullable|string',
            'price' => 'required|numeric',
            'image_path' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:simple,combo',
            'ikpu_code' => 'required|string|max:50',
            'package_code' => 'required|string|max:50',
            'vat_percent' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'modifiers' => 'sometimes|array',
            'modifiers.*' => 'integer|exists:modifiers,id',
            'combo_items' => 'sometimes|array',
            'combo_items.*' => 'integer|exists:products,id',
        ]);


        // Agar type simple bo'lsa, combo_items kerak emas
        if ($validated['type'] === 'simple') {
            $validated['combo_items'] = [];
        }

        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }

        $product = $this->crudService->CREATE_OR_UPDATE($this->productModel, $validated, $files, null);

        // Modifiers va combo_items ni attach qilish
        if (!empty($validated['modifiers'])) {
            $product->modifiers()->sync($validated['modifiers']);
        }

        if ($validated['type'] === 'combo' && !empty($validated['combo_items'])) {
            // Avval eski combo_itemsni o'chirish (agar update bo'lsa)
            $product->comboItems()->delete();

            // Yangi combo_itemsni yaratish
            foreach ($validated['combo_items'] as $item_id) {
                $product->comboItems()->create([
                    'product_id' => $item_id,
                    'extra_price' => 0, // agar kerak bo'lsa
                ]);
            }
        }

        return response()->json($product, 201);
    }
}
