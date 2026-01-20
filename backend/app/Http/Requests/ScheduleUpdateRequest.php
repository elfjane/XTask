<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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
        ];
    }
}
