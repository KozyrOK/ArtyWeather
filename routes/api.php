<?php

use App\Http\Controllers\Api\Auth\AuthenticatedUserController;
use App\Http\Controllers\Api\WeatherSettingController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthenticatedUserController::class, 'register']);
Route::post('/auth/login', [AuthenticatedUserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/auth/me', [AuthenticatedUserController::class, 'me']);
    Route::post('/auth/logout', [AuthenticatedUserController::class, 'logout']);
    Route::get('/weather-settings', [WeatherSettingController::class, 'show']);
    Route::put('/weather-settings', [WeatherSettingController::class, 'update']);
    Route::patch('/weather-settings', [WeatherSettingController::class, 'update']);
});