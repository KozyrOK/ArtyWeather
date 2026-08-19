<?php

namespace App\Services\Weather;

use App\DTO\Weather\WeatherSnapshot;
use App\Infrastructure\Weather\OpenMeteoClient;
use App\Models\User;
use App\Models\WeatherSetting;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final readonly class WeatherService
{
    private const CACHE_HIT = 'HIT';

    private const CACHE_MISS = 'MISS';

    public function __construct(
        private OpenMeteoClient $client,
        private WeatherNormalizer $normalizer,
        private WeatherConditionResolver $conditionResolver,
        private CacheRepository $cache,
        private WeatherCacheKey $cacheKey,
    ) {}

    public function currentFor(User $user): WeatherReport
    {
        return $this->fetch($user);
    }

    public function refreshFor(User $user): WeatherReport
    {
        return $this->fetch($user, refresh: true);
    }

     private function fetch(User $user, bool $refresh = false): WeatherReport
    {
        $settings = $user->weatherSetting()->firstOrCreate([], WeatherSetting::defaults());
        $key = $this->cacheKey->forSettings($settings);

        if ($refresh) {
            $this->cache->forget($key);
        }

        $snapshot = $this->cache->get($key);
        $cacheStatus = self::CACHE_HIT;

        if (! $snapshot instanceof WeatherSnapshot) {
            $cacheStatus = self::CACHE_MISS;
            $snapshot = $this->freshSnapshot($settings);
            $this->cache->put($key, $snapshot, $this->ttl());
        }

        $condition = $this->conditionResolver->resolve($snapshot);

        return new WeatherReport(
            $snapshot,
            $condition,
            $this->displayData($snapshot, $settings),
            $cacheStatus,
            $key,
        );
    
    }

    private function freshSnapshot(WeatherSetting $settings): WeatherSnapshot
    {
        $payload = $this->client->forecast(
            (float) $settings->latitude,
            (float) $settings->longitude,
            (int) $settings->forecast_period,
        );

        return $this->normalizer->normalize($payload);
    }

    private function ttl(): int
    {
        return max(1, (int) config('services.weather_cache.ttl', 900));
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