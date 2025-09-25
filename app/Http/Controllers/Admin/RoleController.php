<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RolePermissionResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return RolePermissionResource::collection($roles);
    }

    public function store(Request $request)
    {
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'employee'
        ]);
        return new RolePermissionResource($role);
    }

    public function update(Request $request, Role $role)
    {
        $role->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'employee'
        ]);
        return new RolePermissionResource($role);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(null, 204);
    }

    public function permissions(Role $role)
    {
        return RolePermissionResource::collection($role->permissions);
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $role->syncPermissions($request->permissions);
        return response()->json($role->permissions);
    }
}
