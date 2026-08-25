<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WeatherSettingsLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_is_limited_to_supported_values(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson('/api/weather-settings', ['locale' => 'de'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('locale');
    }

    public function test_ukrainian_locale_can_be_saved(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson('/api/weather-settings', ['locale' => 'uk'])
            ->assertOk()
            ->assertJsonPath('data.user.locale', 'uk');
    }
}