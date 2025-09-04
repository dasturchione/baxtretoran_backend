<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return Permission::all();
    }

    public function store(Request $request)
    {
        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'employee'
        ]);

        return response()->json($permission, 201);
    }

    public function update(Request $request, Permission $permission)
    {
        $permission->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'employee'
        ]);
        return response()->json($permission);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json(null, 204);
    }
}
