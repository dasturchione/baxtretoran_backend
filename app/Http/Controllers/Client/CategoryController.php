<?php

namespace App\Http\Controllers\Client;

use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller {

    private $categoryModel;

    public function __construct(Category $categoryModel)
    {
        $this->categoryModel = $categoryModel;
    }

    public function index(){
        $category = $this->categoryModel::active()->get();
        return  CategoryResource::collection($category);
    }
}
