<?php

namespace App\Infrastructure\Weather;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use RuntimeException;

final readonly class OpenMeteoClient
{
    private const CURRENT_VARIABLES = [
        'temperature_2m',
        'apparent_temperature',
        'relative_humidity_2m',
        'precipitation',
        'weather_code',
        'cloud_cover',
        'surface_pressure',
        'wind_speed_10m',
        'wind_direction_10m',
        'wind_gusts_10m',
    ];

    public function __construct(private HttpFactory $http) {}

    /** @return array<string, mixed> */
    public function forecast(float $latitude, float $longitude, int $forecastPeriod): array
    {
        try {
            $response = $this->http
                ->timeout((int) config('services.open_meteo.timeout', 5))
                ->retry(
                    (int) config('services.open_meteo.retry_times', 2),
                    (int) config('services.open_meteo.retry_sleep', 200),
                    throw: false,
                )
                ->get($this->baseUrl(), [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'forecast_days' => $forecastPeriod,
                    'current' => implode(',', self::CURRENT_VARIABLES),
                    'timezone' => 'UTC',
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Open-Meteo request failed: '.$exception->getMessage(), previous: $exception);
        }

        try {
            $response->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException('Open-Meteo returned an HTTP error.', previous: $exception);
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('Open-Meteo returned an invalid JSON response.');
        }

        return $payload;
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.open_meteo.base_url', 'https://api.open-meteo.com/v1/forecast'), '/');
    }
}