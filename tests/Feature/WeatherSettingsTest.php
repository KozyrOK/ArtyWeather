<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeatherSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WeatherSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_read_default_weather_settings(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/weather-settings');

        $response->assertOk()
            ->assertJsonPath('data.latitude', number_format(WeatherSetting::DEFAULT_LATITUDE, 5, '.', ''))
            ->assertJsonPath('data.longitude', number_format(WeatherSetting::DEFAULT_LONGITUDE, 5, '.', ''))
            ->assertJsonPath('data.forecast_period', WeatherSetting::DEFAULT_FORECAST_PERIOD)
            ->assertJsonPath('data.display.temperature', true)
            ->assertJsonPath('data.display.wind_speed', true)
            ->assertJsonPath('data.weather_request_fields', WeatherSetting::DISPLAY_FIELDS)
            ->assertJsonPath('data.user.locale', 'en')
            ->assertJsonPath('data.user.theme', 'system');

        $this->assertDatabaseHas('weather_settings', [
            'user_id' => $user->id,
            'forecast_period' => WeatherSetting::DEFAULT_FORECAST_PERIOD,
        ]);
    }

    public function test_authenticated_user_can_update_weather_settings(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/weather-settings', [
            'latitude' => 48.85661,
            'longitude' => 2.35222,
            'forecast_period' => 10,
            'temperature' => true,
            'apparent_temperature' => false,
            'relative_humidity' => true,
            'precipitation' => false,
            'weather_code' => true,
            'cloud_cover' => false,
            'pressure' => true,
            'wind_speed' => false,
            'wind_direction' => true,
            'wind_gusts' => false,
            'locale' => 'ru',
            'theme' => 'dark',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.latitude', '48.85661')
            ->assertJsonPath('data.longitude', '2.35222')
            ->assertJsonPath('data.forecast_period', 10)
            ->assertJsonPath('data.display.apparent_temperature', false)
            ->assertJsonPath('data.display.wind_speed', false)
            ->assertJsonPath('data.weather_request_fields', WeatherSetting::DISPLAY_FIELDS)
            ->assertJsonPath('data.user.locale', 'ru')
            ->assertJsonPath('data.user.theme', 'dark');

        $this->assertDatabaseHas('weather_settings', [
            'user_id' => $user->id,
            'latitude' => '48.85661',
            'longitude' => '2.35222',
            'forecast_period' => 10,
            'apparent_temperature' => false,
            'wind_speed' => false,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => 'ru',
            'theme' => 'dark',
        ]);
    }

    public function test_weather_settings_validation_rejects_invalid_values(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->putJson('/api/weather-settings', [
            'latitude' => 91,
            'longitude' => -181,
            'forecast_period' => 17,
            'temperature' => 'not-boolean',
            'theme' => 'neon',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'latitude',
                'longitude',
                'forecast_period',
                'temperature',
                'theme',
            ]);
    }

    public function test_weather_settings_require_authentication(): void
    {
        $this->getJson('/api/weather-settings')->assertUnauthorized();
        $this->putJson('/api/weather-settings', [])->assertUnauthorized();
    }
}