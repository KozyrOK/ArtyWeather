<script setup>
import { computed } from 'vue';
import BaseLineChart from './BaseLineChart.vue';
const props = defineProps({ snapshots: { type: Array, default: () => [] }, t: Object });
const points = computed(() => props.snapshots.map((s) => ({ x: s.timestamp, y: s.precipitation })).filter((p) => p.y !== null));
</script>
<template><BaseLineChart v-if="points.length" :title="t.precipitation" unit="mm" :points="points" color="#2563eb" /></template>
