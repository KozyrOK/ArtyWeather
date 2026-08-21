<?php

namespace App\Services\AI;

use App\DTO\Weather\Season;
use App\DTO\Weather\WeatherCondition;

final class WeatherAssetRegistry
{
    /** @return list<string> */
    public function weatherIcons(): array
    {
        return ['clear', 'partly_cloudy', 'cloudy', 'rain', 'heavy_rain', 'snow', 'fog', 'storm'];
    }

    /** @return list<string> */
    public function landscapes(): array
    {
        $landscapes = [];

        foreach (Season::cases() as $season) {
            foreach (WeatherCondition::cases() as $condition) {
                $landscapes[] = $this->landscapeFor($season, $condition);
            }
        }

        return $landscapes;
    }

    public function iconFor(WeatherCondition $condition): string
    {
        return strtolower($condition->value);
    }

    public function landscapeFor(Season $season, WeatherCondition $condition): string
    {
        return $season->assetPrefix().'_'.strtolower($condition->value);
    }

    public function hasWeatherIcon(string $assetId): bool
    {
        return in_array($assetId, $this->weatherIcons(), true);
    }

    public function hasLandscape(string $assetId): bool
    {
        return in_array($assetId, $this->landscapes(), true);
    }
}
