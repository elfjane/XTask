<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project' => 'required|string',
            'title' => 'required|string',
            'status' => 'nullable|string|in:working,finish,in_progress,fail',
            'confirm' => 'required|string|in:Tentatively,Confirmed',
            'deadline' => 'nullable|date',
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date',
            'memo' => 'nullable|string',
        ];
    }
}
