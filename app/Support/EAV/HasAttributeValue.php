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
            // Get attribute definition from cache
            $attributeModel = $this->getAttributeByName($key);

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
        if (!in_array($key, $this->fillable)) {
            $this->setEavAttribute($key, $value);

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

        foreach ($attributes as $key => $value) {
            $result[$key] = $this->setEavAttribute($key, $value);
        }

        return $result;
    }

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
        $attributeModel = $this->getAttributeByName($name);

        if (!$attributeModel) {
            return null;
        }

        $this->ensureAttributeValuesLoaded();

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
        $attributeModel = $this->getAttributeByName($name);

        if (!$attributeModel) {
            return false;
        }

        $this->ensureAttributeValuesLoaded();

        return $this->attributeValues
            ->contains('attribute_id', $attributeModel->id);
    }

    /**
     * Delete a specific dynamic attribute
     */
    public function deleteEavAttribute(string $name): bool
    {
        $attributeModel = $this->getAttributeByName($name);

        if (!$attributeModel) {
            return false;
        }

        $this->ensureAttributeValuesLoaded();

        $toDelete = $this->attributeValues
            ->where('attribute_id', $attributeModel->id)
            ->pluck('id')
            ->toArray();

        if (empty($toDelete)) {
            return false;
        }

        $result = AttributeValue::whereIn('id', $toDelete)->delete() > 0;

        // Update local collection
        if ($result) {
            $this->load('attributeValues');
        }

        return $result;
    }

    /**
     * Delete multiple dynamic attributes
     */
    public function deleteEavAttributes(array $names): int
    {
        $this->initializeAttributeCache();
        $attributeIds = static::$attributesCache
            ->whereIn('name', $names)
            ->pluck('id')
            ->toArray();

        if (empty($attributeIds)) {
            return 0;
        }

        $this->ensureAttributeValuesLoaded();

        $toDelete = $this->attributeValues
            ->whereIn('attribute_id', $attributeIds)
            ->pluck('id')
            ->toArray();

        if (empty($toDelete)) {
            return 0;
        }

        $count = AttributeValue::whereIn('id', $toDelete)->delete();

        // Update local collection
        if ($count > 0) {
            $this->load('attributeValues');
        }

        return $count;
    }

    /**
     * Delete all dynamic attributes for this entity
     */
    public function deleteAllEavAttributes(): int
    {
        $this->ensureAttributeValuesLoaded();

        $count = $this->attributeValues()->delete();

        // Clear local collection
        if ($count > 0 && $this->relationLoaded('attributeValues')) {
            $this->load('attributeValues');
        }

        return $count;
    }

    /**
     * Transform model data including EAV attributes
     */
    public function toArrayWithEav(): array
    {
        $data = $this->toArray();

        // Add EAV attributes
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
     * Detect attribute type based on value
     */
    protected function detectAttributeType(mixed $value): string
    {
        if (is_numeric($value)) {
            return 'number';
        } elseif (is_bool($value)) {
            return 'boolean';
        } elseif (strtotime($value) !== false) {
            return 'date';
        } else {
            return 'text';
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
     * Scope a query to order by a dynamic attribute
     */
    public function scopeOrderByEav(Builder $query, string $name, string $direction = 'asc'): Builder
    {
        $attributeModel = $this->getAttributeByName($name);

        if (!$attributeModel) {
            return $query;
        }

        return $query->join('attribute_values', function ($join) use ($attributeModel) {
            $join->on('attribute_values.entity_id', '=', $this->getTable() . '.id')
                ->where('attribute_values.entity_type', '=', get_class($this))
                ->where('attribute_values.attribute_id', '=', $attributeModel->id);
        })
            ->orderBy('attribute_values.value', $direction)
            ->select($this->getTable() . '.*');
    }

    /**
     * Scope a query to include entities that have a specific attribute
     */
    public function scopeHasEavAttribute(Builder $query, string $name): Builder
    {
        $attributeModel = $this->getAttributeByName($name);

        if (!$attributeModel) {
            return $query;
        }

        return $query->whereHas('attributeValues', function ($q) use ($attributeModel) {
            $q->where('attribute_id', $attributeModel->id);
        });
    }

    /**
     * Scope a query to include entities that have any of the specified attributes
     */
    public function scopeHasAnyEavAttribute(Builder $query, array $names): Builder
    {
        $this->initializeAttributeCache();
        $attributeIds = static::$attributesCache
            ->whereIn('name', $names)
            ->pluck('id')
            ->toArray();

        if (empty($attributeIds)) {
            return $query;
        }

        return $query->whereHas('attributeValues', function ($q) use ($attributeIds) {
            $q->whereIn('attribute_id', $attributeIds);
        });
    }

    /**
     * Scope a query to include entities that have all of the specified attributes
     */
    public function scopeHasAllEavAttributes(Builder $query, array $names): Builder
    {
        foreach ($names as $name) {
            $attributeModel = $this->getAttributeByName($name);

            if ($attributeModel) {
                $query->whereHas('attributeValues', function ($q) use ($attributeModel) {
                    $q->where('attribute_id', $attributeModel->id);
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
}
