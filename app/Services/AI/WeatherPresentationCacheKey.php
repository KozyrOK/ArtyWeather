<?php

namespace App\Services\AI;

use App\DTO\Weather\Season;
use App\DTO\Weather\WeatherCondition;
use App\DTO\Weather\WeatherSnapshot;

final class WeatherPresentationCacheKey
{
    public function forSnapshot(WeatherSnapshot $snapshot, WeatherCondition $condition, Season $season, string $locale = 'ru'): string
    {
        $hash = hash('sha256', json_encode($snapshot->toArray(), JSON_THROW_ON_ERROR));

        return sprintf(
            'weather:presentation:v1:%s:%s:%s:%s:%s:%s',
            number_format($snapshot->latitude, 4, '.', ''),
            number_format($snapshot->longitude, 4, '.', ''),
            $condition->value,
            $season->value,
            $locale,
            $hash,
        );
    }
}
