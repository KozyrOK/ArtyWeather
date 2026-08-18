<?php

namespace App\Services\Weather;

use App\DTO\Weather\WeatherSnapshot;
use App\Infrastructure\Weather\OpenMeteoClient;
use App\Models\User;
use App\Models\WeatherSetting;

final readonly class WeatherService
{
    public function __construct(
        private OpenMeteoClient $client,
        private WeatherNormalizer $normalizer,
        private WeatherConditionResolver $conditionResolver,
    ) {}

    public function currentFor(User $user): WeatherReport
    {
        return $this->fetch($user);
    }

    public function refreshFor(User $user): WeatherReport
    {
        return $this->fetch($user);
    }

    private function fetch(User $user): WeatherReport
    {
        $settings = $user->weatherSetting()->firstOrCreate([], WeatherSetting::defaults());
        $payload = $this->client->forecast((float) $settings->latitude, (float) $settings->longitude, $settings->forecast_period);
        $snapshot = $this->normalizer->normalize($payload);
        $condition = $this->conditionResolver->resolve($snapshot);

        return new WeatherReport($snapshot, $condition, $this->displayData($snapshot, $settings));
    }

    /** @return array<string, mixed> */
    private function displayData(WeatherSnapshot $snapshot, WeatherSetting $settings): array
    {
        return array_intersect_key(
            $snapshot->toArray(),
            array_filter(array_intersect_key($settings->getAttributes(), array_flip(WeatherSetting::DISPLAY_FIELDS)))
        );
    }
}