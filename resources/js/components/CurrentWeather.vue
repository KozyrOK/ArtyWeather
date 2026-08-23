<script setup>
import { computed } from 'vue';
import { weatherIconMap } from '../assets/weatherAssets';

const props = defineProps({
    weather: {
        type: Object,
        default: null,
    },

    enabled: {
        type: Object,
        default: () => ({}),
    },

    t: {
        type: Object,
        required: true,
    },
});

const fields = {
    temperature: ['temperature', '°C'],
    apparent_temperature: ['apparentTemperature', '°C'],
    relative_humidity: ['relativeHumidity', '%'],
    precipitation: ['precipitation', ' mm'],
    cloud_cover: ['cloudCover', '%'],
    pressure: ['pressure', ' hPa'],
    wind_speed: ['windSpeed', ' km/h'],
    wind_direction: ['windDirection', '°'],
    wind_gusts: ['windGusts', ' km/h'],
    weather_code: ['weatherCode', ''],
};

const condition = computed(
    () => props.weather?.weather_condition || 'CLEAR'
);

const icon = computed(
    () => weatherIconMap[condition.value.toLowerCase()]
);
</script>

<template>
    <section class="card current-weather">
        <div class="current-weather__main">
            <div>
                <span class="eyebrow">
                    {{ t.currentWeather }}
                </span>

                <div class="current-weather__temperature">
                    <span v-if="enabled.temperature">
                        {{ weather?.snapshot?.temperature }}
                    </span>

                    <small v-if="enabled.temperature">
                        °C
                    </small>
                </div>

                <div class="current-weather__condition">
                    {{ t.weatherCondition }}:
                    {{ condition }}
                </div>

                <div class="current-weather__coordinates">
                    {{ t.coordinates }}:
                    {{ weather?.snapshot?.latitude }},
                    {{ weather?.snapshot?.longitude }}
                </div>
            </div>

            <div class="current-weather__icon">
                <img
                    v-if="icon"
                    :src="icon"
                    :alt="condition"
                    class="weather-icon"
                />
            </div>
        </div>

        <div class="metrics">
            <div
                v-for="(meta, key) in fields"
                v-show="
                    enabled[key] &&
                    weather?.snapshot?.[key] !== undefined
                "
                :key="key"
                class="metric"
            >
                <span>
                    {{ t[meta[0]] || meta[0] }}
                </span>

                <strong>
                    {{ weather.snapshot[key] }}{{ meta[1] }}
                </strong>
            </div>
        </div>
    </section>
</template>