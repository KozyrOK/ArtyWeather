<?php

namespace App\Services\Weather;

use App\DTO\Weather\WeatherCondition;
use App\DTO\Weather\WeatherSnapshot;

final class WeatherConditionResolver
{
    public function resolve(WeatherSnapshot $snapshot): WeatherCondition
    {
        return match (true) {
            $this->isStorm($snapshot->weatherCode) => WeatherCondition::STORM,
            $this->isSnow($snapshot->weatherCode) => WeatherCondition::SNOW,
            $this->isHeavyRain($snapshot) => WeatherCondition::HEAVY_RAIN,
            $this->isRain($snapshot) => WeatherCondition::RAIN,
            $this->isFog($snapshot->weatherCode) => WeatherCondition::FOG,
            $snapshot->cloudCover >= 85 => WeatherCondition::CLOUDY,
            $snapshot->cloudCover >= 35 => WeatherCondition::PARTLY_CLOUDY,
            default => WeatherCondition::CLEAR,
        };
    }

    private function isStorm(int $code): bool
    {
        return in_array($code, [95, 96, 99], true);
    }

    private function isSnow(int $code): bool
    {
        return ($code >= 71 && $code <= 77) || ($code >= 85 && $code <= 86);
    }

    private function isHeavyRain(WeatherSnapshot $snapshot): bool
    {
        return in_array($snapshot->weatherCode, [65, 67, 82], true) || $snapshot->precipitation >= 7.5;
    }

    private function isRain(WeatherSnapshot $snapshot): bool
    {
        return ($snapshot->weatherCode >= 51 && $snapshot->weatherCode <= 67)
            || ($snapshot->weatherCode >= 80 && $snapshot->weatherCode <= 82)
            || $snapshot->precipitation > 0;
    }

    private function isFog(int $code): bool
    {
        return in_array($code, [45, 48], true);
    }
}