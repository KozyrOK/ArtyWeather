<?php

namespace App\Http\Resources;

use App\Services\Weather\WeatherReport;
use App\Services\Weather\WeatherConditionResolver;
use App\DTO\Weather\WeatherSnapshot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WeatherReport */
class WeatherResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'snapshot' => array_merge($this->snapshot->toArray(), [
                'forecast' => $this->forecastWithConditions(),
            ]),
            'weather_condition' => $this->condition->value,
            'display' => $this->display,
            'cache' => [
                'status' => $this->cacheStatus,
                'key' => $this->cacheKey,
            ],
        ];
    }

/** @return array<int, array<string, mixed>> */
    private function forecastWithConditions(): array
    {
        $resolver = app(WeatherConditionResolver::class);

        return array_map(function (array $hour) use ($resolver): array {
            $snapshot = new WeatherSnapshot(
                latitude: $this->snapshot->latitude,
                longitude: $this->snapshot->longitude,
                timestamp: new \DateTimeImmutable($hour['timestamp']),
                temperature: (float) ($hour['temperature'] ?? 0),
                apparentTemperature: (float) ($hour['apparent_temperature'] ?? 0),
                relativeHumidity: (int) ($hour['relative_humidity'] ?? 0),
                precipitation: (float) ($hour['precipitation'] ?? 0),
                weatherCode: (int) ($hour['weather_code'] ?? 0),
                cloudCover: (int) ($hour['cloud_cover'] ?? 0),
                pressure: (float) ($hour['pressure'] ?? 0),
                windSpeed: (float) ($hour['wind_speed'] ?? 0),
                windDirection: (int) ($hour['wind_direction'] ?? 0),
                windGusts: (float) ($hour['wind_gusts'] ?? 0),
            );

            return [...$hour, 'weather_condition' => $resolver->resolve($snapshot)->value];
        }, $this->snapshot->forecast);
    }
}