<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Services\TaskImportService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Task::class);

        $query = Task::with([
            'assignee',
            'reviewer',
            'latestRemark'
        ]);

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

        if ($sortField === 'id') {
            $query->orderBy('sort_order', $sortOrder)
                ->orderBy('id', $sortOrder);
        } elseif (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('sort_order', 'desc')
                ->orderBy('id', 'desc');
        }

        return response()->json($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskStoreRequest $request)
    {
        Gate::authorize('create', Task::class);

        $validated = $request->validated();
        $validated['level'] = $validated['level'] ?? 1;
        $validated['status'] = $validated['status'] ?? 'in progress';

        if (isset($validated['points'])) {
            $validated['points'] = round($validated['points'] * 2) / 2;
        }

        $maxSortOrder = Task::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxSortOrder + 1;

        $task = Task::create($validated);

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        Gate::authorize('view', $task);
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
    public function update(TaskUpdateRequest $request, Task $task)
    {
        Gate::authorize('update', $task);

        $validated = $request->validated();

        if (isset($validated['points'])) {
            $validated['points'] = round($validated['points'] * 2) / 2;
        }

        if (isset($validated['status'])) {
            $currentStatus = $task->status;
            $newStatus = $validated['status'];

            if ($newStatus === 'finished' && $currentStatus !== 'finished' && $task->review_status !== 'approved') {
                $validated['review_status'] = 'submitted';
            } elseif ($newStatus !== 'finished' && $currentStatus === 'finished' && $task->review_status === 'submitted') {
                $validated['review_status'] = 'unsubmitted';
            }
        }

        if (isset($validated['review_status'])) {
            $oldReviewStatus = $task->review_status;
            $newReviewStatus = $validated['review_status'];

            if ($newReviewStatus !== $oldReviewStatus && in_array($newReviewStatus, ['approved', 'failed'])) {
                $validated['reviewed_by'] = $request->user()->id;
                $validated['reviewed_at'] = now();

                if ($newReviewStatus === 'approved') {
                    $validated['approved_at'] = now();
                    $validated['failed_at'] = null;
                    if (empty($task->actual_finish_date)) {
                        $validated['actual_finish_date'] = now();
                    }
                } elseif ($newReviewStatus === 'failed') {
                    $validated['failed_at'] = now();
                    $validated['approved_at'] = null;
                    $validated['status'] = 'failed';
                }
            } elseif ($newReviewStatus === 'unsubmitted' && $oldReviewStatus !== 'unsubmitted') {
                $validated['reviewed_by'] = null;
                $validated['reviewed_at'] = null;
                $validated['approved_at'] = null;
                $validated['failed_at'] = null;
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
        Gate::authorize('viewAny', Task::class);

        $perPage = $request->input('per_page', 20);
        $sort = $request->input('sort', 'desc');

        $user = $request->user();
        $query = Task::with(['assignee', 'reviewer', 'latestRemark']);

        if ($user->isExecutor()) {
            $query->where(function ($q) use ($user) {
                $q->where('department', $user->department?->name)
                    ->orWhere('user_id', $user->id)
                    ->orWhere('related_personnel', 'like', '%' . $user->name . '%');
            });
        }

        $query->where(function ($q) {
            $q->where('status', 'finished')
                ->orWhere('review_status', 'approved');
        })->where('review_status', '!=', 'submitted');

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

        if ($sort === 'asc') {
            $query->orderByRaw('COALESCE(approved_at, actual_finish_date) IS NULL')
                ->orderByRaw('COALESCE(approved_at, actual_finish_date) ASC');
        } else {
            $query->orderByRaw('COALESCE(approved_at, actual_finish_date) IS NULL')
                ->orderByRaw('COALESCE(approved_at, actual_finish_date) DESC');
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Get task statistics (Admin only)
     */
    public function statistics(Request $request)
    {
        Gate::authorize('viewStatistics', Task::class);

        $endMonth = $request->input('end_month')
            ? Carbon::createFromFormat('Y-m', $request->input('end_month'))->endOfMonth()
            : now()->endOfMonth();

        $startMonth = $request->input('start_month')
            ? Carbon::createFromFormat('Y-m', $request->input('start_month'))->startOfMonth()
            : $endMonth->copy()->subMonths(11)->startOfMonth();

        $tasks = Task::with('assignee')
            ->where(function ($q) {
                $q->where('status', 'finished')
                    ->orWhere('review_status', 'approved');
            })
            ->where('review_status', '!=', 'submitted')
            ->whereRaw('COALESCE(approved_at, actual_finish_date) >= ?', [$startMonth])
            ->whereRaw('COALESCE(approved_at, actual_finish_date) <= ?', [$endMonth])
            ->orderByRaw('COALESCE(approved_at, actual_finish_date) ASC')
            ->get();

        $stats = [];
        $current = $startMonth->copy();
        while ($current <= $endMonth) {
            $monthKey = $current->format('Y-m');
            $stats[$monthKey] = [
                'month' => $monthKey,
                'total_points' => 0,
                'assignee_stats' => []
            ];
            $current->addMonth();
        }

        foreach ($tasks as $task) {
            $date = $task->approved_at ? Carbon::parse($task->approved_at) : Carbon::parse($task->actual_finish_date);
            $monthKey = $date->format('Y-m');

            if (!isset($stats[$monthKey]))
                continue;

            $points = (float) $task->points;
            $stats[$monthKey]['total_points'] += $points;

            $assigneeName = $task->assignee ? $task->assignee->name : 'Unknown';
            $assigneeId = $task->assignee ? $task->assignee->id : 0;

            if (!isset($stats[$monthKey]['assignee_stats'][$assigneeId])) {
                $stats[$monthKey]['assignee_stats'][$assigneeId] = [
                    'id' => $assigneeId,
                    'name' => $assigneeName,
                    'points' => 0
                ];
            }
            $stats[$monthKey]['assignee_stats'][$assigneeId]['points'] += $points;
        }

        $result = array_values($stats);
        foreach ($result as &$monthStat) {
            $monthStat['assignee_stats'] = array_values($monthStat['assignee_stats']);
            usort($monthStat['assignee_stats'], fn($a, $b) => $b['points'] <=> $a['points']);
        }

        return response()->json($result);
    }

    /**
     * Import tasks from Excel.
     */
    public function import(Request $request, TaskImportService $importService)
    {
        $request->validate(['file' => 'required|file']);

        try {
            $count = $importService->import($request->file('file'), $request->user());

            return response()->json([
                'message' => "Successfully imported $count tasks.",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error processing Excel: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reorder tasks.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
        ]);

        $taskIds = $request->input('task_ids');
        $count = count($taskIds);

        foreach ($taskIds as $index => $id) {
            Task::where('id', $id)->update(['sort_order' => $count - $index]);
        }

        return response()->json(['message' => 'Tasks reordered successfully']);
    }
}
