<?php

namespace App\Actions\Timesheet;

use App\Models\Timesheet;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTimesheetAction
{
    use AsAction;

    public function handle(Timesheet $timesheet, array $data): Timesheet
    {
        $timesheet->update($data);

        return $timesheet;
    }
}
