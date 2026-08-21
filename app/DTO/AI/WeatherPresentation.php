<?php

namespace App\DTO\AI;

use App\DTO\Weather\Season;
use App\DTO\Weather\WeatherCondition;

final readonly class WeatherPresentation
{
    public function __construct(
        public WeatherCondition $weatherCondition,
        public Season $season,
        public string $weatherIcon,
        public string $landscape,
        public string $summary,
        public string $recommendation,
    ) {}

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'weather_condition' => $this->weatherCondition->value,
            'season' => $this->season->value,
            'weather_icon' => $this->weatherIcon,
            'landscape' => $this->landscape,
            'summary' => $this->summary,
            'recommendation' => $this->recommendation,
        ];
    }
}
