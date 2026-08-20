<script setup>
import { computed } from 'vue';
import { landscapeMap } from '../assets/weatherAssets';
const props = defineProps({ weather: Object });
const season = computed(() => ['winter','winter','spring','spring','spring','summer','summer','summer','autumn','autumn','autumn','winter'][new Date(props.weather?.snapshot?.timestamp || Date.now()).getUTCMonth()]);
const key = computed(() => `${season.value}_${(props.weather?.weather_condition || 'clear').toLowerCase()}`);
const image = computed(() => landscapeMap[key.value]);
</script>
<template><section class="card landscape"><img v-if="image" :src="image" :alt="key"><div><h2>Weather Landscape</h2><p>{{ key }}</p></div></section></template>
