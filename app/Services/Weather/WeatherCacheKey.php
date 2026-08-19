<?php

namespace App\Services\Weather;

use App\Models\WeatherSetting;

final class WeatherCacheKey
{
    public function forSettings(WeatherSetting $settings): string
    {
        return $this->forCoordinates(
            (float) $settings->latitude,
            (float) $settings->longitude,
            (int) $settings->forecast_period,
        );
    }

    public function forCoordinates(float $latitude, float $longitude, int $forecastPeriod): string
    {
        return sprintf(
            'weather:%s:%s:%d',
            $this->coordinate($latitude),
            $this->coordinate($longitude),
            $forecastPeriod,
        );
    }

    private function coordinate(float $coordinate): string
    {
        return number_format($coordinate, 5, '.', '');
    }
}
