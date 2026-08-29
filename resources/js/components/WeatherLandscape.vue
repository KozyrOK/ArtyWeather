<script setup>
import { computed } from 'vue';
import { landscapeMap } from '../assets/weatherAssets';
const props = defineProps({ weather: Object, t: Object });
const season = computed(() => ['winter','winter','spring','spring','spring','summer','summer','summer','autumn','autumn','autumn','winter'][new Date(props.weather?.snapshot?.timestamp || Date.now()).getUTCMonth()]);
const key = computed(() => `${season.value}_${(props.weather?.weather_condition || 'clear').toLowerCase()}`);
const image = computed(() => landscapeMap[key.value]);
</script>
<template>
    <section class="card landscape" :aria-label="t.currentWeather">
        <img v-if="image" :src="image" width="200" height="150" alt="">
    </section>
</template>
