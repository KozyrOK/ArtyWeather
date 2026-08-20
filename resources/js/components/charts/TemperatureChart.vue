<script setup>
import { computed } from 'vue';
import BaseLineChart from './BaseLineChart.vue';
const props = defineProps({ snapshots: Array });
const points = computed(() => props.snapshots.map((s) => ({ x: s.timestamp, y: s.temperature })).filter((p) => p.y !== null));
</script>
<template><BaseLineChart v-if="points.length" title="Temperature" unit="°C" :points="points" color="#f97316" /></template>
