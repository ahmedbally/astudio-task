<?php

namespace App\Actions\Timesheet;

use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTimesheetsAction
{
    use AsAction;

    public function handle(User $user): LengthAwarePaginator
    {
        return Timesheet::with('project')
            ->where('user_id', $user->id)
            ->paginate();
    }
}
