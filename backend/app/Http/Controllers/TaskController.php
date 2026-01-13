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
        $query = Task::with([
            'assignee',
            'reviewer',
            'remarks' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }
        ]);

        if ($user->isExecutor()) {
            $query->where(function ($q) use ($user) {
                $q->where('department', $user->department?->name)
                    ->orWhere('user_id', $user->id)
                    ->orWhere('related_personnel', 'like', '%' . $user->name . '%');
            });
        }

        if ($request->has('review_status')) {
            $query->where('review_status', $request->input('review_status'));
        }

        if ($request->has('exclude_review_status')) {
            $query->whereNotIn('review_status', explode(',', $request->input('exclude_review_status')));
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
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $task);
        return response()->json($task->load([
            'assignee',
            'reviewer',
            'remarks' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $task);

        $validated = $request->validate([
            'level' => 'sometimes|integer|in:1,2,3',
            'status' => 'sometimes|string',
            'user_id' => 'sometimes|exists:users,id',
            'related_personnel' => 'sometimes|nullable|string|max:255',
            'project' => 'sometimes|string|max:255',
            'item' => 'sometimes|nullable|string|max:255',
            'department' => 'sometimes|string|max:255',
            'work' => 'sometimes|string|max:255',
            'points' => 'sometimes|numeric|min:0.5|max:21',
            'release_date' => 'sometimes|nullable|date',
            'start_date' => 'sometimes|nullable|date',
            'expected_finish_date' => 'sometimes|nullable|date',
            'actual_finish_date' => 'sometimes|nullable|date',
            'output_url' => 'sometimes|nullable|string|max:255',
            'memo' => 'sometimes|nullable|string',
            'review_status' => 'sometimes|string|in:unsubmitted,submitted,failed,approved',
        ]);

        if (isset($validated['status'])) {
            if ($validated['status'] === 'finished') {
                $validated['review_status'] = 'submitted';
            } else {
                $validated['review_status'] = 'unsubmitted';
            }
        }

        if (isset($validated['review_status'])) {
            $oldReviewStatus = $task->review_status;
            $newReviewStatus = $validated['review_status'];

            // If review status is changing to approved or failed, record reviewer and timestamp
            if ($newReviewStatus !== $oldReviewStatus && in_array($newReviewStatus, ['approved', 'failed'])) {
                $validated['reviewed_by'] = $request->user()->id;
                $validated['reviewed_at'] = now();

                if ($newReviewStatus === 'approved') {
                    $validated['approved_at'] = now();
                    $validated['failed_at'] = null; // Clear failed timestamp if exists
                } elseif ($newReviewStatus === 'failed') {
                    $validated['failed_at'] = now();
                    $validated['approved_at'] = null; // Clear approved timestamp if exists
                    $validated['status'] = 'failed';
                }
            }
        }

        $task->update($validated);

        return response()->json($task->load([
            'assignee',
            'reviewer',
            'remarks' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }
        ]));
    }
}
