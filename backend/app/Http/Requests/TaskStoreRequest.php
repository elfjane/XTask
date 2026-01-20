<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gates will handle authorization in Controller
    }

    public function rules(): array
    {
        return [
            'level' => 'nullable|integer|in:1,2,3',
            'status' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'related_personnel' => 'nullable|string|max:255',
            'project' => 'required|string|max:255',
            'item' => 'nullable|string|max:255',
            'department' => 'required|string|max:255',
            'work' => 'required|string|max:255',
            'points' => 'required|numeric|min:0|max:21',
            'release_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'expected_finish_date' => 'nullable|date',
            'actual_finish_date' => 'nullable|date',
            'output_url' => 'nullable|string',
            'memo' => 'nullable|string',
        ];
    }
}
