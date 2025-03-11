<?php

namespace App\Support\EAV;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use InvalidArgumentException;

trait HasAttributeValue
{
    /**
     * Cached attributes collection
     */
    protected static ?Collection $attributesCache = null;

    /**
     * Temporary storage for EAV attributes before the model is saved
     */
    protected array $pendingEavAttributes = [];

    /**
     * Boot the trait
     */
    public static function bootHasAttributeValue(): void
    {
        // Register a "saved" event to save pending EAV attributes after the model is saved
        static::saved(function ($model) {
            if (!empty($model->pendingEavAttributes)) {
                // Batch process all pending EAV attributes after model is saved
                $attributesToProcess = $model->pendingEavAttributes;
                $model->pendingEavAttributes = []; // Clear before processing to avoid recursion

                foreach ($attributesToProcess as $key => $value) {
                    $model->setEavAttribute($key, $value);
                }
            }
        });
    }

    /**
     * Get all attribute values for this model
     */
    public function attributeValues(): MorphMany
    {
        return $this->morphMany(AttributeValue::class, 'entity');
    }

    /**
     * Initialize attribute cache if not already done
     */
    protected function initializeAttributeCache(): void
    {
        if (static::$attributesCache === null) {
            static::$attributesCache = Attribute::all();
        }
    }

    /**
     * Get attribute by ID (uses cache)
     */
    protected function getAttributeById(int $id): ?Attribute
    {
        $this->initializeAttributeCache();

        return static::$attributesCache->firstWhere('id', $id);
    }

    /**
     * Get attribute by name (uses cache)
     */
    protected function getAttributeByName(string $name): ?Attribute
    {
        $this->initializeAttributeCache();

        return static::$attributesCache->firstWhere('name', $name);
    }

    /**
     * Ensure attribute values are loaded
     */
    protected function ensureAttributeValuesLoaded(): void
    {
        if (!$this->relationLoaded('attributeValues')) {
            $this->load('attributeValues');
        }
    }

    /**
     * Override the getAttribute method to handle dynamic attributes
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key): mixed
    {
        $attribute = parent::getAttribute($key);

        // If the attribute is not found in the model's attributes
        if ($attribute === null && !isset($this->attributes[$key]) && !$this->isRelation($key)) {
            return $this->getEavAttribute($key);
        }

        return $attribute;
    }

    /**
     * Override the setAttribute method to handle dynamic attributes
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute($key, $value): static
    {
        if ($this->getAttributeByName($key) !== null) {
            // Always store in pending attributes for deferred processing
            $this->pendingEavAttributes[$key] = $value;
            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Set a dynamic attribute value
     */
    public function setEavAttribute(string $key, mixed $value): bool
    {
        // Get existing attribute from cache
        $attributeModel = $this->getAttributeByName($key);

        if (!$attributeModel) {
            throw new InvalidArgumentException("Attribute $key does not exist");
        }

        // Handle null values
        if ($value === null) {
            // Delete the attribute value
            $this->ensureAttributeValuesLoaded();

            // Find and remove from local collection
            $toDelete = $this->attributeValues
                ->where('attribute_id', $attributeModel->id)
                ->pluck('id')
                ->toArray();

            if (!empty($toDelete)) {
                AttributeValue::whereIn('id', $toDelete)->delete();

                $this->load('attributeValues');
            }

            return true;
        }

        // Validate and format the value based on attribute type
        $validatedValue = $this->validateAttributeValue($attributeModel, $value);

        // Save the attribute value
        $attributeValue = AttributeValue::updateOrCreate(
            [
                'attribute_id' => $attributeModel->id,
                'entity_id' => $this->id,
                'entity_type' => $this->getMorphClass(),
            ],
            ['value' => $validatedValue]
        );

        // Update local collection if loaded
        $this->ensureAttributeValuesLoaded();

        $existing = $this->attributeValues
            ->firstWhere('attribute_id', $attributeModel->id);

        if ($existing) {
            $existing->value = $validatedValue;
        } else {
            $this->attributeValues->push($attributeValue);
        }

        return true;
    }

