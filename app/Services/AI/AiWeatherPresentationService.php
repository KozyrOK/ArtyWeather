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
        private WeatherRecommendationResolver $recommendations,
        private WeatherAssetRegistry $assets,
        private WeatherPresentationCacheKey $cacheKey,
        private CacheRepository $cache,
    ) {}

    public function cached(
        WeatherSnapshot $snapshot,
        WeatherCondition $condition,
        string $locale = 'ru',
    ): ?WeatherPresentation {
        $season = $this->seasonFor($snapshot);

        $presentation = $this->cache->get(
            $this->cacheKey->forSnapshot(
                $snapshot,
                $condition,
                $season,
                $locale
            )
        );

        return $presentation instanceof WeatherPresentation
            ? $presentation
            : null;
    }

    public function generate(
        WeatherSnapshot $snapshot,
        WeatherCondition $condition,
        string $locale = 'ru',
    ): WeatherPresentation {
        $season = $this->seasonFor($snapshot);

        $key = $this->cacheKey->forSnapshot(
            $snapshot,
            $condition,
            $season,
            $locale
        );

        $cached = $this->cache->get($key);

        if ($cached instanceof WeatherPresentation) {
            return $cached;
        }

        try {
            $prompt = $this->promptBuilder->build(
                $snapshot,
                $condition,
                $season,
                $locale
            );

            $payload = $this->client->generate(
                $prompt['system'],
                $prompt['user']
            );

            $presentation = $this->fromValidatedPayload(
                $payload,
                $condition,
                $season
            );
        } catch (Throwable) {
            $presentation = $this->fallback(
                $snapshot,
                $condition,
                $season,
                $locale
            );
        }

        $this->cache->put(
            $key,
            $presentation,
            $this->ttl()
        );

        return $presentation;
    }

    public function fallback(
        WeatherSnapshot $snapshot,
        WeatherCondition $condition,
        Season $season,
        string $locale = 'en',
    ): WeatherPresentation {
        $recommendationContext = $this->recommendations->resolve(
            $snapshot,
            $condition
        );

        $summary = $this->fallbackSummary(
            $condition,
            $locale
        );

        $recommendation = $this->fallbackRecommendation(
            $recommendationContext->priority,
            $recommendationContext->factors,
            $locale
        );

        return new WeatherPresentation(
            $condition,
            $season,
            $this->assets->iconFor($condition),
            $this->assets->landscapeFor($season, $condition),
            $summary,
            $recommendation,
        );
    }

    private function fromValidatedPayload(
        array $payload,
        WeatherCondition $condition,
        Season $season,
    ): WeatherPresentation {
        $expectedIcon = $this->assets->iconFor($condition);
        $expectedLandscape = $this->assets->landscapeFor(
            $season,
            $condition
        );

        $data = Validator::make($payload, [
            'weather_icon' => [
                'required',
                'string',
                Rule::in([$expectedIcon]),
            ],

            'landscape' => [
                'required',
                'string',
                Rule::in([$expectedLandscape]),
            ],

            'summary' => [
                'required',
                'string',
                'min:8',
                'max:280',
            ],

            'recommendation' => [
                'required',
                'string',
                'min:8',
                'max:280',
            ],
        ])->validate();

        return new WeatherPresentation(
            $condition,
            $season,
            $data['weather_icon'],
            $data['landscape'],
            $data['summary'],
            $data['recommendation'],
        );
    }

    private function seasonFor(
        WeatherSnapshot $snapshot
    ): Season {
        return Season::fromMonth(
            (int) $snapshot->timestamp->format('n')
        );
    }

    private function fallbackSummary(
        WeatherCondition $condition,
        string $locale,
    ): string {
        return match ($locale) {
            'ru' => match ($condition) {
                WeatherCondition::CLEAR =>
                    'Сейчас ясно, без существенных осадков.',

                WeatherCondition::PARTLY_CLOUDY =>
                    'Сейчас переменная облачность с периодами прояснения.',

                WeatherCondition::CLOUDY =>
                    'Сейчас преимущественно облачная погода.',

                WeatherCondition::RAIN =>
                    'Сейчас дождливая погода с осадками.',

                WeatherCondition::HEAVY_RAIN =>
                    'Сейчас наблюдается сильный дождь.',

                WeatherCondition::SNOW =>
                    'Сейчас снежная погода с осадками в виде снега.',

                WeatherCondition::FOG =>
                    'Сейчас туманная погода с пониженной видимостью.',

                WeatherCondition::STORM =>
                    'Сейчас наблюдаются условия, соответствующие штормовой погоде.',
            },

            'uk' => match ($condition) {
                WeatherCondition::CLEAR =>
                    'Зараз ясно, без істотних опадів.',

                WeatherCondition::PARTLY_CLOUDY =>
                    'Зараз мінлива хмарність із періодами прояснення.',

                WeatherCondition::CLOUDY =>
                    'Зараз переважно хмарна погода.',

                WeatherCondition::RAIN =>
                    'Зараз дощова погода з опадами.',

                WeatherCondition::HEAVY_RAIN =>
                    'Зараз спостерігається сильний дощ.',

                WeatherCondition::SNOW =>
                    'Зараз сніжна погода з опадами у вигляді снігу.',

                WeatherCondition::FOG =>
                    'Зараз туманна погода зі зниженою видимістю.',

                WeatherCondition::STORM =>
                    'Зараз спостерігаються умови, характерні для штормової погоди.',
            },

            default => match ($condition) {
                WeatherCondition::CLEAR =>
                    'Current conditions are clear with no significant precipitation.',

                WeatherCondition::PARTLY_CLOUDY =>
                    'Current conditions are partly cloudy with periods of clearing.',

                WeatherCondition::CLOUDY =>
                    'Current conditions are predominantly cloudy.',

                WeatherCondition::RAIN =>
                    'Current conditions include rain and precipitation.',

                WeatherCondition::HEAVY_RAIN =>
                    'Current conditions include heavy rain.',

                WeatherCondition::SNOW =>
                    'Current conditions include snow and wintry precipitation.',

                WeatherCondition::FOG =>
                    'Current conditions include fog and reduced visibility.',

                WeatherCondition::STORM =>
                    'Current conditions are consistent with stormy weather.',
            },
        };
    }

    /**
     * @param list<string> $factors
     */
    private function fallbackRecommendation(
        ?string $priority,
        array $factors,
        string $locale,
    ): string {
        $secondary = array_values(
            array_filter(
                $factors,
                static fn (string $factor): bool =>
                    $factor !== $priority
            )
        );

        return match ($locale) {
            'ru' => $this->fallbackRecommendationRu(
                $priority,
                $secondary
            ),

            'uk' => $this->fallbackRecommendationUk(
                $priority,
                $secondary
            ),

            default => $this->fallbackRecommendationEn(
                $priority,
                $secondary
            ),
        };
    }

    /**
     * @param list<string> $secondary
     */
    private function fallbackRecommendationRu(
        ?string $priority,
        array $secondary,
    ): string {
        return match ($priority) {
            'storm_risk' =>
                'По возможности избегайте ненужного пребывания на улице и следите за локальными погодными предупреждениями.',

            'dangerous_gusts' =>
                'Будьте осторожны на открытых участках и закрепите лёгкие предметы; сильные порывы ветра могут быть опасны.',

            'low_visibility' =>
                'Соблюдайте особую осторожность при вождении или езде на велосипеде из-за сниженной видимости.',

            'heavy_rain' =>
                'Возьмите зонт или непромокаемую одежду и заложите дополнительное время на дорогу.',

            'snow' =>
                'Наденьте тёплую непромокаемую обувь и будьте осторожны на скользких участках.',

            'rain' =>
                'Возьмите зонт или непромокаемую куртку перед выходом.',

            'freezing_temperature' =>
                'Оденьтесь тепло, особенно защитите руки, голову и ноги от холода.',

            'high_temperature' =>
                'Выбирайте лёгкую одежду, пейте достаточно воды и по возможности избегайте долгого пребывания на солнце.',

            'strong_wind' =>
                'Выбирайте ветрозащитную верхнюю одежду и будьте осторожны на открытых участках.',

            'wind_chill' =>
                'Одевайтесь теплее, чем подсказывает измеренная температура: ветер делает погоду ощутимо холоднее.',

            'heat_discomfort' =>
                'Одежда из лёгких дышащих тканей будет комфортнее из-за более высокой ощущаемой температуры.',

            'cold_temperature' =>
                'Для выхода лучше выбрать тёплый внешний слой одежды.',

            'warm_temperature' =>
                'Лёгкая одежда будет наиболее комфортной в течение тёплой части дня.',

            'high_humidity' =>
                'Выбирайте дышащую одежду и пейте достаточно воды при длительном пребывании на улице.',

            'precipitation' =>
                'Перед выходом стоит взять защиту от осадков.',

            default => $this->fallbackSecondaryRu($secondary),
        };
    }

    /**
     * @param list<string> $secondary
     */
    private function fallbackSecondaryRu(array $secondary): string
    {
        if (in_array('high_humidity', $secondary, true)) {
            return 'Дышащая одежда поможет чувствовать себя комфортнее при высокой влажности.';
        }

        if (in_array('strong_wind', $secondary, true)) {
            return 'Ветрозащитный внешний слой будет полезен на открытом воздухе.';
        }

        if (in_array('precipitation', $secondary, true)) {
            return 'Перед выходом стоит проверить осадки и взять защиту от дождя.';
        }

        return 'Подберите одежду с учётом текущей температуры и ощущаемой температуры.';
    }

    /**
     * @param list<string> $secondary
     */
    private function fallbackRecommendationUk(
        ?string $priority,
        array $secondary,
    ): string {
        return match ($priority) {
            'storm_risk' =>
                'За можливості уникайте зайвого перебування надворі та стежте за місцевими погодними попередженнями.',

            'dangerous_gusts' =>
                'Будьте обережні на відкритих ділянках і закріпіть легкі предмети; сильні пориви вітру можуть бути небезпечними.',

            'low_visibility' =>
                'Будьте особливо уважні під час водіння або їзди на велосипеді через знижену видимість.',

            'heavy_rain' =>
                'Візьміть парасолю або водонепроникний одяг і закладіть додатковий час на дорогу.',

            'snow' =>
                'Вдягніть тепле водонепроникне взуття та будьте обережні на слизьких ділянках.',

            'rain' =>
                'Візьміть парасолю або водонепроникну куртку перед виходом.',

            'freezing_temperature' =>
                'Вдягніться тепло, особливо захистіть руки, голову та ноги від холоду.',

            'high_temperature' =>
                'Обирайте легкий одяг, пийте достатньо води та за можливості уникайте тривалого перебування на сонці.',

            'strong_wind' =>
                'Оберіть вітрозахисний верхній одяг і будьте обережні на відкритих ділянках.',

            'wind_chill' =>
                'Вдягайтеся тепліше, ніж підказує виміряна температура: вітер робить погоду відчутно холоднішою.',

            'heat_discomfort' =>
                'Одяг із легких дихаючих тканин буде комфортнішим через вищу відчутну температуру.',

            'cold_temperature' =>
                'Для виходу краще обрати теплий верхній шар одягу.',

            'warm_temperature' =>
                'Легкий одяг буде найкомфортнішим у теплу частину дня.',

            'high_humidity' =>
                'Обирайте дихаючий одяг і пийте достатньо води під час тривалого перебування надворі.',

            'precipitation' =>
                'Перед виходом варто взяти захист від опадів.',

            default => $this->fallbackSecondaryUk($secondary),
        };
    }

    /**
     * @param list<string> $secondary
     */
    private function fallbackSecondaryUk(array $secondary): string
    {
        if (in_array('high_humidity', $secondary, true)) {
            return 'Дихаючий одяг допоможе почуватися комфортніше за високої вологості.';
        }

        if (in_array('strong_wind', $secondary, true)) {
            return 'Вітрозахисний верхній шар буде корисним на відкритому повітрі.';
        }

        if (in_array('precipitation', $secondary, true)) {
            return 'Перед виходом варто перевірити опади та взяти захист від дощу.';
        }

        return 'Підберіть одяг з урахуванням поточної та відчутної температури.';
    }

    /**
     * @param list<string> $secondary
     */
    private function fallbackRecommendationEn(
        ?string $priority,
        array $secondary,
    ): string {
        return match ($priority) {
            'storm_risk' =>
                'Avoid unnecessary outdoor activity where possible and follow local weather warnings.',

            'dangerous_gusts' =>
                'Take extra care in exposed areas and secure loose items because strong gusts can be hazardous.',

            'low_visibility' =>
                'Take extra care when driving or cycling because visibility may be reduced.',

            'heavy_rain' =>
                'Take an umbrella or waterproof outer layer and allow extra time for travel.',

            'snow' =>
                'Wear warm, water-resistant footwear and take extra care on slippery surfaces.',

            'rain' =>
                'Take an umbrella or waterproof jacket before heading outside.',

            'freezing_temperature' =>
                'Wear warm layers and protect your hands, head, and feet from the cold.',

            'high_temperature' =>
                'Choose light clothing, stay hydrated, and limit prolonged exposure to direct sun where possible.',

            'strong_wind' =>
                'Choose a wind-resistant outer layer and take extra care in exposed areas.',

            'wind_chill' =>
                'Dress warmer than the measured temperature suggests because the wind will make it feel colder.',

            'heat_discomfort' =>
                'Light, breathable clothing will feel more comfortable because the apparent temperature is higher.',

            'cold_temperature' =>
                'A warm outer layer will be useful when heading outside.',

            'warm_temperature' =>
                'Light clothing should be comfortable during the warmer part of the day.',

            'high_humidity' =>
                'Choose breathable clothing and stay hydrated during longer periods outside.',

            'precipitation' =>
                'Take some protection from the precipitation before heading outside.',

            default => $this->fallbackSecondaryEn($secondary),
        };
    }

    /**
     * @param list<string> $secondary
     */
    private function fallbackSecondaryEn(array $secondary): string
    {
        if (in_array('high_humidity', $secondary, true)) {
            return 'Breathable clothing may feel more comfortable in the high humidity.';
        }

        if (in_array('strong_wind', $secondary, true)) {
            return 'A wind-resistant outer layer will be useful in exposed areas.';
        }

        if (in_array('precipitation', $secondary, true)) {
            return 'Check the precipitation before going out and take suitable protection.';
        }

        return 'Choose clothing based on the current and apparent temperatures.';
    }

    private function ttl(): int
    {
        return max(
            60,
            (int) config('ai.presentation_cache_ttl', 3600)
        );
    }
}