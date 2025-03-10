<?php

namespace App\Http\Requests;

use App\Rules\ProjectExistRule;
use Illuminate\Foundation\Http\FormRequest;

class TimesheetRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'task_name' => [
                'required',
                'string',
                'max:255',
            ],
            'date' => [
                'required',
                'date'
            ],
            'hours' => [
                'required',
                'numeric',
            ],
            'project_id' => [
                'required',
                new ProjectExistRule($this->user())
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
