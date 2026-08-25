<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Http\Resources\WeatherPresentationResource;
use App\Http\Resources\WeatherResource;
use App\Services\AI\AiWeatherPresentationService;
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

    public function presentation(Request $request, WeatherService $weatherService, AiWeatherPresentationService $presentations): JsonResponse
    {
        $report = $weatherService->currentFor($request->user());        
         $locale = in_array($request->user()->locale, ['en', 'ru', 'ua'], true)
            ? $request->user()->locale
            : ($request->getPreferredLanguage(['en', 'ru', 'ua']) ?? 'en');
        $presentation = $presentations->cached($report->snapshot, $report->condition, $locale);

        if ($presentation !== null) {
            return response()->json([
                'status' => 'ready',
                'presentation' => WeatherPresentationResource::make($presentation),
            ]);
        }
        
        return response()->json([
            'status' => 'ready',
            'presentation' => WeatherPresentationResource::make(
                $presentations->generate($report->snapshot, $report->condition, $locale),
            ),
        ]);
    }
}