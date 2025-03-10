<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\DB;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $this->hasAccess($user, $project);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Project $project): bool
    {
        return $this->hasAccess($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->hasAccess($user, $project);
    }

    private function hasAccess(User $user, Project $project): bool
    {
        return $project->users()->where('id', $user->id)->exists();
    }
}
