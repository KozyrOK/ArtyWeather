<script setup>
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import 'chartjs-adapter-date-fns';

import {
    Chart as ChartJS,
    Filler,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    TimeScale,
    Title,
    Tooltip,
} from 'chart.js';

ChartJS.register(
    Filler,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    TimeScale,
    Title,
    Tooltip,
);

const props = defineProps({
    title: {
        type: String,
        default: '',
    },

    unit: {
        type: String,
        default: '',
    },

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
    datasets: [
        {
            label: `${props.title}${props.unit ? `, ${props.unit}` : ''}`,
            data: props.points,
            borderColor: props.color,
            backgroundColor: `${props.color}22`,
            tension: 0.35,
            fill: true,
            pointRadius: 3,
            pointHoverRadius: 5,
        },
    ],
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
            type: 'time',

            time: {
                tooltipFormat: 'PPpp',
            },

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
        legend: {
            display: true,
        },

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
        <Line
            :data="data"
            :options="options"
        />
    </div>
</template>