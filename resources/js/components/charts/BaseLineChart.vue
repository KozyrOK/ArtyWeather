<script setup>
import { Line } from 'vue-chartjs';
import 'chartjs-adapter-date-fns';
import {
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    Filler,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    Title,
    Tooltip,
);

const props = defineProps({
    title: String,
    unit: String,
    points: {
        type: Array,
        default: () => [],
    },
    color: {
        type: String,
        default: '#2563eb',
    },
});

const data = computed(() => ({
    labels: props.points.map((point) => point.label),
    datasets: [{
        label: `${props.title}, ${props.unit}`,
        data: props.points.map((point) => point.y),
        borderColor: props.color,
        backgroundColor: `${props.color}22`,
        tension: 0.35,
        fill: true,
        pointRadius: 3,
    }],
}));

const options = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        mode: 'nearest',
        intersect: false,
    },
    scales: {
        x: {
            ticks: {
                maxRotation: 0,
                autoSkip: true,
                maxTicksLimit: 8,
            },
        },
        y: {
            ticks: {
                callback: (value) => `${value} ${props.unit}`,
            },
        },
    },
    plugins: {
        tooltip: {
            callbacks: {
                label: (context) => `${context.parsed.y} ${props.unit}`,
            },
        },
    },
}));

</script>

<template>
    <div class="chart-card">
        <Line :data="data" :options="options" />
    </div>
</template>