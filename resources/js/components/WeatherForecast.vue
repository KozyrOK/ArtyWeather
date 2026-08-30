<script setup>
import { computed, ref, watch } from 'vue';
import { weatherIconMap } from '../assets/weatherAssets';

const props = defineProps({
    snapshots: { type: Array, default: () => [] },
    enabled: { type: Object, default: () => ({}) },
    forecastPeriod: { type: Number, default: 0 },
    t: { type: Object, required: true },
    locale: { type: String, default: 'en' },
});

const fields = [
    ['apparent_temperature', 'apparentTemperature', '°C'],
    ['relative_humidity', 'relativeHumidity', '%'],
    ['precipitation', 'precipitation', ' mm'],
    ['weather_code', 'weatherCode', ''],
    ['cloud_cover', 'cloudCover', '%'],
    ['pressure', 'pressure', ' hPa'],
    ['wind_speed', 'windSpeed', ' km/h'],
    ['wind_direction', 'windDirection', '°'],
    ['wind_gusts', 'windGusts', ' km/h'],
];

const selectedDay = ref(null);
const dayKey = (timestamp) => new Date(timestamp).toISOString().slice(0, 10);
const formatDay = (timestamp) => new Intl.DateTimeFormat(props.locale, { weekday: 'short', day: 'numeric', month: 'short', timeZone: 'UTC' }).format(new Date(timestamp));
const formatTime = (timestamp) => new Intl.DateTimeFormat(props.locale, { hour: '2-digit', minute: '2-digit', timeZone: 'UTC' }).format(new Date(timestamp));
const formatTemperature = (value) => value === null || value === undefined ? '—' : `${Math.round(value)}°`;
const conditionIcon = (snapshot) => weatherIconMap[(snapshot.weather_condition || 'CLEAR').toLowerCase()];

const days = computed(() => {
    const grouped = new Map();

    props.snapshots.forEach((snapshot) => {
        const key = dayKey(snapshot.timestamp);
        grouped.set(key, [...(grouped.get(key) || []), snapshot]);
    });

    const period = Number.isFinite(props.forecastPeriod) && props.forecastPeriod > 0
        ? props.forecastPeriod
        : grouped.size;

    return [...grouped.entries()].slice(0, period).map(([key, hours]) => ({
        key,
        hours,
        timestamp: hours[0].timestamp,
        high: Math.max(...hours.map(({ temperature }) => temperature).filter(Number.isFinite)),
        low: Math.min(...hours.map(({ temperature }) => temperature).filter(Number.isFinite)),
        iconSnapshot: hours.find(({ weather_condition }) => weather_condition) || hours[0],
    }));
});

const selected = computed(() => days.value.find(({ key }) => key === selectedDay.value) || days.value[0] || null);
const selectedFields = computed(() => fields.filter(([key]) => props.enabled[key]));

watch(days, (nextDays) => {
    if (!nextDays.some(({ key }) => key === selectedDay.value)) {
        selectedDay.value = nextDays[0]?.key || null;
    }
}, { immediate: true });

</script>

<template>
    <div v-if="days.length" class="forecast">
        <div class="forecast__strip" aria-label="Daily forecast">
            <button
                v-for="day in days"
                :key="day.key"
                class="forecast-day"
                :class="{ 'forecast-day--selected': selectedDay === day.key }"
                type="button"
                :aria-pressed="selectedDay === day.key"
                @click="selectedDay = day.key"
            >
                <span class="forecast-day__date">{{ formatDay(day.timestamp) }}</span>
                <img v-if="conditionIcon(day.iconSnapshot)" :src="conditionIcon(day.iconSnapshot)" :alt="day.iconSnapshot.weather_condition" class="forecast-day__icon">
                <span class="forecast-day__temperatures"><strong>{{ formatTemperature(day.high) }}</strong><span>{{ formatTemperature(day.low) }}</span></span>
            </button>
        </div>

        <section v-if="selected" class="forecast-details" :aria-label="formatDay(selected.timestamp)">
            <div class="forecast-details__heading">
                <span class="eyebrow">{{ t.forecast }}</span>
                <h2>{{ formatDay(selected.timestamp) }}</h2>
            </div>
            <div class="forecast-hours">
                <article v-for="snapshot in selected.hours" :key="snapshot.timestamp" class="forecast-hour">
                    <time :datetime="snapshot.timestamp" class="forecast-hour__time">{{ formatTime(snapshot.timestamp) }}</time>
                    <img v-if="conditionIcon(snapshot)" :src="conditionIcon(snapshot)" :alt="snapshot.weather_condition" class="forecast-hour__icon">
                    <strong class="forecast-hour__temperature">{{ formatTemperature(snapshot.temperature) }}</strong>
                    <dl v-if="selectedFields.some(([key]) => snapshot[key] !== null && snapshot[key] !== undefined)" class="forecast-hour__metrics">
                        <template v-for="[key, label, unit] in selectedFields" :key="key">
                            <dt v-if="snapshot[key] !== null && snapshot[key] !== undefined">{{ t[label] }}</dt>
                            <dd v-if="snapshot[key] !== null && snapshot[key] !== undefined">{{ snapshot[key] }}{{ unit }}</dd>
                        </template>
                    </dl>
                </article>
            </div>
        </section>
    </div>
    <p v-else class="empty-table">{{ t.noForecast }}</p>    
</template>