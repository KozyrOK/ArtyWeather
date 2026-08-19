<?php

namespace Tests\Feature;

use App\DTO\Weather\WeatherSnapshot;
use App\Models\User;
use App\Models\WeatherSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_weather_endpoint_caches_normalized_weather_snapshot_for_reuse(): void
    {
        Cache::flush();
        $user = $this->userWithWeatherSettings(['temperature' => true]);
        Http::fake(['*' => Http::response($this->payload(21.5))]);

        $first = $this->actingAs($user)->getJson('/api/weather');
        $second = $this->actingAs($user)->getJson('/api/weather');

        $first->assertOk()
            ->assertJsonPath('data.cache.status', 'MISS')
            ->assertJsonPath('data.cache.key', 'weather:55.75583:37.61722:7')
            ->assertJsonPath('data.snapshot.temperature', 21.5);
        $second->assertOk()
            ->assertJsonPath('data.cache.status', 'HIT')
            ->assertJsonPath('data.snapshot.temperature', 21.5);

        $this->assertInstanceOf(WeatherSnapshot::class, Cache::get('weather:55.75583:37.61722:7'));
        Http::assertSentCount(1);
    }

    public function test_display_booleans_do_not_change_cache_key_or_force_new_external_request(): void
    {
        Cache::flush();
        $first = $this->userWithWeatherSettings(['temperature' => true, 'pressure' => false]);
        $second = $this->userWithWeatherSettings(['temperature' => false, 'pressure' => true]);
        Http::fake(['*' => Http::response($this->payload(21.5))]);

        $this->actingAs($first)->getJson('/api/weather')->assertJsonPath('data.cache.status', 'MISS');
        $response = $this->actingAs($second)->getJson('/api/weather');

        $response->assertOk()
            ->assertJsonPath('data.cache.status', 'HIT')
            ->assertJsonPath('data.cache.key', 'weather:55.75583:37.61722:7')
            ->assertJsonMissingPath('data.display.temperature')
            ->assertJsonPath('data.display.pressure', 1012.4);
        Http::assertSentCount(1);
    }

    public function test_refresh_invalidates_existing_cache_and_fetches_fresh_snapshot(): void
    {
        Cache::flush();
        $user = $this->userWithWeatherSettings();

        Http::fakeSequence()
            ->push($this->payload(18.5))
            ->push($this->payload(24.5));

        $this->actingAs($user)->getJson('/api/weather')
            ->assertJsonPath('data.cache.status', 'MISS')
            ->assertJsonPath('data.snapshot.temperature', 18.5);

        $this->actingAs($user)->postJson('/api/weather/refresh')
            ->assertOk()
            ->assertJsonPath('data.cache.status', 'MISS')
            ->assertJsonPath('data.snapshot.temperature', 24.5);
        Http::assertSentCount(2);
    }

    /** @param array<string, mixed> $overrides */
    private function userWithWeatherSettings(array $overrides = []): User
    {
        $user = User::factory()->create();
        $user->weatherSetting()->create(array_merge(WeatherSetting::defaults(), $overrides));

        return $user;
    }

    /** @return array<string, mixed> */
    private function payload(float $temperature): array
    {
        return [
            'latitude' => 55.75583,
            'longitude' => 37.61722,
            'current' => [
                'time' => '2026-08-18T12:00',
                'temperature_2m' => $temperature,
                'apparent_temperature' => $temperature + 1,
                'relative_humidity_2m' => 60,
                'precipitation' => 0.0,
                'weather_code' => 1,
                'cloud_cover' => 20,
                'surface_pressure' => 1012.4,
                'wind_speed_10m' => 4.2,
                'wind_direction_10m' => 180,
                'wind_gusts_10m' => 7.1,
            ],
        ];
    }
}