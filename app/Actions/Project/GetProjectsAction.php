<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class GetProjectsAction
{
    use AsAction;

    public function handle(User $user): LengthAwarePaginator
    {
        return Project::whereHas('users', function ($query) use ($user) {
            $query->where('id', $user->id);
        })->paginate();
    }
}
