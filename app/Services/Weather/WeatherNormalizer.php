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
            forecast: $this->forecast($payload),
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, array<string, mixed>>
     */
    private function forecast(array $payload): array
    {
        $hourly = Arr::get($payload, 'hourly');

        if (! is_array($hourly) || ! isset($hourly['time']) || ! is_array($hourly['time'])) {
            return [];
        }

        $forecast = [];

        foreach ($hourly['time'] as $index => $time) {
            if (! is_string($time) || $time === '') {
                continue;
            }

            $forecast[] = [
                'timestamp' => (new DateTimeImmutable($time))->format(DATE_ATOM),
                'temperature' => $this->nullableFloatAt($hourly, 'temperature_2m', $index),
                'apparent_temperature' => $this->nullableFloatAt($hourly, 'apparent_temperature', $index),
                'relative_humidity' => $this->nullableIntAt($hourly, 'relative_humidity_2m', $index),
                'precipitation' => $this->nullableFloatAt($hourly, 'precipitation', $index),
                'weather_code' => $this->nullableIntAt($hourly, 'weather_code', $index),
                'cloud_cover' => $this->nullableIntAt($hourly, 'cloud_cover', $index),
                'pressure' => $this->nullableFloatAt($hourly, 'surface_pressure', $index),
                'wind_speed' => $this->nullableFloatAt($hourly, 'wind_speed_10m', $index),
                'wind_direction' => $this->nullableIntAt($hourly, 'wind_direction_10m', $index),
                'wind_gusts' => $this->nullableFloatAt($hourly, 'wind_gusts_10m', $index),
            ];
        }

        return $forecast;
    }

    /** @param array<string, mixed> $data */
    private function nullableFloatAt(array $data, string $key, int $index): ?float
    {
        $values = $data[$key] ?? null;
        $value = is_array($values) ? ($values[$index] ?? null) : null;

        return is_numeric($value) ? (float) $value : null;
    }

    /** @param array<string, mixed> $data */
    private function nullableIntAt(array $data, string $key, int $index): ?int
    {
        $values = $data[$key] ?? null;
        $value = is_array($values) ? ($values[$index] ?? null) : null;

        return is_numeric($value) ? (int) $value : null;
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