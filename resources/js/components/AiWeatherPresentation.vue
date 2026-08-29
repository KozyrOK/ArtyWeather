<script setup>
import { computed } from 'vue';
import WeatherLandscape from './WeatherLandscape.vue';

const props = defineProps({ weather: Object, presentation: Object, loading: Boolean, t: Object });
const data = computed(() => props.presentation?.presentation ?? props.presentation?.fallback ?? null);
const status = computed(() => props.presentation?.status ?? 'processing');

</script>

<template>
    <section class="card ai-presentation">
        <WeatherLandscape :weather="weather" :t="t" />
        <div class="ai-presentation__content">
            <h1>{{ t.aiPresentation }}</h1>
            <p v-if="loading || status === 'processing'">{{ t.loadingPresentation }}</p>
            <template v-else-if="data">
                <p><strong>{{ t.summary }}.</strong> {{ data.summary }}</p>
                <p><strong>{{ t.recommendation }}.</strong> {{ data.recommendation }}</p>
            </template>
            <p v-else>{{ t.presentationFallback }}</p>
        </div>
    </section>
</template>