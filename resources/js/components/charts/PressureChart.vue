<script setup>
import { computed } from 'vue';
import BaseLineChart from './BaseLineChart.vue';
const props = defineProps({ snapshots: Array });
const points = computed(() => props.snapshots.map((s) => ({ x: s.timestamp, y: s.pressure })).filter((p) => p.y !== null));
</script>
<template><BaseLineChart v-if="points.length" title="Pressure" unit="hPa" :points="points" color="#7c3aed" /></template>
