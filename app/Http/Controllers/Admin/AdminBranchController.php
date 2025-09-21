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
        $query = $this->branchModel->active()->with('serviceAreas');

        if ($perPage) {
            $branch = $query->paginate($perPage);
        } else {
            $branch = $query->get();
        }
        return BranchResource::collection($branch);
    }

    public function show($id){
        $branch = $this->branchModel::with('serviceAreas')->findOrFail($id);

        return new BranchResource($branch);
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
