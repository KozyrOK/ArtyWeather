<script setup>
import { computed } from 'vue';
import BaseLineChart from './BaseLineChart.vue';
const props = defineProps({ snapshots: { type: Array, default: () => [] }, t: Object });
const points = computed(() => props.snapshots
    .filter((snapshot) => Number.isFinite(Number(snapshot.temperature)))
    .map((snapshot) => ({ label: new Date(snapshot.timestamp).toLocaleString(), y: Number(snapshot.temperature) })));
</script>
<template><BaseLineChart v-if="points.length" :title="t.temperature" unit="°C" :points="points" color="#f97316" /></template>
