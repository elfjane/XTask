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

        // Search functionality
        if ($request->has('search') && $search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('work', 'like', "%{$search}%")
                    ->orWhere('project', 'like', "%{$search}%")
                    ->orWhere('item', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('memo', 'like', "%{$search}%")
                    ->orWhereHas('assignee', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sorting functionality
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        $allowedSortFields = [
            'id',
            'level',
            'status',
            'project',
            'department',
            'points',
            'release_date',
            'start_date',
            'expected_finish_date',
            'actual_finish_date'
        ];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('id', 'desc');
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

                    // If actual_finish_date is empty, set it to current date/time when approved
                    if (empty($task->actual_finish_date)) {
                        $validated['actual_finish_date'] = now();
                    }
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

    /**
     * Get completed tasks (finished or approved)
     */
    public function completed(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('viewAny', Task::class);

        $perPage = $request->input('per_page', 20);
        $sort = $request->input('sort', 'desc'); // asc or desc

        $user = $request->user();
        $query = Task::with([
            'assignee',
            'reviewer',
            'remarks' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }
        ]);

        // Apply user-specific filtering
        if ($user->isExecutor()) {
            $query->where(function ($q) use ($user) {
                $q->where('department', $user->department?->name)
                    ->orWhere('user_id', $user->id)
                    ->orWhere('related_personnel', 'like', '%' . $user->name . '%');
            });
        }

        // Filter for completed tasks: status='finished' OR review_status='approved'
        $query->where(function ($q) {
            $q->where('status', 'finished')
                ->orWhere('review_status', 'approved');
        });

        // Search functionality
        if ($request->has('search') && $search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('work', 'like', "%{$search}%")
                    ->orWhere('project', 'like', "%{$search}%")
                    ->orWhere('item', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('memo', 'like', "%{$search}%")
                    ->orWhereHas('assignee', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('reviewer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sorting: prioritize approved_at if exists, otherwise actual_finish_date
        // MySQL compatible sorting (NULLS LAST equivalent)
        if ($sort === 'asc') {
            $query->orderByRaw('COALESCE(approved_at, actual_finish_date) IS NULL')
                ->orderByRaw('COALESCE(approved_at, actual_finish_date) ASC');
        } else {
            $query->orderByRaw('COALESCE(approved_at, actual_finish_date) IS NULL')
                ->orderByRaw('COALESCE(approved_at, actual_finish_date) DESC');
        }

        return response()->json($query->paginate($perPage));
    }
}
