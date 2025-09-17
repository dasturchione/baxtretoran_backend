<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\SiteInfoResource;
use App\Models\SiteInfo;
use App\Services\CrudService;
use Illuminate\Http\Request;

class SiteInfoController extends Controller
{
    protected $siteInfoModel;
    protected $crudService;

    public function __construct(SiteInfo $siteInfoModel, CrudService $crudService)
    {
        $this->siteInfoModel = $siteInfoModel;
        $this->crudService = $crudService;
    }
    // Bitta yozuv bo‘ladi (singleton)
    public function show()
    {
        return new SiteInfoResource(SiteInfo::first());
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'logo'      => 'nullable|string',
            'phone'     => 'required|string',
            'email'     => 'required|email',
            'address'   => 'required|string',
            'facebook'  => 'required|url',
            'instagram' => 'required|url',
            'telegram'  => 'required|url',
            'youtube'   => 'required|url',
        ]);
        $files = [];
        if ($request->hasFile('logo')) {
            $files['logo'] = $request->file('logo');
        }
        $siteInfo = $this->crudService->CREATE_OR_UPDATE($this->siteInfoModel, $data, $files, 1);

        return response()->json($siteInfo);
    }
}
