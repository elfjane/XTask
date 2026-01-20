<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Http\Requests\ScheduleStoreRequest;
use App\Http\Requests\ScheduleUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Schedule::class);
        return response()->json(Schedule::with([
            'memos' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }
        ])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScheduleStoreRequest $request)
    {
        Gate::authorize('create', Schedule::class);

        $validated = $request->validated();
        $validated['status'] = $validated['status'] ?? 'in_progress';

        $schedule = Schedule::create($validated);

        return response()->json($schedule, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        Gate::authorize('view', $schedule);
        return response()->json($schedule->load([
            'memos' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScheduleUpdateRequest $request, Schedule $schedule)
    {
        Gate::authorize('update', $schedule);

        $schedule->update($request->validated());

        return response()->json($schedule);
    }
}
