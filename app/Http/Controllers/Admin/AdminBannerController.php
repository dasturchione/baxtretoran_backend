<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use App\Services\CrudService;
use Illuminate\Http\Request;

class AdminBannerController extends Controller
{

    private $bannerModel;
    private $crudService;

    public function __construct(Banner $bannerModel, CrudService $crudService)
    {
        $this->bannerModel = $bannerModel;
        $this->crudService = $crudService;
    }

    public function index(Request $request)
    {
        // Query param: ?paginate=10
        $perPage = $request->query('paginate');
        $query = $this->bannerModel->active();

        if ($perPage) {
            $banner = $query->paginate($perPage);
        } else {
            $banner = $query->get();
        }
        return BannerResource::collection($banner);
    }

}
