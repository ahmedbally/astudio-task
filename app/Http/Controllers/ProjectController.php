<?php

namespace App\Http\Controllers;

use App\Actions\Project\CreateProjectAction;
use App\Actions\Project\GetProjectsAction;
use App\Actions\Project\UpdateProjectAction;
use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function index(
        Request $request,
        GetProjectsAction $getProjectsAction
    ): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        $projects = $getProjectsAction->handle($request->user());

        return api()
            ->success(ProjectResource::collection($projects))
            ->respond();
    }

    /**
     * @throws Throwable
     */
    public function store(
        ProjectRequest $request,
        CreateProjectAction $createProject
    ): JsonResponse
    {
        $this->authorize('create', Project::class);

        return DB::transaction(function () use ($createProject, $request) {
            $project = $createProject->handle($request->user(), $request->validated());

            return api()
                ->success(ProjectResource::make($project))
                ->respond(JsonResponse::HTTP_CREATED);
        });

    }

    public function show(
        Project $project
    ): JsonResponse
    {
        $this->authorize('view', $project);

        return api()
            ->success(ProjectResource::make($project))
            ->respond();
    }

    /**
     * @throws Throwable
     */
    public function update(
        ProjectRequest $request,
        Project $project,
        UpdateProjectAction $updateProject
    ): JsonResponse
    {
        $this->authorize('update', $project);

        return DB::transaction(function () use ($request, $project, $updateProject) {
            $project = $updateProject->handle($project, $request->validated());

            return api()
                ->success(ProjectResource::make($project))
                ->respond();
        });
    }

    /**
     * @throws Throwable
     */
    public function destroy(
        Project $project
    ): JsonResponse
    {
        $this->authorize('delete', $project);

        return DB::transaction(function() use ($project) {
            $project->delete();

            return api()
                ->success()
                ->respond();
        });
    }
}
