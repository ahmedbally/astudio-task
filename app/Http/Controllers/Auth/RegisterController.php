<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __invoke(
        RegisterRequest $request,
        RegisterAction $registerAction
    ): JsonResponse
    {
        $user = $registerAction($request->validated());

        return api()
            ->success(UserResource::make($user))
            ->respond();
    }
}
