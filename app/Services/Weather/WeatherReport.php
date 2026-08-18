<?php

namespace App\Services\Weather;

use App\DTO\Weather\WeatherCondition;
use App\DTO\Weather\WeatherSnapshot;

final readonly class WeatherReport
{
    /** @param array<string, mixed> $display */
    public function __construct(
        public WeatherSnapshot $snapshot,
        public WeatherCondition $condition,
        public array $display,
    ) {}
}