    /**
     * Set multiple dynamic attributes at once
     */
    public function setEavAttributes(array $attributes): array
    {
        $result = [];

        // Always store in pending attributes for deferred processing
        foreach ($attributes as $key => $value) {
            if ($this->getAttributeByName($key) !== null) {
                $this->pendingEavAttributes[$key] = $value;
                $result[$key] = true;
            }
        }

        return $result;
    }

    // Rest of the trait methods remain unchanged...

    /**
     * Get all dynamic attributes
     */
    public function getEavAttributes(): array
    {
        $this->ensureAttributeValuesLoaded();
        $attributes = [];

        foreach ($this->attributeValues as $attributeValue) {
            $attribute = $this->getAttributeById($attributeValue->attribute_id);
            if ($attribute) {
                $attributes[$attribute->name] = $this->castEavValue($attributeValue, $attribute);
            }
        }

        return $attributes;
    }

    /**
     * Get a specific dynamic attribute by name
     */
    public function getEavAttribute(string $name): mixed
    {
        // Check pending attributes first (for both new and existing models)
        if (array_key_exists($name, $this->pendingEavAttributes)) {
            return $this->pendingEavAttributes[$name];
        }

        // Get attribute definition from cache
        $attributeModel = $this->getAttributeByName($name);

        if (!$attributeModel) {
            return null;
        }

        // Ensure attributeValues are loaded
        $this->ensureAttributeValuesLoaded();

        // Find the attribute value by attribute ID in loaded relation
        $attributeValue = $this->attributeValues
            ->first(function ($value) use ($attributeModel) {
                return $value->attribute_id === $attributeModel->id;
            });

        return $attributeValue ? $this->castEavValue($attributeValue, $attributeModel) : null;
    }

    /**
     * Get multiple EAV attributes by name
     */
    public function getEavAttributesByNames(array $names): array
    {
        $this->ensureAttributeValuesLoaded();
        $this->initializeAttributeCache();
        $attributes = [];

        // Include pending attributes (for both new and existing models)
        foreach ($names as $name) {
            if (array_key_exists($name, $this->pendingEavAttributes)) {
                $attributes[$name] = $this->pendingEavAttributes[$name];
            }
        }

        if (count($attributes) === count($names)) {
            return $attributes;
        }

        // Get attribute IDs from names
        $attributeIds = static::$attributesCache
            ->whereIn('name', $names)
            ->pluck('id')
            ->toArray();

        foreach ($this->attributeValues as $attributeValue) {
            if (in_array($attributeValue->attribute_id, $attributeIds)) {
                $attribute = $this->getAttributeById($attributeValue->attribute_id);
                if ($attribute) {
                    $attributes[$attribute->name] = $this->castEavValue($attributeValue, $attribute);
                }
            }
        }

        return $attributes;
    }

    /**
     * Check if a dynamic attribute exists
     */
    public function hasEavAttribute(string $name): bool
    {
        // Check pending attributes (for both new and existing models)
        if (array_key_exists($name, $this->pendingEavAttributes)) {
            return true;
        }

        $attributeModel = $this->getAttributeByName($name);

        if (!$attributeModel) {
            return false;
        }

        $this->ensureAttributeValuesLoaded();

        return $this->attributeValues
            ->contains('attribute_id', $attributeModel->id);
    }

    /**
     * Transform model data including EAV attributes
     */
    public function toArrayWithEav(): array
    {
        $data = $this->toArray();

        // Add pending EAV attributes (for both new and existing models)
        if (!empty($this->pendingEavAttributes)) {
            $data = array_merge($data, $this->pendingEavAttributes);
        }

        // Add saved EAV attributes
        $eavAttributes = $this->getEavAttributes();
        $data = array_merge($data, $eavAttributes);

        // Remove attribute_values from array if present
        unset($data['attribute_values']);

        return $data;
    }

    /**
     * Cast the attribute value based on its type
     */
    protected function castEavValue(AttributeValue $attributeValue, ?Attribute $attribute = null): mixed
    {
        $value = $attributeValue->value;

        // Get the attribute if not provided
        if (!$attribute) {
            $attribute = $this->getAttributeById($attributeValue->attribute_id);
        }

        if (!$attribute) {
            return $value; // Fallback if attribute not found
        }

        switch ($attribute->type) {
            case AttributeType::NUMBER:
                return is_numeric($value) ? (float) $value : null;

            case AttributeType::DATE:
                $timestamp = strtotime($value);
                return $timestamp ? date('Y-m-d', $timestamp) : null;

            case AttributeType::TEXT:
            case AttributeType::SELECT:
            default:
                return $value;
        }
    }

