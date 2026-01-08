<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Task::with(['assignee', 'remarks'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'level' => 'required|integer|in:1,2,3',
            'status' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'project' => 'required|string',
            'department' => 'required|string',
            'work' => 'required|string|max:255',
            'points' => 'required|numeric|min:0.5|max:21',
            'release_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'expected_finish_date' => 'nullable|date',
        ]);

        $task = Task::create($validated);

        return response()->json($task, 201);
    }
}
