<?php
 
namespace App\Http\Controllers\Api;
 
use App\DTO\Weather\Season;
use App\Http\Controllers\Controller;
use App\Http\Resources\WeatherPresentationResource;
use App\Http\Resources\WeatherResource;
use App\Jobs\GenerateWeatherPresentationJob;
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
         $locale = in_array($request->user()->locale, ['en', 'ru'], true)
            ? $request->user()->locale
            : ($request->getPreferredLanguage(['en', 'ru']) ?? 'en');
        $presentation = $presentations->cached($report->snapshot, $report->condition, $locale);

        if ($presentation !== null) {
            return response()->json([
                'status' => 'ready',
                'presentation' => WeatherPresentationResource::make($presentation),
            ]);
        }

        GenerateWeatherPresentationJob::dispatch($report->snapshot, $report->condition, $locale);

        return response()->json([
            'status' => 'processing',
            'fallback' => WeatherPresentationResource::make(
                $presentations->fallback(
                    $report->condition,
                    Season::fromMonth((int) $report->snapshot->timestamp->format('n')),
                ),
            ),
        ]);
    }
}