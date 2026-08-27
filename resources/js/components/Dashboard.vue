<script setup>
import { reactive, watchEffect } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        default: null,
    },

    saving: {
        type: Boolean,
        default: false,
    },

    t: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['navigate', 'save']);

const form = reactive({});

const parameters = [
    'temperature',
    'apparent_temperature',
    'relative_humidity',
    'precipitation',
    'weather_code',
    'cloud_cover',
    'pressure',
    'wind_speed',
    'wind_direction',
    'wind_gusts',
];

const labels = {
    temperature: 'temperature',
    apparent_temperature: 'apparentTemperature',
    relative_humidity: 'relativeHumidity',
    precipitation: 'precipitation',
    weather_code: 'weatherCode',
    cloud_cover: 'cloudCover',
    pressure: 'pressure',
    wind_speed: 'windSpeed',
    wind_direction: 'windDirection',
    wind_gusts: 'windGusts',
};

watchEffect(() => {
    if (!props.settings) {
        return;
    }

    Object.assign(form, {
        latitude: props.settings.latitude,
        longitude: props.settings.longitude,
        forecast_period: props.settings.forecast_period,
        locale: props.settings.user?.locale || 'en',
        theme: props.settings.user?.theme || 'light',
        ...props.settings.display,
    });
});
</script>

<template>
    <main class="card dashboard">
        <div class="dashboard__heading">
            <button
                class="secondary-button dashboard__back-button"
                type="button"
                @click="emit('navigate', 'weather')"
            >
                {{ t.backToWeather }}
            </button>

            <h1>{{ t.dashboard }}</h1>
        </div>
        <form
            v-if="settings"
            class="settings-form"
            @submit.prevent="emit('save', { ...form })"
        >
            <div class="settings-grid">
                <label>
                    <span>{{ t.latitude }}</span>
                    <input
                        v-model.number="form.latitude"
                        type="number"
                        step="0.00001"
                    />
                </label>

                <label>
                    <span>{{ t.longitude }}</span>
                    <input
                        v-model.number="form.longitude"
                        type="number"
                        step="0.00001"
                    />
                </label>
            </div>

            <label>
                <span>{{ t.forecastDays }}</span>

                <input
                    v-model.number="form.forecast_period"
                    type="number"
                    min="1"
                    max="16"
                />
            </label>

            <div class="settings-grid">
                <label>
                    <span>{{ t.locale }}</span>

                    <select v-model="form.locale">
                        <option value="en">English</option>
                        <option value="ru">Русский</option>
                        <option value="uk">Українська</option>
                    </select>
                </label>

                <label>
                    <span>{{ t.theme }}</span>

                    <select v-model="form.theme">
                        <option value="light">
                            {{ t.light }}
                        </option>

                        <option value="dark">
                            {{ t.dark }}
                        </option>
                    </select>
                </label>
            </div>

            <fieldset>
                <legend>{{ t.weatherParameters }}</legend>

                <label
                    v-for="parameter in parameters"
                    :key="parameter"
                    class="check"
                >
                    <input
                        v-model="form[parameter]"
                        type="checkbox"
                    />

                    <span>
                        {{ t[labels[parameter]] || parameter }}
                    </span>
                </label>
            </fieldset>

            <button
                class="primary-button"
                type="submit"
                :disabled="saving"
            >
                {{ saving ? t.saving : t.saveSettings }}
            </button>
        </form>

        <p
            v-else
            class="state state--compact"
        >
            {{ t.settingsLoading }}
        </p>
    </main>
</template>