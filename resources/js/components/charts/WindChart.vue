<script setup>
import { computed } from 'vue';
import BaseLineChart from './BaseLineChart.vue';
const props = defineProps({ snapshots: Array });
const points = computed(() => props.snapshots.map((s) => ({ x: s.timestamp, y: s.wind_speed })).filter((p) => p.y !== null));
</script>
<template><BaseLineChart v-if="points.length" title="Wind speed" unit="km/h" :points="points" color="#0891b2" /></template>
