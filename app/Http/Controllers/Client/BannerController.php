<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;

class BannerController extends Controller
{
    private $bannerModel;

    public function __construct(Banner $bannerModel)
    {
        $this->bannerModel = $bannerModel;
    }

    public function index() {
        $banners = $this->bannerModel::get();
        return BannerResource::collection($banners);
    }
}
