<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;

class BranchController extends Controller
{

    private $branchModel;

    public function __construct(Branch $branchModel)
    {
        $this->branchModel = $branchModel;
    }

    public function index()
    {
        $branch = $this->branchModel::active()->get();
        return  BranchResource::collection($branch);
    }
}
