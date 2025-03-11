<?php

namespace App\Http\Requests;

use App\Support\EAV\HasAttributeValueValidation;
use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    use HasAttributeValueValidation;

    public function rules(): array
    {
        return array_merge([
            'name' => ['required'],
            'status' => ['required', 'integer'],
        ], $this->getAttributeValueRules());
    }

    public function authorize(): bool
    {
        return true;
    }
}
