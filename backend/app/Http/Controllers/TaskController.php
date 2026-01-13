<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

    /**
     * Get task statistics (Admin only)
     */
    public function statistics(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('viewStatistics', Task::class);

        // Date range filtering
        $endMonth = $request->input('end_month')
            ? Carbon::createFromFormat('Y-m', $request->input('end_month'))->endOfMonth()
            : now()->endOfMonth();

        $startMonth = $request->input('start_month')
            ? Carbon::createFromFormat('Y-m', $request->input('start_month'))->startOfMonth()
            : $endMonth->copy()->subMonths(11)->startOfMonth(); // Default to last 12 months

        // Query tasks: finished OR approved within the date range
        // We use COALESCE(approved_at, actual_finish_date) as the completion date
        $tasks = Task::with('assignee')
            ->where(function ($q) {
                $q->where('status', 'finished')
                    ->orWhere('review_status', 'approved');
            })
            ->whereRaw('COALESCE(approved_at, actual_finish_date) >= ?', [$startMonth])
            ->whereRaw('COALESCE(approved_at, actual_finish_date) <= ?', [$endMonth])
            // Order by date to make processing easier
            ->orderByRaw('COALESCE(approved_at, actual_finish_date) ASC')
            ->get();

        // Process data
        $stats = [];

        // Initialize months array
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

            // Skip if somehow outside range (though query should cover it)
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

        // Transform to array and sort assignee stats by points desc
        $result = array_values($stats);
        foreach ($result as &$monthStat) {
            $monthStat['assignee_stats'] = array_values($monthStat['assignee_stats']);
            usort($monthStat['assignee_stats'], function ($a, $b) {
                return $b['points'] <=> $a['points'];
            });
        }

        return response()->json($result);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            // Get headers from first row to find column indexes if they change
            $headers = [];
            for ($col = 1; $col <= \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); $col++) {
                $headers[$col] = $sheet->getCell([$col, 1])->getValue();
            }

            $admin = $request->user();
            $importedCount = 0;

            for ($row = 2; $row <= $highestRow; $row++) {
                // Check if row has any data
                $hasData = false;
                $rowData = [];
                for ($col = 1; $col <= count($headers); $col++) {
                    $cell = $sheet->getCell([$col, $row]);
                    $val = $cell->getValue();
                    $header = $headers[$col];
                    $rowData[$header] = $val;

                    // Specific handling for Hyperlinks in "工作" column
                    if ($header === '工作') {
                        $hyperlink = $cell->getHyperlink()->getUrl();
                        if ($hyperlink) {
                            $rowData['工作_link'] = $hyperlink;
                        }
                    }

                    if ($val !== null && $val !== '')
                        $hasData = true;
                }

                if (!$hasData)
                    continue;

                $task = new Task();

                $task->level = $rowData['級別'] ?? 1;
                $task->status = 'finished';

                $assigneeName = $rowData['執行人員'] ?? null;
                if ($assigneeName) {
                    $user = \App\Models\User::where('name', $assigneeName)->first();
                    if ($user) {
                        $task->user_id = $user->id;
                        $task->department = $user->department?->name ?: 'Unknown';
                    } else {
                        $task->user_id = $admin->id;
                        $task->department = $admin->department?->name ?: 'Admin Dept';
                    }
                } else {
                    $task->user_id = $admin->id;
                    $task->department = 'Unknown';
                }

                $task->related_personnel = $rowData['相關人員'] ?? null;
                $task->project = $rowData['專案'] ?? 'Imported';
                $task->item = $rowData['項目'] ?? ($rowData['類別'] ?? null);

                $workText = $rowData['工作'] ?? '';
                $embeddedLink = $rowData['工作_link'] ?? null;

                $task->work = $workText;
                $task->points = $rowData['點數'] ?? 0;

                $task->release_date = $this->parseExcelValueAsDate($rowData['發佈日'] ?? null);
                $task->start_date = $this->parseExcelValueAsDate($rowData['起始日'] ?? null);
                $task->expected_finish_date = $this->parseExcelValueAsDate($rowData['預計完成日'] ?? null);
                $task->actual_finish_date = $this->parseExcelValueAsDate($rowData['完成日'] ?? null);

                $originalMemo = $rowData['備註說明'] ?? '';
                if ($embeddedLink) {
                    $task->memo = $embeddedLink . ($originalMemo ? "\n" . $originalMemo : "");
                } else {
                    $task->memo = $originalMemo ?: null;
                }

                $task->output_url = $rowData['產出的文件或建檔(詳細說明)'] ?? null;

                $task->review_status = 'approved';
                $task->reviewed_by = $admin->id;

                $finishDate = $task->actual_finish_date ?: now();
                $task->reviewed_at = $finishDate;
                $task->approved_at = $finishDate;

                $task->save();
                $importedCount++;
            }

            return response()->json([
                'message' => "Successfully imported $importedCount tasks.",
                'count' => $importedCount
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error processing Excel: ' . $e->getMessage()], 500);
        }
    }

    private function parseExcelValueAsDate($value)
    {
        if (!$value)
            return null;

        // PhpSpreadsheet might return numeric value for dates
        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->toDateString();
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }
}
