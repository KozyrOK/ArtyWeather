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
        .filter((snapshot) => Number.isFinite(Number(snapshot.wind_speed)))
        .map((snapshot) => ({
            x: snapshot.timestamp,
            y: Number(snapshot.wind_speed),
        }))
);
</script>

<template>
    <BaseLineChart
        :title="t.windSpeed"
        unit="km/h"
        :points="points"
        color="#0891b2"
    />
</template>
