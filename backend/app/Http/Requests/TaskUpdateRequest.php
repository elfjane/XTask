<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gates will handle authorization in Controller
    }

    public function rules(): array
    {
        return [
            'level' => 'sometimes|integer|in:1,2,3',
            'status' => 'sometimes|string',
            'user_id' => 'sometimes|exists:users,id',
            'related_personnel' => 'sometimes|nullable|string|max:255',
            'project' => 'sometimes|string|max:255',
            'item' => 'sometimes|nullable|string|max:255',
            'department' => 'sometimes|string|max:255',
            'work' => 'sometimes|string|max:255',
            'points' => 'sometimes|numeric|min:0|max:21',
            'release_date' => 'sometimes|nullable|date',
            'start_date' => 'sometimes|nullable|date',
            'expected_finish_date' => 'sometimes|nullable|date',
            'actual_finish_date' => 'sometimes|nullable|date',
            'output_url' => 'sometimes|nullable|string',
            'memo' => 'sometimes|nullable|string',
            'review_status' => 'sometimes|nullable|string|in:unsubmitted,submitted,failed,approved',
        ];
    }
}
