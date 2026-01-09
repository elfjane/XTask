<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Project;

class ProjectController extends Controller
{
    public function index()
    {
        return Project::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|unique:projects,name']);
        $project = Project::create($validated);
        return response()->json($project, 201);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate(['name' => 'required|string|unique:projects,name,' . $project->id]);
        $project->update($validated);
        return response()->json($project);
    }
}
