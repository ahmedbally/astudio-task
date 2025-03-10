<?php

namespace App\Http\Controllers;

use App\Actions\Timesheet\CreateTimesheetAction;
use App\Actions\Timesheet\GetTimesheetsAction;
use App\Actions\Timesheet\UpdateTimesheetAction;
use App\Http\Requests\TimesheetRequest;
use App\Http\Resources\TimesheetResource;
use App\Models\Timesheet;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TimesheetController extends Controller
{
    use AuthorizesRequests;

    public function index(
        Request $request,
        GetTimesheetsAction $getTimesheets,
    ): JsonResponse
    {
        $this->authorize('viewAny', Timesheet::class);

        $timesheets = $getTimesheets->handle($request->user());

        return api()
            ->success(TimesheetResource::collection($timesheets))
            ->respond();
    }

    /**
     * @throws Throwable
     */
    public function store(
        TimesheetRequest $request,
        CreateTimesheetAction $createTimesheet
    ): JsonResponse
    {
        $this->authorize('create', Timesheet::class);

        return DB::transaction(function () use ($createTimesheet, $request) {
            $timesheet = $createTimesheet->handle($request->user(), $request->validated());

            return api()
                ->success(TimesheetResource::make($timesheet))
                ->respond(JsonResponse::HTTP_CREATED);
        });
    }

    public function show(
        Timesheet $timesheet
    ): JsonResponse
    {
        $this->authorize('view', $timesheet);

        return api()
            ->success(TimesheetResource::make($timesheet))
            ->respond();
    }

    /**
     * @throws Throwable
     */
    public function update(
        TimesheetRequest $request,
        Timesheet $timesheet,
        UpdateTimesheetAction $updateTimesheet,
    ): JsonResponse
    {
        $this->authorize('update', $timesheet);

        return DB::transaction(function () use ($updateTimesheet, $request, $timesheet) {
            $updateTimesheet->handle($timesheet, $request->validated());

            return api()
                ->success(TimesheetResource::make($timesheet))
                ->respond();
        });
    }

    /**
     * @throws Throwable
     */
    public function destroy(Timesheet $timesheet)
    {
        $this->authorize('delete', $timesheet);

        DB::transaction(function () use ($timesheet) {
            $timesheet->delete();

            return api()
                ->success()
                ->respond();
        });
    }
}
