<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\CrudService;

class EmployeeController extends Controller
{

    private $employeeModel;
    private $crudService;

    public function __construct(Employee $employeeModel, CrudService $crudService)
    {
        $this->employeeModel = $employeeModel;
        $this->crudService = $crudService;
    }

    public function index()
    {
        $employee = $this->employeeModel::all();

        return EmployeeResource::collection($employee);
    }

    public function show($id)
    {
        $users = $this->employeeModel::findOrFail($id);
        return new EmployeeResource($users);
    }

    public function store(EmployeeStoreRequest $request)
    {
        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }
        $employee = $this->crudService->CREATE_OR_UPDATE($this->employeeModel, $request->validated(), $files, null);

        return response()->json([
            'employee' => $employee,
            'roles'    => $employee->getRoleNames(),
        ], 201);
    }

    public function update(EmployeeUpdateRequest $request, $id)
    {
        $files = [];
        if ($request->hasFile('image_path')) {
            $files['image_path'] = $request->file('image_path');
        }
        $employee = $this->crudService->CREATE_OR_UPDATE($this->employeeModel, $request->validated(), $files, $id);

        return response()->json([
            'employee' => $employee,
            'roles'    => $employee->getRoleNames(),
        ]);
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return response()->json(null, 204);
    }
}
