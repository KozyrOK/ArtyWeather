<?php

namespace App\Services\Weather;

use App\DTO\Weather\WeatherSnapshot;
use DateTimeImmutable;
use Illuminate\Support\Arr;
use InvalidArgumentException;

final class WeatherNormalizer
{
    /** @param array<string, mixed> $payload */
    public function normalize(array $payload): WeatherSnapshot
    {
        $current = Arr::get($payload, 'current');

        if (! is_array($current)) {
            throw new InvalidArgumentException('Open-Meteo response does not contain current weather data.');
        }

        return new WeatherSnapshot(
            latitude: $this->float($payload, 'latitude'),
            longitude: $this->float($payload, 'longitude'),
            timestamp: new DateTimeImmutable($this->string($current, 'time')),
            temperature: $this->float($current, 'temperature_2m'),
            apparentTemperature: $this->float($current, 'apparent_temperature'),
            relativeHumidity: $this->int($current, 'relative_humidity_2m'),
            precipitation: $this->float($current, 'precipitation'),
            weatherCode: $this->int($current, 'weather_code'),
            cloudCover: $this->int($current, 'cloud_cover'),
            pressure: $this->float($current, 'surface_pressure'),
            windSpeed: $this->float($current, 'wind_speed_10m'),
            windDirection: $this->int($current, 'wind_direction_10m'),
            windGusts: $this->float($current, 'wind_gusts_10m'),
        );
    }

    /** @param array<string, mixed> $data */
    private function string(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        if (! is_string($value) || $value === '') {
            throw new InvalidArgumentException("Open-Meteo response field [{$key}] is missing or invalid.");
        }

        return $value;
    }

    /** @param array<string, mixed> $data */
    private function float(array $data, string $key): float
    {
        $value = $data[$key] ?? null;

        if (! is_numeric($value)) {
            throw new InvalidArgumentException("Open-Meteo response field [{$key}] is missing or invalid.");
        }

        return (float) $value;
    }

    /** @param array<string, mixed> $data */
    private function int(array $data, string $key): int
    {
        $value = $data[$key] ?? null;

        if (! is_numeric($value)) {
            throw new InvalidArgumentException("Open-Meteo response field [{$key}] is missing or invalid.");
        }

        return (int) $value;
    }
}