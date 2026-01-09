<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Gate::authorize('viewAny', Project::class);
        return response()->json(Project::all());
    }
}
