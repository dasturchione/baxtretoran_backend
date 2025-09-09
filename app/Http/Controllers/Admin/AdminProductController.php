<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\AdminProductShowResource;
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

    public function show($id)
    {
        $product = $this->productModel->with([
            'comboItems.product.category',
            'modifiers'
        ])->findOrFail($id);

        return new AdminProductShowResource($product);
    }

    public function store(ProductStoreRequest $request)
    {
        $validated = $request->validated();

        // Agar type simple bo'lsa, combo_items kerak emas
        if ($validated['type'] === 'simple') {
            $validated['combo_items'] = [];
        }

        // Faylni ajratish
        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }

        // CREATE OR UPDATE
        $product = $this->crudService->CREATE_OR_UPDATE($this->productModel, $validated, $files, null);

        // ✅ modifiers: [{ modifier_id: 1 }, { modifier_id: 2 }]
        if (!empty($validated['modifiers'])) {
            $modifierIds = collect($validated['modifiers'])->pluck('modifier_id')->toArray();
            $product->modifiers()->sync($modifierIds);
        }

        // ✅ combo_items: [{ product_id: 10 }, { product_id: 12 }]
        if ($validated['type'] === 'combo' && !empty($validated['combo_items'])) {
            $product->comboItems()->delete();

            foreach ($validated['combo_items'] as $item) {
                $product->comboItems()->create([
                    'product_id' => $item['product_id'],
                    'extra_price' => 0, // kerak bo‘lsa dynamic qiling
                ]);
            }
        }

        return response()->json($product, 201);
    }

    public function update(ProductUpdateRequest $request, $id)
    {
        $validated = $request->validated();

        // Agar type simple bo'lsa, combo_items kerak emas
        if ($validated['type'] === 'simple') {
            $validated['combo_items'] = [];
        }

        // Faylni ajratish
        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }

        // CREATE OR UPDATE
        $product = $this->crudService->CREATE_OR_UPDATE($this->productModel, $validated, $files, $id);

        // ✅ modifiers: [{ modifier_id: 1 }, { modifier_id: 2 }]
        if (!empty($validated['modifiers'])) {
            $modifierIds = collect($validated['modifiers'])->pluck('modifier_id')->toArray();
            $product->modifiers()->sync($modifierIds);
        }

        // ✅ combo_items: [{ product_id: 10 }, { product_id: 12 }]
        if ($validated['type'] === 'combo' && !empty($validated['combo_items'])) {
            $product->comboItems()->delete();

            foreach ($validated['combo_items'] as $item) {
                $product->comboItems()->create([
                    'product_id' => $item['product_id'],
                    'extra_price' => 0, // kerak bo‘lsa dynamic qiling
                ]);
            }
        }

        return response()->json($product, 201);
    }
}
