<?php

namespace Tests\Unit\Weather;

use App\DTO\Weather\WeatherCondition;
use App\DTO\Weather\WeatherSnapshot;
use App\Services\Weather\WeatherConditionResolver;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WeatherConditionResolverTest extends TestCase
{
    #[DataProvider('conditions')]
    public function test_it_resolves_conditions_deterministically(int $code, float $precipitation, int $cloudCover, WeatherCondition $expected): void
    {
        $snapshot = new WeatherSnapshot(55.75, 37.62, new DateTimeImmutable('2026-08-17T12:00:00Z'), 20, 20, 50, $precipitation, $code, $cloudCover, 1000, 10, 90, 15);

        $this->assertSame($expected, (new WeatherConditionResolver)->resolve($snapshot));
    }

    /** @return array<string, array{int, float, int, WeatherCondition}> */
    public static function conditions(): array
    {
        return [
            'storm' => [95, 0.0, 10, WeatherCondition::STORM],
            'snow' => [71, 0.0, 10, WeatherCondition::SNOW],
            'heavy rain' => [61, 8.0, 10, WeatherCondition::HEAVY_RAIN],
            'rain' => [61, 1.0, 10, WeatherCondition::RAIN],
            'fog' => [45, 0.0, 10, WeatherCondition::FOG],
            'cloudy' => [3, 0.0, 90, WeatherCondition::CLOUDY],
            'partly cloudy' => [2, 0.0, 40, WeatherCondition::PARTLY_CLOUDY],
            'clear' => [0, 0.0, 5, WeatherCondition::CLEAR],
        ];
    }
}