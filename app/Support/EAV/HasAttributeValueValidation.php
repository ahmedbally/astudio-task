<?php

namespace App\Support\EAV;

use App\Enums\AttributeType;
use App\Models\Attribute;
use Illuminate\Validation\Rule;

trait HasAttributeValueValidation
{
    /**
     * Get EAV attribute validation rules
     */
    protected function getAttributeValueRules(): array
    {
        // Get all attributes
        $attributes = Attribute::all();
        $rules = [];

        foreach ($attributes as $attribute) {
            $rules[$attribute->name] = $this->buildRuleForAttribute($attribute);
        }

        return $rules;
    }

    /**
     * Build validation rules for a specific attribute
     *
     * @param Attribute $attribute
     * @return array
     */
    protected function buildRuleForAttribute(Attribute $attribute): array
    {
        $rules = [];

        // Optional by default
        $rules[] = 'nullable';

        switch ($attribute->type) {
            case AttributeType::TEXT:
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;

            case AttributeType::NUMBER:
                $rules[] = 'numeric';
                break;

            case AttributeType::DATE:
                $rules[] = 'date';
                $rules[] = 'date_format:Y-m-d';
                break;

            case AttributeType::SELECT:
                if (!empty($attribute->options)) {
                    $rules[] = Rule::in($attribute->options);
                }
                break;
        }

        return $rules;
    }

}
