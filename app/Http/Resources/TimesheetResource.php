<?php

namespace App\Http\Resources;

use App\Models\Timesheet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Timesheet */
class TimesheetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_name' => $this->task_name,
            'date' => $this->date,
            'hours' => $this->hours,
            'project' => $this->whenLoaded('project', function () {
                return ProjectResource::make($this->project);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
