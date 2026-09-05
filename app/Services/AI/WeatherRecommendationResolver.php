<?php

namespace App\Services\AI;

use App\DTO\AI\WeatherRecommendationContext;
use App\DTO\Weather\WeatherCondition;
use App\DTO\Weather\WeatherSnapshot;

final class WeatherRecommendationResolver
{
    public function resolve(
        WeatherSnapshot $snapshot,
        WeatherCondition $condition,
    ): WeatherRecommendationContext {
        $factors = [];

        /*
         * Temperature
         */
        if ($snapshot->temperature <= $this->threshold('temperature.freezing_max', 0)) {
            $factors[] = 'freezing_temperature';
        } elseif ($snapshot->temperature <= $this->threshold('temperature.cold_max', 8)) {
            $factors[] = 'cold_temperature';
        }

        if ($snapshot->temperature >= $this->threshold('temperature.hot_min', 30)) {
            $factors[] = 'high_temperature';
        } elseif ($snapshot->temperature >= $this->threshold('temperature.warm_min', 25)) {
            $factors[] = 'warm_temperature';
        }

        /*
         * Temperature vs apparent temperature
         */
        $apparentDelta = $snapshot->apparentTemperature - $snapshot->temperature;

        if (
            $apparentDelta <=
            $this->threshold('apparent_temperature.wind_chill_delta_max', -4)
        ) {
            $factors[] = 'wind_chill';
        } elseif (
            $apparentDelta >=
            $this->threshold('apparent_temperature.heat_discomfort_delta_min', 4)
        ) {
            $factors[] = 'heat_discomfort';
        }

        /*
         * Precipitation
         */
        if ($snapshot->precipitation > $this->threshold('precipitation.min', 0)) {
            $factors[] = 'precipitation';
        }

        /*
         * Semantic weather condition
         *
         * WeatherCondition itself is resolved outside of this service.
         * This service only translates it into recommendation factors.
         */
        match ($condition) {
            WeatherCondition::RAIN => $factors[] = 'rain',
            WeatherCondition::HEAVY_RAIN => $factors[] = 'heavy_rain',
            WeatherCondition::SNOW => $factors[] = 'snow',
            WeatherCondition::FOG => $factors[] = 'low_visibility',
            WeatherCondition::STORM => $factors[] = 'storm_risk',
            default => null,
        };

        /*
         * Wind
         */
        if ($snapshot->windSpeed >= $this->threshold('wind.strong_min', 30)) {
            $factors[] = 'strong_wind';
        }

        if ($snapshot->windGusts >= $this->threshold('wind.dangerous_gust_min', 45)) {
            $factors[] = 'dangerous_gusts';
        }

        /*
         * Humidity
         */
        if ($snapshot->relativeHumidity >= $this->threshold('humidity.high_min', 85)) {
            $factors[] = 'high_humidity';
        }

        $factors = array_values(array_unique($factors));

        return new WeatherRecommendationContext(
            factors: $factors,
            priority: $this->resolvePriority($condition, $factors),
        );
    }

    /**
     * @param list<string> $factors
     */
    private function resolvePriority(
        WeatherCondition $condition,
        array $factors,
    ): ?string {
        /*
         * Storm is always the dominant recommendation factor.
         */
        if ($condition === WeatherCondition::STORM) {
            return 'storm_risk';
        }

        foreach ($this->priorityOrder() as $factor) {
            if (in_array($factor, $factors, true)) {
                return $factor;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function priorityOrder(): array
    {
        return (array) config('weather.recommendation.priority', [
            'dangerous_gusts',
            'storm_risk',
            'low_visibility',
            'heavy_rain',
            'snow',
            'rain',
            'freezing_temperature',
            'high_temperature',
            'strong_wind',
            'wind_chill',
            'heat_discomfort',
            'cold_temperature',
            'warm_temperature',
            'high_humidity',
            'precipitation',
        ]);
    }

    private function threshold(string $key, int|float $default): int|float
    {
        return config(
            "weather.recommendation.thresholds.{$key}",
            $default
        );
    }
}