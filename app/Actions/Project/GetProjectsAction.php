<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class GetProjectsAction
{
    use AsAction;

    public function handle(User $user, array $filters): LengthAwarePaginator
    {
        $filters = $this->formatFilters($filters);

        return Project::whereHas('users', function ($query) use ($user) {
            $query->where('id', $user->id);
        })
            ->whereEav($filters)
            ->paginate();
    }

    /**
     * Format filters to match the required structure for whereEav
     */
    protected function formatFilters(array $filters): array
    {
        $formattedFilters = [];

        foreach ($filters as $field => $value) {
            // Skip empty values
            if ($value === null || $value === '') {
                continue;
            }

            // Check if the value contains an operator
            if (is_string($value) && str_contains($value, ':')) {
                list($operator, $filterValue) = explode(':', $value, 2);

                // Map URL operators to the exact operators expected by the trait
                $operator = strtolower($operator);

                // Format based on operator
                switch ($operator) {
                    case 'in':
                    case 'not_in':
                        // Convert comma-separated values to array
                        $formattedFilters[$field] = [
                            'operator' => $operator,
                            'value' => explode(',', $filterValue)
                        ];
                        break;

                    case 'between':
                    case 'not_between':
                        // Convert comma-separated values to array
                        $rangeValues = explode(',', $filterValue);
                        if (count($rangeValues) === 2) {
                            $formattedFilters[$field] = [
                                'operator' => $operator,
                                'value' => $rangeValues
                            ];
                        }
                        break;

                    case 'null':
                    case 'is_null':
                    case 'not_null':
                    case 'is_not_null':
                        // No value needed for null checks
                        $formattedFilters[$field] = [
                            'operator' => $operator,
                            'value' => null
                        ];
                        break;

                    default:
                        // All other operators
                        $formattedFilters[$field] = [
                            'operator' => $operator,
                            'value' => $filterValue
                        ];
                }
            } else {
                // Simple equality (no operator)
                $formattedFilters[$field] = $value;
            }
        }

        return $formattedFilters;
    }
}
