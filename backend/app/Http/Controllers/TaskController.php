<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('viewAny', Task::class);

        $user = $request->user();
        $query = Task::with(['assignee', 'remarks']);

        if ($user->isExecutor()) {
            $query->where(function ($q) use ($user) {
                $q->where('department', $user->department?->name)
                    ->orWhere('user_id', $user->id)
                    ->orWhere('related_personnel', 'like', '%' . $user->name . '%');
            });
        }

        return response()->json($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'level' => 'nullable|integer|in:1,2,3',
            'status' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'related_personnel' => 'nullable|string|max:255',
            'project' => 'required|string|max:255',
            'item' => 'nullable|string|max:255',
            'department' => 'required|string|max:255',
            'work' => 'required|string|max:255',
            'points' => 'required|numeric|min:0.5|max:21',
            'release_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'expected_finish_date' => 'nullable|date',
            'actual_finish_date' => 'nullable|date',
            'output_url' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
        ]);

        $validated['level'] = $validated['level'] ?? 1;
        $validated['status'] = $validated['status'] ?? 'in progress';

        \Illuminate\Support\Facades\Gate::authorize('create', Task::class);

        $task = Task::create($validated);

        return response()->json($task, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $task);

        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'status' => 'sometimes|required|string',
            'department' => 'sometimes|required|string|max:255',
            // Allow other fields if needed
        ]);

        $task->update($validated);

        return response()->json($task->load(['assignee', 'remarks']));
    }
}
