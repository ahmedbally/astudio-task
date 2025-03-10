<?php

namespace App\Rules;

use App\Models\Project;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProjectExistRule implements ValidationRule
{
    public function __construct(private readonly User $user)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->userHasAccessToProject($value)) {
            $fail('validation.exists')->translate();
        }
    }

    private function userHasAccessToProject(mixed $projectId): bool
    {
        return Project::query()
            ->whereHas('users', fn($query) => $query->where('id', $this->user->id))
            ->where('id', $projectId)
            ->exists();
    }
}
