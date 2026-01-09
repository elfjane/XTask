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
        return response()->json(Schedule::with('memos')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project' => 'required|string',
            'title' => 'required|string',
            'status' => 'required|string|in:working,finish,in_progress,fail',
            'confirm' => 'required|string|in:Tentatively,Confirmed',
            'deadline' => 'nullable|date',
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date',
            'actual_start' => 'nullable|date',
            'actual_finish' => 'nullable|date',
            'memo' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\Gate::authorize('create', Schedule::class);

        $schedule = Schedule::create($validated);

        return response()->json($schedule, 201);
    }
}
