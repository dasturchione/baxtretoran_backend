<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModifierStoreRequest;
use App\Http\Requests\ModifierUpdateRequest;
use App\Http\Resources\ModifierResource;
use App\Models\Modifier;
use App\Services\CrudService;
use Illuminate\Http\Request;

class AdminModifierController extends Controller
{

    private $modifierModel;
    private $crudService;

    public function __construct(Modifier $modifierModel, CrudService $crudService)
    {
        $this->modifierModel = $modifierModel;
        $this->crudService = $crudService;
    }

    public function index(Request $request)
    {
        $perPage = $request->query('paginate');
        $query = $this->modifierModel->active()->withCount('products');

        if ($perPage) {
            $modifier = $query->paginate($perPage);
        } else {
            $modifier = $query->get();
        }
        return ModifierResource::collection($modifier);
    }

    public function show($id)
    {
        $modifier = $this->modifierModel->findOrFail($id);

        return new ModifierResource($modifier);
    }

    public function store(ModifierStoreRequest $request)
    {

        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }
        $modifier = $this->crudService->CREATE_OR_UPDATE($this->modifierModel, $request->validated(), $files, null);

        return response()->json([
            'status'    => "success",
            'message'   => "Product created"
        ]);
    }

    public function update(ModifierUpdateRequest $request, $id)
    {

        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }
        $modifier = $this->crudService->CREATE_OR_UPDATE($this->modifierModel, $request->validated(), $files, $id);

        return response()->json([
            'status'    => "success",
            'message'   => "Product created"
        ]);
    }
}
