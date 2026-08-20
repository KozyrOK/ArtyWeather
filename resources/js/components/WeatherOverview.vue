<script setup>
import CurrentWeather from './CurrentWeather.vue';
import WeatherLandscape from './WeatherLandscape.vue';
import WeatherForecast from './WeatherForecast.vue';
import TemperatureChart from './charts/TemperatureChart.vue';
import PressureChart from './charts/PressureChart.vue';
import WindChart from './charts/WindChart.vue';
import PrecipitationChart from './charts/PrecipitationChart.vue';
defineProps({ weather: Object, enabled: Object, forecast: Array, loading: Boolean, error: Object });
defineEmits(['refresh']);
</script>
<template><main class="overview"><div class="state" v-if="loading">Loading weather…</div><div class="state error" v-else-if="error">{{ error.message }}</div><template v-else-if="weather"><button class="refresh" @click="$emit('refresh')">Refresh weather</button><div class="overview-grid"><CurrentWeather :weather="weather" :enabled="enabled"/><WeatherLandscape :weather="weather"/></div><WeatherForecast :snapshots="forecast" :enabled="enabled"/><section class="charts"><TemperatureChart v-if="enabled.temperature" :snapshots="forecast"/><PressureChart v-if="enabled.pressure" :snapshots="forecast"/><WindChart v-if="enabled.wind_speed" :snapshots="forecast"/><PrecipitationChart v-if="enabled.precipitation" :snapshots="forecast"/></section></template><div v-else class="state">Empty weather data.</div></main></template>
