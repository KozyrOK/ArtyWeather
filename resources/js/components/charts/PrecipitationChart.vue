<script setup>
import { computed } from 'vue';
import BaseLineChart from './BaseLineChart.vue';
const props = defineProps({ snapshots: Array });
const points = computed(() => props.snapshots.map((s) => ({ x: s.timestamp, y: s.precipitation })).filter((p) => p.y !== null));
</script>
<template><BaseLineChart v-if="points.length" title="Precipitation" unit="mm" :points="points" color="#2563eb" /></template>
