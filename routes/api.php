<?php

use App\Http\Controllers\Api\Auth\AuthenticatedUserController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Controllers\Api\WeatherSettingController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthenticatedUserController::class, 'register']);
Route::post('/auth/login', [AuthenticatedUserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/auth/me', [AuthenticatedUserController::class, 'me']);
    Route::post('/auth/logout', [AuthenticatedUserController::class, 'logout']);
    Route::get('/weather', [WeatherController::class, 'show']);
    Route::post('/weather/refresh', [WeatherController::class, 'refresh']);
    Route::get('/weather/presentation', [WeatherController::class, 'presentation']);
    Route::get('/weather-settings', [WeatherSettingController::class, 'show']);
    Route::put('/weather-settings', [WeatherSettingController::class, 'update']);
    Route::patch('/weather-settings', [WeatherSettingController::class, 'update']);
});