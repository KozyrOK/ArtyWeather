<script setup>
import { computed } from 'vue';
import BaseLineChart from './BaseLineChart.vue';

const props = defineProps({
    snapshots: {
        type: Array,
        default: () => [],
    },

    t: {
        type: Object,
        default: () => ({}),
    },
});

const points = computed(() =>
    props.snapshots
        .filter((snapshot) => Number.isFinite(Number(snapshot.pressure)))
        .map((snapshot) => ({
            x: snapshot.timestamp,
            y: Number(snapshot.pressure),
        }))
);
</script>

<template>
    <BaseLineChart
        :title="t.pressure"
        unit="hPa"
        :points="points"
        color="#7c3aed"
    />
</template>