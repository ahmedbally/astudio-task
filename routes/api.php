<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\Auth\AuthenticateController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TimesheetController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest', 'throttle:5,1'])
    ->post('login', [AuthenticateController::class, 'store'])
    ->name('auth.login');

Route::middleware(['guest'])
    ->post('register', RegisterController::class)
    ->name('auth.register');

Route::middleware('auth:api')->group(function() {
    Route::post('logout', [AuthenticateController::class, 'destroy'])
        ->name('auth.logout');

    Route::get('user', UserController::class);

    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('timesheets', TimesheetController::class);
    Route::apiResource('attributes', AttributeController::class);
});

