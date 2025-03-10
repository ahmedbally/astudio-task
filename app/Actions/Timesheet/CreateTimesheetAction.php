<?php

namespace App\Actions\Timesheet;

use App\Models\Timesheet;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTimesheetAction
{
    use AsAction;

    public function handle(User $user, array $data): Timesheet
    {
        return Timesheet::create($data + [
                'user_id' => $user->id,
            ]);
    }
}
