<?php

namespace Tests\Feature\Weather;

use App\Models\User;
use App\Models\WeatherSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WeatherApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_weather(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response($this->openMeteoPayload(), 200),
        ]);

        $user = User::factory()->create();
        $user->weatherSetting()->create(array_merge(WeatherSetting::defaults(), [
            'temperature' => true,
            'pressure' => true,
            'wind_speed' => false,
        ]));
        Sanctum::actingAs($user);

        $this->getJson('/api/weather')
            ->assertOk()
            ->assertJsonPath('data.snapshot.temperature', 18.6)
            ->assertJsonPath('data.weather_condition', 'RAIN')
            ->assertJsonPath('data.display.temperature', 18.6)
            ->assertJsonPath('data.snapshot.forecast.0.temperature', 18.6)
            ->assertJsonMissingPath('data.display.wind_speed');

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://api.open-meteo.com/v1/forecast')
            && $request['latitude'] === 55.75583
            && $request['longitude'] === 37.61722
            && $request['forecast_days'] === 7
            && str_contains($request['current'], 'temperature_2m')
            && str_contains($request['current'], 'wind_speed_10m')
            && str_contains($request['hourly'], 'wind_speed_10m'));
    }

    public function test_authenticated_user_can_refresh_weather(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response($this->openMeteoPayload(['weather_code' => 0, 'cloud_cover' => 0, 'precipitation' => 0]), 200),
        ]);

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/weather/refresh')
            ->assertOk()
            ->assertJsonPath('data.weather_condition', 'CLEAR');
    }

    public function test_open_meteo_http_errors_are_reported(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response(['reason' => 'bad request'], 400),
        ]);

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/weather')->assertStatus(500);
    }

    /** @param array<string, mixed> $overrides */
    private function openMeteoPayload(array $overrides = []): array
    {
        return [
            'latitude' => 55.75583,
            'longitude' => 37.61722,
            'current' => array_merge([
                'time' => '2026-08-17T12:00',
                'temperature_2m' => 18.6,
                'apparent_temperature' => 17.9,
                'relative_humidity_2m' => 74,
                'precipitation' => 1.2,
                'weather_code' => 61,
                'cloud_cover' => 88,
                'surface_pressure' => 1002.4,
                'wind_speed_10m' => 9.3,
                'wind_direction_10m' => 210,
                'wind_gusts_10m' => 16.8,
            ], $overrides),
            'hourly' => [
                'time' => ['2026-08-17T12:00', '2026-08-17T13:00'],
                'temperature_2m' => [18.6, 19.1],
                'apparent_temperature' => [17.9, 18.5],
                'relative_humidity_2m' => [74, 72],
                'precipitation' => [1.2, 0.7],
                'weather_code' => [61, 61],
                'cloud_cover' => [88, 84],
                'surface_pressure' => [1002.4, 1002.1],
                'wind_speed_10m' => [9.3, 9.8],
                'wind_direction_10m' => [210, 215],
                'wind_gusts_10m' => [16.8, 17.2],
            ],
        ];
    }
}