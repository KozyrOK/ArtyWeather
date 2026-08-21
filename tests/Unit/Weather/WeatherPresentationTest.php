<?php

namespace Tests\Unit\Weather;

use App\DTO\Weather\Season;
use App\DTO\Weather\WeatherCondition;
use App\DTO\Weather\WeatherSnapshot;
use App\Services\AI\AiWeatherPresentationService;
use App\Services\AI\OllamaWeatherPresentationClient;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeatherPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fallback_uses_condition_season_and_existing_assets(): void
    {
        $presentation = app(AiWeatherPresentationService::class)->fallback(WeatherCondition::RAIN, Season::AUTUMN);

        $this->assertSame('RAIN', $presentation->toArray()['weather_condition']);
        $this->assertSame('AUTUMN', $presentation->toArray()['season']);
        $this->assertSame('rain', $presentation->toArray()['weather_icon']);
        $this->assertSame('autumn_rain', $presentation->toArray()['landscape']);
    }

    public function test_invalid_ai_asset_identifier_falls_back(): void
    {
        $this->mock(OllamaWeatherPresentationClient::class, function ($mock): void {
            $mock->shouldReceive('generate')->once()->andReturn([
                'weather_icon' => 'new_icon_from_ai',
                'landscape' => 'http://example.test/image.svg',
                'summary' => 'AI summary text.',
                'recommendation' => 'AI recommendation text.',
            ]);
        });

        $presentation = app(AiWeatherPresentationService::class)->generate($this->snapshot(), WeatherCondition::RAIN);

        $this->assertSame('rain', $presentation->weatherIcon);
        $this->assertSame('summer_rain', $presentation->landscape);
    }

    public function test_valid_structured_output_is_cached(): void
    {
        $this->mock(OllamaWeatherPresentationClient::class, function ($mock): void {
            $mock->shouldReceive('generate')->once()->andReturn([
                'weather_icon' => 'rain',
                'landscape' => 'summer_rain',
                'summary' => 'Ожидается тёплый летний дождь.',
                'recommendation' => 'Возьмите зонт и нескользящую обувь.',
            ]);
        });

        $service = app(AiWeatherPresentationService::class);
        $first = $service->generate($this->snapshot(), WeatherCondition::RAIN);
        $second = $service->generate($this->snapshot(), WeatherCondition::RAIN);

        $this->assertSame('Ожидается тёплый летний дождь.', $first->summary);
        $this->assertSame($first->toArray(), $second->toArray());
    }

    private function snapshot(): WeatherSnapshot
    {
        return new WeatherSnapshot(55.7558, 37.6172, new DateTimeImmutable('2026-08-17T12:00:00+00:00'), 18.6, 17.9, 74, 1.2, 61, 88, 1002.4, 9.3, 210, 16.8);
    }
}
