<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        return Role::with('permissions')->get();
    }

    public function store(Request $request)
    {
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'employee'
        ]);
        return response()->json($role, 201);
    }

    public function update(Request $request, Role $role)
    {
        $role->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'employee'
        ]);
        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(null, 204);
    }

    public function permissions(Role $role)
    {
        return response()->json($role->permissions);
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $role->syncPermissions($request->permissions);
        return response()->json($role->permissions);
    }
}
