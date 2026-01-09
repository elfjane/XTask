<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Project;

class ProjectController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Gate::authorize('viewAny', Project::class);
        return Project::all();
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('create', Project::class);
        $validated = $request->validate(['name' => 'required|string|unique:projects,name']);
        $project = Project::create($validated);
        return response()->json($project, 201);
    }

    public function update(Request $request, Project $project)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $project);
        $validated = $request->validate(['name' => 'required|string|unique:projects,name,' . $project->id]);
        $project->update($validated);
        return response()->json($project);
    }
}
