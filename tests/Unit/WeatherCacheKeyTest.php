<?php

namespace Tests\Unit;

use App\Models\WeatherSetting;
use App\Services\Weather\WeatherCacheKey;
use PHPUnit\Framework\TestCase;

class WeatherCacheKeyTest extends TestCase
{
    public function test_generates_logical_weather_cache_key_from_coordinates_and_forecast_period(): void
    {
        $key = (new WeatherCacheKey)->forCoordinates(55.75583, 37.61722, 7);

        $this->assertSame('weather:55.75583:37.61722:7', $key);
    }

    public function test_boolean_display_settings_do_not_change_weather_cache_key(): void
    {
        $first = new WeatherSetting(array_merge(WeatherSetting::defaults(), [
            'temperature' => true,
            'pressure' => false,
        ]));
        $second = new WeatherSetting(array_merge(WeatherSetting::defaults(), [
            'temperature' => false,
            'pressure' => true,
        ]));

        $factory = new WeatherCacheKey;

        $this->assertSame($factory->forSettings($first), $factory->forSettings($second));
    }
}