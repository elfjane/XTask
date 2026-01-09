<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Remark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskRemarkController extends Controller
{
    /**
     * Store a newly created remark for a specific task.
     */
    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $remark = new Remark();
        $remark->task_id = $task->id;
        $remark->user_name = Auth::user()->name;
        $remark->content = $validated['content'];
        $remark->save();

        return response()->json($remark, 201);
    }
}
