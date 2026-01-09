<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        return Department::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|unique:departments,name']);
        $department = Department::create($validated);
        return response()->json($department, 201);
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate(['name' => 'required|string|unique:departments,name,' . $department->id]);
        $department->update($validated);
        return response()->json($department);
    }
}
