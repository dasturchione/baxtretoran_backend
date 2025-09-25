<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceDetailResource;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\CrudService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{

    private $serviceModel;
    private $crudService;

    public function __construct(Service $serviceModel, CrudService $crudService)
    {
        $this->serviceModel = $serviceModel;
        $this->crudService = $crudService;
    }


    public function index(Request $request)
    {
        $perPage = $request->query('paginate');
        $query = $this->serviceModel;

        if ($perPage) {
            $content = $query->paginate($perPage);
        } else {
            $content = $query->get();
        }

        return ServiceResource::collection($content);
    }

    public function show($id)
    {
        $content = $this->serviceModel::findOrFail($id);
        return new ServiceDetailResource($content);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_uz' => 'required|string|max:255',
            'name_ru' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_uz' => 'required|string',
            'description_ru' => 'required|string',
            'description_en' => 'required|string',
            'image_path' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }

        $content = $this->crudService->CREATE_OR_UPDATE($this->serviceModel, $validated, $files, null);

        return new ServiceDetailResource($content);
    }

    // PUT /api/contents/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name_uz' => 'sometimes|string|max:255',
            'name_ru' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'description_uz' => 'sometimes|string',
            'description_ru' => 'sometimes|string',
            'description_en' => 'sometimes|string',
            'image_path' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
            'config' => 'sometimes|array',
            'content' => 'sometimes|array',
        ]);

        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }

        $content = $this->crudService->CREATE_OR_UPDATE($this->serviceModel, $validated, $files, $id);

        return new ServiceResource($content);
    }

    // DELETE /api/contents/{id}
    public function destroy($id)
    {
        $content = $this->serviceModel::findOrFail($id);
        $content->delete();

        return response()->json(['message' => 'Content deleted successfully']);
    }
}
