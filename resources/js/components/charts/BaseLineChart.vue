<script setup>
import { Line } from 'vue-chartjs';
import 'chartjs-adapter-date-fns';
import { Chart as ChartJS, Filler, Legend, LineElement, LinearScale, PointElement, TimeScale, Title, Tooltip } from 'chart.js';
import { computed } from 'vue';

ChartJS.register(Filler, Legend, LineElement, LinearScale, PointElement, TimeScale, Title, Tooltip);

const props = defineProps({ title: String, unit: String, points: Array, color: { type: String, default: '#2563eb' } });

const data = computed(() => ({ datasets: [{ label: `${props.title}, ${props.unit}`, data: props.points, borderColor: props.color, backgroundColor: `${props.color}22`, tension: 0.35, fill: true, pointRadius: 3 }] }));
const options = computed(() => ({ responsive: true, maintainAspectRatio: false, parsing: false, interaction: { mode: 'nearest', intersect: false }, scales: { x: { type: 'time', time: { tooltipFormat: 'PPpp' } }, y: { ticks: { callback: (value) => `${value} ${props.unit}` } } }, plugins: { tooltip: { callbacks: { label: (ctx) => `${ctx.parsed.y} ${props.unit}` } } } }));
</script>
<template><div class="chart-card"><Line :data="data" :options="options" /></div></template>
