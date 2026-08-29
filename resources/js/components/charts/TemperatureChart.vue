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
        .filter((snapshot) => Number.isFinite(Number(snapshot.temperature)))
        .map((snapshot) => ({
            x: snapshot.timestamp,
            y: Number(snapshot.temperature),
        }))
);
</script>

<template>
    <BaseLineChart
        :title="t.temperature"
        unit="°C"
        :points="points"
        color="#f97316"
    />
</template>