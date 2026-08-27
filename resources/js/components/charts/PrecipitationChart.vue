<script setup>
import { computed } from 'vue';
import BaseLineChart from './BaseLineChart.vue';
const props = defineProps({ snapshots: { type: Array, default: () => [] }, t: Object });
const points = computed(() => props.snapshots
    .filter((snapshot) => Number.isFinite(Number(snapshot.precipitation)))
    .map((snapshot) => ({ label: new Date(snapshot.timestamp).toLocaleString(), y: Number(snapshot.precipitation) })));
</script>
<template><BaseLineChart v-if="points.length" :title="t.precipitation" unit="mm" :points="points" color="#2563eb" /></template>