    /**
     * Validate the attribute value based on its type
     *
     * @throws InvalidArgumentException
     */
    protected function validateAttributeValue(Attribute $attribute, mixed $value): mixed
    {
        switch ($attribute->type) {
            case AttributeType::DATE:
                if (!strtotime($value)) {
                    throw new InvalidArgumentException("Invalid date format for attribute $attribute->name");
                }
                return date('Y-m-d', strtotime($value));

            case AttributeType::NUMBER:
                if (!is_numeric($value)) {
                    throw new InvalidArgumentException("Value for attribute $attribute->name must be a number");
                }
                return (float) $value;

            case AttributeType::SELECT:
                if (!in_array($value, $attribute->options ?? [])) {
                    throw new InvalidArgumentException(
                        "Invalid option for attribute $attribute->name. " .
                        "Valid options are: " . implode(', ', $attribute->options ?? [])
                    );
                }
                return (string) $value;

            case AttributeType::TEXT:
            default:
                return (string) $value;
        }
    }

    /**
     * Scope a query to filter by dynamic attributes
     */
    public function scopeWhereEav(Builder $query, array $attributes): Builder
    {
        foreach ($attributes as $name => $condition) {
            $attributeModel = $this->getAttributeByName($name);

            if (!$attributeModel) {
                continue;
            }

            if (is_array($condition) && isset($condition['operator'], $condition['value'])) {
                $this->applyEavOperatorFilter($query, $attributeModel->id, $condition['operator'], $condition['value']);
            } else {
                $query->whereHas('attributeValues', function ($q) use ($attributeModel, $condition) {
                    $q->where('attribute_id', $attributeModel->id)
                        ->where('value', $condition);
                });
            }
        }

        return $query;
    }

    /**
     * Apply operator-based filter for EAV attributes
     */
    protected function applyEavOperatorFilter(Builder $query, int $attributeId, string $operator, $value): void
    {
        $query->whereHas('attributeValues', function ($q) use ($attributeId, $operator, $value) {
            $q->where('attribute_id', $attributeId);

            switch (strtolower($operator)) {
                case 'like':
                case 'contains':
                    $q->where('value', 'LIKE', "%$value%");
                    break;
                case 'starts_with':
                    $q->where('value', 'LIKE', "$value%");
                    break;
                case 'ends_with':
                    $q->where('value', 'LIKE', "%$value");
                    break;
                case '>':
                case 'gt':
                    $q->where('value', '>', $value);
                    break;
                case '>=':
                case 'gte':
                    $q->where('value', '>=', $value);
                    break;
                case '<':
                case 'lt':
                    $q->where('value', '<', $value);
                    break;
                case '<=':
                case 'lte':
                    $q->where('value', '<=', $value);
                    break;
                case 'in':
                    if (is_array($value)) {
                        $q->whereIn('value', $value);
                    }
                    break;
                case 'not_in':
                    if (is_array($value)) {
                        $q->whereNotIn('value', $value);
                    }
                    break;
                case 'between':
                    if (is_array($value) && count($value) === 2) {
                        $q->whereBetween('value', $value);
                    }
                    break;
                case 'not_between':
                    if (is_array($value) && count($value) === 2) {
                        $q->whereNotBetween('value', $value);
                    }
                    break;
                case 'null':
                case 'is_null':
                    $q->whereNull('value');
                    break;
                case 'not_null':
                case 'is_not_null':
                    $q->whereNotNull('value');
                    break;
                default:
                    $q->where('value', $value);
            }
        });
    }

    public function getFillable(): array
    {
        // for migrating first time/fresh
        if (! app()->runningInConsole()) {
            $this->initializeAttributeCache();
        }

        return array_merge(
            $this->fillable,
            self::$attributesCache?->pluck('name')->toArray() ?? []
        );
    }
}
