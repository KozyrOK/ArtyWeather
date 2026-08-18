<?php

namespace App\DTO\Weather;

use DateTimeImmutable;

final readonly class WeatherSnapshot
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public DateTimeImmutable $timestamp,
        public float $temperature,
        public float $apparentTemperature,
        public int $relativeHumidity,
        public float $precipitation,
        public int $weatherCode,
        public int $cloudCover,
        public float $pressure,
        public float $windSpeed,
        public int $windDirection,
        public float $windGusts,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'timestamp' => $this->timestamp->format(DATE_ATOM),
            'temperature' => $this->temperature,
            'apparent_temperature' => $this->apparentTemperature,
            'relative_humidity' => $this->relativeHumidity,
            'precipitation' => $this->precipitation,
            'weather_code' => $this->weatherCode,
            'cloud_cover' => $this->cloudCover,
            'pressure' => $this->pressure,
            'wind_speed' => $this->windSpeed,
            'wind_direction' => $this->windDirection,
            'wind_gusts' => $this->windGusts,
        ];
    }
}