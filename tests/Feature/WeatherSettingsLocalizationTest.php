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
}
