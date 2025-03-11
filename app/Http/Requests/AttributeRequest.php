<?php

namespace App\Http\Requests;

use App\Enums\AttributeType;
use App\Models\Attribute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttributeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Attribute::class, 'name')
                    ->ignore($this->route('attribute'))
            ],
            'type' => [
                'required',
                'string',
                Rule::in(AttributeType::cases())
            ],
            'options' => [
                Rule::requiredIf($this->type === AttributeType::SELECT->value),
                'nullable',
                'array',
            ],
            'options.*' => [
                'required',
                'string',
                'distinct'
            ]
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
