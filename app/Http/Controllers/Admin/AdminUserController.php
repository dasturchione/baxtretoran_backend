<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\CrudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminUserController extends Controller
{
    private $userModel;
    private $crudService;

    public function __construct(User $userModel, CrudService $crudService)
    {
        $this->userModel = $userModel;
        $this->crudService = $crudService;
    }

    public function index(Request $request)
    {
        // Query param: ?paginate=10
        $perPage = $request->query('paginate');

        if ($perPage) {
            // paginate qilingan natija
            $users = $this->userModel::paginate($perPage);
        } else {
            // hamma userlarni qaytarish
            $users = $this->userModel::all();
        }

        return UserResource::collection($users);
    }

    public function show($id)
    {
        $users = $this->userModel::findOrFail($id);


        return new UserResource($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'birthday'   => 'required|date',
            'phone'      => 'required',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }

        $user = $this->crudService->CREATE_OR_UPDATE($this->userModel, $validated, $files, null);

        return new UserResource($user);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'birthday'   => 'required|date',
            'phone'      => 'required',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }

        $user = $this->crudService->CREATE_OR_UPDATE($this->userModel, $validated, $files, $id);

        return new UserResource($user);
    }
}
