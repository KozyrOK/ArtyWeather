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
        .filter((snapshot) => Number.isFinite(Number(snapshot.precipitation)))
        .map((snapshot) => ({
            x: snapshot.timestamp,
            y: Number(snapshot.precipitation),
        }))
);
</script>

<template>
    <BaseLineChart
        :title="t.precipitation"
        unit="mm"
        :points="points"
        color="#2563eb"
    />
</template>