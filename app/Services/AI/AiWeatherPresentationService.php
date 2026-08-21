<?php

namespace App\Services\AI;

use App\DTO\AI\WeatherPresentation;
use App\DTO\Weather\Season;
use App\DTO\Weather\WeatherCondition;
use App\DTO\Weather\WeatherSnapshot;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

final readonly class AiWeatherPresentationService
{
    public function __construct(
        private OllamaWeatherPresentationClient $client,
        private WeatherPresentationPromptBuilder $promptBuilder,
        private WeatherAssetRegistry $assets,
        private WeatherPresentationCacheKey $cacheKey,
        private CacheRepository $cache,
    ) {}

    public function cached(WeatherSnapshot $snapshot, WeatherCondition $condition, string $locale = 'ru'): ?WeatherPresentation
    {
        $season = $this->seasonFor($snapshot);
        $presentation = $this->cache->get($this->cacheKey->forSnapshot($snapshot, $condition, $season, $locale));

        return $presentation instanceof WeatherPresentation ? $presentation : null;
    }

    public function generate(WeatherSnapshot $snapshot, WeatherCondition $condition, string $locale = 'ru'): WeatherPresentation
    {
        $season = $this->seasonFor($snapshot);
        $key = $this->cacheKey->forSnapshot($snapshot, $condition, $season, $locale);
        $cached = $this->cache->get($key);

        if ($cached instanceof WeatherPresentation) {
            return $cached;
        }

        try {
            $prompt = $this->promptBuilder->build($snapshot, $condition, $season, $locale);
            $payload = $this->client->generate($prompt['system'], $prompt['user']);
            $presentation = $this->fromValidatedPayload($payload, $condition, $season);
        } catch (Throwable) {
            $presentation = $this->fallback($condition, $season);
        }

        $this->cache->put($key, $presentation, $this->ttl());

        return $presentation;
    }

    public function fallback(WeatherCondition $condition, Season $season): WeatherPresentation
    {
        $label = match ($condition) {
            WeatherCondition::CLEAR => 'ясная',
            WeatherCondition::PARTLY_CLOUDY => 'переменно облачная',
            WeatherCondition::CLOUDY => 'облачная',
            WeatherCondition::RAIN => 'дождливая',
            WeatherCondition::HEAVY_RAIN => 'с сильным дождём',
            WeatherCondition::SNOW => 'снежная',
            WeatherCondition::FOG => 'туманная',
            WeatherCondition::STORM => 'штормовая',
        };

        return new WeatherPresentation(
            $condition,
            $season,
            $this->assets->iconFor($condition),
            $this->assets->landscapeFor($season, $condition),
            "Сейчас {$label} погода. Фактические данные доступны без AI-описания.",
            'Ориентируйтесь на текущие показатели погоды и подготовьтесь к условиям на улице.',
        );
    }

    private function fromValidatedPayload(array $payload, WeatherCondition $condition, Season $season): WeatherPresentation
    {
        $expectedIcon = $this->assets->iconFor($condition);
        $expectedLandscape = $this->assets->landscapeFor($season, $condition);

        $data = Validator::make($payload, [
            'weather_icon' => ['required', 'string', Rule::in([$expectedIcon])],
            'landscape' => ['required', 'string', Rule::in([$expectedLandscape])],
            'summary' => ['required', 'string', 'min:8', 'max:280'],
            'recommendation' => ['required', 'string', 'min:8', 'max:280'],
        ])->validate();

        return new WeatherPresentation($condition, $season, $data['weather_icon'], $data['landscape'], $data['summary'], $data['recommendation']);
    }

    private function seasonFor(WeatherSnapshot $snapshot): Season
    {
        return Season::fromMonth((int) $snapshot->timestamp->format('n'));
    }

    private function ttl(): int
    {
        return max(60, (int) config('ai.presentation_cache_ttl', 3600));
    }
}
