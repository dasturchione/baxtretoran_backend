<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{

    public function index () {
        $employee = Employee::all();

        return $employee;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:employees,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|string|exists:roles,name',
        ]);

        $employee = Employee::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $employee->syncRoles([$validated['role']]); // bitta role beramiz

        return response()->json([
            'employee' => $employee,
            'roles'    => $employee->getRoleNames(),
        ], 201);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'email'    => [
                'sometimes',
                'required',
                'email',
                Rule::unique('employees')->ignore($employee->id),
            ],
            'password' => 'nullable|string|min:6', // kelmasa o‘zgarmaydi
            'role'     => 'sometimes|required|string|exists:roles,name',
        ]);

        $data = $request->only(['name', 'email']);
        if (!empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }

        $employee->update($data);

        if (!empty($validated['role'])) {
            $employee->syncRoles([$validated['role']]);
        }

        return response()->json([
            'employee' => $employee,
            'roles'    => $employee->getRoleNames(),
        ]);
    }
}
