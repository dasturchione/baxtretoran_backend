<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliverStoreRequest;
use App\Http\Requests\DeliverUpdateRequest;
use App\Http\Resources\DeliverResource;
use App\Models\Deliver;
use App\Services\CrudService;
use Illuminate\Http\Request;

class DeliverController extends Controller
{

    private $deliverModel;
    private $crudService;

    public function __construct(Deliver $deliverModel, CrudService $crudService)
    {
        $this->deliverModel = $deliverModel;
        $this->crudService = $crudService;
    }

    public function index(Request $request)
    {
        // Query param: ?paginate=10
        $perPage = $request->query('paginate');
        $query = $this->deliverModel;

        if ($perPage) {
            $deliver = $query->paginate($perPage);
        } else {
            $deliver = $query->get();
        }
        return DeliverResource::collection($deliver);
    }

    public function show($id)
    {
        // Query param: ?paginate=10
        $deliver = $this->deliverModel::findOrFail($id);

        return new DeliverResource($deliver);
    }

    public function store(DeliverStoreRequest $request)
    {
        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }

        $deliver = $this->crudService->CREATE_OR_UPDATE($this->deliverModel, $request->validated(), $files, null);

        return response()->json($deliver);
    }

    public function update(DeliverUpdateRequest $request, $id)
    {
        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }

        $deliver = $this->crudService->CREATE_OR_UPDATE($this->deliverModel, $request->validated(), $files, $id);

        return response()->json($deliver);
    }
}
