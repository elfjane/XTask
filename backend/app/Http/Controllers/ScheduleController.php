<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        \Illuminate\Support\Facades\Gate::authorize('viewAny', Schedule::class);
        return response()->json(Schedule::with([
            'memos' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }
        ])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project' => 'required|string',
            'title' => 'required|string',
            'status' => 'nullable|string|in:working,finish,in_progress,fail',
            'confirm' => 'required|string|in:Tentatively,Confirmed',
            'deadline' => 'nullable|date',
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date',
            'memo' => 'nullable|string',
        ]);

        $validated['status'] = $validated['status'] ?? 'in_progress';

        \Illuminate\Support\Facades\Gate::authorize('create', Schedule::class);

        $schedule = Schedule::create($validated);

        return response()->json($schedule, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $schedule);
        return response()->json($schedule->load([
            'memos' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'project' => 'sometimes|string',
            'title' => 'sometimes|string',
            'status' => 'sometimes|string|in:working,finish,in_progress,fail',
            'confirm' => 'sometimes|string|in:Tentatively,Confirmed',
            'deadline' => 'nullable|date',
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date',
            'actual_start' => 'nullable|date',
            'actual_finish' => 'nullable|date',
            'memo' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\Gate::authorize('update', $schedule);

        $schedule->update($validated);

        return response()->json($schedule);
    }
}
