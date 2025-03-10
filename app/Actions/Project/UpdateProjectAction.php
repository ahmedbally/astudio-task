<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateProjectAction
{
    use AsAction;

    public function handle(Project $project, array $data): Project
    {
        $project->update($data);

        return $project;
    }
}
