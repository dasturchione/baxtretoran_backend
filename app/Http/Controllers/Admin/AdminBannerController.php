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
        $query = $this->bannerModel;

        if ($perPage) {
            $banner = $query->paginate($perPage);
        } else {
            $banner = $query->get();
        }
        return BannerResource::collection($banner);
    }

    public function show($id){
        $banner = $this->bannerModel::findOrFail($id);

        return new BannerResource($banner);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'image_path' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link'       => 'nullable|string',
            'sort'       => 'nullable|integer',
            'is_active'  => 'boolean',
        ]);

        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }

        $banner = $this->crudService->CREATE_OR_UPDATE($this->bannerModel, $data, $files, null);

        return response()->json([
            'message' => 'Banner muvaffaqiyatli qo‘shildi',
            'data'    => $banner
        ], 201);
    }

    // 🟢 Banner yangilash
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link'       => 'nullable|string',
            'sort'       => 'nullable|integer',
            'is_active'  => 'boolean',
        ]);

        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }

        $banner = $this->crudService->CREATE_OR_UPDATE($this->bannerModel, $data, $files, $id);

        return response()->json([
            'message' => 'Banner yangilandi',
            'data'    => $banner
        ]);
    }

    // 🟢 Banner o‘chirish
    public function destroy(Banner $banner)
    {
        $banner->delete();

        return response()->json([
            'message' => 'Banner o‘chirildi'
        ]);
    }
}
