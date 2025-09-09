<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CrudService;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{

    private $categoryModel;
    private $crudService;

    public function __construct(Category $categoryModel, CrudService $crudService)
    {
        $this->categoryModel = $categoryModel;
        $this->crudService = $crudService;
    }

    public function index(Request $request)
    {
        // Query param: ?paginate=10
        $perPage = $request->query('paginate');
        $query = $this->categoryModel->withCount('products');

        if ($perPage) {
            $category = $query->paginate($perPage);
        } else {
            $category = $query->get();
        }
        return CategoryResource::collection($category);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'name_uz'       => 'required|string|max:255',
            'name_ru'       => 'required|string|max:255',
            'name_en'       => 'required|string|max:255',
        ]);

        $category = $this->crudService->CREATE_OR_UPDATE($this->categoryModel, $validated, [], null);
        return new CategoryResource($category);
    }

    public function show($id)
    {
        $category = $this->categoryModel::findOrFail($id);
        return new CategoryResource($category);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name_uz'       => 'required|string|max:255',
            'name_ru'       => 'required|string|max:255',
            'name_en'       => 'required|string|max:255',
        ]);

        $category = $this->crudService->CREATE_OR_UPDATE($this->categoryModel, $validated, [], $id);

        return new CategoryResource($category);
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategoriya topilmadi.'
            ], 404);
        }

        $category->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Kategoriya muvaffaqiyatli o‘chirildi.'
        ], 200);
    }
}
