<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Memo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleMemoController extends Controller
{
    /**
     * Store a newly created memo for a specific schedule.
     */
    public function store(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $memo = new Memo();
        $memo->schedule_id = $schedule->id;
        $memo->user_name = Auth::user()->name;
        $memo->content = $validated['content'];
        $memo->save();

        return response()->json($memo, 201);
    }
}
