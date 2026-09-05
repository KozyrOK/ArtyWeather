<?php

namespace App\Services\AI;

use App\DTO\AI\WeatherRecommendationContext;
use App\DTO\Weather\Season;
use App\DTO\Weather\WeatherCondition;
use App\DTO\Weather\WeatherSnapshot;

final readonly class WeatherPresentationPromptBuilder
{
    public function __construct(
        private WeatherAssetRegistry $assets,
        private WeatherRecommendationResolver $recommendations,
    ) {}

    /**
     * @return array{system: string, user: string}
     */
    public function build(
        WeatherSnapshot $snapshot,
        WeatherCondition $condition,
        Season $season,
        string $locale = 'ru',
    ): array {
        $icon = $this->assets->iconFor($condition);
        $landscape = $this->assets->landscapeFor($season, $condition);

        $recommendationContext = $this->recommendations->resolve(
            $snapshot,
            $condition,
        );

        return [
            'system' => implode("\n", [
                'You are the AI Presentation Layer for ArtyWeather.',

                'Open-Meteo and the Laravel application are the only sources of factual weather data.',

                'Do not determine or change weather_condition, season, or factual weather values.',

                'Do not generate images, SVG, filenames, URLs, filesystem paths, or new asset identifiers.',

                'Return only valid JSON with exactly these keys:',
                'weather_icon, landscape, summary, recommendation.',

                'Copy the locked weather_icon and landscape values exactly.',

                'Use concise, practical text in the requested locale.',
                'Supported locales are: en, ru, uk.',

                /*
                 * Summary rules
                 */
                'The summary should briefly describe the overall weather situation.',
                'Do not invent weather conditions, hazards, or measurements.',

                /*
                 * Recommendation rules
                 */
                'Generate a practical and actionable recommendation for going outside.',
                'The recommendation must be based only on the supplied weather data and recommendation context.',
                'Focus primarily on the priority recommendation factor.',
                'Mention a secondary factor only when it materially changes what the user should do.',
                'Prefer one or two concrete actions over generic advice.',
                'Avoid repeating the summary verbatim.',
                'Avoid generic recommendations such as "prepare appropriately before going outside".',
                'Do not mention factors that are absent from the recommendation context.',
                'Do not invent safety risks unsupported by the supplied data.',
                'Keep the recommendation concise and natural.',
            ]),

            'user' => json_encode([
                'locale' => $locale,

                'locked_values' => [
                    'weather_condition' => $condition->value,
                    'season' => $season->value,
                    'weather_icon' => $icon,
                    'landscape' => $landscape,
                ],

                'allowed_assets' => [
                    'weather_icon' => [$icon],
                    'landscape' => [$landscape],
                ],

                'weather_snapshot' => $snapshot->toArray(),

                'recommendation_context' => $recommendationContext->toArray(),

                'task' => [
                    'summary' => 'Create a concise factual weather summary.',
                    'recommendation' => 'Create one concise, practical recommendation based on the recommendation context.',
                    'asset_identifiers' => 'Copy the locked asset identifiers exactly.',
                ],
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ];
    }
}
