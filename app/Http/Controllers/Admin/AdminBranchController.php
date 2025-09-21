<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Models\BranchServiceArea;
use App\Services\CrudService;
use Illuminate\Http\Request;

class AdminBranchController extends Controller
{
    private $branchModel;
    private $branchServiceAreaModel;
    private $crudService;

    public function __construct(Branch $branchModel, BranchServiceArea $branchServiceAreaModel, CrudService $crudService)
    {
        $this->branchModel = $branchModel;
        $this->branchServiceAreaModel = $branchServiceAreaModel;
        $this->crudService = $crudService;
    }

    public function index(Request $request)
    {
        // Query param: ?paginate=10
        $perPage = $request->query('paginate');
        $query = $this->branchModel->with('serviceAreas');

        if ($perPage) {
            $branch = $query->paginate($perPage);
        } else {
            $branch = $query->get();
        }
        return BranchResource::collection($branch);
    }

    public function show($id)
    {
        $branch = $this->branchModel::with('serviceAreas')->findOrFail($id);

        return new BranchResource($branch);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lat'  => 'required|numeric',
            'long' => 'required|numeric',
            'is_active' => 'boolean',
            'work_time_start' => 'required|date_format:H:i',
            'work_time_end'   => 'required|date_format:H:i',
        ]);

        $branch = Branch::create($validated);

        return response()->json([
            'message' => 'Branch created successfully',
            'data' => $branch
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lat'  => 'required|numeric',
            'long' => 'required|numeric',
            'is_active' => 'boolean',
            'work_time_start' => 'required|date_format:H:i',
            'work_time_end'   => 'required|date_format:H:i',
        ]);

        $branch = Branch::findOrFail($id);
        $branch->update($validated);

        return response()->json([
            'message' => 'Branch updated successfully',
            'data' => $branch
        ]);
    }


    public function storeArea(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'coordinates' => 'required|array|min:3',
            'coordinates.*' => 'array|size:2' // [lat, long]
        ]);

        $area = $this->branchServiceAreaModel::updateOrCreate(
            ['branch_id' => $data['branch_id']], // mavjud bo'lsa yangilaydi
            ['coordinates' => $data['coordinates']]
        );

        return response()->json([
            'success' => true,
            'data' => $area
        ]);
    }
}
