<script setup>
import CurrentWeather from './CurrentWeather.vue';
import WeatherLandscape from './WeatherLandscape.vue';
import WeatherForecast from './WeatherForecast.vue';
import AiWeatherPresentation from './AiWeatherPresentation.vue';

import TemperatureChart from './charts/TemperatureChart.vue';
import PressureChart from './charts/PressureChart.vue';
import WindChart from './charts/WindChart.vue';
import PrecipitationChart from './charts/PrecipitationChart.vue';

defineProps({
    weather: Object,
    presentation: Object,
    enabled: Object,
    forecast: Array,
    loading: Boolean,
    presentationLoading: Boolean,
    error: Object,
    t: Object,
});

defineEmits(['refresh']);
</script>

<template>
    <main class="overview">
        <div
            v-if="loading"
            class="state"
        >
            {{ t.loadingWeather }}
        </div>

        <div
            v-else-if="error"
            class="state error"
        >
            {{ error.message }}
        </div>

        <template v-else-if="weather">
            <div class="overview__toolbar">
                <div>
                    <span class="eyebrow">
                        {{ t.weatherOverview }}
                    </span>

                    <h2>{{ t.currentWeather }}</h2>
                </div>

                <button
                    class="secondary-button"
                    @click="$emit('refresh')"
                >
                    {{ t.refreshWeather }}
                </button>
            </div>

            <section class="overview-hero">
                <CurrentWeather
                    :weather="weather"
                    :enabled="enabled"
                    :t="t"
                />

                <WeatherLandscape :weather="weather" />
            </section>

            <AiWeatherPresentation
                :presentation="presentation"
                :loading="presentationLoading"
                :t="t"
            />

            <section class="card forecast-card">
                <div class="panel-heading">
                    <div>
                        <span class="eyebrow">
                            {{ t.forecast }}
                        </span>

                        <h2>{{ t.forecast }}</h2>
                    </div>
                </div>

                <WeatherForecast
                    :snapshots="forecast"
                    :enabled="enabled"
                />
            </section>

            <section class="charts-section">
                <div class="panel-heading">
                    <div>
                        <span class="eyebrow">
                            {{ t.charts }}
                        </span>

                        <h2>{{ t.charts }}</h2>
                    </div>
                </div>

                <div class="charts">
                    <TemperatureChart
                        v-if="enabled.temperature"
                        :snapshots="forecast"
                    />

                    <PressureChart
                        v-if="enabled.pressure"
                        :snapshots="forecast"
                    />

                    <WindChart
                        v-if="enabled.wind_speed"
                        :snapshots="forecast"
                    />

                    <PrecipitationChart
                        v-if="enabled.precipitation"
                        :snapshots="forecast"
                    />
                </div>
            </section>
        </template>

        <div
            v-else
            class="state"
        >
            {{ t.emptyWeather }}
        </div>
    </main>
</template>