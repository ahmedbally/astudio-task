<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateProjectAction
{
    use AsAction;

    public function handle(User $user, array $data): Project
    {
        $project = Project::create($data);

        $project->users()->attach($user);

        return $project;
    }
}
