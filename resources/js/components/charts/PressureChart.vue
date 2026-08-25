<script setup>
import { computed } from 'vue';
import BaseLineChart from './BaseLineChart.vue';
const props = defineProps({ snapshots: { type: Array, default: () => [] }, t: Object });
const points = computed(() => props.snapshots.map((s) => ({ x: s.timestamp, y: s.pressure })).filter((p) => p.y !== null));
</script>
<template><BaseLineChart v-if="points.length" :title="t.pressure" unit="hPa" :points="points" color="#7c3aed" /></template>
