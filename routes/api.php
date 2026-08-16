<?php

use App\Http\Controllers\Api\Auth\AuthenticatedUserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthenticatedUserController::class, 'register']);
Route::post('/auth/login', [AuthenticatedUserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/auth/me', [AuthenticatedUserController::class, 'me']);
    Route::post('/auth/logout', [AuthenticatedUserController::class, 'logout']);
});
