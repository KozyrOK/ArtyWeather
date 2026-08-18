<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WeatherResource;
use App\Services\Weather\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function show(Request $request, WeatherService $weatherService): JsonResponse
    {
        return WeatherResource::make($weatherService->currentFor($request->user()))->response()->setStatusCode(200);
    }

    public function refresh(Request $request, WeatherService $weatherService): JsonResponse
    {
        return WeatherResource::make($weatherService->refreshFor($request->user()))->response()->setStatusCode(200);
    }
}