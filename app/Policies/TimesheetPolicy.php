<?php

namespace App\Policies;

use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TimesheetPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Timesheet $timesheet): bool
    {
        return $timesheet->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Timesheet $timesheet): bool
    {
        return $timesheet->user_id === $user->id;
    }

    public function delete(User $user, Timesheet $timesheet): bool
    {
        return $timesheet->user_id === $user->id;
    }
}
