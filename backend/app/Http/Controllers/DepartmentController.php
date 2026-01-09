<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Gate::authorize('viewAny', Department::class);
        return response()->json(Department::all());
    }
}
