<?php

namespace Tests\Unit\Weather;

use App\Services\Weather\WeatherNormalizer;
use PHPUnit\Framework\TestCase;

class WeatherNormalizerTest extends TestCase
{
    public function test_it_normalizes_open_meteo_current_payload(): void
    {
        $snapshot = (new WeatherNormalizer)->normalize([
            'latitude' => 55.75,
            'longitude' => 37.62,
            'current' => [
                'time' => '2026-08-17T12:00',
                'temperature_2m' => 21.4,
                'apparent_temperature' => 20.9,
                'relative_humidity_2m' => 64,
                'precipitation' => 0.2,
                'weather_code' => 61,
                'cloud_cover' => 90,
                'surface_pressure' => 1007.3,
                'wind_speed_10m' => 12.1,
                'wind_direction_10m' => 180,
                'wind_gusts_10m' => 22.5,
            ],
        ]);

        $this->assertSame(21.4, $snapshot->temperature);
        $this->assertSame(61, $snapshot->weatherCode);
        $this->assertSame('2026-08-17T12:00:00+00:00', $snapshot->timestamp->format(DATE_ATOM));
        $this->assertSame([], $snapshot->forecast);
    }
}