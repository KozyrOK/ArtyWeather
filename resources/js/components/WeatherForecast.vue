<script setup>
defineProps({ snapshots: Array, enabled: Object });
</script>
<template><section class="card"><h2>Forecast</h2><p v-if="!snapshots.length">No forecast data available.</p><div class="forecast-grid"><article v-for="snapshot in snapshots" :key="snapshot.timestamp"><strong>{{ new Date(snapshot.timestamp).toLocaleString() }}</strong><p v-if="enabled.temperature">{{ snapshot.temperature }}°C</p><p v-if="enabled.precipitation">{{ snapshot.precipitation }} mm</p><p v-if="enabled.pressure">{{ snapshot.pressure }} hPa</p><p v-if="enabled.wind_speed">{{ snapshot.wind_speed }} km/h</p></article></div></section></template>
