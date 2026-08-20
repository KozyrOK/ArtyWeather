<script setup>
import { computed } from 'vue';
import { weatherIconMap } from '../assets/weatherAssets';
const props = defineProps({ weather: Object, enabled: Object });
const fields = { temperature:['Temperature','°C'], apparent_temperature:['Feels like','°C'], relative_humidity:['Humidity','%'], precipitation:['Precipitation','mm'], cloud_cover:['Cloud cover','%'], pressure:['Pressure','hPa'], wind_speed:['Wind speed','km/h'], wind_direction:['Wind direction','°'], wind_gusts:['Wind gusts','km/h'], weather_code:['Weather code',''] };
const icon = computed(() => weatherIconMap[(props.weather?.weather_condition || 'CLEAR').toLowerCase()]);
</script>
<template><section class="card hero"><div><p>{{ weather?.snapshot?.latitude }}, {{ weather?.snapshot?.longitude }}</p><h1 v-if="enabled.temperature">{{ weather.snapshot.temperature }}°C</h1><h2>{{ weather?.weather_condition }}</h2></div><img v-if="icon" :src="icon" alt="Weather icon" class="weather-icon"><div class="metrics"><div v-for="(meta,key) in fields" v-show="enabled[key] && weather?.snapshot?.[key] !== undefined" :key="key"><span>{{ meta[0] }}</span><strong>{{ weather.snapshot[key] }}{{ meta[1] }}</strong></div></div></section></template>