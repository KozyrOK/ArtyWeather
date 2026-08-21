<?php

namespace App\Services\AI;

use App\DTO\Weather\Season;
use App\DTO\Weather\WeatherCondition;
use App\DTO\Weather\WeatherSnapshot;

final readonly class WeatherPresentationPromptBuilder
{
    public function __construct(private WeatherAssetRegistry $assets) {}

    /** @return array{system: string, user: string} */
    public function build(WeatherSnapshot $snapshot, WeatherCondition $condition, Season $season, string $locale = 'ru'): array
    {
        $icon = $this->assets->iconFor($condition);
        $landscape = $this->assets->landscapeFor($season, $condition);

        return [
            'system' => implode("\n", [
                'You are the AI Presentation Layer for ArtyWeather.',
                'Open-Meteo and the Laravel application are the only sources of factual weather data.',
                'Do not determine or change weather_condition, season, or factual weather values.',
                'Do not generate images, SVG, filenames, URLs, filesystem paths, or new asset identifiers.',
                'Return only valid JSON with keys: weather_icon, landscape, summary, recommendation.',
                'Use concise, practical Russian text unless locale explicitly requires otherwise.',
            ]),
            'user' => json_encode([
                'locale' => $locale,
                'locked_values' => [
                    'weather_condition' => $condition->value,
                    'season' => $season->value,
                    'weather_icon' => $icon,
                    'landscape' => $landscape,
                ],
                'allowed_assets' => [
                    'weather_icon' => [$icon],
                    'landscape' => [$landscape],
                ],
                'weather_snapshot' => $snapshot->toArray(),
                'task' => 'Create a short friendly summary and recommendation. Copy the locked asset identifiers exactly.',
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ];
    }
}
