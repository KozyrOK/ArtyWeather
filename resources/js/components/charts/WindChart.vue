<script setup>
import { computed } from 'vue';
import BaseLineChart from './BaseLineChart.vue';
const props = defineProps({ snapshots: { type: Array, default: () => [] }, t: Object });
const points = computed(() => props.snapshots
    .filter((snapshot) => Number.isFinite(Number(snapshot.wind_speed)))
    .map((snapshot) => ({ label: new Date(snapshot.timestamp).toLocaleString(), y: Number(snapshot.wind_speed) })));
</script>
<template><BaseLineChart v-if="points.length" :title="t.windSpeed" unit="km/h" :points="points" color="#0891b2" /></template>
